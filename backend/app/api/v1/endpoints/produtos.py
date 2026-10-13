from typing import List, Optional, Any
from fastapi import APIRouter, Depends, HTTPException, Query, status
from pydantic import BaseModel
from sqlalchemy.orm import Session
from app.core.database import get_db
from app.models import Produto, Categoria
from app.schemas import ProdutoResponse, ProdutoCreate, ProdutoUpdate
from app.api.deps import get_current_admin

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
    skip: int = 0,
    limit: int = 50,
) -> Any:
    """RF002 / UC05: Catálogo de produtos com busca e filtragem por categoria"""
    query = db.query(Produto).filter(Produto.ativo == True)

    if busca:
        query = query.filter(Produto.nome.ilike(f"%{busca}%"))

    if categoria:
        query = query.join(Produto.categorias).filter(Categoria.slug == categoria)

    if apenas_disponiveis:
        query = query.filter(Produto.estoque > 0)

    produtos = query.offset(skip).limit(limit).all()
    return produtos


@router.get("/{id_ou_slug}", response_model=ProdutoResponse)
def obter_produto(id_ou_slug: str, db: Session = Depends(get_db)) -> Any:
    """Tela 3 / UC05: Obter detalhes do produto por ID ou Slug"""
    if id_ou_slug.isdigit():
        produto = db.query(Produto).filter(Produto.id == int(id_ou_slug)).first()
    else:
        produto = db.query(Produto).filter(Produto.slug == id_ou_slug).first()

    if not produto:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Produto não encontrado.")
    return produto


@router.post("/", response_model=ProdutoResponse, status_code=status.HTTP_201_CREATED)
def criar_produto(
    produto_in: ProdutoCreate,
    db: Session = Depends(get_db),
    admin: Any = Depends(get_current_admin),
) -> Any:
    """RF012 / Feature 6: Cadastro de produtos via Painel Administrativo"""
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


@router.patch("/{id}/estoque", response_model=ProdutoResponse)
def ajustar_estoque(
    id: int,
    dados: EstoqueUpdateRequest,
    db: Session = Depends(get_db),
    admin: Any = Depends(get_current_admin),
) -> Any:
    """UC04 / RF008: Ajuste manual de estoque pelo administrador (vendas de balcão na loja física)"""
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
