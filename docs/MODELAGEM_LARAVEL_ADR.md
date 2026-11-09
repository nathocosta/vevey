# Vevey — Migração para Laravel + Filament: discussão de modelagem (ADRs)

Data: 2026-11-02 / 2026-11-03 (sincronizado com o artefato v15 em 2026-11-03)
Artefato visual (mapa do sistema atual + seção "Discussões"): https://claude.ai/artifact/D5RZ1j8AxWj1yDQ7Enmyao (republicado na conta 3pontos em 2026-11-07; original na conta brd: PtMF9CTJ1kAbpXwAHjYVkE)

Este arquivo registra tudo o que foi discutido sobre a nova modelagem antes de migrar o sistema
de FastAPI + frontend estático para Laravel + Filament. Serve de base para os ADRs do projeto novo
e como contexto para continuar a conversa em outra sessão.

---

## 1. Contexto: o sistema atual

- **Stack**: FastAPI (Python), SQLAlchemy 2.0, Pydantic v2, PyJWT HS256, bcrypt, slowapi (rate limit),
  PostgreSQL no Supabase (sa-east-1, pooler 6543). Frontend em Vanilla JS + Tailwind CDN, 12 páginas HTML,
  `js/app.js` (~1700 linhas), `admin.html` (~2900 linhas) com fallbacks em localStorage.
- **Tabelas (10)**: categorias, produtos, produtos_categorias, clientes, enderecos, pedidos, itens_pedido,
  pagamentos, historico_status_pedidos, usuarios_admin.
- **Enums Python/Postgres**: CargoAdmin (ADMIN/OPERADOR/LOGISTICA), StatusPedido, MetodoPagamento,
  StatusPagamento, TipoEntrega.
- **Triggers no banco (3)** em `database/schema.sql`: baixa de estoque, histórico de status, updated_at.

### Achados relevantes para a modelagem

| Achado | Onde | Efeito |
|---|---|---|
| Estoque debitado duas vezes | código + trigger | saldo errado após cada pedido |
| Dinheiro em float/Numeric sem padrão | models + schemas | divergência de preço entre tela e API |
| Cliente "placeholder" com senha fake para pedido de WhatsApp/visitante | pedidos.py | bloqueia cadastro futuro do mesmo email |
| Status de pedido único misturando pagamento, separação e entrega | StatusPedido | transições inválidas, `aprovar-teste` usa `EM_SEPARACAO` que não existe (500) e não tem auth |
| `minha-conta` chama `GET /pedidos/` (rota admin) em vez de `/pedidos/meus` | frontend | 403 para cliente |
| Upload de imagem no disco do servidor da API | produtos.py | some em deploy efêmero |
| `schema.sql` desatualizado em relação aos models | database/ | fonte de verdade ambígua |
| Admin assume cargo ADMIN quando API está fora | admin.html | escalação de permissão na UI |

---

## 2. Decisões já tomadas (Richard)

Regras transversais, todas decididas em 2026-11-02:

1. **Nada de triggers no banco.** Regras de negócio ficam na aplicação (`DB::transaction` + `lockForUpdate`).
   Constraints declarativas (CHECK, UNIQUE, FK, NOT NULL) continuam bem-vindas. Motivo: "pode dar b.o muito fácil".
2. **Sem enum nativo no Postgres.** Coluna `string` + cast para PHP enum no Eloquent.
3. **Nomenclatura padrão Laravel, em inglês.** Tabelas plural snake_case (`orders`, `order_items`),
   models singular PascalCase, pivôs singulares em ordem alfabética (`category_product`). Português só em labels de UI.
4. **Imagens com `spatie/laravel-medialibrary`.** Disco S3/Supabase Storage, conversions em WebP,
   `SpatieMediaLibraryFileUpload` / `SpatieMediaLibraryImageColumn` no Filament.
