import enum
from sqlalchemy import (
    Column,
    Integer,
    BigInteger,
    String,
    Text,
    Numeric,
    Boolean,
    DateTime,
    ForeignKey,
    Enum,
    Table,
    CheckConstraint,
)
from sqlalchemy.orm import declarative_base, relationship
from sqlalchemy.sql import func
from sqlalchemy.dialects.postgresql import JSONB

Base = declarative_base()

# ==========================================
# 1. ENUMS (Mapeamento de Tipos PostgreSQL)
# ==========================================

class CargoAdmin(enum.Enum):
    ADMIN = 'ADMIN'
    OPERADOR = 'OPERADOR'


class StatusPedido(enum.Enum):
    PENDENTE = 'Pendente'
    AGUARDANDO_PAGAMENTO = 'Aguardando Pagamento'
    PAGO = 'Pago'
    ENVIADO = 'Enviado'
    ENTREGUE = 'Entregue'
    CANCELADO = 'Cancelado'


class MetodoPagamento(enum.Enum):
    PIX = 'Pix'
    CARTAO_CREDITO = 'Cartão de Crédito'
    BOLETO = 'Boleto'


class StatusPagamento(enum.Enum):
    PENDENTE = 'Pendente'
    APROVADO = 'Aprovado'
    RECUSADO = 'Recusado'
    ESTORNADO = 'Estornado'
    EM_ANALISE = 'Em Análise'


class TipoEntrega(enum.Enum):
    RETIRADA_LOJA = 'RETIRADA_LOJA'
    ENVIO_TRANSPORTADORA = 'ENVIO_TRANSPORTADORA'


# ==========================================
# 2. TABELA ASSOCIATIVA (N:N Produtos e Categorias)
# ==========================================

produtos_categorias = Table(
    'produtos_categorias',
    Base.metadata,
    Column('produto_id', BigInteger, ForeignKey('produtos.id', ondelete='CASCADE'), primary_key=True),
    Column('categoria_id', BigInteger, ForeignKey('categorias.id', ondelete='CASCADE'), primary_key=True)
)


# ==========================================
# 3. MODELOS ORM
# ==========================================

class Categoria(Base):
    __tablename__ = 'categorias'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    nome = Column(String(100), unique=True, nullable=False)
    slug = Column(String(100), unique=True, nullable=False, index=True)
    descricao = Column(Text)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)

    # Relacionamentos
    produtos = relationship("Produto", secondary=produtos_categorias, back_populates="categorias")


class Produto(Base):
    __tablename__ = 'produtos'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    nome = Column(String(150), nullable=False)
    slug = Column(String(150), unique=True, nullable=False, index=True)
    descricao = Column(Text)
    preco = Column(Numeric(10, 2), nullable=False)
    preco_promocional = Column(Numeric(10, 2))
    imagem_url = Column(String(500))
    estoque = Column(Integer, nullable=False, server_default='0')
    ativo = Column(Boolean, server_default='true')
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)

    __table_args__ = (
        CheckConstraint('preco >= 0', name='produtos_preco_check'),
        CheckConstraint('preco_promocional IS NULL OR (preco_promocional >= 0 AND preco_promocional < preco)', name='produtos_preco_promocional_check'),
        CheckConstraint('estoque >= 0', name='produtos_estoque_check'),
    )

    # Relacionamentos
    categorias = relationship("Categoria", secondary=produtos_categorias, back_populates="produtos")
    itens_pedido = relationship("ItemPedido", back_populates="produto")


class Cliente(Base):
    __tablename__ = 'clientes'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    nome = Column(String(150), nullable=False)
    cpf = Column(String(11), unique=True, nullable=False, index=True)
    email = Column(String(150), unique=True, nullable=False, index=True)
    telefone = Column(String(20))
    senha_hash = Column(String(255), nullable=False)
    ativo = Column(Boolean, server_default='true')
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)

    __table_args__ = (
        CheckConstraint('length(cpf) = 11', name='clientes_cpf_check'),
    )

    # Relacionamentos
    enderecos = relationship("Endereco", back_populates="cliente", cascade="all, delete-orphan")
    pedidos = relationship("Pedido", back_populates="cliente")


