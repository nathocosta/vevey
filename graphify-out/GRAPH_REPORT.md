# Graph Report - Vevey - tcc  (2026-10-22)

## Corpus Check
- 23 files · ~37,921 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 3 file(s) not represented in the graph (top: (none) 1, .example 1, .css 1)

## Summary
- 272 nodes · 554 edges · 14 communities (10 shown, 4 thin omitted)
- Extraction: 90% EXTRACTED · 10% INFERRED · 0% AMBIGUOUS · INFERRED: 54 edges (avg confidence: 0.95)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `de6004db`
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

## God Nodes (most connected - your core abstractions)
1. `criar_pedido_manual_whatsapp()` - 18 edges
2. `criar_pedido_online()` - 16 edges
3. `Cliente` - 12 edges
4. `Produto` - 11 edges
5. `login()` - 10 edges
6. `pedidos` - 10 edges
7. `register_client()` - 8 edges
8. `atualizar_status_pedido()` - 8 edges
9. `criar_produto()` - 8 edges
10. `StatusPedido` - 8 edges

## Surprising Connections (you probably didn't know these)
- `get_current_client()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/deps.py → backend/app/models.py
- `register_client()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/models.py
- `register_client()` --uses--> `ClienteCreate`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/schemas.py
- `login()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/models.py
- `get_me()` --uses--> `Cliente`  [INFERRED]
  backend/app/api/v1/endpoints/auth.py → backend/app/models.py

## Import Cycles
- None detected.

## Communities (14 total, 4 thin omitted)

### Community 0 - "schemas.py"
Cohesion: 0.07
Nodes (41): CargoAdmin, TipoEntrega, CategoriaBase, CategoriaCreate, CategoriaResponse, CategoriaUpdate, ClienteBase, ClienteCreate (+33 more)

### Community 1 - "auth.py"
Cohesion: 0.06
Nodes (17): get_current_admin(), get_current_client(), get_me(), login(), LoginRequest, register_client(), TokenResponse, Settings (+9 more)

### Community 2 - "pedidos.py"
Cohesion: 0.15
Nodes (20): atualizar_status_pedido(), criar_pedido_manual_whatsapp(), criar_pedido_online(), ItemPedidoInput, listar_meus_pedidos(), listar_todos_pedidos(), PedidoManualCreate, PedidoOnlineCreate (+12 more)

### Community 3 - "app.js"
Cohesion: 0.24
Nodes (17): addToCart(), changeItemQty(), DEFAULT_CART_ITEMS, getCart(), initCart(), initCartPage(), initCatalogPage(), applyFilters() (+9 more)

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
Cohesion: 0.38
Nodes (4): calcular_frete(), CalculoFreteRequest, CalculoFreteResponse, OpcaoFrete

### Community 8 - "Vevey Doces — E-Commerce & Varejo de Festas (TCC)"
Cohesion: 0.22
Nodes (8): 1. Banco de Dados (Supabase), 2. Backend (FastAPI), 3. Frontend, 🏗️ Arquitetura do Sistema, 🚀 Como Executar Localmente, 📁 Estrutura de Diretórios, 🛠️ Tecnologias e Serviços, Vevey Doces — E-Commerce & Varejo de Festas (TCC)

### Community 9 - "schema.sql"
Cohesion: 0.14
Nodes (26): idx_usuarios_admin_email, trg_registrar_historico_status, usuarios_admin, categorias, clientes, devolver_estoque_pedido_cancelado(), enderecos, gerenciar_estoque_pedido() (+18 more)

## Knowledge Gaps
- **4 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Cliente` connect `pedidos.py` to `auth.py`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **Are the 11 inferred relationships involving `criar_pedido_manual_whatsapp()` (e.g. with `Cliente` and `Endereco`) actually correct?**
  _`criar_pedido_manual_whatsapp()` has 11 INFERRED edges - model-reasoned connections that need verification._
- **Should `schemas.py` be split into smaller, more focused modules?**
  _Cohesion score 0.06753246753246753 - nodes in this community are weakly interconnected._
- **Why does `Produto` connect `pedidos.py` to `auth.py`, `criar_produto`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **Are the 9 inferred relationships involving `criar_pedido_online()` (e.g. with `Cliente` and `Endereco`) actually correct?**
  _`criar_pedido_online()` has 9 INFERRED edges - model-reasoned connections that need verification._
- **Should `auth.py` be split into smaller, more focused modules?**
  _Cohesion score 0.061581920903954805 - nodes in this community are weakly interconnected._
- **Why does `criar_pedido_manual_whatsapp()` connect `pedidos.py` to `schemas.py`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._