5. **Auditoria de produto com `spatie/laravel-activitylog`.** Não há tabela de versão de produto (ver ADR 5).
6. **Soft deletes** em produto, cliente, usuário. Produto nunca é apagado fisicamente (pedidos antigos referenciam).
7. **Guards separados**: `users` (equipe, acessa o painel via `FilamentUser::canAccessPanel`) e `customers` (loja).
8. **Arquitetura: monólito modular** (decidido 2026-11-04). Richard vai detalhar depois.
9. **Autorização por RBAC** (decidido 2026-11-04). Richard tem um módulo de projeto pronto para compartilhar;
   os papéis da equipe (hoje admin/operador/logística no repo, atendente/administrador no TCC) viram dados desse
   módulo, não enum. Detalhes quando o módulo for apresentado.

---

## 3. ADR 1 — Dinheiro

**Contexto.** Hoje preços são float/Numeric sem regra, e o frontend recalcula totais.

**Decisão recomendada (não marcada como decidida explicitamente, mas sem objeção):**
- Todo valor monetário é **inteiro em centavos** (`bigint`), sufixo `_cents`: `price_cents`, `subtotal_cents`,
  `discount_cents`, `shipping_cents`, `total_cents`.
- Sem coluna `currency`: só BRL. Se um dia precisar, adiciona com default.
- Cast `brick/money` no Eloquent; Filament usa `->money('BRL', divideBy: 100)`.
- **Totais do pedido são fato histórico**, gravados na criação, nunca recalculados a partir dos produtos atuais.
  CHECK: `total_cents = subtotal_cents - discount_cents + shipping_cents`.
- Tabela `order_adjustments` (cupom, desconto Pix, frete grátis, ajuste manual) para explicar o `discount_cents`.
- Desconto rateado por linha com `Money::allocate()` para que a soma das linhas feche com o total.
- Tabela `coupons` separada.

**Pagamentos.**
- `payments` 1:N com o pedido: `amount_cents`, `method`, `provider`, `provider_id`, `status`, `expires_at`, `paid_at`.
- `payment_events`: payload bruto do webhook + chave de idempotência (`provider`, `event_id` unique).
- Regra: **webhook dispara, GET no provedor é a verdade.** Nunca mudar status só pelo payload do webhook.

---

## 4. ADR 2 — Estoque: ledger simples + reservas + cache de saldo

### 4.1 Por que ledger e não só um update atômico?

Pergunta feita em 2026-11-03. Resposta:

- O update atômico (`UPDATE stock_levels SET on_hand = on_hand - 3 WHERE product_id = ? AND on_hand >= 3`)
  resolve **concorrência**. Ele continua existindo: é o que `stock_levels` faz dentro da transação com lock.
- O ledger resolve **explicabilidade**. Com só o número atual não há como responder "por que tem 7 se comprei 20
  e vendi 10?". Estoque físico diverge sempre (quebra, erro de contagem, brinde, devolução não registrada);
  sem histórico só dá para corrigir o número, nunca achar onde divergiu.
- O ledger compra: auditoria (quem, quando, por quê, referência), reconciliação na contagem física,
  reversão limpa (cancelamento = movimentação compensatória), idempotência via unique em
  `(reference_type, reference_id, reason)`, relatórios (`GROUP BY` no ledger).
- Custo: um insert a mais por movimento e a disciplina de que **todo ajuste passa por um único serviço**.
  Nunca expor `on_hand` como campo editável no Filament; só uma ação "ajustar estoque" que grava movimentação.

### 4.2 Por que não double-entry?

Double-entry (débito/crédito entre "contas" de estoque) só paga o custo quando existe:
- segundo local de estoque (transferência loja ↔ depósito),
- valoração (custo médio, CMV contábil),
- necessidade de provar que "nada se cria nem se perde" entre locais.

Vevey hoje tem um estoque. Decisão: **ledger de entrada única** (uma linha por movimento), append-only.
Se aparecer um segundo local, adiciona `location_id` nas tabelas e, se precisar, migra para double-entry depois.

### 4.3 Tabelas

**`stock_movements`** (append-only, nunca UPDATE/DELETE)

