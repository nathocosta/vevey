from typing import Optional
from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from sqlalchemy.orm import Session
from app.core.database import get_db
from app.core.security import decode_access_token
from app.models import Cliente, UsuarioAdmin

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/api/v1/auth/login", auto_error=False)


def get_current_client(
    db: Session = Depends(get_db),
    token: Optional[str] = Depends(oauth2_scheme)
) -> Cliente:
    """Valida token JWT e retorna o cliente autenticado"""
    if not token:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Token de autenticação não fornecido.",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    payload = decode_access_token(token)
    if not payload or payload.get("role") != "cliente":
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciais inválidas ou token expirado.",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    cliente_id = payload.get("sub")
    cliente = db.query(Cliente).filter(Cliente.id == int(cliente_id), Cliente.ativo == True).first()
    if not cliente:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Cliente não encontrado.")
    
    return cliente


def get_current_admin(
    db: Session = Depends(get_db),
    token: Optional[str] = Depends(oauth2_scheme)
) -> UsuarioAdmin:
    """Valida token JWT e retorna o usuário administrativo (ADMIN, OPERADOR ou LOGISTICA)"""
    if not token:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Acesso restrito ao painel administrativo. Token ausente.",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    payload = decode_access_token(token)
    if not payload or payload.get("role") not in ["ADMIN", "OPERADOR", "LOGISTICA"]:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Acesso não autorizado para este perfil.",
        )
    
    admin_id = payload.get("sub")
    admin = db.query(UsuarioAdmin).filter(UsuarioAdmin.id == int(admin_id)).first()
    if not admin:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Usuário administrativo não encontrado.")
    
    return admin


def require_admin_role(
    current_admin: UsuarioAdmin = Depends(get_current_admin),
) -> UsuarioAdmin:
    """Valida se o usuário autenticado possui cargo de ADMIN exclusivamente"""
    role_str = current_admin.cargo.value if hasattr(current_admin.cargo, "value") else str(current_admin.cargo)
    if role_str.upper() != "ADMIN":
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Acesso negado: operação exclusiva para administradores gerais (ADMIN).",
        )
    return current_admin

