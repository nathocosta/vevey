# Graph Report - Vevey - tcc  (2026-10-25)

## Corpus Check
- 30 files · ~71,414 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 3 file(s) not represented in the graph (top: (none) 1, .example 1, .css 1)

## Summary
- 379 nodes · 792 edges · 21 communities (14 shown, 7 thin omitted)
- Extraction: 89% EXTRACTED · 11% INFERRED · 0% AMBIGUOUS · INFERRED: 89 edges (avg confidence: 0.95)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `757e5a17`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- schemas.py
- auth.py
- models.py
- app.js
- criar_produto
- Design System & Guia de Estilos — Vevey Doces
- E-COMMERCE VEVEY: DOCUMENTO DE ESPECIFICAÇÃO E MODELAGEM DE BANCO DE DADOS
- login
- Vevey Doces — E-Commerce & Varejo de Festas (TCC)
- schema.sql
- 4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)
- image-converter.js
- main.py
- Settings
- 2. 🎯 O Que Falta Implementar (Backlog Priorizado para a Próxima Sessão)
- atualizar_funcionario

## God Nodes (most connected - your core abstractions)
1. `checkout_web_loja()` - 19 edges
2. `criar_pedido_online()` - 18 edges
3. `criar_pedido_manual_whatsapp()` - 18 edges
4. `Produto` - 16 edges
5. `UsuarioAdmin` - 16 edges
6. `Cliente` - 15 edges
7. `login()` - 12 edges
8. `register_client()` - 11 edges
9. `get_password_hash()` - 10 edges
10. `StatusPedido` - 10 edges

## Surprising Connections (you probably didn't know these)
- `🔵 Camada 2: Lógica de Negócios (Backend API)` --references--> `get_db()`  [INFERRED]
  ARQUITETURA_3_CAMADAS.md → backend/app/core/database.py
