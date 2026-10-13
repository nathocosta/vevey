import enum
from datetime import datetime
from decimal import Decimal
from pydantic import BaseModel, Field, field_validator, model_validator, ConfigDict
import re
from typing import List, Optional, Any, Dict, TYPE_CHECKING

if TYPE_CHECKING:
    from pydantic import EmailStr
else:
    try:
        import email_validator
        from pydantic import EmailStr
    except ImportError:
        from pydantic import AfterValidator
        from typing_extensions import Annotated
        
        EMAIL_REGEX = re.compile(r"^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$")
        
        def validate_email_format(v: str) -> str:
            if not EMAIL_REGEX.match(v):
                raise ValueError("Formato de e-mail inválido.")
            return v
            
        EmailStr = Annotated[str, AfterValidator(validate_email_format)]

# Importando Enums do models.py para manter a consistência de tipos
try:
    from app.models import CargoAdmin, StatusPedido, MetodoPagamento, StatusPagamento, TipoEntrega
except ImportError:
    from models import CargoAdmin, StatusPedido, MetodoPagamento, StatusPagamento, TipoEntrega




# ==========================================
# FUNÇÕES DE VALIDAÇÃO COMPLEMENTARES
# ==========================================

def valida_cpf(cpf: str) -> str:
    # Remove qualquer caractere que não seja dígito
    cpf_limpo = "".join(filter(str.isdigit, cpf))
    
    if len(cpf_limpo) != 11:
        raise ValueError("O CPF deve conter exatamente 11 dígitos numéricos.")
        
    # Impede CPFs conhecidos inválidos com todos os números iguais
    if len(set(cpf_limpo)) == 1:
        raise ValueError("CPF inválido.")
        
    # Validação do primeiro dígito verificador
    soma = sum(int(cpf_limpo[i]) * (10 - i) for i in range(9))
    resto = (soma * 10) % 11
    digito_1 = 0 if resto in (10, 11) else resto
    if int(cpf_limpo[9]) != digito_1:
        raise ValueError("CPF inválido (primeiro dígito verificador incorreto).")
        
    # Validação do segundo dígito verificador
    soma = sum(int(cpf_limpo[i]) * (11 - i) for i in range(10))
    resto = (soma * 10) % 11
    digito_2 = 0 if resto in (10, 11) else resto
    if int(cpf_limpo[10]) != digito_2:
        raise ValueError("CPF inválido (segundo dígito verificador incorreto).")
        
    return cpf_limpo


def valida_cep(cep: str) -> str:
    # Remove qualquer caractere não-numérico
    cep_limpo = "".join(filter(str.isdigit, cep))
    if len(cep_limpo) != 8:
        raise ValueError("O CEP deve conter exatamente 8 dígitos numéricos.")
    return cep_limpo


# ==========================================
# 1. CATEGORIA SCHEMAS
# ==========================================

class CategoriaBase(BaseModel):
    nome: str = Field(..., max_length=100, description="Nome da categoria")
    slug: str = Field(..., max_length=100, description="Slug único para URLs amigáveis")
    descricao: Optional[str] = Field(None, description="Descrição detalhada da categoria")


class CategoriaCreate(CategoriaBase):
    pass


class CategoriaUpdate(BaseModel):
    nome: Optional[str] = Field(None, max_length=100)
    slug: Optional[str] = Field(None, max_length=100)
    descricao: Optional[str] = None


class CategoriaResponse(CategoriaBase):
    id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 2. PRODUTO SCHEMAS
# ==========================================

class ProdutoBase(BaseModel):
    nome: str = Field(..., max_length=150)
    slug: str = Field(..., max_length=150)
    descricao: Optional[str] = None
    preco: Decimal = Field(..., ge=0, decimal_places=2, description="Preço do produto (deve ser maior ou igual a 0)")
    preco_promocional: Optional[Decimal] = Field(None, ge=0, decimal_places=2, description="Preço promocional opcional")
    imagem_url: Optional[str] = Field(None, max_length=500)
    estoque: int = Field(0, ge=0, description="Quantidade em estoque")
    ativo: bool = Field(True, description="Indica se o produto está ativo no catálogo")


class ProdutoCreate(ProdutoBase):
    @model_validator(mode='after')
    def validate_precos(self) -> 'ProdutoCreate':
        if self.preco_promocional is not None and self.preco_promocional >= self.preco:
            raise ValueError("O preço promocional deve ser menor que o preço original.")
        return self