| coluna | tipo | nota |
|---|---|---|
| id | bigint | |
| product_id | FK products | |
| qty | integer, signed, NOT NULL, CHECK qty <> 0 | **única verdade**; positivo entra, negativo sai |
| direction | string, **stored generated** | `CASE WHEN qty > 0 THEN 'in' ELSE 'out' END`; app nunca escreve |
| reason | string (cast enum PHP) | `purchase`, `sale`, `return`, `adjustment` |
| reference_type / reference_id | string / bigint | polimórfico: order, purchase, adjustment |
| balance_after | integer | saldo do produto após o movimento, gravado sob lock |
| user_id | FK users nullable | quem fez |
| note | text nullable | |
| created_at | timestampTz | |

Unique: `(reference_type, reference_id, reason)` como guarda de idempotência (webhook repetido não baixa duas vezes).

Discussão sobre `direction`:
- Pergunta: não é mais legível ter `qty_in`/`qty_out` em vez de qty negativa? Resposta: duas colunas criam
  estado inválido (as duas preenchidas, as duas zero) e obrigam `COALESCE` em todo SUM.
- Decisão: `qty` signed + `direction` como coluna gerada **stored** (não virtual) para legibilidade em consultas
  e no Filament. Redundância controlada: o banco calcula, a aplicação só lê.
- Risco de coluna stored: não dá para `ALTER` a expressão, só drop + recreate; não aceita default nem ser
  escrita. Para esta coluna isso é irrelevante, então mantém. Se um dia mudar, migration de drop/recreate.
- Sem enum nativo: `direction` e `reason` são `string`.

**`stock_reservations`**

| coluna | nota |
|---|---|
| order_id, product_id, qty | |
| status | `active`, `fulfilled`, `released` (string + enum PHP) |
| expires_at | timestampTz |
| created_at / updated_at | |

- Criada no checkout, antes do pagamento. Não toca no ledger.
- Pagamento confirmado → `fulfilled` + movimento `sale` no ledger (ou na expedição, ver aberto).
- Cancelamento / expiração → `released`. Nada no ledger.
- **Expiração lazy**: `reserved` conta só `active AND expires_at > now()`. Job no scheduler marca `released`
  periodicamente para limpar, mas a correção não depende do job rodar.

**`stock_levels`** (cache de saldo, uma linha por produto)

| coluna | nota |
|---|---|
| product_id | PK/unique |
| on_hand | saldo físico = SUM(stock_movements.qty) |
| reserved | SUM(reservations active não expiradas) |
| available | gerado: `on_hand - reserved` |
| reorder_point | estoque mínimo para alerta (o que Richard chamava de "SKU" no início) |

- Atualizada **na mesma transação** que insere no ledger, com `lockForUpdate`.
- Para evitar deadlock em pedidos com vários itens: travar as linhas **ordenadas por `product_id`**.
- Pode ser recomputada do ledger a qualquer momento (`stock:rebuild`).

### 4.4 Reservations vs Redis

Pergunta: a reserva serve como garantia em vez de um Redis? Sim. A garantia é a transação + lock de linha no
Postgres. Redis fica para cache, filas, rate limit e sessão. Nunca para correção de estoque: um Redis que cai
ou reinicia perde reservas, e aí vende sem lastro.

### 4.5 Simulação (2 dias) registrada no artefato

- Compra de 20 brigadeiros → `purchase +20`, balance_after 20.
- Pedido web A (3 un.) → reserva active (expira em 30 min). Pago → `sale -3`, balance_after 17, reserva fulfilled.
- Pedido web B (5 un.) → reserva active. Cliente desiste / Pix expira → reserva released. Ledger intocado. Saldo 17.
- Venda no balcão de 2 → pedido com `channel = pos` (ADR 5) e `sale -2`, balance_after 15.
- Contagem física encontra 14 → `adjustment -1`, nota "quebra", balance_after 14.

---

## 5. ADR 3 — Snapshot nas linhas do pedido, sem versionamento de produto

**Contexto.** Se o produto muda de preço, nome ou descrição, o pedido antigo precisa continuar mostrando o que foi
vendido. Opção A: tabela `product_versions` e `order_items.product_version_id`. Opção B: snapshot na linha.

**Discussão.** Richard perguntou se "gravar o histórico com a versão do produto resolve". Resolve a descrição,
mas cria um join a mais em todo relatório, obriga a criar versão a cada edição (inclusive correção de typo) e
não cobre variações de preço por cupom/canal.

