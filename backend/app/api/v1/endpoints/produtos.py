from typing import List, Optional, Any
import os
import uuid
import io
from PIL import Image
from fastapi import APIRouter, Depends, HTTPException, Query, status, UploadFile, File
from pydantic import BaseModel
from sqlalchemy.orm import Session, selectinload
from app.core.database import get_db
from app.models import Produto, Categoria, ItemPedido
from app.schemas import ProdutoResponse, ProdutoCreate, ProdutoUpdate
from app.api.deps import get_current_admin, require_admin_role

router = APIRouter()


class EstoqueUpdateRequest(BaseModel):
    novo_estoque: int


class StatusUpdateRequest(BaseModel):
    ativo: bool


@router.get("/", response_model=List[ProdutoResponse])
def listar_produtos(
    db: Session = Depends(get_db),
    busca: Optional[str] = Query(None, description="Busca por nome do produto (RF002 / UC05)"),
    categoria: Optional[str] = Query(None, description="Slug da categoria"),
    apenas_disponiveis: bool = Query(False, description="Filtrar apenas itens com estoque > 0"),
    incluir_inativos: bool = Query(False, description="Incluir itens inativos (para uso no admin)"),
    skip: int = Query(0, ge=0, description="Offset de paginação"),
    limit: int = Query(100, ge=1, le=200, description="Limite máximo por página"),
) -> Any:
    """RF002 / UC05: Catálogo de produtos com busca e filtragem por categoria"""
    query = db.query(Produto).options(selectinload(Produto.categorias))

    if not incluir_inativos:
        query = query.filter(Produto.ativo == True)

    if busca:
        query = query.filter(Produto.nome.ilike(f"%{busca}%"))

    if categoria:
        query = query.join(Produto.categorias).filter(Categoria.slug == categoria)

    if apenas_disponiveis:
        query = query.filter(Produto.estoque > 0)

    produtos = query.order_by(Produto.id.asc()).offset(skip).limit(limit).all()
    return produtos


@router.get("/{id_ou_slug}", response_model=ProdutoResponse)
def obter_produto(id_ou_slug: str, db: Session = Depends(get_db)) -> Any:
    """Tela 3 / UC05: Obter detalhes do produto por ID ou Slug"""
    base_query = db.query(Produto).options(selectinload(Produto.categorias))
    if id_ou_slug.isdigit():
        produto = base_query.filter(Produto.id == int(id_ou_slug)).first()
    else:
        produto = base_query.filter(Produto.slug == id_ou_slug).first()

    if not produto:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Produto não encontrado.")
    return produto


@router.post("/", response_model=ProdutoResponse, status_code=status.HTTP_201_CREATED)
def criar_produto(
    produto_in: ProdutoCreate,
    db: Session = Depends(get_db),
    admin: Any = Depends(require_admin_role),
) -> Any:
    """RF012 / Feature 6: Cadastro de produtos via Painel Administrativo (Exclusivo ADMIN)"""
    if db.query(Produto).filter(Produto.slug == produto_in.slug).first():
        raise HTTPException(status_code=400, detail="Slug já existente no catálogo.")

    db_produto = Produto(
        nome=produto_in.nome,
        slug=produto_in.slug,
        descricao=produto_in.descricao,
        preco=produto_in.preco,
        preco_promocional=produto_in.preco_promocional,
        imagem_url=produto_in.imagem_url,
        estoque=produto_in.estoque,
        ativo=produto_in.ativo,
    )
    if produto_in.categoria_ids:
        categorias = db.query(Categoria).filter(Categoria.id.in_(produto_in.categoria_ids)).all()
        db_produto.categorias = categorias

    db.add(db_produto)
    db.commit()
    db.refresh(db_produto)
    return db_produto


@router.put("/{id}", response_model=ProdutoResponse)
def atualizar_produto(
    id: int,
    produto_in: ProdutoUpdate,
    db: Session = Depends(get_db),
    admin: Any = Depends(require_admin_role),
) -> Any:
    """Atualização completa de produto no catálogo (Exclusivo ADMIN)"""
    produto = db.query(Produto).filter(Produto.id == id).first()
    if not produto:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Produto não encontrado.")

    if produto_in.slug and produto_in.slug != produto.slug:
        if db.query(Produto).filter(Produto.slug == produto_in.slug, Produto.id != id).first():
            raise HTTPException(status_code=400, detail="Já existe outro produto cadastrado com este slug.")
        produto.slug = produto_in.slug

    if produto_in.nome is not None:
        produto.nome = produto_in.nome
    if produto_in.descricao is not None:
        produto.descricao = produto_in.descricao
    if produto_in.preco is not None:
        produto.preco = produto_in.preco
    if "preco_promocional" in produto_in.model_fields_set:
        produto.preco_promocional = produto_in.preco_promocional
    if produto_in.imagem_url is not None:
        produto.imagem_url = produto_in.imagem_url
    if produto_in.estoque is not None:
        produto.estoque = produto_in.estoque
    if produto_in.ativo is not None:
        produto.ativo = produto_in.ativo

    if produto_in.categoria_ids is not None:
        categorias = db.query(Categoria).filter(Categoria.id.in_(produto_in.categoria_ids)).all()
        produto.categorias = categorias

    db.commit()
    db.refresh(produto)
    return produto