class ProdutoUpdate(BaseModel):
    nome: Optional[str] = Field(None, max_length=150)
    slug: Optional[str] = Field(None, max_length=150)
    descricao: Optional[str] = None
    preco: Optional[Decimal] = Field(None, ge=0, decimal_places=2)
    preco_promocional: Optional[Decimal] = Field(None, ge=0, decimal_places=2)
    imagem_url: Optional[str] = Field(None, max_length=500)
    estoque: Optional[int] = Field(None, ge=0)
    ativo: Optional[bool] = None

    @model_validator(mode='after')
    def validate_precos(self) -> 'ProdutoUpdate':
        p = self.preco
        p_promo = self.preco_promocional
        if p is not None and p_promo is not None:
            if p_promo >= p:
                raise ValueError("O preço promocional deve ser menor que o preço original.")
        return self


class ProdutoResponse(ProdutoBase):
    id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 3. CLIENTE SCHEMAS
# ==========================================

class ClienteBase(BaseModel):
    nome: str = Field(..., max_length=150)
    cpf: str = Field(..., description="CPF válido de 11 dígitos")
    email: EmailStr = Field(..., max_length=150)
    telefone: Optional[str] = Field(None, max_length=20)
    ativo: bool = Field(True)

    @field_validator('cpf')
    @classmethod
    def validate_cpf_field(cls, v: str) -> str:
        return valida_cpf(v)


class ClienteCreate(ClienteBase):
    senha: str = Field(..., min_length=6, description="Senha do cliente (mínimo de 6 caracteres)")


class ClienteUpdate(BaseModel):
    nome: Optional[str] = Field(None, max_length=150)
    cpf: Optional[str] = None
    email: Optional[EmailStr] = Field(None, max_length=150)
    telefone: Optional[str] = Field(None, max_length=20)
    ativo: Optional[bool] = None
    senha: Optional[str] = Field(None, min_length=6)

    @field_validator('cpf')
    @classmethod
    def validate_cpf_field(cls, v: Optional[str]) -> Optional[str]:
        if v is not None:
            return valida_cpf(v)
        return v


class ClienteResponse(ClienteBase):
    id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 4. ENDERECO SCHEMAS
# ==========================================

class EnderecoBase(BaseModel):
    identificador: str = Field(..., max_length=50, description="Identificador do endereço (Ex: Casa, Trabalho)")
    cep: str = Field(..., description="CEP de 8 dígitos")
    logradouro: str = Field(..., max_length=150)
    numero: str = Field(..., max_length=20)
    complemento: Optional[str] = Field(None, max_length=100)
    bairro: str = Field(..., max_length=100)
    cidade: str = Field(..., max_length=100)
    estado: str = Field(..., min_length=2, max_length=2, description="Sigla do Estado (UF)")
    principal: bool = Field(False, description="Define se é o endereço padrão de entrega")

    @field_validator('cep')
    @classmethod
    def validate_cep_field(cls, v: str) -> str:
        return valida_cep(v)


class EnderecoCreate(EnderecoBase):
    cliente_id: int


class EnderecoUpdate(BaseModel):
    identificador: Optional[str] = Field(None, max_length=50)
    cep: Optional[str] = None
    logradouro: Optional[str] = Field(None, max_length=150)
    numero: Optional[str] = Field(None, max_length=20)
    complemento: Optional[str] = Field(None, max_length=100)
    bairro: Optional[str] = Field(None, max_length=100)
    cidade: Optional[str] = Field(None, max_length=100)
    estado: Optional[str] = Field(None, min_length=2, max_length=2)
    principal: Optional[bool] = None

    @field_validator('cep')
    @classmethod
    def validate_cep_field(cls, v: Optional[str]) -> Optional[str]:
        if v is not None:
            return valida_cep(v)
        return v


class EnderecoResponse(EnderecoBase):
    id: int
    cliente_id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 5. ITEM PEDIDO SCHEMAS
# ==========================================

class ItemPedidoBase(BaseModel):
    produto_id: int
    quantidade: int = Field(..., gt=0, description="Quantidade do produto no pedido (deve ser > 0)")
    preco_unitario: Decimal = Field(..., ge=0, decimal_places=2, description="Preço unitário cobrado pelo item")


