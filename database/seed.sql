-- Seed Data - E-commerce Vevey
-- Foco: Dados realistas para validação inicial de fluxo e integridade

-- Limpar dados existentes antes do insert (opcional, útil para resetar)
TRUNCATE TABLE pagamentos, itens_pedido, historico_status_pedidos, pedidos, enderecos, clientes, produtos_categorias, produtos, categorias CASCADE;

-- ==========================================
-- 1. POPULANDO CATEGORIAS
-- ==========================================
INSERT INTO categorias (id, nome, slug, descricao) VALUES
(1, 'Chocolates', 'chocolates', 'Chocolates artesanais finos e importados.'),
(2, 'Cookies', 'cookies', 'Cookies crocantes por fora e macios por dentro com recheios gourmet.'),
(3, 'Pastas de Amendoim', 'pastas-de-amendoim', 'Pastas de amendoim 100% integrais com blends exclusivos.');

-- ==========================================
-- 2. POPULANDO PRODUTOS
-- ==========================================
INSERT INTO produtos (id, nome, slug, descricao, preco, preco_promocional, imagem_url, estoque, ativo) VALUES
(1, 'Chocolate Belga ao Leite com Avelãs', 'chocolate-belga-ao-leite-avelas', 'Barra de chocolate belga cremoso 33% cacau com pedaços selecionados de avelã torrada.', 28.90, NULL, 'https://vevey.com.br/images/belga-avelas.jpg', 50, TRUE),
(2, 'Chocolate Intenso 70% com Frutas Vermelhas', 'chocolate-intenso-70-frutas-vermelhas', 'Barra de chocolate amargo 70% cacau com framboesa e morango desidratados.', 32.50, 29.90, 'https://vevey.com.br/images/intenso-vermelhas.jpg', 40, TRUE),
(3, 'Cookie Clássico com Gotas de Chocolate', 'cookie-classico-gotas-chocolate', 'Massa tradicional de baunilha recheada com abundantes gotas de chocolate meio amargo.', 12.00, 9.90, 'https://vevey.com.br/images/cookie-classico.jpg', 120, TRUE),
(4, 'Cookie Red Velvet Recheado com Leite Ninho', 'cookie-red-velvet-ninho', 'Massa vermelha aveludada com gotas de chocolate branco e recheio cremoso de leite Ninho.', 15.00, NULL, 'https://vevey.com.br/images/cookie-red-velvet.jpg', 80, TRUE),
(5, 'Pasta de Amendoim Crocante Gourmet', 'pasta-amendoim-crocante-gourmet', 'Pasta integral de amendoim torrado com pedaços crocantes e toque leve de sal marinho.', 24.90, NULL, 'https://vevey.com.br/images/pasta-crocante.jpg', 60, TRUE),
(6, 'Pasta de Amendoim com Cacau e Avelã', 'pasta-amendoim-cacau-avela', 'Blend cremoso de amendoim com cacau puro 100% e avelãs trituradas.', 29.90, 26.90, 'https://vevey.com.br/images/pasta-cacau-avela.jpg', 45, TRUE);

-- ==========================================
-- 3. VINCULANDO PRODUTOS E CATEGORIAS (Múltiplas Categorias)
-- ==========================================
INSERT INTO produtos_categorias (produto_id, categoria_id) VALUES
(1, 1), -- Chocolate Belga em Chocolates
(2, 1), -- Chocolate Intenso em Chocolates
(3, 2), -- Cookie Clássico em Cookies
(4, 2), -- Cookie Red Velvet em Cookies
(5, 3), -- Pasta Crocante em Pastas
(6, 3), -- Pasta Cacau em Pastas
(6, 1); -- Pasta Cacau também cadastrada em Chocolates para fins promocionais

-- ==========================================
-- 4. POPULANDO CLIENTES
-- ==========================================
-- senhas criptografadas simuladas (hashes fictícios apenas para representação)
INSERT INTO clientes (id, nome, cpf, email, telefone, senha_hash, ativo) VALUES
(1, 'Nathan Silva', '12345678901', 'nathan.silva@email.com', '11999998888', '$2b$12$Kse9O39d8FskfJkskdfjhsue9Dfkjhsuiyehfshf983jdhkasdfjh', TRUE),
(2, 'Mariana Costa', '98765432100', 'mariana.costa@email.com', '21988887777', '$2b$12$Lksd9fjhskdfjhsuerhfusehfjshdfjhs83jdkasldfjhseufhj', TRUE);

