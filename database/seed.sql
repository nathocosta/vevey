-- ==============================================================================
-- VEVEY DOCES — SCRIPT DE SEED INICIAL (SUPABASE / POSTGRESQL)
-- Executável diretamente no Supabase SQL Editor para popular o banco de dados.
-- ==============================================================================

-- 1. Inserção de Categorias
INSERT INTO categorias (id, nome, slug, descricao, created_at, updated_at) VALUES
  (1, 'Balões & Festas', 'baloes', 'Balões metalizados, látex biodegradável, arcos e acessórios para festas', NOW(), NOW()),
  (2, 'Confeitaria Artística', 'confeitaria', 'Bicos de confeitar em inox, pasta americana, corantes e chocolates', NOW(), NOW()),
  (3, 'Descartáveis & Embalagens', 'descartaveis', 'Forminhas 4 pétalas, embalagens individuais e pratos descartáveis', NOW(), NOW()),
  (4, 'Decoração de Mesa', 'decoracao', 'Topos de bolo em acrílico, suportes, bandejas e peças temáticas', NOW(), NOW()),
  (5, 'Acessórios & Infladores', 'acessorios', 'Infladores elétricos, bombinhas manuais, fitas e suportes', NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- 2. Inserção de Produtos
INSERT INTO produtos (id, nome, slug, descricao, preco, preco_promocional, imagem_url, estoque, ativo, created_at, updated_at) VALUES
  (1, 'Kit Balões Metalizados Rose Gold & Cereja (50 unid.)', 'bl-9921', 'Kit com 50 balões metalizados e perolados de 12 polegadas nas cores Rose Gold e Cereja. Acompanha fita para arco desconstruído de 5 metros.', 64.90, 49.90, 'celebration', 15, true, NOW(), NOW()),
  (2, 'Balão Bubble Cristal Transparente com Confetes (24 pol.)', 'bb-1024', 'Balão Bubble inflável em silicone cristalino de 24 polegadas com confetes metálicos dourados. Alta durabilidade e brilho intenso.', 29.90, NULL, 'bubble_chart', 30, true, NOW(), NOW()),
  (3, 'Jogo de Bicos de Confeitar em Inox (12 Peças + Adaptador)', 'cf-4402', 'Kit profissional com 12 bicos de confeitar sem emenda em aço inoxidável 304 (Pitanga, Folha, Babado e Flor), inclui 1 adaptador de engate rápido.', 79.90, 69.90, 'cake', 8, true, NOW(), NOW()),
  (4, 'Pasta Americana Tradicional Vermelho Rubi 800g', 'pa-1029', 'Pasta de açúcar elástica pronta para cobrir bolos e modelar doces finos. Sabor suave de baunilha e coloração uniforme Vermelho Rubi.', 39.90, NULL, 'cookie', 3, true, NOW(), NOW()),
  (5, 'Forminhas 4 Pétalas Rosa Bebê (100 unid.)', 'fd-5510', 'Caixa com 100 forminhas de papel offset 180g com acabamento fosco e corte a laser perfeito para doces finos e brigadeiros gourmet.', 18.90, NULL, 'celebration', 45, true, NOW(), NOW()),
  (6, 'Topo de Bolo Dourado ''Parabéns'' em Acrílico Espelhado', 'to-3310', 'Topper elegante cortado a laser em acrílico espelhado dourado de 2mm com película protetora. Altura de 15cm com haste resistente.', 24.90, NULL, 'cake', 12, true, NOW(), NOW()),
  (7, 'Inflador de Balões Elétrico Profissional 600W (Bivolt)', 'in-8801', 'Compressor elétrico bivolt de alta potência com 2 bicos para inflar centenas de balões em minutos. Acionamento contínuo ou por toque.', 189.90, 169.90, 'local_shipping', 4, true, NOW(), NOW()),
  (8, 'Chocolate Nobre Gotas Meio Amargo 1,01kg', 'ch-7720', 'Gotas de chocolate nobre com 54% de cacau para derretimento rápido, têmpera perfeita e brilho excepcional em trufas e bombons.', 54.90, NULL, 'cookie', 19, true, NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- 3. Associação Produtos <-> Categorias
INSERT INTO produtos_categorias (produto_id, categoria_id) VALUES
  (1, 1),
  (2, 1),
  (3, 2),
  (4, 2),
  (5, 3),
  (6, 4),
  (7, 5),
  (8, 2)
ON CONFLICT (produto_id, categoria_id) DO NOTHING;

-- 4. Usuários Administradores (Senha inicial: "admin123")
-- Hash gerado com bcrypt compatível com passlib
INSERT INTO usuarios_admin (id, nome, email, senha_hash, cargo, created_at, updated_at) VALUES
  (1, 'Miriam Sócia', 'miriam@veveydoces.com.br', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', 'ADMIN', NOW(), NOW()),
  (2, 'Juliana Atendente', 'juliana@veveydoces.com.br', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', 'OPERADOR', NOW(), NOW()),
  (3, 'Carlos Logística', 'carlos@veveydoces.com.br', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', 'LOGISTICA', NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- 5. Cliente de Demonstração (Senha inicial: "cliente123")
INSERT INTO clientes (id, nome, cpf, email, telefone, senha_hash, ativo, created_at, updated_at) VALUES
  (1, 'Nathan Costa Santos', '12345678909', 'nathan@exemplo.com.br', '11987654321', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', true, NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- 6. Endereço do Cliente
INSERT INTO enderecos (id, cliente_id, identificador, cep, logradouro, numero, complemento, bairro, cidade, estado, principal, created_at, updated_at) VALUES
  (1, 1, 'Casa', '01310100', 'Avenida Paulista', '1000', 'Apto 42', 'Bela Vista', 'São Paulo', 'SP', true, NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- Sincroniza sequências dos IDs
SELECT setval('categorias_id_seq', (SELECT MAX(id) FROM categorias));
SELECT setval('produtos_id_seq', (SELECT MAX(id) FROM produtos));
SELECT setval('usuarios_admin_id_seq', (SELECT MAX(id) FROM usuarios_admin));
SELECT setval('clientes_id_seq', (SELECT MAX(id) FROM clientes));
SELECT setval('enderecos_id_seq', (SELECT MAX(id) FROM enderecos));