**Decisão (2026-11-02): snapshot na linha, sem tabela de versão.**
`order_items`: `product_id` (FK, para análise), `sku`, `name`, `unit_price_cents`, `quantity`, `discount_cents`,
`total_cents`, `product_snapshot` jsonb opcional (descrição, imagem, atributos na hora da venda).
Histórico de edição do produto fica no `activitylog`, não no modelo de domínio.

**Glossário — SKU.** *Stock Keeping Unit*: código único que identifica um produto (ou variante) no catálogo,
ex. `BRIG-TRAD-UN`. Não é estoque mínimo (isso é `reorder_point`) e não é por local de estoque: um mesmo SKU
pode ter saldo em vários locais. Produto também terá `barcode` (EAN) opcional. Sem variantes: cada cor é um produto (decidido 2026-11-03).

---

## 6. ADR 4 — Pedido: status dividido, snapshots de cliente e endereço

- `orders.payment_status`: `pending`, `paid`, `failed`, `refunded`, `expired`.
- `orders.fulfillment_status`: `pending`, `preparing`, `ready`, `shipped`, `delivered`, `cancelled`.
- `orders.delivery_method`: `delivery`, `pickup`.
- Timestamps de transição: `paid_at`, `shipped_at`, `delivered_at`, `cancelled_at`.
- `orders.number` gravado (ex. `VV-2026-000123`), não derivado do id.
- `orders.customer_id` **nullable**; pedido guarda snapshot do cliente (`customer_name`, `customer_email`,
  `customer_phone`). Visitante e WhatsApp não geram conta fake.
- Endereço de entrega é snapshot no pedido (`shipping_*`); `pickup` não tem endereço.
- `order_status_histories` gravada pela aplicação (não por trigger).
- `orders.seller_id`: FK users, **nullable, criado desde já** (decidido 2026-11-04). Preenchido no balcão quando
  houver quem informe; ranking e comissão ficam para quando o stakeholder pedir. Não vira regra obrigatória.

---

## 7. ADR 5 — Venda física é um pedido, não um ajuste de estoque

**Contexto.** O documento acadêmico original (UC04, Cenário 3) trata a venda presencial como o administrador
abrindo o produto e digitando o estoque novo ("de 20 para 15"). Isso apaga o motivo da saída e é a maior fonte de
divergência entre estoque físico e online, risco que o próprio documento classificou como alto.

**Decisão (Richard, 2026-11-03): "é registrar uma venda mesmo, não faz sentido baixa de estoque na mão".**
- Venda de balcão vira um `order` com `channel = pos`, itens, totais e pagamento como qualquer outro pedido.
- O ledger recebe `sale`, com `reference` apontando para o pedido. `adjustment` fica só para contagem, quebra e erro.
- `on_hand` nunca é editável diretamente no Filament. Existem duas ações: "registrar venda" (cria pedido) e
  "contagem / ajuste de estoque" (usuário informa o contado, sistema grava `adjustment` com a diferença e um motivo).
- `orders.channel`: `web`, `pos`, `whatsapp` (string + enum PHP). Decorre desta decisão.

**Consequências.** Relatórios de vendas por canal saem do mesmo lugar (`orders`), sem somar ajustes. O balcão
precisa de uma tela rápida de pedido no painel (busca por SKU/nome, quantidade, forma de pagamento, concluir).
Pedido `pos` nasce já `paid` e `delivered`, sem endereço, sem frete.

**Saldo negativo no balcão: permitido (Richard, 2026-11-03, "se não vamos atrapalhar as vendas").**
Cenário: o produto está na prateleira mas o sistema diz 0 (entrada do fornecedor não lançada, erro de contagem).
Pedido `pos` passa, `on_hand` fica negativo e o painel mostra alerta "estoque negativo, lance a entrada ou faça
contagem". Consequências: sem CHECK `on_hand >= 0` no banco; a regra "site não vende sem saldo" fica no serviço
de reserva (`available > 0`), nunca no banco; `stock_levels.on_hand < 0` é um sinal de divergência a tratar, não um bug.
Site e WhatsApp continuam bloqueando: o cliente remoto não vê a prateleira.

