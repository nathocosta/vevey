# Graph Report - Vevey - tcc  (2026-10-23)

## Corpus Check
- 28 files · ~244,315 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 3 file(s) not represented in the graph (top: (none) 1, .example 1, .css 1)

## Summary
- 332 nodes · 652 edges · 20 communities (14 shown, 6 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 58 edges (avg confidence: 0.95)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `511772f3`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- schemas.py
- auth.py
- pedidos.py
- app.js
- criar_produto
- Design System & Guia de Estilos — Vevey Doces
- E-COMMERCE VEVEY: DOCUMENTO DE ESPECIFICAÇÃO E MODELAGEM DE BANCO DE DADOS
- frete.py
- Vevey Doces — E-Commerce & Varejo de Festas (TCC)
- schema.sql
- 4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)
- login
- main.py
- Settings
- 2. 🎯 O Que Falta Implementar (Backlog Priorizado para a Próxima Sessão)

## God Nodes (most connected - your core abstractions)
1. `criar_pedido_online()` - 18 edges
2. `criar_pedido_manual_whatsapp()` - 18 edges
3. `Cliente` - 14 edges
4. `Produto` - 13 edges
5. `login()` - 12 edges
6. `register_client()` - 11 edges
7. `pedidos` - 10 edges
8. `showToast()` - 10 edges
9. `UsuarioAdmin` - 9 edges
10. `get_current_admin()` - 8 edges

## Surprising Connections (you probably didn't know these)
- `🔵 Camada 2: Lógica de Negócios (Backend API)` --references--> `get_db()`  [INFERRED]
  ARQUITETURA_3_CAMADAS.md → backend/app/core/database.py
- `register_client()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/models.py
- `register_client()` --uses--> `ClienteCreate`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/schemas.py
- `login()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/models.py
- `login()` --uses--> `UsuarioAdmin`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/models.py

## Import Cycles
- None detected.

## Communities (20 total, 6 thin omitted)

### Community 0 - "schemas.py"
Cohesion: 0.07
Nodes (44): PedidoOnlineCreate, MetodoPagamento, StatusPagamento, StatusPedido, TipoEntrega, CategoriaBase, CategoriaCreate, CategoriaResponse (+36 more)

### Community 1 - "auth.py"
Cohesion: 0.11
Nodes (13): get_current_admin(), get_current_client(), get_db(), create_access_token(), decode_access_token(), get_password_hash(), CargoAdmin, Categoria (+5 more)

### Community 2 - "pedidos.py"
Cohesion: 0.13
Nodes (12): atualizar_status_pedido(), criar_pedido_manual_whatsapp(), criar_pedido_online(), ItemPedidoInput, listar_meus_pedidos(), listar_todos_pedidos(), PedidoManualCreate, StatusUpdateInput (+4 more)

### Community 3 - "app.js"
Cohesion: 0.18
Nodes (23): addToCart(), changeItemQty(), escapeHtml(), getCart(), getVeveyProductImage(), initAccountPage(), initCart(), initCartPage() (+15 more)

### Community 4 - "criar_produto"
Cohesion: 0.16
Nodes (7): ajustar_estoque(), alternar_status_produto(), criar_produto(), EstoqueUpdateRequest, listar_produtos(), obter_produto(), StatusUpdateRequest

### Community 5 - "Design System & Guia de Estilos — Vevey Doces"
Cohesion: 0.11
Nodes (17): 1. Paleta de Cores (Color Tokens), 2. Tipografia (Typography System), 3.1 Botões (Buttons), 3.2 Campos de Busca & Inputs, 3.3 Barras de Acento / Progresso (Progress Bars), 3.4 Badges Circulares & Chips de Ação, 3. Componentes de Interface (UI Elements), 4. Configuração Tailwind CSS Pronta para Uso (+9 more)

