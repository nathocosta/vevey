from datetime import timedelta
from typing import Any
from fastapi import APIRouter, Depends, HTTPException, Request, status
from pydantic import BaseModel, EmailStr
from sqlalchemy.orm import Session
from app.core.database import get_db
from app.core.security import verify_password, get_password_hash, create_access_token
from app.core.limiter import limiter
from app.models import Cliente, UsuarioAdmin
from app.schemas import ClienteCreate, ClienteResponse
from app.api.deps import get_current_client, get_current_admin


router = APIRouter()


def validar_cpf(cpf: str) -> bool:
    """Valida cálculo formal dos dígitos verificadores do CPF (módulo 11)"""
    cpf_limpo = "".join(filter(str.isdigit, cpf))
    if len(cpf_limpo) != 11:
        return False
    if cpf_limpo == cpf_limpo[0] * 11:
        return False
    soma = sum(int(cpf_limpo[i]) * (10 - i) for i in range(9))
    d1 = (soma * 10 % 11) % 10
    if d1 != int(cpf_limpo[9]):
        return False
    soma = sum(int(cpf_limpo[i]) * (11 - i) for i in range(10))
    d2 = (soma * 10 % 11) % 10
    return d2 == int(cpf_limpo[10])


class LoginRequest(BaseModel):
    email: EmailStr
    senha: str


class TokenResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"
    role: str
    usuario_nome: str


@router.post("/register", response_model=ClienteResponse, status_code=status.HTTP_201_CREATED)
@limiter.limit("10/minute")
def register_client(request: Request, cliente_in: ClienteCreate, db: Session = Depends(get_db)) -> Any:
    """RF001 / UC01: Cadastro de novos clientes com validação estrita de CPF e hash de senha"""
    cpf_limpo = "".join(filter(str.isdigit, cliente_in.cpf))
    if not validar_cpf(cpf_limpo):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="CPF inválido. Verifique os dígitos informados.",
        )

    # Previne enumeração de usuários agregando verificação
    if db.query(Cliente).filter((Cliente.email == cliente_in.email) | (Cliente.cpf == cpf_limpo)).first():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Já existe uma conta cadastrada com este e-mail ou CPF.",
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
@limiter.limit("5/minute")
def login(request: Request, login_data: LoginRequest, db: Session = Depends(get_db)) -> Any:
    """UC01: Autentica cliente ou funcionário com proteção contra força bruta (Rate Limit)"""

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


@router.get("/admin/me")
def get_admin_me(current_admin: UsuarioAdmin = Depends(get_current_admin)) -> Any:
    """Valida no servidor se o token JWT pertence a um ADMIN ou OPERADOR legítimo"""
    role_str = current_admin.cargo.value if hasattr(current_admin.cargo, "value") else str(current_admin.cargo)
    return {
        "id": current_admin.id,
        "nome": current_admin.nome,
        "email": current_admin.email,
        "role": role_str,
        "autenticado": True,
    }