**Ainda em aberto:** `orders.seller_id` (quem vendeu) depende de o stakeholder querer ranking/comissão.

## 8. ADR 6 — Catálogo plano, atributos para filtro, busca por texto

**Contexto.** Os protótipos do TCC filtram a listagem por "Paleta de Cores" e "Tipo de produto" e têm um
"seletor de variações" na página do produto. O DER deles ignora isso (`produtos.estoque` é um número só).

**Decisão 1 (2026-11-03): catálogo plano, sem `product_variants`.** Cada cor é um produto com SKU e saldo
próprios ("por mais que seja um balão, não deixa de ser outro produto"). Ledger, reservas e `order_items` apontam
sempre para `products`. No Filament, ação "duplicar produto" para cadastrar cores em série.

**Decisão 2 (2026-11-03): atributos genéricos, opção completa.** Coluna `color` em `products` descartada como
gambiarra (amanhã vem tamanho, material, e cada um vira coluna nula para metade do catálogo). Tags tipadas
(`spatie/laravel-tags`) descartadas por serem texto livre. `ILIKE` descartado: é busca, não filtro, e não usa índice.

| tabela | colunas | restrições |
|---|---|---|
| `attributes` | id, name, slug | unique slug |
| `attribute_values` | id, attribute_id, value, slug | unique (attribute_id, slug) |
| `attribute_value_product` | attribute_value_id, product_id | unique (attribute_value_id, product_id), índice em product_id |

Dados de exemplo: Cor {Azul, Rosa, Dourado}, Tipo {Metalizado, Látex}; produtos `BAL-MET-9-AZ`, `BAL-MET-9-RS`,
`BAL-LTX-11-AZ`, `PRT-DESC-AZ`. O prato tem Cor e não tem Tipo: o atributo não se aplica, e a coluna solta não
expressa isso.

Regra de filtro: valores do mesmo atributo são OU, atributos diferentes são E.

```sql
-- Cor = Azul e Tipo = Metalizado
select p.* from products p
where exists (select 1 from attribute_value_product avp
              where avp.product_id = p.id and avp.attribute_value_id in (1))
  and exists (select 1 from attribute_value_product avp
              where avp.product_id = p.id and avp.attribute_value_id in (4));

-- lateral de filtros com contagem
select a.name, av.value, count(*)
from attribute_value_product avp
join attribute_values av on av.id = avp.attribute_value_id
join attributes a on a.id = av.attribute_id
join products p on p.id = avp.product_id and p.is_active
group by a.name, av.value order by 1, 2;
```

**Busca por texto é outra ferramenta.** `$table->fullText(['name','description'])->language('portuguese')` na
migration (índice GIN sobre `to_tsvector`), `Product::whereFullText([...], $termo)` no Eloquent. Nunca `ILIKE`.

**Consequências.** Novo atributo é uma linha no painel, sem migration. Eloquent: `Product belongsToMany
AttributeValue`, `AttributeValue belongsTo Attribute`; filtro vira `whereHas` por atributo marcado. Filament: um
`Select` múltiplo por atributo no produto e um resource simples de atributos/valores. O "seletor de variações"
vira navegação entre produtos irmãos, se um dia for pedido.

---

## 9. ADR 7 — Identidades do cliente em modelo próprio

**Contexto.** A atendente precisa achar o cliente pelo telefone (RF013). Um cliente pode ter mais de um número,
pode trocar de número, e no futuro pode chegar por outras plataformas (Instagram, marketplace). Com `phone` e
`email` como colunas únicas em `customers`, trocar de número apaga o histórico de busca e um segundo número não
cabe. O erro do repo atual era criar conta com senha fake, não ter o registro do cliente.

**Decisão (Richard, 2026-11-04): modelo externo de identidade.**

