---
version: 1.0.0
name: Vevey Doces — Design System
brand: Vevey Doces
theme: Festive Confectionery & Party Commerce
inspirations:
  - "Airbnb (vitrine photo-first, geometria suave, busca em pílula, elevação disciplinada)"
  - "Starbucks (calor de varejo alimentício, faixas editoriais creme/escuras, foco apetitoso em produto)"
  - "Stripe (precisão em valores monetários com tabular-nums, hierarquia limpa para o painel admin)"
scopes:
  - storefront (Laravel + Blade/Tailwind CSS v4)
  - panel-admin (Filament v5 — app-modules/panel-admin)

colors:
  primary: "#E11D48"
  primary-hover: "#F43F5E"
  primary-active: "#BE123C"
  primary-soft: "#FFF1F2"
  primary-border: "#FFE4E6"
  primary-disabled: "#FDA4AF"
  primary-deep: "#4C0519"
  secondary: "#F59E0B"
  secondary-hover: "#D97706"
  secondary-soft: "#FFFBEB"
  secondary-border: "#FEF3C7"
  tertiary: "#8B5CF6"
  tertiary-hover: "#7C3AED"
  tertiary-soft: "#F5F3FF"
  tertiary-border: "#EDE9FE"
  canvas: "#FFFFFF"
  canvas-warm: "#FFFBF8"
  canvas-soft: "#F9FAFB"
  surface-subtle: "#F3F4F6"
  surface-dark: "#1F2937"
  surface-dark-deep: "#111827"
  ink: "#111827"
  body: "#1F2937"
  muted: "#6B7280"
  muted-soft: "#9CA3AF"
  hairline: "#E5E7EB"
  hairline-soft: "#F3F4F6"
  border-strong: "#D1D5DB"
  on-primary: "#FFFFFF"
  on-dark: "#FFFFFF"
  success: "#15803D"
  success-soft: "#F0FDF4"
  warning: "#B45309"
  warning-soft: "#FFFBEB"
  danger: "#B91C1C"
  danger-soft: "#FEF2F2"
  info: "#1D4ED8"
  info-soft: "#EFF6FF"
  scrim: "#111827"

typography:
  display-xl:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 40px
    fontWeight: 800
    lineHeight: 1.12
    letterSpacing: -0.025em
  display-lg:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 30px
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: -0.02em
  heading-md:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 22px
    fontWeight: 700
    lineHeight: 1.28
    letterSpacing: -0.015em
  heading-sm:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 18px
    fontWeight: 600
    lineHeight: 1.33
    letterSpacing: -0.01em
  title-card:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 16px
    fontWeight: 600
    lineHeight: 1.35
    letterSpacing: 0
  body-md:
    fontFamily: "'Inter', system-ui, -apple-system, sans-serif"
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.55
    letterSpacing: 0
  body-sm:
    fontFamily: "'Inter', system-ui, -apple-system, sans-serif"
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.45
    letterSpacing: 0
  price-lg:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 24px
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: -0.01em
    fontFeature: tnum
  price-card:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 17px
    fontWeight: 700
    lineHeight: 1.25
    letterSpacing: 0
    fontFeature: tnum
  money-table:
    fontFamily: "'Inter', system-ui, -apple-system, sans-serif"
    fontSize: 14px
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: 0
    fontFeature: tnum
  label:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 14px
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: 0
  badge:
    fontFamily: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
    fontSize: 12px
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: 0.01em
  micro:
    fontFamily: "'Inter', system-ui, -apple-system, sans-serif"
    fontSize: 12px
    fontWeight: 500
    lineHeight: 1.35
    letterSpacing: 0.02em

rounded:
  none: 0px
  xs: 4px
  sm: 8px
  md: 12px
  lg: 16px
  xl: 24px
  full: 9999px

spacing:
  xxs: 2px
  xs: 4px
  sm: 8px
  md: 12px
  base: 16px
  lg: 24px
  xl: 32px
  xxl: 48px
  section: 64px

