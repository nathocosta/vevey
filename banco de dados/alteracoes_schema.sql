-- ==========================================
-- 1. ADAPTAÇÃO PARA O SQLALCHEMY (ENUMS NATIVOS)
-- ==========================================

-- A) Criando os tipos ENUM nativos do PostgreSQL
CREATE TYPE status_pedido AS ENUM ('Pendente', 'Aguardando Pagamento', 'Pago', 'Enviado', 'Entregue', 'Cancelado');
CREATE TYPE metodo_pagamento AS ENUM ('Pix', 'Cartão de Crédito', 'Boleto');
CREATE TYPE status_pagamento AS ENUM ('Pendente', 'Aprovado', 'Recusado', 'Estornado', 'Em Análise');

-- B) Alterando a tabela 'pedidos'
-- Remover temporariamente o trigger que depende da coluna 'status'
DROP TRIGGER IF EXISTS trg_registrar_historico_status ON pedidos;

-- Remover o valor padrão temporariamente e a restrição CHECK antiga
ALTER TABLE pedidos ALTER COLUMN status DROP DEFAULT;
ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS pedidos_status_check;

-- Alterar o tipo da coluna com conversão explícita
ALTER TABLE pedidos 
  ALTER COLUMN status TYPE status_pedido USING status::status_pedido;

-- Restaurar o valor padrão com o tipo correto
ALTER TABLE pedidos ALTER COLUMN status SET DEFAULT 'Pendente'::status_pedido;

-- Recriar o trigger que depende da coluna 'status'
CREATE TRIGGER trg_registrar_historico_status
AFTER INSERT OR UPDATE OF status ON pedidos
FOR EACH ROW
EXECUTE FUNCTION registrar_historico_status_pedido();


-- C) Alterando a tabela 'pagamentos'
-- Remover o valor padrão temporariamente e as restrições CHECK antigas
ALTER TABLE pagamentos ALTER COLUMN status DROP DEFAULT;
ALTER TABLE pagamentos DROP CONSTRAINT IF EXISTS pagamentos_metodo_check;
ALTER TABLE pagamentos DROP CONSTRAINT IF EXISTS pagamentos_status_check;

-- Alterar o tipo das colunas com conversão explícita
ALTER TABLE pagamentos 
  ALTER COLUMN metodo TYPE metodo_pagamento USING metodo::metodo_pagamento,
  ALTER COLUMN status TYPE status_pagamento USING status::status_pagamento;

-- Restaurar o valor padrão para status
ALTER TABLE pagamentos ALTER COLUMN status SET DEFAULT 'Pendente'::status_pagamento;


-- ==========================================
-- 2. CRIAÇÃO DO CONTROLE DE ACESSO ADMINISTRATIVO (RBAC)
-- ==========================================

-- A) Criando o tipo ENUM nativo para cargos administrativos
CREATE TYPE cargo_admin AS ENUM ('ADMIN', 'OPERADOR');

-- B) Criando a tabela de usuários administrativos
CREATE TABLE usuarios_admin (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    cargo cargo_admin NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- C) Criando índice para otimização de busca por email (Autenticação/Login)
CREATE INDEX idx_usuarios_admin_email ON usuarios_admin(email);


-- ==========================================
-- 3. AJUSTE LOGÍSTICO NA TABELA DE PEDIDOS
-- ==========================================

-- A) Criando o tipo ENUM nativo para tipo de entrega
CREATE TYPE tipo_entrega AS ENUM ('RETIRADA_LOJA', 'ENVIO_TRANSPORTADORA');

-- B) Adicionando a nova coluna à tabela 'pedidos'
-- Definido um valor padrão ('ENVIO_TRANSPORTADORA') para manter compatibilidade com registros existentes
ALTER TABLE pedidos 
  ADD COLUMN tipo_entrega tipo_entrega NOT NULL DEFAULT 'ENVIO_TRANSPORTADORA';