class Endereco(Base):
    __tablename__ = 'enderecos'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    cliente_id = Column(BigInteger, ForeignKey('clientes.id', ondelete='CASCADE'), nullable=False, index=True)
    identificador = Column(String(50), nullable=False)
    cep = Column(String(8), nullable=False)
    logradouro = Column(String(150), nullable=False)
    numero = Column(String(20), nullable=False)
    complemento = Column(String(100))
    bairro = Column(String(100), nullable=False)
    cidade = Column(String(100), nullable=False)
    estado = Column(String(2), nullable=False)
    principal = Column(Boolean, server_default='false')
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)

    __table_args__ = (
        CheckConstraint('length(cep) = 8', name='enderecos_cep_check'),
    )

    # Relacionamentos
    cliente = relationship("Cliente", back_populates="enderecos")
    pedidos = relationship("Pedido", back_populates="endereco_entrega")


class Pedido(Base):
    __tablename__ = 'pedidos'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    cliente_id = Column(BigInteger, ForeignKey('clientes.id', ondelete='RESTRICT'), nullable=False, index=True)
    endereco_entrega_id = Column(BigInteger, ForeignKey('enderecos.id', ondelete='RESTRICT'), nullable=False)
    valor_produtos = Column(Numeric(10, 2), nullable=False)
    valor_frete = Column(Numeric(10, 2), nullable=False, server_default='0.00')
    valor_total = Column(Numeric(10, 2), nullable=False)
    status = Column(Enum(StatusPedido, native_enum=True, name='status_pedido'), nullable=False, server_default='Pendente')
    codigo_rastreio = Column(String(100))
    tipo_entrega = Column(Enum(TipoEntrega, native_enum=True, name='tipo_entrega'), nullable=False, server_default='ENVIO_TRANSPORTADORA')
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)

    __table_args__ = (
        CheckConstraint('valor_produtos >= 0', name='pedidos_valor_produtos_check'),
        CheckConstraint('valor_frete >= 0', name='pedidos_valor_frete_check'),
        CheckConstraint('valor_total >= 0', name='pedidos_valor_total_check'),
    )

    # Relacionamentos
    cliente = relationship("Cliente", back_populates="pedidos")
    endereco_entrega = relationship("Endereco", back_populates="pedidos")
    itens = relationship("ItemPedido", back_populates="pedido", cascade="all, delete-orphan")
    pagamento = relationship("Pagamento", back_populates="pedido", uselist=False)
    historico_status = relationship("HistoricoStatusPedido", back_populates="pedido", cascade="all, delete-orphan")


class ItemPedido(Base):
    __tablename__ = 'itens_pedido'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    pedido_id = Column(BigInteger, ForeignKey('pedidos.id', ondelete='CASCADE'), nullable=False, index=True)
    produto_id = Column(BigInteger, ForeignKey('produtos.id', ondelete='RESTRICT'))
    quantidade = Column(Integer, nullable=False)
    preco_unitario = Column(Numeric(10, 2), nullable=False)

    __table_args__ = (
        CheckConstraint('quantidade > 0', name='itens_pedido_quantidade_check'),
        CheckConstraint('preco_unitario >= 0', name='itens_pedido_preco_unitario_check'),
    )

    # Relacionamentos
    pedido = relationship("Pedido", back_populates="itens")
    produto = relationship("Produto", back_populates="itens_pedido")


class Pagamento(Base):
    __tablename__ = 'pagamentos'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    pedido_id = Column(BigInteger, ForeignKey('pedidos.id', ondelete='RESTRICT'), unique=True, nullable=False, index=True)
    transacao_gateway_id = Column(String(100), unique=True)
    metodo = Column(Enum(MetodoPagamento, native_enum=True, name='metodo_pagamento'), nullable=False)
    status = Column(Enum(StatusPagamento, native_enum=True, name='status_pagamento'), nullable=False, server_default='Pendente')
    detalhes_gateway = Column(JSONB)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)

    # Relacionamentos
    pedido = relationship("Pedido", back_populates="pagamento")


class HistoricoStatusPedido(Base):
    __tablename__ = 'historico_status_pedidos'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    pedido_id = Column(BigInteger, ForeignKey('pedidos.id', ondelete='CASCADE'), nullable=False)
    status = Column(String(30), nullable=False)
    observacao = Column(Text)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)

    # Relacionamentos
    pedido = relationship("Pedido", back_populates="historico_status")


class UsuarioAdmin(Base):
    __tablename__ = 'usuarios_admin'

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    nome = Column(String(150), nullable=False)
    email = Column(String(150), unique=True, nullable=False, index=True)
    senha_hash = Column(String(255), nullable=False)
    cargo = Column(Enum(CargoAdmin, native_enum=True, name='cargo_admin'), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)