-- ==========================================
-- 5. POPULANDO ENDEREÇOS
-- ==========================================
INSERT INTO enderecos (id, cliente_id, identificador, cep, logradouro, numero, complemento, bairro, cidade, estado, principal) VALUES
(1, 1, 'Casa Principal', '01311000', 'Avenida Paulista', '1000', 'Apto 42', 'Bela Vista', 'São Paulo', 'SP', TRUE),
(2, 1, 'Trabalho', '04571010', 'Avenida Engenheiro Luís Carlos Berrini', '500', 'Andar 10', 'Brooklin Novo', 'São Paulo', 'SP', FALSE),
(3, 2, 'Apartamento', '22021001', 'Avenida Atlântica', '1700', 'Apto 1002', 'Copacabana', 'Rio de Janeiro', 'RJ', TRUE);

-- ==========================================
-- 6. POPULANDO PEDIDOS E ITENS DE PEDIDO
-- ==========================================

-- Pedido 1: Nathan Silva (Status: Pago)
INSERT INTO pedidos (id, cliente_id, endereco_entrega_id, valor_produtos, valor_frete, valor_total, status, codigo_rastreio) VALUES
(1, 1, 1, 87.70, 15.00, 102.70, 'Pago', 'BR123456789PT');

-- Itens Pedido 1 (O trigger trg_subtrair_estoque será ativado e reduzirá os estoques dos produtos 1 e 5)
-- Produto 1 (2 unidades): 28.90 cada. Produto 5 (1 unidade): 24.90. (No dia, sem descontos ativos nos itens comprados)
INSERT INTO itens_pedido (id, pedido_id, produto_id, quantidade, preco_unitario) VALUES
(1, 1, 1, 2, 28.90),
(2, 1, 5, 1, 29.90); -- Preço cobrado no dia (exemplo de preservação histórica)

-- Pagamento do Pedido 1
INSERT INTO pagamentos (id, pedido_id, transacao_gateway_id, metodo, status, detalhes_gateway) VALUES
(1, 1, 'pay_mp_7894561230', 'Pix', 'Aprovado', '{"payment_method_id": "pix", "acquirer_name": "mercadopago", "approved_at": "2026-08-09T21:55:00Z"}');


-- Pedido 2: Mariana Costa (Status: Pendente)
INSERT INTO pedidos (id, cliente_id, endereco_entrega_id, valor_produtos, valor_frete, valor_total, status, codigo_rastreio) VALUES
(2, 2, 3, 29.70, 18.50, 48.20, 'Pendente', NULL);

-- Itens Pedido 2
-- Produto 3 (3 unidades do Cookie Clássico com preço promocional de 9.90 aplicado)
INSERT INTO itens_pedido (id, pedido_id, produto_id, quantidade, preco_unitario) VALUES
(3, 2, 3, 3, 9.90);

-- Pagamento pendente para Pedido 2
INSERT INTO pagamentos (id, pedido_id, transacao_gateway_id, metodo, status, detalhes_gateway) VALUES
(2, 2, 'pay_stripe_1122334455', 'Cartão de Crédito', 'Pendente', '{"status": "requires_payment_method", "client_secret": "sec_123_abc"}');

-- ==========================================
-- 8. RESETAR SEQUÊNCIAS DO POSTGRESQL (Evitar erros de Primary Key posteriores)
-- ==========================================
SELECT setval('categorias_id_seq', COALESCE((SELECT MAX(id) FROM categorias), 1));
SELECT setval('produtos_id_seq', COALESCE((SELECT MAX(id) FROM produtos), 1));
SELECT setval('clientes_id_seq', COALESCE((SELECT MAX(id) FROM clientes), 1));
SELECT setval('enderecos_id_seq', COALESCE((SELECT MAX(id) FROM enderecos), 1));
SELECT setval('pedidos_id_seq', COALESCE((SELECT MAX(id) FROM pedidos), 1));
SELECT setval('itens_pedido_id_seq', COALESCE((SELECT MAX(id) FROM itens_pedido), 1));
SELECT setval('pagamentos_id_seq', COALESCE((SELECT MAX(id) FROM pagamentos), 1));
SELECT setval('historico_status_pedidos_id_seq', COALESCE((SELECT MAX(id) FROM historico_status_pedidos), 1));
