from fastapi import APIRouter
from app.api.v1.endpoints import auth, produtos, pedidos, frete

api_router = APIRouter()

api_router.include_router(auth.router, prefix="/auth", tags=["Autenticação & Clientes"])
api_router.include_router(produtos.router, prefix="/produtos", tags=["Catálogo & Produtos"])
api_router.include_router(pedidos.router, prefix="/pedidos", tags=["Vendas, Pedidos & WhatsApp"])
api_router.include_router(frete.router, prefix="/frete", tags=["Logística & Frete"])
