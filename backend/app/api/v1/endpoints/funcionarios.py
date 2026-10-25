from typing import List, Any
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.core.database import get_db
from app.core.security import get_password_hash
from app.models import UsuarioAdmin
from app.schemas import (
    UsuarioAdminResponse,
    UsuarioAdminCreate,
    UsuarioAdminUpdate,
)
from app.api.deps import require_admin_role

router = APIRouter()


@router.get("/", response_model=List[UsuarioAdminResponse])
def listar_funcionarios(
    db: Session = Depends(get_db),
    admin: UsuarioAdmin = Depends(require_admin_role),
) -> Any:
    """RF016 / Painel Admin: Listar todos os colaboradores administrativos e operacionais"""
    funcionarios = db.query(UsuarioAdmin).order_by(UsuarioAdmin.id.asc()).all()
    return funcionarios


@router.get("/{id}", response_model=UsuarioAdminResponse)
def obter_funcionario(
    id: int,
    db: Session = Depends(get_db),
    admin: UsuarioAdmin = Depends(require_admin_role),
) -> Any:
    """Obter detalhes cadastrais de um colaborador por ID"""
    funcionario = db.query(UsuarioAdmin).filter(UsuarioAdmin.id == id).first()
    if not funcionario:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Colaborador não encontrado.")
    return funcionario


@router.post("/", response_model=UsuarioAdminResponse, status_code=status.HTTP_201_CREATED)
def criar_funcionario(
    dados: UsuarioAdminCreate,
    db: Session = Depends(get_db),
    admin: UsuarioAdmin = Depends(require_admin_role),
) -> Any:
    """Cadastro de novo colaborador administrativo ou operacional (ADMIN ou LOGISTICA)"""
    email_limpo = dados.email.strip().lower()
    if db.query(UsuarioAdmin).filter(UsuarioAdmin.email == email_limpo).first():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Já existe um colaborador cadastrado com este e-mail.",
        )

    novo_funcionario = UsuarioAdmin(
        nome=dados.nome.strip(),
        email=email_limpo,
        senha_hash=get_password_hash(dados.senha),
        cargo=dados.cargo,
    )
    db.add(novo_funcionario)
    db.commit()
    db.refresh(novo_funcionario)
    return novo_funcionario


@router.put("/{id}", response_model=UsuarioAdminResponse)
def atualizar_funcionario(
    id: int,
    dados: UsuarioAdminUpdate,
    db: Session = Depends(get_db),
    admin: UsuarioAdmin = Depends(require_admin_role),
) -> Any:
    """Atualização cadastral de um colaborador (nome, e-mail, cargo, senha opcional)"""
    funcionario = db.query(UsuarioAdmin).filter(UsuarioAdmin.id == id).first()
    if not funcionario:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Colaborador não encontrado.")

    if dados.email:
        email_limpo = dados.email.strip().lower()
        existente = db.query(UsuarioAdmin).filter(UsuarioAdmin.email == email_limpo, UsuarioAdmin.id != id).first()
        if existente:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Outro colaborador já utiliza este e-mail.",
            )
        funcionario.email = email_limpo

    if dados.nome is not None:
        funcionario.nome = dados.nome.strip()

    if dados.cargo is not None:
        funcionario.cargo = dados.cargo

    if dados.senha:
        funcionario.senha_hash = get_password_hash(dados.senha)

    db.commit()
    db.refresh(funcionario)
    return funcionario


@router.delete("/{id}", status_code=status.HTTP_200_OK)
def excluir_funcionario(
    id: int,
    db: Session = Depends(get_db),
    admin: UsuarioAdmin = Depends(require_admin_role),
) -> Any:
    """Exclusão de colaborador. Não permite excluir a si mesmo."""
    if admin.id == id:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Operação não permitida: você não pode excluir a sua própria conta ativa.",
        )

    funcionario = db.query(UsuarioAdmin).filter(UsuarioAdmin.id == id).first()
    if not funcionario:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Colaborador não encontrado.")

    db.delete(funcionario)
    db.commit()

    return {"mensagem": f"Colaborador '{funcionario.nome}' excluído com sucesso.", "id": id}
