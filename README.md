# Vevey Doces — E-commerce, Estoque Integrado & PDV Balcão

Plataforma completa de vendas (varejo, atacado/caixas fechadas e balcão presencial) desenvolvida em **Laravel 13 + Filament 5 + PostgreSQL** seguindo arquitetura de **monólito modular** (`internachi/modular`) documentada em [`docs/MODELAGEM_LARAVEL_ADR.md`](docs/MODELAGEM_LARAVEL_ADR.md).

---

## Arquitetura Modular (ADR 8)

| Módulo | Camada | Responsabilidade |
| :--- | :--- | :--- |
| `app-modules/catalog` | `base` | Produtos planos (cada cor = SKU próprio — ADR 6), categorias, atributos dinâmicos, busca full-text (`tsvector` + GIN) e mídia (`spatie/laravel-medialibrary`). |
| `app-modules/inventory` | `base` | Ledger append-only (`stock_movements`), reservas de 30 min (`stock_reservations`) e cache de saldo (`stock_levels`) sob `lockForUpdate` ordenado por `product_id` (ADR 2). |
| `app-modules/customers` | `base` | Guard separado `customers`, identidades de contato normalizadas com aposentadoria (`customer_identities` — ADR 7) e endereços salvos. |
| `app-modules/orders` | `orchestrator` | Serviço único `PlaceOrder` (Site, PDV Balcão e WhatsApp), snapshot histórico de linha (ADR 3), cupons, rateio de desconto via `Money::allocate()` (ADR 1), pagamentos 1:N e histórico de status. |
| `app-modules/permissions` | `shared` | RBAC sobre `spatie/laravel-permission` com papéis `SuperAdmin`, `Admin`, `Attendant` e `Logistics`. |
| `app-modules/panel-admin` | `interface` | Painel administrativo Filament (`/admin`): Dashboard de métricas, PDV Balcão (`/admin/pdv`), Pedidos, Cupons, Estoque, Clientes, Catálogo e **Logs de auditoria** (`/admin/audit-log`). |
| `app-modules/panel-site` | `interface` | Loja pública em Blade + Tailwind v4: Home editorial, busca preditiva ao vivo, Catálogo com filtros ADR 6, PDP com estoque real e cores irmãs, Carrinho, Checkout (ViaCEP/SEDEX/PAC + cupom) e Área do Cliente (`/conta`). |

### Decisões técnicas principais
- **Dinheiro em centavos (`brick/money` + `MoneyCast`)** com `CHECK (total_cents = subtotal_cents - discount_cents + shipping_cents)` no PostgreSQL (ADR 1).
- **Zero triggers e zero enums nativos no banco**: toda regra roda em transações PHP com enums tipados.
- **Venda física é pedido `pos` (ADR 5)**: nasce paga e entregue no PDV Balcão, com permissão controlada para saldo negativo + alerta visual de divergência.
- **UUIDs (v7)** em todas as chaves primárias e estrangeiras.

---

## Como rodar localmente

```bash
# 1. Instalar dependências PHP e JS
composer install
npm install && npm run build

# 2. Configurar .env apontando para PostgreSQL e rodar migrations
cp .env.example .env
php artisan key:generate
php artisan migrate

# 3. Popular catálogo de exemplo, estoque inicial, equipe RBAC e permissões
php artisan db:seed --class="Vevey\Catalog\Database\Seeders\CatalogSeeder"
php artisan db:seed --class="Vevey\Inventory\Database\Seeders\InitialStockSeeder"
php artisan db:seed --class="Database\Seeders\StaffUsersSeeder"
php artisan sync:permissions

# 4. Subir servidor local
php -S 127.0.0.1:8000 -t public
```

### Comandos úteis de operação
- `php artisan stock:rebuild` — recalcula `stock_levels` a partir do ledger `stock_movements`.
- `php artisan stock:expire-reservations` — libera reservas vencidas (agendado a cada 5 min em `routes/console.php`).
- `php artisan sync:permissions` — sincroniza permissões RBAC de todos os módulos.

---

## Acessos para teste local

- **Loja:** `http://127.0.0.1:8000/` (cupom ativo para teste no checkout: **`VEVEY10`** — 10% OFF acima de R$ 30)
- **Área do Cliente:** `http://127.0.0.1:8000/entrar` / `/cadastro` / `/conta`
- **Painel Admin:** `http://127.0.0.1:8000/admin` (senha padrão: **`password`**)
  - `admin@vevey.com` — Super Admin (acesso total + Logs de auditoria)
  - `dona@vevey.com` — Administrador (dona da loja)
  - `atendente@vevey.com` — Atendente (PDV Balcão, pedidos e clientes)
  - `logistica@vevey.com` — Logística (separação, entrega e estoque)