@router.delete("/{id}", status_code=status.HTTP_200_OK)
def excluir_produto(
    id: int,
    db: Session = Depends(get_db),
    admin: Any = Depends(require_admin_role),
) -> Any:
    """Excluir produto do catálogo. Se tiver vendas associadas, realiza soft-delete (inativação)"""
    produto = db.query(Produto).filter(Produto.id == id).first()
    if not produto:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Produto não encontrado.")

    # Verifica integridade referencial: se o produto possui itens de pedido associados
    itens_vinculados = db.query(ItemPedido).filter(ItemPedido.produto_id == id).first()
    if itens_vinculados:
        produto.ativo = False
        db.commit()
        return {
            "mensagem": f"O produto '{produto.nome}' possui histórico de vendas e foi inativado do catálogo para preservar a integridade dos pedidos.",
            "soft_deleted": True,
            "id": id,
        }

    db.delete(produto)
    db.commit()
    return {
        "mensagem": f"Produto '{produto.nome}' excluído com sucesso do catálogo.",
        "soft_deleted": False,
        "id": id,
    }


@router.patch("/{id}/estoque", response_model=ProdutoResponse)
def ajustar_estoque(
    id: int,
    dados: EstoqueUpdateRequest,
    db: Session = Depends(get_db),
    admin: Any = Depends(get_current_admin),
) -> Any:
    """UC04 / RF008: Ajuste manual de estoque pelo operador/administrador"""
    if dados.novo_estoque < 0:
        raise HTTPException(status_code=400, detail="O estoque não pode ser negativo.")

    produto = db.query(Produto).filter(Produto.id == id).first()
    if not produto:
        raise HTTPException(status_code=404, detail="Produto não encontrado.")

    produto.estoque = dados.novo_estoque
    db.commit()
    db.refresh(produto)
    return produto


@router.patch("/{id}/status", response_model=ProdutoResponse)
def alternar_status_produto(
    id: int,
    dados: StatusUpdateRequest,
    db: Session = Depends(get_db),
    admin: Any = Depends(get_current_admin),
) -> Any:
    """RF012: Ativa ou desativa produto da vitrine online"""
    produto = db.query(Produto).filter(Produto.id == id).first()
    if not produto:
        raise HTTPException(status_code=404, detail="Produto não encontrado.")

    produto.ativo = dados.ativo
    db.commit()
    db.refresh(produto)
    return produto


@router.post("/upload-imagem")
async def upload_imagem_produto(
    file: UploadFile = File(...),
    admin: Any = Depends(get_current_admin),
) -> Any:
    """
    Recebe imagem (preferencialmente WebP convertida pelo frontend ou qualquer JPEG/PNG),
    garante formato WebP otimizado e salva na pasta de imagens estáticas.
    """
    if not file.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="O arquivo enviado deve ser uma imagem.")

    # Caminho de destino para produtos
    # Salva em frontend/assets/images/produtos/
    base_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", "..", "..", "..", "frontend", "assets", "images", "produtos"))
    os.makedirs(base_dir, exist_ok=True)

    # Gera nome único para o arquivo
    extensao = ".webp"
    nome_arquivo = f"prod_{uuid.uuid4().hex[:12]}{extensao}"
    caminho_final = os.path.join(base_dir, nome_arquivo)

    conteudo = await file.read()

    # Se já é WebP, grava diretamente para máxima eficiência
    if file.content_type == "image/webp" or file.filename.lower().endswith(".webp"):
        with open(caminho_final, "wb") as f:
            f.write(conteudo)
    else:
        # Fallback: se veio JPEG/PNG sem conversão no frontend, converte com Pillow
        try:
            img = Image.open(io.BytesIO(conteudo))
            if img.mode in ("RGBA", "P"):
                img = img.convert("RGBA")
            else:
                img = img.convert("RGB")
            img.thumbnail((1200, 1200), Image.Resampling.LANCZOS)
            img.save(caminho_final, format="WEBP", quality=82, method=6)
        except Exception as e:
            raise HTTPException(status_code=400, detail=f"Erro ao processar imagem: {str(e)}")

    tamanho_final = os.path.getsize(caminho_final)
    url_relativa = f"assets/images/produtos/{nome_arquivo}"

    return {
        "imagem_url": url_relativa,
        "nome_arquivo": nome_arquivo,
        "tamanho_bytes": tamanho_final,
        "mensagem": "Imagem enviada e salva como WebP otimizado com sucesso!"
    }