class ItemPedidoCreate(ItemPedidoBase):
    pass


class ItemPedidoUpdate(BaseModel):
    quantidade: Optional[int] = Field(None, gt=0)
    preco_unitario: Optional[Decimal] = Field(None, ge=0, decimal_places=2)


class ItemPedidoResponse(ItemPedidoBase):
    id: int
    pedido_id: int

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 6. PEDIDO SCHEMAS
# ==========================================

class PedidoBase(BaseModel):
    cliente_id: int
    endereco_entrega_id: int
    valor_produtos: Decimal = Field(..., ge=0, decimal_places=2)
    valor_frete: Decimal = Field(Decimal('0.00'), ge=0, decimal_places=2)
    valor_total: Decimal = Field(..., ge=0, decimal_places=2)
    status: StatusPedido = Field(StatusPedido.PENDENTE)
    codigo_rastreio: Optional[str] = Field(None, max_length=100)
    tipo_entrega: TipoEntrega = Field(TipoEntrega.ENVIO_TRANSPORTADORA)


class PedidoCreate(BaseModel):
    cliente_id: int
    endereco_entrega_id: int
    valor_frete: Decimal = Field(Decimal('0.00'), ge=0, decimal_places=2)
    tipo_entrega: TipoEntrega = Field(TipoEntrega.ENVIO_TRANSPORTADORA)
    itens: List[ItemPedidoCreate] = Field(..., min_length=1, description="Lista de itens do pedido")


class PedidoUpdate(BaseModel):
    status: Optional[StatusPedido] = None
    codigo_rastreio: Optional[str] = Field(None, max_length=100)
    tipo_entrega: Optional[TipoEntrega] = None


class PedidoResponse(PedidoBase):
    id: int
    created_at: datetime
    updated_at: datetime
    itens: List[ItemPedidoResponse] = []

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 7. PAGAMENTO SCHEMAS
# ==========================================

class PagamentoBase(BaseModel):
    pedido_id: int
    transacao_gateway_id: Optional[str] = Field(None, max_length=100, description="ID de transação externa do Gateway de Pagamento")
    metodo: MetodoPagamento
    status: StatusPagamento = Field(StatusPagamento.PENDENTE)
    detalhes_gateway: Optional[Dict[str, Any]] = Field(None, description="Metadados retornados pelo Gateway em formato JSON")


class PagamentoCreate(PagamentoBase):
    pass


class PagamentoUpdate(BaseModel):
    transacao_gateway_id: Optional[str] = Field(None, max_length=100)
    metodo: Optional[MetodoPagamento] = None
    status: Optional[StatusPagamento] = None
    detalhes_gateway: Optional[Dict[str, Any]] = None


class PagamentoResponse(PagamentoBase):
    id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 8. HISTORICO STATUS PEDIDO SCHEMAS
# ==========================================

class HistoricoStatusPedidoBase(BaseModel):
    pedido_id: int
    status: str = Field(..., max_length=30, description="Status registrado no histórico")
    observacao: Optional[str] = Field(None, description="Observação ou justificativa adicional")


class HistoricoStatusPedidoCreate(HistoricoStatusPedidoBase):
    pass


class HistoricoStatusPedidoResponse(HistoricoStatusPedidoBase):
    id: int
    created_at: datetime

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 9. USUARIO ADMIN SCHEMAS (RBAC)
# ==========================================

class UsuarioAdminBase(BaseModel):
    nome: str = Field(..., max_length=150)
    email: EmailStr = Field(..., max_length=150)
    cargo: CargoAdmin = Field(..., description="Função administrativa do colaborador: ADMIN ou OPERADOR")


class UsuarioAdminCreate(UsuarioAdminBase):
    senha: str = Field(..., min_length=6, description="Senha do usuário (mínimo de 6 caracteres)")


class UsuarioAdminUpdate(BaseModel):
    nome: Optional[str] = Field(None, max_length=150)
    email: Optional[EmailStr] = Field(None, max_length=150)
    cargo: Optional[CargoAdmin] = None
    senha: Optional[str] = Field(None, min_length=6)


class UsuarioAdminResponse(UsuarioAdminBase):
    id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)


# ==========================================
# 10. AUTENTICAÇÃO E LOGIN SCHEMAS
# ==========================================

class LoginRequest(BaseModel):
    email: EmailStr
    senha: str


class TokenResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"