### Community 6 - "E-COMMERCE VEVEY: DOCUMENTO DE ESPECIFICAÇÃO E MODELAGEM DE BANCO DE DADOS"
Cohesion: 0.14
Nodes (13): 1. INTRODUÇÃO, 2. DIAGRAMA DE ENTIDADE-RELACIONAMENTO (DER), 3.1 Transição de Restrições Check para Enums Nativos (SQLAlchemy), 3.2 Controle de Acesso Baseado em Regras (RBAC), 3.3 Logística e Filas de Trabalho (Painel do Operador), 3. ESPECIFICAÇÃO TÉCNICA DAS MUDANÇAS E JUSTIFICATIVAS, 4. DICIONÁRIO DE DADOS (NOVOS ELEMENTOS E ALTERAÇÕES), 5. DIRETRIZES PARA BACKEND (PYTHON / FASTAPI / SQLALCHEMY) (+5 more)

### Community 7 - "frete.py"
Cohesion: 0.27
Nodes (4): calcular_frete(), CalculoFreteRequest, CalculoFreteResponse, OpcaoFrete

### Community 8 - "Vevey Doces — E-Commerce & Varejo de Festas (TCC)"
Cohesion: 0.22
Nodes (8): 1. Banco de Dados (Supabase), 2. Backend (FastAPI), 3. Frontend, 🏗️ Arquitetura do Sistema, 🚀 Como Executar Localmente, 📁 Estrutura de Diretórios, 🛠️ Tecnologias e Serviços, Vevey Doces — E-Commerce & Varejo de Festas (TCC)

### Community 9 - "schema.sql"
Cohesion: 0.14
Nodes (26): idx_usuarios_admin_email, trg_registrar_historico_status, usuarios_admin, categorias, clientes, devolver_estoque_pedido_cancelado(), enderecos, gerenciar_estoque_pedido() (+18 more)

### Community 14 - "4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)"
Cohesion: 0.13
Nodes (14): 1. Visão Geral da Arquitetura, 2. Detalhamento de Cada Camada, 3. Fluxo de Vida de uma Compra (End-to-End), 4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase), 5. Justificativa Arquitetural para a Banca do TCC, Arquitetura em 3 Camadas — Vevey Doces (TCC), 🟡 Camada 1: Apresentação (Frontend), 🔵 Camada 2: Lógica de Negócios (Backend API) (+6 more)

### Community 15 - "login"
Cohesion: 0.12
Nodes (8): get_admin_me(), get_me(), login(), LoginRequest, register_client(), TokenResponse, validar_cpf(), verify_password()

### Community 17 - "main.py"
Cohesion: 0.16
Nodes (4): custom_403_exception_handler(), health_check(), root(), security_headers_middleware()

### Community 19 - "2. 🎯 O Que Falta Implementar (Backlog Priorizado para a Próxima Sessão)"
Cohesion: 0.18
Nodes (10): 1. 🧹 Limpeza de Códigos Mortos e Dependências Realizada Nesta Sessão, 2. 🎯 O Que Falta Implementar (Backlog Priorizado para a Próxima Sessão), 3. 🚀 Guia Rápido de Execução, Como Rodar o Backend:, Como Rodar o Frontend:, Credenciais Padrão (Seed):, Prioridade Alta (Essencial para Apresentação do TCC & Fluxo Completo), Prioridade Baixa / Polimento Final (+2 more)

## Knowledge Gaps
- **6 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `get_db()` connect `auth.py` to `4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
- **Are the 9 inferred relationships involving `criar_pedido_online()` (e.g. with `Cliente` and `Endereco`) actually correct?**
  _`criar_pedido_online()` has 9 INFERRED edges - model-reasoned connections that need verification._
- **Should `schemas.py` be split into smaller, more focused modules?**
  _Cohesion score 0.06545879602571596 - nodes in this community are weakly interconnected._
- **Why does `🔵 Camada 2: Lógica de Negócios (Backend API)` connect `4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)` to `auth.py`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Are the 11 inferred relationships involving `criar_pedido_manual_whatsapp()` (e.g. with `Cliente` and `Endereco`) actually correct?**
  _`criar_pedido_manual_whatsapp()` has 11 INFERRED edges - model-reasoned connections that need verification._
- **Should `auth.py` be split into smaller, more focused modules?**
  _Cohesion score 0.10815602836879433 - nodes in this community are weakly interconnected._
- **Are the 7 inferred relationships involving `Cliente` (e.g. with `get_current_client()` and `get_me()`) actually correct?**
  _`Cliente` has 7 INFERRED edges - model-reasoned connections that need verification._