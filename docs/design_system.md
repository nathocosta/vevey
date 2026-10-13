# Design System & Guia de Estilos — Vevey Doces
> **Tema:** Festive Confectionery & Party Commerce  
> **Objetivo:** Padronização visual da identidade da marca, tokens de design, paleta cromática, tipografia e componentes de interface para o e-commerce e protótipos do TCC.

---

## 1. Paleta de Cores (Color Tokens)

### Cores Principais
| Papel | Nome | HEX Principal | Descrição / Uso |
| :--- | :--- | :--- | :--- |
| **Primary** | Carmim / Cereja Festivo | `#E11D48` | Cor principal da marca, botões de ação (CTA), destaques, badges principais e links ativos. |
| **Secondary** | Âmbar / Dourado Caramelo | `#F59E0B` | Cor secundária quente, estrelas de avaliação, badges de status pendente/alerta e detalhes festivos. |
| **Tertiary** | Violeta Confeitaria | `#8B5CF6` | Acentos especiais, tags de categorias premium, novidades e elementos lúdicos. |
| **Neutral** | Carvão Escuro (Slate) | `#1F2937` | Textos principais, fundos invertidos, títulos e bordas estruturais. |

### Escalas de Tons (Color Scales)

#### 🔴 Primary (`#E11D48`)
- **50:** `#FFF1F2` (Fundos sutis, alertas suaves, hover em abas)
- **100:** `#FFE4E6` (Bordas suaves, tags leves)
- **200:** `#FECDD3`
- **300:** `#FDA4AF`
- **400:** `#FB7185`
- **500:** `#F43F5E` (Hover para botão primário)
- **600 (Base):** `#E11D48` (Cor de marca padrão)
- **700:** `#BE123C` (Estados ativos / pressionados)
- **800:** `#9F1239`
- **900:** `#4C0519` (Texto de alto contraste sobre fundos claros)

#### 🟡 Secondary (`#F59E0B`)
- **50:** `#FFFBEB`
- **100:** `#FEF3C7` (Fundos de aviso/alerta suave)
- **200:** `#FDE68A`
- **300:** `#FCD34D`
- **400:** `#FBBF24`
- **500 (Base):** `#F59E0B` (Dourado/Âmbar para status e estrelas)
- **600:** `#D97706`
- **700:** `#B45309`
- **800:** `#92400E`
- **900:** `#78350F`

#### 🟣 Tertiary (`#8B5CF6`)
- **50:** `#F5F3FF`
- **100:** `#EDE9FE`
- **200:** `#DDD6FE`
- **300:** `#C4B5FD`
- **400:** `#A78BFA`
- **500 (Base):** `#8B5CF6` (Acentos, tags de novidade, cursos)
- **600:** `#7C3AED`
- **700:** `#6D28D9`
- **800:** `#5B21B6`
- **900:** `#4C1D95`

#### ⚫ Neutral & Backgrounds (`#1F2937`)
- **Branco:** `#FFFFFF` (Superfície de cartões, formulários, modais)
- **Surface / Background:** `#F8F9FF` ou `#F9FAFB` (Fundo geral da página)
- **Gray 100:** `#F3F4F6` (Bordas secundárias, placeholders de imagem)
- **Gray 200:** `#E5E7EB` (Divisores, linhas de tabelas)
- **Gray 400:** `#9CA3AF` (Ícones secundários, textos desativados)
- **Gray 500:** `#6B7280` (Textos de apoio, legendas, códigos SKU)
- **Gray 800 (Neutral Base):** `#1F2937` (Cor padrão de leitura/texto)
- **Gray 900:** `#111827` (Títulos destacados, cabeçalhos de tabelas)

---

## 2. Tipografia (Typography System)

