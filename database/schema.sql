-- Schema de Banco de Dados - E-commerce Vevey
-- Foco: Alta Performance, Segurança e Integridade Referencial
-- Dialeto: PostgreSQL

-- ==========================================
-- 1. BLOCO DE CATÁLOGO (Produtos e Categorias)
-- ==========================================

CREATE TABLE categorias (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL, -- Utilizado para SEO URLs amigáveis
    descricao TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produtos (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL, -- Utilizado para SEO URLs amigáveis
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL CHECK (preco >= 0),
    preco_promocional DECIMAL(10, 2) CHECK (preco_promocional IS NULL OR (preco_promocional >= 0 AND preco_promocional < preco)),
    imagem_url VARCHAR(500),
    estoque INT NOT NULL DEFAULT 0 CHECK (estoque >= 0),
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Tabela associativa para suportar N para N (Múltiplas categorias por produto)
CREATE TABLE produtos_categorias (
    produto_id BIGINT NOT NULL REFERENCES produtos(id) ON DELETE CASCADE,
    categoria_id BIGINT NOT NULL REFERENCES categorias(id) ON DELETE CASCADE,
    PRIMARY KEY (produto_id, categoria_id)
);

-- ==========================================
-- 2. BLOCO DE USUÁRIOS (Clientes e Endereços)
-- ==========================================

CREATE TABLE clientes (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf CHAR(11) UNIQUE NOT NULL CHECK (length(cpf) = 11),
    email VARCHAR(150) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    senha_hash VARCHAR(255) NOT NULL, -- Salvar como Hash seguro (bcrypt/argon2) no Backend
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE enderecos (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL REFERENCES clientes(id) ON DELETE CASCADE,
    identificador VARCHAR(50) NOT NULL, -- Ex: 'Casa', 'Trabalho', 'Entregar para Maria'
    cep CHAR(8) NOT NULL CHECK (length(cep) = 8),
    logradouro VARCHAR(150) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100),
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL, -- UF de 2 dígitos (ex: SP, RJ, MG)
    principal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 3. BLOCO DE VENDAS (Pedidos e Itens de Compra)
-- ==========================================

CREATE TABLE pedidos (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL REFERENCES clientes(id) ON DELETE RESTRICT,
    endereco_entrega_id BIGINT NOT NULL REFERENCES enderecos(id) ON DELETE RESTRICT,
    valor_produtos DECIMAL(10, 2) NOT NULL CHECK (valor_produtos >= 0),
    valor_frete DECIMAL(10, 2) NOT NULL DEFAULT 0.00 CHECK (valor_frete >= 0),
    valor_total DECIMAL(10, 2) NOT NULL CHECK (valor_total >= 0),
    status VARCHAR(30) NOT NULL DEFAULT 'Pendente' CHECK (status IN ('Pendente', 'Aguardando Pagamento', 'Pago', 'Enviado', 'Entregue', 'Cancelado')),
    codigo_rastreio VARCHAR(100), -- Gerado pela integração com Melhor Envio
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE itens_pedido (
    id BIGSERIAL PRIMARY KEY,
    pedido_id BIGINT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    produto_id BIGINT REFERENCES produtos(id) ON DELETE RESTRICT, -- Preserva histórico do produto
    quantidade INT NOT NULL CHECK (quantidade > 0),
    preco_unitario DECIMAL(10, 2) NOT NULL CHECK (preco_unitario >= 0) -- Congela o preço praticado no dia da compra
);

-- ==========================================
-- 4. BLOCO FINANCEIRO (Pagamentos)
-- ==========================================

CREATE TABLE pagamentos (
    id BIGSERIAL PRIMARY KEY,
    pedido_id BIGINT NOT NULL UNIQUE REFERENCES pedidos(id) ON DELETE RESTRICT,
    transacao_gateway_id VARCHAR(100) UNIQUE, -- ID gerado pela Stripe/Mercado Pago
    metodo VARCHAR(30) NOT NULL CHECK (metodo IN ('Pix', 'Cartão de Crédito', 'Boleto')),
    status VARCHAR(30) NOT NULL DEFAULT 'Pendente' CHECK (status IN ('Pendente', 'Aprovado', 'Recusado', 'Estornado', 'Em Análise')),
    detalhes_gateway JSONB, -- Payload bruto retornado via Webhook para auditoria técnica
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 5. BLOCO DE LOGS E HISTÓRICO (Auditoria)
-- ==========================================

CREATE TABLE historico_status_pedidos (
    id BIGSERIAL PRIMARY KEY,
    pedido_id BIGINT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    status VARCHAR(30) NOT NULL,
    observacao TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 6. ÍNDICES DE PERFORMANCE (Otimização de Consultas)
-- ==========================================

CREATE INDEX idx_produtos_slug ON produtos(slug);
CREATE INDEX idx_categorias_slug ON categorias(slug);
CREATE INDEX idx_clientes_email ON clientes(email);
CREATE INDEX idx_clientes_cpf ON clientes(cpf);
CREATE INDEX idx_enderecos_cliente ON enderecos(cliente_id);
CREATE INDEX idx_pedidos_cliente ON pedidos(cliente_id);
CREATE INDEX idx_itens_pedido_pedido ON itens_pedido(pedido_id);
CREATE INDEX idx_pagamentos_pedido ON pagamentos(pedido_id);

-- ==========================================
-- 7. TRIGGERS E FUNÇÕES (Automações)
-- ==========================================

-- Trigger A: Subtrair estoque ao inserir itens em um pedido
CREATE OR REPLACE FUNCTION gerenciar_estoque_pedido()
RETURNS TRIGGER AS $$
BEGIN
    -- Validar se há estoque suficiente antes de deduzir
    IF (SELECT estoque FROM produtos WHERE id = NEW.produto_id) < NEW.quantidade THEN
        RAISE EXCEPTION 'Estoque insuficiente para o produto ID %', NEW.produto_id;
    END IF;

    -- Subtração
    UPDATE produtos
    SET estoque = estoque - NEW.quantidade
    WHERE id = NEW.produto_id;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_subtrair_estoque
AFTER INSERT ON itens_pedido
FOR EACH ROW
EXECUTE FUNCTION gerenciar_estoque_pedido();


-- Trigger B: Devolver estoque em caso de pedido Cancelado
CREATE OR REPLACE FUNCTION devolver_estoque_pedido_cancelado()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'Cancelado' AND OLD.status != 'Cancelado' THEN
        UPDATE produtos p
        SET estoque = p.estoque + ip.quantidade
        FROM itens_pedido ip
        WHERE ip.pedido_id = NEW.id AND p.id = ip.produto_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_devolver_estoque_cancelamento
AFTER UPDATE ON pedidos
FOR EACH ROW
EXECUTE FUNCTION devolver_estoque_pedido_cancelado();


-- Trigger C: Registro automatizado de histórico de status de pedidos
CREATE OR REPLACE FUNCTION registrar_historico_status_pedido()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' OR OLD.status != NEW.status THEN
        INSERT INTO historico_status_pedidos (pedido_id, status, observacao)
        VALUES (
            NEW.id, 
            NEW.status, 
            CASE 
                WHEN TG_OP = 'INSERT' THEN 'Pedido criado no sistema com status inicial.'
                ELSE 'Alteração de status do pedido: ' || OLD.status || ' -> ' || NEW.status
            END
        );
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_registrar_historico_status
AFTER INSERT OR UPDATE OF status ON pedidos
FOR EACH ROW
EXECUTE FUNCTION registrar_historico_status_pedido();