- `get_current_client()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/deps.py → backend/app/models.py
- `register_client()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/models.py
- `register_client()` --uses--> `ClienteCreate`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/schemas.py
- `login()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/models.py

## Import Cycles
- None detected.

## Communities (21 total, 7 thin omitted)

### Community 0 - "schemas.py"
Cohesion: 0.06
Nodes (41): criar_funcionario(), CargoAdmin, CategoriaBase, CategoriaCreate, CategoriaResponse, CategoriaUpdate, ClienteBase, ClienteCreate (+33 more)

### Community 1 - "auth.py"
Cohesion: 0.10
Nodes (11): get_current_admin(), get_current_client(), require_admin_role(), get_db(), create_access_token(), decode_access_token(), get_password_hash(), verify_password() (+3 more)

### Community 2 - "models.py"
Cohesion: 0.13
Nodes (23): atualizar_status_pedido(), checkout_web_loja(), CheckoutWebInput, criar_pedido_manual_whatsapp(), criar_pedido_online(), ItemPedidoInput, listar_meus_pedidos(), listar_todos_pedidos() (+15 more)

### Community 3 - "app.js"
Cohesion: 0.16
Nodes (26): addToCart(), changeItemQty(), escapeHtml(), getApiUrl(), getCart(), getVeveyProductImage(), initAccountPage(), loadCustomerOrders() (+18 more)

### Community 4 - "criar_produto"
Cohesion: 0.10
Nodes (10): ajustar_estoque(), alternar_status_produto(), atualizar_produto(), criar_produto(), EstoqueUpdateRequest, excluir_produto(), listar_produtos(), obter_produto() (+2 more)

### Community 5 - "Design System & Guia de Estilos — Vevey Doces"
Cohesion: 0.11
Nodes (17): 1. Paleta de Cores (Color Tokens), 2. Tipografia (Typography System), 3.1 Botões (Buttons), 3.2 Campos de Busca & Inputs, 3.3 Barras de Acento / Progresso (Progress Bars), 3.4 Badges Circulares & Chips de Ação, 3. Componentes de Interface (UI Elements), 4. Configuração Tailwind CSS Pronta para Uso (+9 more)

### Community 6 - "E-COMMERCE VEVEY: DOCUMENTO DE ESPECIFICAÇÃO E MODELAGEM DE BANCO DE DADOS"
Cohesion: 0.14
Nodes (13): 1. INTRODUÇÃO, 2. DIAGRAMA DE ENTIDADE-RELACIONAMENTO (DER), 3.1 Transição de Restrições Check para Enums Nativos (SQLAlchemy), 3.2 Controle de Acesso Baseado em Regras (RBAC), 3.3 Logística e Filas de Trabalho (Painel do Operador), 3. ESPECIFICAÇÃO TÉCNICA DAS MUDANÇAS E JUSTIFICATIVAS, 4. DICIONÁRIO DE DADOS (NOVOS ELEMENTOS E ALTERAÇÕES), 5. DIRETRIZES PARA BACKEND (PYTHON / FASTAPI / SQLALCHEMY) (+5 more)

### Community 7 - "login"
Cohesion: 0.13
Nodes (7): get_admin_me(), get_me(), login(), LoginRequest, register_client(), TokenResponse, validar_cpf()

### Community 8 - "Vevey Doces — E-Commerce & Varejo de Festas (TCC)"
Cohesion: 0.22
Nodes (8): 1. Banco de Dados (Supabase), 2. Backend (FastAPI), 3. Frontend, 🏗️ Arquitetura do Sistema, 🚀 Como Executar Localmente, 📁 Estrutura de Diretórios, 🛠️ Tecnologias e Serviços, Vevey Doces — E-Commerce & Varejo de Festas (TCC)

### Community 9 - "schema.sql"
Cohesion: 0.14
Nodes (26): idx_usuarios_admin_email, trg_registrar_historico_status, usuarios_admin, categorias, clientes, devolver_estoque_pedido_cancelado(), enderecos, gerenciar_estoque_pedido() (+18 more)

### Community 14 - "4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)"
Cohesion: 0.13
Nodes (14): 1. Visão Geral da Arquitetura, 2. Detalhamento de Cada Camada, 3. Fluxo de Vida de uma Compra (End-to-End), 4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase), 5. Justificativa Arquitetural para a Banca do TCC, Arquitetura em 3 Camadas — Vevey Doces (TCC), 🟡 Camada 1: Apresentação (Frontend), 🔵 Camada 2: Lógica de Negócios (Backend API) (+6 more)

### Community 17 - "main.py"
Cohesion: 0.10
Nodes (8): calcular_frete(), CalculoFreteRequest, CalculoFreteResponse, OpcaoFrete, custom_403_exception_handler(), health_check(), root(), security_headers_middleware()

### Community 19 - "2. 🎯 O Que Falta Implementar (Backlog Priorizado para a Próxima Sessão)"
Cohesion: 0.18
Nodes (10): 1. 🧹 Limpeza de Códigos Mortos e Dependências Realizada Nesta Sessão, 2. 🎯 O Que Falta Implementar (Backlog Priorizado para a Próxima Sessão), 3. 🚀 Guia Rápido de Execução, Como Rodar o Backend:, Como Rodar o Frontend:, Credenciais Padrão (Seed):, Prioridade Alta (Essencial para Apresentação do TCC & Fluxo Completo), Prioridade Baixa / Polimento Final (+2 more)

### Community 20 - "atualizar_funcionario"
Cohesion: 0.21
Nodes (4): atualizar_funcionario(), excluir_funcionario(), listar_funcionarios(), obter_funcionario()

## Knowledge Gaps
- **7 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `get_db()` connect `auth.py` to `models.py`, `4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)`?**
  _High betweenness centrality (0.057) - this node is a cross-community bridge._
- **Are the 10 inferred relationships involving `checkout_web_loja()` (e.g. with `Cliente` and `Endereco`) actually correct?**
  _`checkout_web_loja()` has 10 INFERRED edges - model-reasoned connections that need verification._
- **Should `schemas.py` be split into smaller, more focused modules?**
  _Cohesion score 0.060655737704918035 - nodes in this community are weakly interconnected._
- **Why does `🔵 Camada 2: Lógica de Negócios (Backend API)` connect `4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)` to `auth.py`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Are the 9 inferred relationships involving `criar_pedido_online()` (e.g. with `Cliente` and `Endereco`) actually correct?**
  _`criar_pedido_online()` has 9 INFERRED edges - model-reasoned connections that need verification._
- **Should `auth.py` be split into smaller, more focused modules?**
  _Cohesion score 0.10202020202020202 - nodes in this community are weakly interconnected._
- **Are the 11 inferred relationships involving `criar_pedido_manual_whatsapp()` (e.g. with `Cliente` and `Endereco`) actually correct?**
  _`criar_pedido_manual_whatsapp()` has 11 INFERRED edges - model-reasoned connections that need verification._