As fontes são importadas via Google Fonts:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
```

### Regras de Aplicação:
| Tipo | Família da Fonte | Pesos Recomendados | Onde Usar |
| :--- | :--- | :--- | :--- |
| **Headline (Títulos)** | `Plus Jakarta Sans` | `SemiBold (600)`, `Bold (700)`, `ExtraBold (800)` | Título de páginas (`h1`), nomes de produtos (`h2`), seções (`h3`), valores em destaque. |
| **Body (Corpo)** | `Inter` | `Regular (400)`, `Medium (500)` | Parágrafos, descrições longas, especificações técnicas, itens do carrinho, inputs de formulário. |
| **Label (Rótulos)** | `Plus Jakarta Sans` | `SemiBold (600)`, `Bold (700)` | Botões de ação, badges de status, chips, cabeçalhos de tabela, menus de navegação. |

---

## 3. Componentes de Interface (UI Elements)

### 3.1 Botões (Buttons)
- **Primary:**  
  `bg-[#E11D48] hover:bg-[#BE123C] text-white font-bold rounded-lg px-4 py-2 shadow-sm transition-all`  
  *Uso:* Adicionar ao carrinho, Finalizar Compra, Confirmar Pedido.
- **Secondary:**  
  `bg-gray-100 hover:bg-gray-200 text-[#1F2937] font-semibold rounded-lg px-4 py-2 transition-all`  
  *Uso:* Voltar, Cancelar, Ver Detalhes.
- **Inverted:**  
  `bg-[#1F2937] hover:bg-black text-white font-semibold rounded-lg px-4 py-2 transition-all`  
  *Uso:* Ações complementares escuras, rodapé, modo noturno.
- **Outlined:**  
  `border border-gray-300 hover:border-[#E11D48] text-gray-700 hover:text-[#E11D48] bg-transparent font-semibold rounded-lg px-4 py-2 transition-all`  
  *Uso:* Botões secundários em cards de pedidos, aplicar cupom, calcular frete.

### 3.2 Campos de Busca & Inputs
- **Search Bar:**  
  `bg-gray-50 border border-gray-200 focus:border-[#E11D48] focus:bg-white text-sm rounded-lg pl-10 pr-4 py-2 outline-none transition-all`  
  Acompanhado de ícone de busca `search` posicionado à esquerda ou direita.

### 3.3 Barras de Acento / Progresso (Progress Bars)
- **Trilha de Fundo:** `bg-gray-100 h-1.5 rounded-full`
- **Preenchimento:**
  - Primário: `bg-[#E11D48]` (Progresso de entrega, checkout)
  - Secundário: `bg-[#F59E0B]` (Em preparação, alerta de estoque baixo)
  - Terciário: `bg-[#8B5CF6]` (Pontuação, benefícios VIP)

### 3.4 Badges Circulares & Chips de Ação
- **Destaque / Novidade (Sparkles):** Círculo `#E11D48` com ícone branco `auto_awesome`.
- **Categorias (Shapes):** Círculo `#855300` / Dourado com ícone `category`.
- **Ofertas / Cupons (Tags):** Círculo `#8B5CF6` com ícone `local_offer`.
- **Remoção / Excluir (Destrutivo):** Círculo `#BA1A1A` com ícone `delete`.

---

## 4. Configuração Tailwind CSS Pronta para Uso

Copie e cole este bloco de configuração no `<head>` dos seus arquivos HTML para ativar todas as classes personalizadas do Design System:

```html
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          headline: ['"Plus Jakarta Sans"', 'sans-serif'],
          body: ['"Inter"', 'sans-serif'],
          label: ['"Plus Jakarta Sans"', 'sans-serif'],
        },
        colors: {
          brand: {
            primary: '#E11D48',
            secondary: '#F59E0B',
            tertiary: '#8B5CF6',
            neutral: '#1F2937',
            50: '#FFF1F2',
            100: '#FFE4E6',
            500: '#F43F5E',
            600: '#E11D48',
            700: '#BE123C',
          }
        },
        borderRadius: {
          'card': '0.75rem',
          'pill': '9999px',
        }
      }
    }
  }
</script>
```

---

## 5. Ícones Oficiais (Material Symbols Outlined)
Utilizar a biblioteca Google Material Symbols:
```html
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
```

- **Carrinho de Compras:** `shopping_cart` ou `shopping_bag`
- **Conta / Usuário:** `person`
- **Busca:** `search`
- **Favoritos:** `favorite`
- **Entrega / Caminhão:** `local_shipping`
- **Confeitaria / Bolo:** `cake`
- **Suporte / Ajuda:** `support_agent`
- **Pagamentos:** `payments`, `credit_card`, `qr_code_scanner`
- **Status Concluído:** `check_circle` ou `task_alt`