| coluna | nota |
|---|---|
| id | |
| customer_id | FK customers |
| type | string + enum PHP: `phone`, `email`, `whatsapp`, `instagram`, ... |
| value | normalizado (telefone em E.164, email em minúsculas) |
| source | string + enum PHP: de onde veio (`web`, `pos`, `whatsapp`, `instagram`, `import`) |
| is_primary | bool, um por tipo por cliente (unique parcial) |
| verified_at | timestampTz nullable |
| retired_at | timestampTz nullable: número antigo não é apagado, é aposentado |
| created_at / updated_at | |

- Unique parcial `(type, value) WHERE retired_at IS NULL`: um número ativo pertence a um cliente só; um número
  aposentado pode ser reatribuído a outra pessoa no futuro sem conflito.
- **Troca de número:** aposenta a identidade antiga, insere a nova. Os pedidos continuam apontando para
  `customer_id`, então o histórico não se perde; o snapshot do pedido guarda o telefone usado na época.
- **Busca por telefone:** `identities → customer`, incluindo aposentadas (com indicação), para a atendente
  reconhecer "esse era o número antigo da Carla".
- `customers` fica com dados da pessoa: nome, cpf (nullable, unique parcial), `password` nullable, timestamps.
  Cliente de WhatsApp existe sem senha; ativa a conta depois pelo email ou telefone já cadastrado.
- Snapshot no pedido (`customer_name`, `customer_email`, `customer_phone`) continua, com `customer_id` preenchido
  sempre que o cliente for identificado.

**Escopo esclarecido (2026-11-08):** `customer_identities` é sobre **contatos e canais** (mais de um número de
WhatsApp, troca de número, outra plataforma no futuro), não sobre credencial. Login da loja continua padrão Laravel:
`customers.email` + `customers.password` (ambos nullable; cliente de WhatsApp existe sem login). Sem `UserProvider`
custom. Se um dia houver "login por WhatsApp", a identidade vira credencial como decisão separada.

---

## 10. Cruzamento com o documento de requisitos do TCC (2026-11-03)

Fonte: `~/Downloads/3_-_LabEngSW_AulaRemota_14_10.docx` (visão, requisitos, casos de uso, protótipos, classes,
DER, diagramas de sequência). Ele responde parte das perguntas abertas, erra em outras e adiciona itens.

### O que ele responde

| Pergunta | Resposta no documento | Leitura |
|---|---|---|
| Baixa de estoque quando? | No pagamento confirmado (UC02 passo 5, `confirmarPagamento`) | **Aceito.** A reserva cobre a janela entre pedido e Pix |
| Segundo local de estoque? | Não. Estoque único integrado físico + online (Feature 5, riscos) | **Aceito.** Confirma ledger simples sem `location_id` |
| Variantes? | Protótipos filtram por cor e têm seletor de variações; DER ignora | Resolvido com catálogo plano + atributos (ADR 6) |
| Venda física? | Admin digita o estoque novo (UC04, Cenário 3) | **Rejeitado.** Virou pedido `pos` (ADR 5) |
| Canal e vendedor? | Não existe, mas a dona pede "produtos mais vendidos, ticket médio" e ver "todos os pedidos (WhatsApp + site)" | Justifica `channel`. `seller_id` segue sem respaldo |

### Onde o documento está errado ou fraco

- **Pagamento 1:0..1** com unique em `pedido_id`. Quebra no primeiro Pix que expira e o cliente tenta no cartão. Mantido 1:N.
- **`cliente_id` e `endereco_entrega_id` obrigatórios** no pedido. Contradiz o próprio `tipo_entrega` com retirada;
  o UC03 "cadastrar o cliente na hora" é o que gerou as senhas fake no repo. Snapshot no pedido resolve.
- **CPF obrigatório e único** inviabiliza cliente de WhatsApp e visitante. Nullable com unique parcial (`WHERE cpf IS NOT NULL`).
- **Dinheiro em numeric, enums nativos, status único.** Já decidido diferente. "Pendente" = "aguardando pagamento"
  reforça separar `payment_status` de `fulfillment_status`.

### O que ele adiciona ao modelo

