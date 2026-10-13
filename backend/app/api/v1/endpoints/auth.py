from datetime import timedelta
from typing import Any
from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel, EmailStr
from sqlalchemy.orm import Session
from app.core.database import get_db
from app.core.security import verify_password, get_password_hash, create_access_token
from app.models import Cliente, UsuarioAdmin
from app.schemas import ClienteCreate, ClienteResponse
from app.api.deps import get_current_client

router = APIRouter()


class LoginRequest(BaseModel):
    email: EmailStr
    senha: str


class TokenResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"
    role: str
    usuario_nome: str


@router.post("/register", response_model=ClienteResponse, status_code=status.HTTP_201_CREATED)
def register_client(cliente_in: ClienteCreate, db: Session = Depends(get_db)) -> Any:
    """RF001 / UC01: Cadastro de novos clientes com hash de senha seguro"""
    # Valida se email já existe
    if db.query(Cliente).filter(Cliente.email == cliente_in.email).first():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Já existe um cadastro associado a este e-mail.",
        )
    
    # Valida se CPF já existe
    cpf_limpo = "".join(filter(str.isdigit, cliente_in.cpf))
    if db.query(Cliente).filter(Cliente.cpf == cpf_limpo).first():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Já existe um cadastro associado a este CPF.",
        )

    novo_cliente = Cliente(
        nome=cliente_in.nome,
        cpf=cpf_limpo,
        email=cliente_in.email,
        telefone=cliente_in.telefone,
        senha_hash=get_password_hash(cliente_in.senha),
        ativo=True,
    )
    db.add(novo_cliente)
    db.commit()
    db.refresh(novo_cliente)
    return novo_cliente


@router.post("/login", response_model=TokenResponse)
def login(login_data: LoginRequest, db: Session = Depends(get_db)) -> Any:
    """UC01: Autentica cliente ou funcionário do administrativo e emite token JWT"""
    # 1. Verifica se é cliente
    cliente = db.query(Cliente).filter(Cliente.email == login_data.email).first()
    if cliente and cliente.ativo:
        if verify_password(login_data.senha, cliente.senha_hash):
            token = create_access_token(subject=cliente.id, role="cliente")
            return {
                "access_token": token,
                "token_type": "bearer",
                "role": "cliente",
                "usuario_nome": cliente.nome,
            }

    # 2. Verifica se é usuário administrativo (Miriam / Juliana)
    admin = db.query(UsuarioAdmin).filter(UsuarioAdmin.email == login_data.email).first()
    if admin:
        if verify_password(login_data.senha, admin.senha_hash):
            role_str = admin.cargo.value if hasattr(admin.cargo, 'value') else str(admin.cargo)
            token = create_access_token(subject=admin.id, role=role_str)
            return {
                "access_token": token,
                "token_type": "bearer",
                "role": role_str,
                "usuario_nome": admin.nome,
            }

    raise HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="E-mail ou senha incorretos.",
    )


@router.get("/me", response_model=ClienteResponse)
def get_me(current_client: Cliente = Depends(get_current_client)) -> Any:
    """Retorna dados cadastrais do cliente autenticado"""
    return current_client