components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
    padding: 12px 20px
    height: 44px
  button-primary-pill:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.label}"
    rounded: "{rounded.full}"
    padding: 10px 22px
    height: 44px
  button-secondary:
    backgroundColor: "{colors.surface-subtle}"
    textColor: "{colors.body}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
    padding: 12px 20px
    height: 44px
  button-outlined:
    backgroundColor: transparent
    textColor: "{colors.body}"
    borderColor: "{colors.border-strong}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
    padding: 11px 19px
    height: 44px
  search-bar-pill:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.body}"
    borderColor: "{colors.hairline}"
    rounded: "{rounded.full}"
    height: 52px
  product-card:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline}"
    rounded: "{rounded.md}"
---

# DESIGN.md — Vevey Doces

> **Tema:** Festive Confectionery & Party Commerce  
> **Documento de referência visual para agentes de IA** (formato [Google Stitch / Awesome DESIGN.md](https://github.com/VoltAgent/awesome-design-md)).  
> Evolui o guia original em [`docs/design_system.md`](docs/design_system.md) para cobrir tanto a **Loja Pública (E-commerce)** quanto o **Painel Administrativo (`panel-admin` em Filament)**.

---

## 1. Tema Visual & Atmosfera

A **Vevey Doces** combina o calor festivo de uma confeitaria e loja de artigos para festa com a clareza de um e-commerce moderno orientado a fotografia. A interface nunca compete com os produtos: o canvas permanece limpo — alternando entre branco puro (`#FFFFFF`) nos cards e um off-white quente/suave (`#FFFBF8` / `#F9FAFB`) nas seções — para que fotos de chocolates, doces, embalagens e itens de festa ganhem protagonismo.

A linguagem visual sintetiza três referências complementares:
- **Airbnb (vitrine e descoberta):** cards *photo-first* com cantos arredondados (`12px`), barra de busca em pílula com botão circular de ação, elevação contida em uma única sombra suave no hover e respiro generoso entre seções.
- **Starbucks (calor de varejo alimentício):** ritmo de página em blocos claros e escuros (hero luminoso → vitrine limpa → faixa promocional escura ou creme quente → rodapé ancorado em carvão), com micro-interações táteis (`active:scale-[0.98]`).
- **Stripe (precisão transacional e painel admin):** alinhamento numérico impecável com `tabular-nums` (`font-variant-numeric: tabular-nums`) em todos os preços em BRL (`R$ 49,90`), tabelas escaneáveis e superfícies neutras no Filament para reduzir fadiga operacional da equipe.

### Características-Chave
- **Um acento dominante com disciplina:** **Carmim Cereja (`#E11D48`)** carrega CTAs principais, orbe de busca, badges de oferta e estados ativos. Cerca de 85–90% da tela permanece em superfícies claras + tipografia Carvão (`#1F2937` / `#111827`).
- **Papéis claros para secundária e terciária:** **Âmbar Caramelo (`#F59E0B`)** é reservado a avaliações, alertas de estoque baixo, status "em preparação" e detalhes sazonais; **Violeta Confeitaria (`#8B5CF6`)** marca novidades, kits festa/premium e cupons VIP.
- **Geometria acolhedora:** nenhum canto vivo em elementos interativos. Botões padrão em `8px` (`rounded-lg`), CTAs de destaque e busca em `9999px` (`rounded-full`), cards em `12px` (`rounded-xl`).
- **Duas densidades, mesma marca:** a **Loja** privilegia fotografia e descoberta leve; o **Painel Admin (Filament)** prioriza densidade operacional, leitura rápida de SKUs, saldos de estoque e valores financeiros sem perder o DNA cromático da marca.

---

## 2. Paleta de Cores & Papéis

### 2.1 Marca & Acentos

| Papel | Nome | HEX | Uso Funcional |
| :--- | :--- | :--- | :--- |
| **Primary (Base)** | Carmim / Cereja Festivo | `#E11D48` | CTAs principais ("Adicionar ao carrinho", "Finalizar pedido"), orbe de busca, links ativos, marca. |
| **Primary Hover** | Carmim Vivo | `#F43F5E` | Estado `:hover` de botões primários e destaques interativos. |
| **Primary Active** | Carmim Intenso | `#BE123C` | Estado `:active` / pressionado de botões primários. |
| **Primary Soft** | Rosa Glacê | `#FFF1F2` | Fundos sutis de banners de promoção, item de menu ativo, hover leve. |
| **Primary Border** | Rosa Claro | `#FFE4E6` | Bordas de badges suaves, divisores em alertas da marca. |
| **Primary Deep** | Vinho Cereja | `#4C0519` | Texto de alto contraste sobre fundos `#FFF1F2` / `#FFE4E6`. |
| **Secondary (Base)** | Âmbar / Dourado Caramelo | `#F59E0B` | Estrelas de avaliação, status pendente/preparação, selos festivos e estoque baixo. |
| **Secondary Hover** | Âmbar Queimado | `#D97706` | Hover sobre elementos secundários e destaque de ícones âmbar. |
| **Secondary Soft** | Creme Baunilha | `#FFFBEB` | Fundo de avisos suaves, faixa de frete grátis quase atingido. |
| **Tertiary (Base)** | Violeta Confeitaria | `#8B5CF6` | Tags "Novidade", "Kit Festa", cupons exclusivos e benefícios VIP. |
| **Tertiary Soft** | Lavanda Suave | `#F5F3FF` | Fundo de badges de cupons e destaques editoriais. |

### 2.2 Escalas Completas (Tokens)
- **Primary (`brand`):** `50 #FFF1F2` · `100 #FFE4E6` · `200 #FECDD3` · `300 #FDA4AF` · `400 #FB7185` · `500 #F43F5E` · `600 #E11D48` · `700 #BE123C` · `800 #9F1239` · `900 #4C0519`
- **Secondary (`amber`):** `50 #FFFBEB` · `100 #FEF3C7` · `200 #FDE68A` · `300 #FCD34D` · `400 #FBBF24` · `500 #F59E0B` · `600 #D97706` · `700 #B45309` · `800 #92400E` · `900 #78350F`
- **Tertiary (`violet`):** `50 #F5F3FF` · `100 #EDE9FE` · `200 #DDD6FE` · `300 #C4B5FD` · `400 #A78BFA` · `500 #8B5CF6` · `600 #7C3AED` · `700 #6D28D9` · `800 #5B21B6` · `900 #4C1D95`

### 2.3 Superfícies, Neutros & Texto

| Papel | HEX | Uso |
| :--- | :--- | :--- |
| **Canvas (Card/Modal)** | `#FFFFFF` | Cards de produto, modais, drawer de carrinho, inputs em foco, formulários. |
| **Canvas Warm (Loja)** | `#FFFBF8` | Fundo de seções editoriais e hero da loja (tom quente inspirado em confeitaria). |
| **Canvas Soft (Geral/Admin)** | `#F9FAFB` | Fundo padrão de listagens e área de conteúdo do painel administrativo. |
| **Surface Subtle** | `#F3F4F6` | Placeholders de imagem, botões secundários, fundos de tabela zebrada/hover. |
| **Hairline (Borda Padrão)** | `#E5E7EB` | Divisores, bordas de cards, linhas de tabela, borda padrão de inputs. |
| **Border Strong** | `#D1D5DB` | Botões outlined, bordas de inputs com maior contraste. |
| **Muted Soft** | `#9CA3AF` | Ícones inativos, placeholders, textos desabilitados. |
| **Muted (Apoio)** | `#6B7280` | Legendas, SKU, categorias secundárias, datas, rótulos de coluna. |
| **Body (Leitura)** | `#1F2937` | Corpo de texto padrão, descrições de produto, itens de menu. |
| **Ink (Títulos)** | `#111827` | Títulos (`h1`–`h3`), preços principais, cabeçalhos de tabela. |
| **Surface Dark** | `#1F2937` | Rodapé da loja, faixas promocionais invertidas, botões invertidos. |

### 2.4 Semântica (Distinta do Carmim de Marca)
> **Regra crítica:** Nunca usar o Carmim da marca (`#E11D48`) para mensagens de erro de formulário ou exclusão destrutiva — usar o vermelho semântico `#B91C1C` para que "ação de compra" e "erro/perigo" sejam inconfundíveis.

- **Sucesso (Pago / Entregue / Em estoque):** texto `#15803D`, fundo `#F0FDF4`, borda `#BBF7D0`
- **Alerta (Pendente / Estoque baixo / Em separação):** texto `#B45309`, fundo `#FFFBEB`, borda `#FDE68A`
- **Erro / Destrutivo (Cancelado / Sem estoque / Excluir):** texto `#B91C1C`, fundo `#FEF2F2`, borda `#FECACA`
- **Info (Em transporte / Aguardando retirada):** texto `#1D4ED8`, fundo `#EFF6FF`, borda `#BFDBFE`

### 2.5 Mapeamento para o Painel Admin (Filament)
No `AdminPanelProvider` (`app-modules/panel-admin`):
- **Primary:** `Color::Rose` (escala alinhada ao `#E11D48` da marca) ou `Color::Amber` (se mantido o perfil atual de operação quente — ao atualizar a identidade do painel, preferir `Color::hex('#E11D48')` para unificar loja e admin).
- **Gray / Chrome:** `Color::Slate` / `Color::Gray` para sidebar, tabelas e superfícies neutras.
- **Warning:** `Color::Amber` · **Success:** `Color::Emerald` · **Danger:** `Color::Red` · **Info:** `Color::Violet` (ou `Color::Blue` para status logísticos).

---

## 3. Regras de Tipografia

### 3.1 Famílias & Importação
- **Headline & Labels:** `'Plus Jakarta Sans', system-ui, -apple-system, sans-serif` — geométrica, amigável e moderna. Usada em títulos, preços, botões, badges e navegação.
- **Body & UI:** `'Inter', system-ui, -apple-system, sans-serif` — legibilidade máxima em descrições, especificações, formulários e tabelas densas.

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
```

### 3.2 Hierarquia Tipográfica

| Papel | Fonte | Tamanho | Peso | Line Height | Tracking | Uso |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Display XL (Hero)** | Plus Jakarta Sans | `40px` (`2.5rem`) | `800` | `1.12` | `-0.025em` | Manchete principal do hero (mobile: `28px`) |
| **Display LG (Página)** | Plus Jakarta Sans | `30px` (`1.875rem`) | `700` | `1.20` | `-0.02em` | Título de categoria, nome na página de produto |
| **Heading MD (Seção)** | Plus Jakarta Sans | `22px` (`1.375rem`) | `700` | `1.28` | `-0.015em` | Títulos de vitrine ("Mais vendidos", "Monte sua festa") |
| **Heading SM** | Plus Jakarta Sans | `18px` (`1.125rem`) | `600` | `1.33` | `-0.01em` | Subtítulos de blocos, cabeçalhos de modal/drawer |
| **Title Card** | Plus Jakarta Sans | `16px` (`1rem`) | `600` | `1.35` | `0` | Nome do produto no card (máx. 2 linhas com `line-clamp-2`) |
| **Price LG (PDP)** | Plus Jakarta Sans | `24px` (`1.5rem`) | `700` | `1.20` | `-0.01em` | Preço na página de produto (`tabular-nums`) |
| **Price Card** | Plus Jakarta Sans | `17px` (`1.0625rem`) | `700` | `1.25` | `0` | Preço no card de produto (`tabular-nums`) |
| **Body MD** | Inter | `16px` (`1rem`) | `400` | `1.55` | `0` | Descrições de produto, textos institucionais |
| **Body SM** | Inter | `14px` (`0.875rem`) | `400` | `1.45` | `0` | Metadados, inputs, linhas de tabela, itens do carrinho |
| **Money Table** | Inter | `14px` (`0.875rem`) | `500` | `1.40` | `0` | Colunas monetárias no Filament e resumo de checkout (`tabular-nums`) |
| **Label / Botão** | Plus Jakarta Sans | `14px` (`0.875rem`) | `600` | `1.25` | `0` | Rótulos de botões, abas, links de navegação |
| **Badge / Chip** | Plus Jakarta Sans | `12px` (`0.75rem`) | `600` | `1.20` | `+0.01em` | Selos de status, tags de categoria, descontos |
| **Micro / SKU** | Inter | `12px` (`0.75rem`) | `500` | `1.35` | `+0.02em` | Código SKU, legendas auxiliares, rótulos em caixa alta |

### 3.3 Princípios Tipográficos
- **`tabular-nums` obrigatório em dinheiro e quantidades:** todo preço (`R$ 29,90`), subtotal, desconto, frete e saldo de estoque deve usar `tabular-nums` para evitar "salto" visual em tabelas, carrinhos e contadores.
- **Nunca usar preto puro (`#000000`):** títulos usam `#111827` e corpo `#1F2937` para manter conforto visual sobre fundos claros/quentes.
- **Títulos concisos sobre fotografia:** nos cards de vitrine, a foto carrega o peso visual; o título fica em `16px / 600` limitado a 2 linhas.

---

## 4. Estilização de Componentes

### 4.1 Loja / E-commerce (Storefront)

#### Botões
- **Primary (Ação principal):** `bg-[#E11D48] hover:bg-[#F43F5E] active:bg-[#BE123C] active:scale-[0.98] text-white font-semibold rounded-lg px-5 py-2.5 min-h-[44px] transition-all`  
  *Uso:* Adicionar ao carrinho, Finalizar compra, Confirmar pedido.
- **Primary Pill (Destaque / Hero):** mesmo preenchimento com `rounded-full px-6`.
- **Secondary:** `bg-[#F3F4F6] hover:bg-[#E5E7EB] text-[#1F2937] font-semibold rounded-lg px-5 py-2.5 min-h-[44px] transition-all`  
  *Uso:* Voltar, Cancelar, Ver detalhes.
- **Outlined:** `border border-[#D1D5DB] hover:border-[#E11D48] text-[#1F2937] hover:text-[#E11D48] bg-transparent font-semibold rounded-lg px-5 py-2.5 min-h-[44px] transition-all`  
  *Uso:* Aplicar cupom, Calcular frete, Ações em cards de pedido.
- **Inverted (Sobre fundo escuro / Rodapé):** `bg-[#1F2937] hover:bg-[#111827] text-white font-semibold rounded-lg px-5 py-2.5 min-h-[44px] transition-all`.
- **Disabled:** `bg-[#FDA4AF] text-white cursor-not-allowed opacity-80` (sem hover ou scale).

#### Header & Barra de Busca Pill (Inspiração Airbnb)
- **Top Nav:** superfície `#FFFFFF`, altura `72px` (`80px` desktop), borda inferior `1px solid #E5E7EB`, sticky no topo. Logo à esquerda, busca pill centralizada, conta + carrinho com contador Carmim à direita.
- **Barra de Busca Pill:** `bg-white border border-[#E5E7EB] focus-within:border-[#E11D48] rounded-full h-[52px] pl-5 pr-1.5 flex items-center shadow-sm`, terminando em um **orbe circular Carmim** (`40px × 40px`, `bg-[#E11D48] text-white rounded-full`) com ícone `search`.
- **Faixa de Categorias:** rolagem horizontal suave abaixo do header com chips de categoria (`rounded-full`, fundo `#F9FAFB`, borda `#E5E7EB`; ativo com fundo `#FFF1F2`, texto `#E11D48` e borda `#FECDD3`).

#### Card de Produto (*Photo-First*)
- **Container:** `bg-white rounded-xl border border-[#E5E7EB] overflow-hidden transition-all` com elevação suave no hover (`hover:shadow-[0_6px_16px_rgba(17,24,39,0.08)] hover:-translate-y-0.5`).
- **Área da Foto:** proporção fixa `aspect-square` (`1:1`), fundo `#F3F4F6` como placeholder, imagem `object-cover` (WebP gerada pelo `spatie/laravel-medialibrary`).
  - *Canto superior esquerdo:* badge flutuante `rounded-full` — ex.: `"Mais vendido"` (branco com sombra leve e texto `#111827`), `"-15%"` (`bg-[#E11D48] text-white`), `"Novidade"` (`bg-[#8B5CF6] text-white`).
  - *Canto superior direito:* botão circular de favorito (`36px × 36px`, `bg-white/90 backdrop-blur-sm rounded-full`).
- **Bloco de Metadados (`p-4`):**
  1. Categoria em `micro` (`12px`, `#6B7280`).
  2. Nome do produto em `title-card` (`16px / 600`, `#111827`, `line-clamp-2`).
  3. Linha de preço em `price-card` (`17px / 700`, `tabular-nums`, `#111827`) + preço antigo riscado opcional (`13px`, `#9CA3AF`, `line-through`).
  4. Botão compacto de adicionar ao carrinho (`w-full mt-3` ou ícone `+` rápido no mobile).

#### Inputs, Barras de Progresso & Badges
- **Inputs de Formulário:** `bg-[#F9FAFB] border border-[#E5E7EB] focus:border-[#E11D48] focus:bg-white rounded-lg px-3.5 py-2.5 text-sm text-[#1F2937] outline-none transition-colors`. Estado de erro usa borda `#B91C1C` e texto auxiliar `#B91C1C` abaixo.
- **Barras de Progresso (Frete grátis / Status de pedido):** trilha `bg-[#F3F4F6] h-1.5 rounded-full`, preenchimento `#E11D48` (checkout/entrega), `#F59E0B` (preparação/estoque baixo) ou `#8B5CF6` (benefícios).
- **Drawer de Carrinho:** painel lateral direito (`max-w-[420px]`), fundo `#FFFFFF`, backdrop `#111827/50`, lista rolável com stepper de quantidade (`- / qtd / +`), barra de frete grátis no topo e resumo fixo no rodapé com subtotal em `tabular-nums` + CTA Primary full-width.

---

### 4.2 Painel Administrativo (Filament — `app-modules/panel-admin`)

O painel segue os padrões nativos do **Filament**, evitando CSS customizado desnecessário e priorizando consistência operacional:
- **Layout & Navegação:** Sidebar fixa à esquerda (`260px` no desktop, colapsável), fundo neutro `#FFFFFF` sobre canvas `#F9FAFB`. Agrupamento lógico de recursos: **Catálogo** (Produtos, Categorias), **Vendas** (Pedidos, Clientes, Cupons), **Estoque** (Movimentações/Ledger), **Acesso** (Usuários, Papéis/Permissões RBAC).
- **Tabelas (Catalog / Orders / Stock):**
  - Miniaturas de produto via `SpatieMediaLibraryImageColumn` (`rounded-lg`, `40px × 40px`, `square`).
  - Valores monetários via `MoneyColumn` (`->money('BRL', divideBy: 100)`), **sempre alinhados à direita** e com `tabular-nums`.
  - Badges de status semânticos (nunca texto solto): `success` (Pago/Entregue), `warning` (Pendente/Separação/Estoque baixo), `info` (Enviado), `danger` (Cancelado/Sem estoque).
  - Filtros claros no topo e busca instantânea por nome/SKU.
- **Formulários:**
  - `MoneyInput` para campos `*_cents` (entrada em reais, persistência em centavos via `MoneyCast`).
  - `SpatieMediaLibraryFileUpload` com preview em grid e instrução explícita de proporção 1:1.
  - **Regra de domínio (ADR 2 — Estoque):** o saldo `on_hand` **nunca** é um input editável livre no formulário de produto; ele é exibido como leitura e alterado exclusivamente por uma **Action "Ajustar estoque"** (modal com tipo de movimentação, quantidade e motivo, gravando no ledger).
- **Widgets & KPIs:** cartões brancos com borda hairline (`1px solid #E5E7EB`), números em `tabular-nums`, variação percentual discreta em verde/vermelho semântico.

---

## 5. Princípios de Layout

### 5.1 Escala de Espaçamento
Base de **4px** com ritmo primário em múltiplos de **8px**:
`2px (xxs)` · `4px (xs)` · `8px (sm)` · `12px (md)` · `16px (base)` · `24px (lg)` · `32px (xl)` · `48px (xxl)` · `64px (section)`

### 5.2 Containers & Grids
- **Vitrine / Home / Catálogo:** `max-w-7xl` (`1280px`), centralizado, padding lateral `16px` (mobile) / `24px` (tablet) / `32px` (desktop).
- **Página de Produto (PDP) & Checkout:** `max-w-5xl` (`1080px`). Layout de 2 colunas no desktop (~58% galeria + descrição à esquerda, ~42% painel sticky de compra/resumo à direita).
- **Grid de Produtos:** `grid-cols-2` no mobile compacto (ou `1` em telas `< 380px`), `grid-cols-2` / `3` no tablet, `grid-cols-4` no desktop, com gutter de `16px` (`gap-4`) a `24px` (`gap-6`).
- **Seções Verticais:** `py-12` (`48px`) a `py-16` (`64px`) entre faixas da home.
- **Rodapé:** fundo `#1F2937` com texto claro, 4 colunas no desktop (Marca/Contato, Categorias, Ajuda, Formas de Pagamento), colapsando para 1 coluna no mobile.

### 5.3 Filosofia de Respiro
> **"Hero apetitoso e aberto; vitrine densa e escaneável abaixo."**  
As faixas editoriais e o hero recebem `48px–64px` de respiro vertical, enquanto o grid de produtos mantém `16px–20px` de espaçamento para que o cliente compare múltiplos doces e kits em uma única rolagem.

---

## 6. Profundidade & Elevação

Disciplina inspirada no Airbnb: **apenas 2 níveis de sombra** acima da base flat.

| Nível | CSS / Tailwind | Onde Usar |
| :--- | :--- | :--- |
| **0 — Flat (Base)** | `shadow-none` + `border border-[#E5E7EB]` | 90% da interface: cards em repouso, seções, formulários, tabelas do Filament. |
| **1 — Float Suave** | `box-shadow: 0 0 0 1px rgba(17,24,39,0.03), 0 4px 12px rgba(17,24,39,0.08)` | Hover em cards de produto, barra de busca pill, dropdowns, badges flutuantes sobre foto. |
| **2 — Overlay / Modal** | `box-shadow: 0 20px 40px rgba(17,24,39,0.18)` + scrim `rgba(17,24,39,0.50)` | Modais, drawer de carrinho, dialogs de ajuste de estoque. |

- Evitar sombras coloridas neon ou múltiplas camadas pesadas. A separação visual vem principalmente de contraste de superfície (`#FFFFFF` sobre `#FFFBF8`/`#F9FAFB`) e bordas hairline `#E5E7EB`.

---

## 7. O que Fazer e Não Fazer (Do's & Don'ts)

### ✅ Faça (Do's)
- **Use `tabular-nums`** em todos os valores em reais (`R$`), quantidades de estoque e totais de pedido.
- **Mantenha fotos de produto limpas em 1:1** (`aspect-square`, `object-cover`, WebP) com badges discretos nos cantos.
- **Reserve `#E11D48` para o que gera conversão ou indica seleção ativa** — não pinte grandes fundos de página inteiros de vermelho.
- **Respeite o domínio da aplicação:** preços sempre vindos do backend formatados a partir de centavos (`*_cents`); estoque ajustado apenas via ação de movimentação no ledger.
- **Garanta contraste WCAG AA (≥ 4.5:1):** texto branco sobre `#E11D48` passa; sobre `#F59E0B` (Âmbar), prefira texto escuro (`#111827` ou `#78350F`) em vez de branco.

### ❌ Não Faça (Don'ts)
- **Não misture `#E11D48` (marca) com `#B91C1C` (erro/destrutivo):** erro de validação e exclusão devem ser visualmente distintos do botão "Comprar".
- **Não crie gradientes arco-íris ou fundos saturados atrás de tabelas ou textos longos.**
- **Não use fontes genéricas não aprovadas** (como Arial ou Roboto padrão) quando `Plus Jakarta Sans` e `Inter` estiverem disponíveis.
- **Não quebre o layout nativo do Filament** com CSS inline complexo quando um componente/tema oficial do Filament resolve.
- **Não faça recalculo de totais no frontend como fonte de verdade** — a UI exibe os valores calculados pelo servidor.

---

## 8. Comportamento Responsivo

| Breakpoint | Largura | Mudanças Principais |
| :--- | :--- | :--- |
| **Mobile** | `< 640px` | Header compactado (menu hambúrguer + logo + carrinho); busca pill colapsa para botão/barra única; vitrine em 2 colunas compactas; PDP fixa barra inferior com preço + CTA "Adicionar"; Filament colapsa sidebar em drawer. |
| **Tablet** | `640px – 1024px` | Busca pill visível no header; vitrine em 3 colunas; checkout empilha resumo abaixo ou em accordion; tabelas do painel com scroll horizontal suave. |
| **Desktop** | `1024px – 1280px` | Header completo com busca pill + faixa de categorias; vitrine em 4 colunas; PDP e Checkout em 2 colunas com rail direito sticky; sidebar do Filament expandida. |
| **Wide** | `> 1280px` | Loja limitada a `1280px` centralizado (`1080px` em PDP/Checkout); margens laterais absorvem o excedente. |

### Alvos de Toque & Acessibilidade
- Botões primários, itens de navegação mobile e controles de carrinho: mínimo **`44px × 44px`** (preferencialmente `48px` em CTAs de checkout).
- Foco visível em teclado (`focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#E11D48]`).

---

## 9. Guia de Prompt & Tokens Prontos para Agentes

### 9.1 Tokens Tailwind CSS v4 (`resources/css/app.css`)
O projeto utiliza **Tailwind CSS v4** (`@tailwindcss/vite`). Para ativar os tokens no CSS da aplicação:

```css
@import 'tailwindcss';

@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-display: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;

    --color-brand-50: #fff1f2;
    --color-brand-100: #ffe4e6;
    --color-brand-200: #fecdd3;
    --color-brand-300: #fda4af;
    --color-brand-400: #fb7185;
    --color-brand-500: #f43f5e;
    --color-brand-600: #e11d48;
    --color-brand-700: #be123c;
    --color-brand-800: #9f1239;
    --color-brand-900: #4c0519;

    --color-canvas-warm: #fffbf8;
    --color-surface-dark: #1f2937;

    --radius-card: 0.75rem;
    --radius-pill: 9999px;
}
```

### 9.2 Ícones
- **Loja (Storefront):** Google **Material Symbols Outlined** (`shopping_bag`, `search`, `favorite`, `person`, `local_shipping`, `cake`, `local_offer`, `check_circle`, `support_agent`).
- **Painel Admin (Filament):** **Heroicons** padrão do Filament (`heroicon-o-shopping-bag`, `heroicon-o-cube`, `heroicon-o-tag`, `heroicon-o-users`, `heroicon-o-shield-check`).

### 9.3 Referência Rápida de Cores para Prompts
- **CTA / Acento Principal:** `#E11D48` (hover `#F43F5E`, active `#BE123C`, tint `#FFF1F2`)
- **Secundária (Alerta/Estrelas):** `#F59E0B` (tint `#FFFBEB`, texto alto contraste `#78350F`)
- **Terciária (Novidades/Cupons):** `#8B5CF6` (tint `#F5F3FF`)
- **Superfícies:** `#FFFFFF` (cards) · `#FFFBF8` (seções loja) · `#F9FAFB` (geral/admin) · `#1F2937` (rodapé/invertido)
- **Texto & Bordas:** `#111827` (títulos) · `#1F2937` (corpo) · `#6B7280` (apoio) · `#E5E7EB` (bordas)

### 9.4 Prompts Prontos para Uso
- **Nova página da loja:**
  > *"Crie a página de catálogo de produtos em Blade + Tailwind v4 seguindo rigorosamente o `DESIGN.md`: canvas `#FFFBF8` com cards brancos `#FFFFFF` (`rounded-xl`, borda `#E5E7EB`), fotos 1:1 com badge flutuante, títulos em `Plus Jakarta Sans`, preços em BRL com `tabular-nums`, busca em pílula com orbe `#E11D48` e grid responsivo 2→3→4 colunas."*
- **Nova tela no Painel Admin (Filament):**
  > *"Implemente o Resource de Pedidos no módulo `panel-admin` seguindo o `DESIGN.md`: use `MoneyColumn` alinhada à direita com `tabular-nums`, badges semânticos para status de pagamento e entrega, filtros claros e ações em modal para transições de status e ajuste de estoque via ledger."*