- **Cupom** (Tela 4) e **preço à vista no Pix** (Telas 2 e 3): confirmam `coupons` e `order_adjustments`. Desconto Pix é ajuste, não segundo preço.
- **`preco_promocional`** → `products.promo_price_cents` nullable. O snapshot na linha guarda o cobrado.
- **Código de rastreio e frete nomeado** ("Sedex Expresso", "Frete Vevey Grátis") → `orders.tracking_code`, `orders.shipping_method`.
- **Busca de cliente por telefone** (RF013) → índice em `customers.phone`. Motiva a decisão aberta de cliente sem senha.
- **Histórico de status com responsável** (RNF011) → `order_status_histories.user_id`. Já previsto.
- **Carrinho persistente** (RF003) sem tabela no DER. Se for entre dispositivos, precisa de `carts`.
- **Lista de desejos e avaliações** nos protótipos, em nenhum requisito. Decoração até alguém pedir (`wishlists`, `reviews`).
- **Papéis:** documento tem Atendente e Administrador; repo tem três. Alinhar.
- RNF007 (bloqueio após 5 falhas de login) → RateLimiter do Laravel, sem coluna.

### Pedido por WhatsApp

UC03 põe a atendente montando o pedido no painel e gerando link de pagamento. O stakeholder quer automatizar.
Leituras possíveis: carrinho por link que cai no checkout normal (`carts` com token e `created_by_user_id`),
bot na API oficial do WhatsApp, ou só notificações automáticas. **Adiado em 2026-11-03**, fica para o futuro.
Regra que fica: pedido nasce sempre pelo mesmo serviço, seja a origem o site, um link ou o balcão; canal é atributo.

---

## 11. Estado das decisões

| Tema | Estado |
|---|---|
| Triggers no banco | decidido: nenhum, tudo em aplicação |
| Enums no banco | decidido: nunca; string + enum PHP |
| Nomenclatura | decidido: convenção Laravel, inglês |
| Imagens | decidido: spatie/laravel-medialibrary |
| Snapshot vs versão de produto | decidido: snapshot na linha |
| Direção no ledger | decidido: qty com sinal, direction stored, balance_after |
| Dinheiro em centavos (ADR 1) | **decidido 04/11** |
| Ledger simples (ADR 2) | **decidido 04/11** |
| Canal de venda | decidido 03/11: `orders.channel` web, pos, whatsapp |
| Venda física | decidido 03/11: é pedido `pos`, nunca baixa na mão |
| Saldo negativo no balcão | decidido 03/11: sim, com alerta; site e WhatsApp não |
| Saída de estoque | decidido 03/11: no pagamento confirmado |
| Variantes | decidido 03/11: catálogo plano, cada cor é um produto |
| Filtro por cor e tipo | decidido 03/11: atributos genéricos; busca por texto com GIN |
| Segundo local de estoque | decidido 03/11: não, estoque único |
| Pedido por WhatsApp | adiado 03/11 |
| `seller_id` | decidido 04/11: coluna nullable desde já; uso em ranking/comissão fica para o stakeholder |
| Cliente sem senha / identidades | decidido 04/11: `customer_identities` (ADR 7), `password` nulo permitido |
| Arquitetura | decidido 04/11: monólito modular, detalhes depois |
| Autorização | decidido 04/11: RBAC com módulo que Richard vai compartilhar |
| Login da loja | **decidido 2026-11-08**: padrão Laravel em `customers.email`/`password`; identidades são só contatos |

---

## 12. Esboço do schema alvo (nomes)

```
users (+ tabelas do módulo RBAC), customers, customer_identities, customer_addresses
categories, products, category_product
attributes, attribute_values, attribute_value_product
stock_movements, stock_reservations, stock_levels
orders, order_items, order_adjustments, order_status_histories (carts? wishlists? reviews? só se pedidos)
payments, payment_events
coupons
media (spatie), activity_log (spatie)
```

## 13. Pacotes previstos

- `brick/money` (cast de dinheiro)
- `spatie/laravel-medialibrary` (imagens)
- `spatie/laravel-activitylog` (auditoria de produto)
- Filament (painel staff), scheduler do Laravel (expiração de reservas)
- Postgres com `timestampTz` e colunas geradas via `->storedAs(...)`
