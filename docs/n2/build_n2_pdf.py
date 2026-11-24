# -*- coding: utf-8 -*-
"""
Gera a documentação completa N2 da equipe Vevey Co. (Laboratório de Engenharia de Software)
em HTML de alta qualidade editorial e exporta para PDF via Microsoft Edge Headless.
"""
import base64
import os
import subprocess
import shutil
import tempfile

BASE_DIR = r"C:\Users\Nathan\Desktop\Vevey - tcc\docs\n2"
SHOTS_DIR = os.path.join(BASE_DIR, "screenshots")
LOGO_PATH = r"C:\Users\Nathan\Desktop\Vevey - tcc\public\images\brand\vevey-symbol.svg"
HTML_OUT = os.path.join(BASE_DIR, "VeveyCo-LABENGSW-MA5-N1.html")
PDF_OUT = os.path.join(BASE_DIR, "VeveyCo-LABENGSW-MA5-N1.pdf")
PDF_DOWNLOADS = r"C:\Users\Nathan\Downloads\VeveyCo-LABENGSW-MA5-N1.pdf"
EDGE_BIN = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"


def img_b64(filename: str) -> str:
    path = os.path.join(SHOTS_DIR, filename)
    with open(path, "rb") as f:
        return "data:image/png;base64," + base64.b64encode(f.read()).decode("ascii")


def svg_inline(path: str) -> str:
    with open(path, "r", encoding="utf-8") as f:
        return f.read()


# ---------- DIAGRAMAS VETORIAIS EM SVG (FUNDO CLARO, LEGÍVEIS, PADRÃO ACADÊMICO) ----------

SVG_USE_CASES = """
<svg viewBox="0 0 820 390" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <rect x="195" y="16" width="430" height="358" rx="10" fill="#FDFBF7" stroke="#334155" stroke-width="1.6"/>
  <text x="410" y="38" text-anchor="middle" font-family="Arial" font-size="13" font-weight="bold" fill="#1F1B16">Sistema E-commerce &amp; Gestão Vevey Doces</text>

  <!-- Atores Esquerda -->
  <g stroke="#1F1B16" stroke-width="1.6" fill="none">
    <!-- Cliente -->
    <circle cx="70" cy="95" r="13"/>
    <line x1="70" y1="108" x2="70" y2="144"/>
    <line x1="50" y1="120" x2="90" y2="120"/>
    <line x1="70" y1="144" x2="54" y2="168"/>
    <line x1="70" y1="144" x2="86" y2="168"/>
    <!-- Atendente -->
    <circle cx="70" cy="245" r="13"/>
    <line x1="70" y1="258" x2="70" y2="294"/>
    <line x1="50" y1="270" x2="90" y2="270"/>
    <line x1="70" y1="294" x2="54" y2="318"/>
    <line x1="70" y1="294" x2="86" y2="318"/>
  </g>
  <text x="70" y="185" text-anchor="middle" font-family="Arial" font-size="11.5" font-weight="bold" fill="#1F1B16">Cliente (Site)</text>
  <text x="70" y="335" text-anchor="middle" font-family="Arial" font-size="11.5" font-weight="bold" fill="#1F1B16">Atendente (PDV)</text>

  <!-- Atores Direita -->
  <g stroke="#1F1B16" stroke-width="1.6" fill="none">
    <!-- Administrador -->
    <circle cx="745" cy="115" r="13"/>
    <line x1="745" y1="128" x2="745" y2="164"/>
    <line x1="725" y1="140" x2="765" y2="140"/>
    <line x1="745" y1="164" x2="729" y2="188"/>
    <line x1="745" y1="164" x2="761" y2="188"/>
    <!-- Logística -->
    <circle cx="745" cy="255" r="13"/>
    <line x1="745" y1="268" x2="745" y2="304"/>
    <line x1="725" y1="280" x2="765" y2="280"/>
    <line x1="745" y1="304" x2="729" y2="328"/>
    <line x1="745" y1="304" x2="761" y2="328"/>
  </g>
  <text x="745" y="205" text-anchor="middle" font-family="Arial" font-size="11.5" font-weight="bold" fill="#1F1B16">Administrador</text>
  <text x="745" y="345" text-anchor="middle" font-family="Arial" font-size="11.5" font-weight="bold" fill="#1F1B16">Logística</text>

  <!-- Casos de Uso (elipses) -->
  <g font-family="Arial" font-size="10.5" fill="#1F1B16" text-anchor="middle">
    <ellipse cx="325" cy="82" rx="108" ry="22" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.4"/>
    <text x="325" y="86">UC01: Explorar Catálogo e Buscar</text>

    <ellipse cx="325" cy="140" rx="108" ry="22" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.4"/>
    <text x="325" y="144">UC02: Realizar Compra (Checkout)</text>

    <ellipse cx="325" cy="198" rx="108" ry="22" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.4"/>
    <text x="325" y="202">UC03: Cadastrar Conta (2FA)</text>

    <ellipse cx="325" cy="264" rx="108" ry="22" fill="#FFFBEB" stroke="#D97706" stroke-width="1.4"/>
    <text x="325" y="268">UC04: Registrar Venda PDV</text>

    <ellipse cx="505" cy="112" rx="102" ry="22" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <text x="505" y="116">UC05: Gerenciar Catálogo/Cupons</text>

    <ellipse cx="505" cy="180" rx="102" ry="22" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <text x="505" y="184">UC06: Consultar Dashboard/Logs</text>

    <ellipse cx="505" cy="252" rx="102" ry="22" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <text x="505" y="256">UC07: Ajustar Estoque (Ledger)</text>

    <ellipse cx="505" cy="322" rx="102" ry="22" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <text x="505" y="326">UC08: Separar e Entregar Pedido</text>
  </g>

  <!-- Associações -->
  <g stroke="#64748B" stroke-width="1.2">
    <line x1="96" y1="120" x2="217" y2="82"/>
    <line x1="96" y1="128" x2="217" y2="140"/>
    <line x1="96" y1="136" x2="217" y2="198"/>
    <line x1="96" y1="274" x2="217" y2="264"/>
    <line x1="96" y1="286" x2="403" y2="322"/>

    <line x1="720" y1="140" x2="607" y2="112"/>
    <line x1="720" y1="150" x2="607" y2="180"/>
    <line x1="720" y1="160" x2="607" y2="252"/>
    <line x1="720" y1="282" x2="607" y2="252"/>
    <line x1="720" y1="292" x2="607" y2="322"/>
  </g>
</svg>
"""

SVG_NAVIGATION = """
<svg viewBox="0 0 820 270" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="arr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#475569"/>
    </marker>
  </defs>
  <!-- Faixa Loja Pública -->
  <rect x="14" y="14" width="792" height="122" rx="8" fill="#FDFBF7" stroke="#CBD5E1" stroke-width="1.2"/>
  <text x="28" y="34" font-family="Arial" font-size="11" font-weight="bold" fill="#E11D48">LOJA PÚBLICA (panel-site)</text>

  <g font-family="Arial" font-size="10.5" text-anchor="middle" fill="#1F1B16">
    <rect x="28" y="52" width="115" height="44" rx="6" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.4"/>
    <text x="85" y="72" font-weight="bold">Home (/)</text>
    <text x="85" y="87" font-size="9" fill="#64748B">Hero + Busca</text>

    <rect x="175" y="52" width="125" height="44" rx="6" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.4"/>
    <text x="237" y="72" font-weight="bold">Catálogo</text>
    <text x="237" y="87" font-size="9" fill="#64748B">/catalogo (ADR 6)</text>

    <rect x="332" y="52" width="125" height="44" rx="6" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.4"/>
    <text x="394" y="72" font-weight="bold">Produto (PDP)</text>
    <text x="394" y="87" font-size="9" fill="#64748B">/produto/{slug}</text>

    <rect x="489" y="52" width="135" height="44" rx="6" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.5"/>
    <text x="556" y="72" font-weight="bold">Checkout</text>
    <text x="556" y="87" font-size="9" fill="#9F1239">Frete + Cupom + Pix</text>

    <rect x="656" y="52" width="134" height="44" rx="6" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <text x="723" y="72" font-weight="bold">Minha Conta</text>
    <text x="723" y="87" font-size="9" fill="#1D4ED8">/entrar → 2FA → /conta</text>
  </g>

  <g stroke="#475569" stroke-width="1.4" marker-end="url(#arr)">
    <line x1="143" y1="74" x2="173" y2="74"/>
    <line x1="300" y1="74" x2="330" y2="74"/>
    <line x1="457" y1="74" x2="487" y2="74"/>
    <line x1="624" y1="74" x2="654" y2="74"/>
  </g>

  <!-- Faixa Painel Admin -->
  <rect x="14" y="150" width="792" height="106" rx="8" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.2"/>
  <text x="28" y="170" font-family="Arial" font-size="11" font-weight="bold" fill="#334155">PAINEL ADMINISTRATIVO FILAMENT (/admin — RBAC Guard web)</text>

  <g font-family="Arial" font-size="10.2" text-anchor="middle" fill="#1F1B16">
    <rect x="28" y="186" width="112" height="44" rx="6" fill="#FFFFFF" stroke="#334155" stroke-width="1.3"/>
    <text x="84" y="206" font-weight="bold">Dashboard</text>
    <text x="84" y="220" font-size="8.8" fill="#64748B">KPIs + Feriados</text>

    <rect x="154" y="186" width="112" height="44" rx="6" fill="#FFFBEB" stroke="#D97706" stroke-width="1.3"/>
    <text x="210" y="206" font-weight="bold">PDV Balcão</text>
    <text x="210" y="220" font-size="8.8" fill="#92400E">/admin/pdv</text>

    <rect x="280" y="186" width="118" height="44" rx="6" fill="#FFFFFF" stroke="#334155" stroke-width="1.3"/>
    <text x="339" y="206" font-weight="bold">Pedidos/Cupons</text>
    <text x="339" y="220" font-size="8.8" fill="#64748B">/orders · /coupons</text>

    <rect x="412" y="186" width="118" height="44" rx="6" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.3"/>
    <text x="471" y="206" font-weight="bold">Estoque</text>
    <text x="471" y="220" font-size="8.8" fill="#166534">Saldos + Ledger</text>

    <rect x="544" y="186" width="116" height="44" rx="6" fill="#FFFFFF" stroke="#334155" stroke-width="1.3"/>
    <text x="602" y="206" font-weight="bold">Catálogo/Clientes</text>
    <text x="602" y="220" font-size="8.8" fill="#64748B">Produtos · ADR 7</text>

    <rect x="674" y="186" width="116" height="44" rx="6" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.3"/>
    <text x="732" y="206" font-weight="bold">Auditoria</text>
    <text x="732" y="220" font-size="8.8" fill="#1D4ED8">/admin/audit-log</text>
  </g>
</svg>
"""

SVG_PACKAGES = """
<svg viewBox="0 0 820 310" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="arr2" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#475569"/>
    </marker>
  </defs>

  <!-- Camada Interface -->
  <rect x="20" y="14" width="780" height="62" rx="8" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.3"/>
  <text x="34" y="32" font-family="Arial" font-size="10" font-weight="bold" fill="#9F1239">CAMADA DE INTERFACE (podem importar qualquer módulo abaixo)</text>
  <rect x="160" y="40" width="210" height="28" rx="5" fill="#FFFFFF" stroke="#E11D48"/>
  <text x="265" y="58" text-anchor="middle" font-family="Arial" font-size="11" font-weight="bold" fill="#1F1B16">Vevey\PanelAdmin (Filament)</text>
  <rect x="440" y="40" width="210" height="28" rx="5" fill="#FFFFFF" stroke="#E11D48"/>
  <text x="545" y="58" text-anchor="middle" font-family="Arial" font-size="11" font-weight="bold" fill="#1F1B16">Vevey\PanelSite (Loja Blade)</text>

  <!-- Camada Orquestrador -->
  <rect x="20" y="96" width="780" height="58" rx="8" fill="#FFFBEB" stroke="#D97706" stroke-width="1.3"/>
  <text x="34" y="114" font-family="Arial" font-size="10" font-weight="bold" fill="#92400E">CAMADA ORQUESTRADORA (chama Actions+DTOs da base)</text>
  <rect x="250" y="120" width="320" height="26" rx="5" fill="#FFFFFF" stroke="#D97706"/>
  <text x="410" y="137" text-anchor="middle" font-family="Arial" font-size="11" font-weight="bold" fill="#1F1B16">Vevey\Orders (PlaceOrder · UpdateOrderStatus · Coupons)</text>

  <!-- Camada Base -->
  <rect x="20" y="174" width="780" height="60" rx="8" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.3"/>
  <text x="34" y="192" font-family="Arial" font-size="10" font-weight="bold" fill="#166534">MÓDULOS DE DOMÍNIO BASE (isolados entre si — zero imports cruzados)</text>
  <rect x="55" y="200" width="210" height="26" rx="5" fill="#FFFFFF" stroke="#16A34A"/>
  <text x="160" y="217" text-anchor="middle" font-family="Arial" font-size="11" font-weight="bold" fill="#1F1B16">Vevey\Catalog</text>
  <rect x="305" y="200" width="210" height="26" rx="5" fill="#FFFFFF" stroke="#16A34A"/>
  <text x="410" y="217" text-anchor="middle" font-family="Arial" font-size="11" font-weight="bold" fill="#1F1B16">Vevey\Inventory</text>
  <rect x="555" y="200" width="210" height="26" rx="5" fill="#FFFFFF" stroke="#16A34A"/>
  <text x="660" y="217" text-anchor="middle" font-family="Arial" font-size="11" font-weight="bold" fill="#1F1B16">Vevey\Customers</text>

  <!-- Camada Compartilhada -->
  <rect x="20" y="252" width="780" height="46" rx="8" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.3"/>
  <text x="410" y="279" text-anchor="middle" font-family="Arial" font-size="11" font-weight="bold" fill="#1E3A8A">CAMADA COMPARTILHADA: Vevey\Permissions (RBAC)  +  App\ (User, MoneyCast, BrasilApiService, TransactionalMailer)</text>

  <!-- Setas -->
  <g stroke="#475569" stroke-width="1.4" marker-end="url(#arr2)">
    <line x1="410" y1="76" x2="410" y2="95"/>
    <line x1="410" y1="154" x2="410" y2="173"/>
    <line x1="410" y1="234" x2="410" y2="251"/>
  </g>
</svg>
"""

SVG_CLASSES = """
<svg viewBox="0 0 820 340" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <g font-family="Arial" font-size="10" fill="#1F1B16">
    <!-- Product -->
    <rect x="16" y="16" width="180" height="132" rx="6" fill="#FDFBF7" stroke="#334155" stroke-width="1.4"/>
    <rect x="16" y="16" width="180" height="24" rx="6" fill="#E2E8F0" stroke="#334155" stroke-width="1.4"/>
    <text x="106" y="32" text-anchor="middle" font-weight="bold" font-size="11">Product (Catalog)</text>
    <text x="24" y="56">+ id: uuid</text>
    <text x="24" y="71">+ sku, barcode: string</text>
    <text x="24" y="86">+ name, slug: string</text>
    <text x="24" y="101">+ price_cents: Money</text>
    <text x="24" y="116">+ promo_price_cents: ?Money</text>
    <text x="24" y="134" fill="#475569">+ search(term), active()</text>

    <!-- Order -->
    <rect x="224" y="16" width="195" height="150" rx="6" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.5"/>
    <rect x="224" y="16" width="195" height="24" rx="6" fill="#FFE4E6" stroke="#E11D48" stroke-width="1.5"/>
    <text x="321" y="32" text-anchor="middle" font-weight="bold" font-size="11" fill="#9F1239">Order (Orders)</text>
    <text x="232" y="56">+ id: uuid · number: string</text>
    <text x="232" y="71">+ channel: OrderChannel</text>
    <text x="232" y="86">+ payment_status: PaymentStatus</text>
    <text x="232" y="101">+ fulfillment_status: Fulfillment</text>
    <text x="232" y="116">+ subtotal/discount/total: Money</text>
    <text x="232" y="131">+ customer_id, seller_id: ?uuid</text>
    <text x="232" y="150" fill="#9F1239">+ items(), payments(), histories()</text>

    <!-- OrderItem -->
    <rect x="448" y="16" width="175" height="132" rx="6" fill="#FDFBF7" stroke="#334155" stroke-width="1.4"/>
    <rect x="448" y="16" width="175" height="24" rx="6" fill="#E2E8F0" stroke="#334155" stroke-width="1.4"/>
    <text x="535" y="32" text-anchor="middle" font-weight="bold" font-size="11">OrderItem (Snapshot)</text>
    <text x="456" y="56">+ order_id, product_id: uuid</text>
    <text x="456" y="71">+ sku, name: string</text>
    <text x="456" y="86">+ unit_price_cents: Money</text>
    <text x="456" y="101">+ quantity: int</text>
    <text x="456" y="116">+ discount_cents: Money</text>
    <text x="456" y="131">+ product_snapshot: json</text>

    <!-- Customer -->
    <rect x="646" y="16" width="160" height="132" rx="6" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <rect x="646" y="16" width="160" height="24" rx="6" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.4"/>
    <text x="726" y="32" text-anchor="middle" font-weight="bold" font-size="11" fill="#1E3A8A">Customer (ADR 7)</text>
    <text x="654" y="56">+ id: uuid</text>
    <text x="654" y="71">+ name, email, cpf</text>
    <text x="654" y="86">+ password: ?string</text>
    <text x="654" y="101">+ verification_code</text>
    <text x="654" y="116">+ email_verified_at</text>
    <text x="654" y="134" fill="#1D4ED8">+ identities(), addresses()</text>

    <!-- StockLevel -->
    <rect x="16" y="192" width="180" height="128" rx="6" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <rect x="16" y="192" width="180" height="24" rx="6" fill="#DCFCE7" stroke="#16A34A" stroke-width="1.4"/>
    <text x="106" y="208" text-anchor="middle" font-weight="bold" font-size="11" fill="#166534">StockLevel (Cache)</text>
    <text x="24" y="232">+ product_id: uuid (PK)</text>
    <text x="24" y="247">+ on_hand: int (signed)</text>
    <text x="24" y="262">+ reserved: uint</text>
    <text x="24" y="277">+ available: generated</text>
    <text x="24" y="292">+ reorder_point: uint</text>

    <!-- StockMovement -->
    <rect x="224" y="192" width="195" height="128" rx="6" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <rect x="224" y="192" width="195" height="24" rx="6" fill="#DCFCE7" stroke="#16A34A" stroke-width="1.4"/>
    <text x="321" y="208" text-anchor="middle" font-weight="bold" font-size="11" fill="#166534">StockMovement (Ledger)</text>
    <text x="232" y="232">+ id, product_id: uuid</text>
    <text x="232" y="247">+ qty: int (&lt;&gt; 0)</text>
    <text x="232" y="262">+ direction: stored (in/out)</text>
    <text x="232" y="277">+ reason: MovementReason</text>
    <text x="232" y="292">+ balance_after, user_id</text>

    <!-- Payment -->
    <rect x="448" y="192" width="175" height="128" rx="6" fill="#FFFBEB" stroke="#D97706" stroke-width="1.4"/>
    <rect x="448" y="192" width="175" height="24" rx="6" fill="#FEF3C7" stroke="#D97706" stroke-width="1.4"/>
    <text x="535" y="208" text-anchor="middle" font-weight="bold" font-size="11" fill="#92400E">Payment (1:N)</text>
    <text x="456" y="232">+ id, order_id: uuid</text>
    <text x="456" y="247">+ method: PaymentMethod</text>
    <text x="456" y="262">+ amount_cents: Money</text>
    <text x="456" y="277">+ status: PaymentStatus</text>
    <text x="456" y="292">+ expires_at, paid_at</text>

    <!-- CustomerIdentity -->
    <rect x="646" y="192" width="160" height="128" rx="6" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <rect x="646" y="192" width="160" height="24" rx="6" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.4"/>
    <text x="726" y="208" text-anchor="middle" font-weight="bold" font-size="11" fill="#1E3A8A">CustomerIdentity</text>
    <text x="654" y="232">+ id, customer_id: uuid</text>
    <text x="654" y="247">+ type: IdentityType</text>
    <text x="654" y="262">+ value: E.164 / email</text>
    <text x="654" y="277">+ is_primary: bool</text>
    <text x="654" y="292">+ retired_at: ?timestamp</text>
  </g>

  <!-- Linhas de relacionamento -->
  <g stroke="#64748B" stroke-width="1.3">
    <line x1="106" y1="148" x2="106" y2="192"/>
    <line x1="196" y1="80" x2="224" y2="80" stroke-dasharray="4 3"/>
    <line x1="419" y1="80" x2="448" y2="80"/>
    <line x1="419" y1="130" x2="448" y2="230"/>
    <line x1="726" y1="148" x2="726" y2="192"/>
    <line x1="196" y1="256" x2="224" y2="256"/>
  </g>
</svg>
"""

SVG_SEQUENCE = """
<svg viewBox="0 0 820 330" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="seqArr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#1F1B16"/>
    </marker>
  </defs>
  <!-- Lifelines -->
  <g font-family="Arial" font-size="10.5" font-weight="bold" text-anchor="middle" fill="#1F1B16">
    <rect x="20" y="14" width="110" height="32" rx="5" fill="#FFF1F2" stroke="#E11D48"/>
    <text x="75" y="34">Cliente (Web)</text>
    <line x1="75" y1="46" x2="75" y2="312" stroke="#94A3B8" stroke-dasharray="5 4"/>

    <rect x="175" y="14" width="125" height="32" rx="5" fill="#F8FAFC" stroke="#334155"/>
    <text x="237" y="34">CheckoutController</text>
    <line x1="237" y1="46" x2="237" y2="312" stroke="#94A3B8" stroke-dasharray="5 4"/>

    <rect x="345" y="14" width="125" height="32" rx="5" fill="#FFFBEB" stroke="#D97706"/>
    <text x="407" y="34">PlaceOrder (Orders)</text>
    <line x1="407" y1="46" x2="407" y2="312" stroke="#94A3B8" stroke-dasharray="5 4"/>

    <rect x="515" y="14" width="125" height="32" rx="5" fill="#F0FDF4" stroke="#16A34A"/>
    <text x="577" y="34">Inventory (ADR 2)</text>
    <line x1="577" y1="46" x2="577" y2="312" stroke="#94A3B8" stroke-dasharray="5 4"/>

    <rect x="680" y="14" width="120" height="32" rx="5" fill="#EFF6FF" stroke="#2563EB"/>
    <text x="740" y="34">PostgreSQL</text>
    <line x1="740" y1="46" x2="740" y2="312" stroke="#94A3B8" stroke-dasharray="5 4"/>
  </g>

  <!-- Mensagens -->
  <g font-family="Arial" font-size="9.5" fill="#1F1B16">
    <line x1="75" y1="78" x2="233" y2="78" stroke="#1F1B16" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="154" y="72" text-anchor="middle">1. POST /checkout (itens, CEP, cupom)</text>

    <line x1="237" y1="108" x2="403" y2="108" stroke="#1F1B16" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="320" y="102" text-anchor="middle">2. PlaceOrder::handle(PlaceOrderData)</text>

    <line x1="407" y1="138" x2="736" y2="138" stroke="#1F1B16" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="572" y="132" text-anchor="middle">3. Grava order + order_items (snapshot) + adjustments + payment</text>

    <line x1="407" y1="168" x2="573" y2="168" stroke="#1F1B16" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="490" y="162" text-anchor="middle">4. ReserveStock (lockForUpdate)</text>

    <line x1="577" y1="196" x2="736" y2="196" stroke="#16A34A" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="656" y="190" text-anchor="middle">5. INSERT stock_reservations (30m)</text>

    <line x1="237" y1="226" x2="79" y2="226" stroke="#475569" stroke-width="1.2" stroke-dasharray="4 3" marker-end="url(#seqArr)"/>
    <text x="158" y="220" text-anchor="middle">6. 201 Created + QR Code Pix</text>

    <line x1="75" y1="258" x2="233" y2="258" stroke="#E11D48" stroke-width="1.4" marker-end="url(#seqArr)"/>
    <text x="154" y="252" text-anchor="middle" fill="#9F1239">7. Confirmação Pagamento (Sandbox/Admin)</text>

    <line x1="237" y1="288" x2="573" y2="288" stroke="#16A34A" stroke-width="1.4" marker-end="url(#seqArr)"/>
    <text x="405" y="282" text-anchor="middle" fill="#166534">8. FulfillOrderReservations → INSERT stock_movements (sale -qty)</text>
  </g>
</svg>
"""

SVG_DEPLOY = """
<svg viewBox="0 0 820 220" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="depArr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#475569"/>
    </marker>
  </defs>
  <g font-family="Arial" font-size="10.5" text-anchor="middle" fill="#1F1B16">
    <!-- Navegador -->
    <rect x="18" y="68" width="136" height="76" rx="8" fill="#FDFBF7" stroke="#1F1B16" stroke-width="1.4"/>
    <text x="86" y="96" font-weight="bold">Navegador Web</text>
    <text x="86" y="114" font-size="9" fill="#64748B">Cliente (Loja)</text>
    <text x="86" y="128" font-size="9" fill="#64748B">Equipe (/admin · PDV)</text>

    <!-- Render Web Service -->
    <rect x="206" y="26" width="256" height="162" rx="10" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.5"/>
    <text x="334" y="48" font-weight="bold" fill="#9F1239">Container Docker (Render.com)</text>
    <rect x="224" y="60" width="220" height="44" rx="6" fill="#FFFFFF" stroke="#E11D48"/>
    <text x="334" y="80" font-weight="bold">Laravel 13 + PHP 8.4</text>
    <text x="334" y="95" font-size="9" fill="#64748B">7 Módulos (internachi/modular)</text>
    <rect x="224" y="116" width="220" height="54" rx="6" fill="#FFFFFF" stroke="#334155"/>
    <text x="334" y="136" font-weight="bold">Assets Vite + Filament 5</text>
    <text x="334" y="151" font-size="9" fill="#64748B">Tailwind v4 + Scheduler (5 min)</text>

    <!-- PostgreSQL -->
    <rect x="518" y="26" width="134" height="76" rx="8" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <text x="585" y="56" font-weight="bold" fill="#1E3A8A">PostgreSQL 16+</text>
    <text x="585" y="73" font-size="9" fill="#475569">20 Tabelas · UUIDs</text>
    <text x="585" y="87" font-size="9" fill="#475569">GIN Full-Text · CHECK</text>

    <!-- APIs Externas -->
    <rect x="518" y="118" width="280" height="70" rx="8" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <text x="658" y="140" font-weight="bold" fill="#166534">Integrações Externas (HTTPS)</text>
    <text x="658" y="157" font-size="9.5" fill="#1F1B16">BrasilAPI (CEP v2 + Feriados) · ViaCEP</text>
    <text x="658" y="173" font-size="9.5" fill="#1F1B16">Resend API (E-mails 2FA) · Melhor Envio</text>
  </g>

  <g stroke="#475569" stroke-width="1.5" marker-end="url(#depArr)">
    <line x1="154" y1="106" x2="204" y2="106"/>
    <line x1="462" y1="76" x2="516" y2="64"/>
    <line x1="462" y1="146" x2="516" y2="152"/>
  </g>
</svg>
"""


def build_html() -> str:
    logo_svg = svg_inline(LOGO_PATH)
    shot_home = img_b64("home.png")
    shot_catalog = img_b64("catalog.png")
    shot_product = img_b64("product.png")
    shot_register = img_b64("register.png")

    return f"""<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8" />
<title>VeveyCo-LABENGSW-MA5-N1</title>
<style>
  @page {{
    size: A4 portrait;
    margin: 2.2cm 2.0cm 2.2cm 2.5cm;
    @bottom-right {{
      content: "Página " counter(page) " de " counter(pages);
      font-family: Arial, Helvetica, sans-serif;
      font-size: 9pt;
      color: #64748B;
    }}
    @bottom-left {{
      content: "Vevey Co. — Laboratório de Engenharia de Software (N2)";
      font-family: Arial, Helvetica, sans-serif;
      font-size: 9pt;
      color: #64748B;
    }}
  }}
  @page :first {{
    @bottom-left {{ content: none; }}
    @bottom-right {{ content: none; }}
  }}
  * {{ box-sizing: border-box; }}
  body {{
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10.5pt;
    line-height: 1.5;
    color: #1F1B16;
    background: #FFFFFF;
    margin: 0;
  }}
  p {{
    text-align: justify;
    margin: 0 0 10pt 0;
    orphans: 3;
    widows: 3;
  }}
  ul, ol {{
    margin: 0 0 10pt 0;
    padding-left: 18pt;
  }}
  li {{
    text-align: justify;
    margin-bottom: 4pt;
  }}
  h1 {{
    font-size: 16pt;
    font-weight: 700;
    color: #1F1B16;
    border-bottom: 2px solid #E11D48;
    padding-bottom: 4pt;
    margin: 22pt 0 12pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  h2 {{
    font-size: 13pt;
    font-weight: 700;
    color: #1F1B16;
    margin: 16pt 0 8pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  h3 {{
    font-size: 11.2pt;
    font-weight: 700;
    color: #334155;
    margin: 12pt 0 6pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  .page-break {{
    page-break-before: always;
    break-before: page;
  }}
  /* Capa */
  .cover {{
    min-height: 24.5cm;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: center;
    padding: 1.2cm 0;
  }}
  .cover-inst {{
    font-size: 12pt;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #334155;
    text-transform: uppercase;
  }}
  .cover-logo {{
    width: 72px;
    height: 72px;
    margin: 0 auto 14pt auto;
  }}
  .cover-title {{
    font-size: 24pt;
    font-weight: 700;
    color: #1F1B16;
    margin: 0 0 8pt 0;
  }}
  .cover-sub {{
    font-size: 13pt;
    color: #E11D48;
    font-weight: 700;
    margin: 0 0 6pt 0;
  }}
  .cover-team {{
    font-size: 11.5pt;
    color: #475569;
    margin-bottom: 18pt;
  }}
  /* Tabelas padronizadas */
  .table-wrap {{
    margin: 12pt 0 14pt 0;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .table-caption {{
    font-size: 9.5pt;
    font-weight: 700;
    color: #1F1B16;
    margin-bottom: 4pt;
    text-align: left;
  }}
  .source-note {{
    font-size: 8.5pt;
    color: #64748B;
    margin-top: 4pt;
    text-align: left;
  }}
  table {{
    width: 100%;
    border-collapse: collapse;
    font-size: 9.2pt;
    line-height: 1.38;
  }}
  thead {{ display: table-header-group; }}
  tr {{ page-break-inside: avoid; break-inside: avoid; }}
  th {{
    background: #1F1B16;
    color: #FFFFFF;
    font-weight: 700;
    text-align: left;
    padding: 6pt 7pt;
    border: 1px solid #1F1B16;
  }}
  td {{
    padding: 4.6pt 6.5pt;
    border: 1px solid #CBD5E1;
    vertical-align: top;
    text-align: left;
  }}
  tbody tr:nth-child(even) td {{
    background: #F8FAFC;
  }}
  /* Figuras */
  .figure-wrap {{
    margin: 14pt 0 16pt 0;
    padding: 10pt;
    border: 1px solid #E2E8F0;
    border-radius: 6pt;
    background: #FFFFFF;
    text-align: center;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .figure-wrap img {{
    max-width: 100%;
    height: auto;
    border: 1px solid #E2E8F0;
    border-radius: 4pt;
  }}
  .figure-caption {{
    font-size: 9.5pt;
    font-weight: 700;
    color: #1F1B16;
    margin-top: 7pt;
    text-align: center;
  }}
  .figure-source {{
    font-size: 8.5pt;
    color: #64748B;
    margin-top: 2pt;
    text-align: center;
  }}
  /* Sumário e Índices */
  .toc-list {{
    list-style: none;
    padding: 0;
    margin: 8pt 0 14pt 0;
  }}
  .toc-list li {{
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dotted #CBD5E1;
    padding: 3pt 0;
    font-size: 10pt;
    text-align: left;
  }}
  .toc-l1 {{ font-weight: 700; margin-top: 5pt; }}
  .toc-l2 {{ padding-left: 14pt !important; }}
  .toc-l3 {{ padding-left: 28pt !important; font-size: 9.4pt !important; color: #334155; }}
  /* Blocos de Caso de Uso */
  .uc-card {{
    border: 1px solid #CBD5E1;
    border-radius: 6pt;
    padding: 9pt 11pt;
    margin: 10pt 0 12pt 0;
    background: #FFFFFF;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .uc-title {{
    font-size: 10.5pt;
    font-weight: 700;
    color: #9F1239;
    margin-bottom: 5pt;
    border-bottom: 1px solid #FFE4E6;
    padding-bottom: 3pt;
  }}
  .uc-card p {{ margin-bottom: 4pt; font-size: 9.5pt; }}
  .uc-card ol {{ margin: 2pt 0 5pt 0; font-size: 9.3pt; }}
  code {{
    font-family: Consolas, "Courier New", monospace;
    font-size: 9pt;
    background: #F1F5F9;
    padding: 1pt 3.5pt;
    border-radius: 3pt;
  }}
  pre {{
    font-family: Consolas, "Courier New", monospace;
    font-size: 8.5pt;
    background: #F8FAFC;
    border: 1px solid #CBD5E1;
    border-radius: 5pt;
    padding: 8pt 10pt;
    white-space: pre-wrap;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .abnt-ref p {{
    text-align: left;
    margin-bottom: 9pt;
    font-size: 10pt;
  }}
</style>
</head>
<body>

<!-- ==================== CAPA ==================== -->
<div class="cover">
  <div>
    <div class="cover-inst">Faculdade de Tecnologia do Estado de São Paulo — FATEC</div>
    <div style="font-size:10.5pt;color:#475569;margin-top:4pt;">Curso Superior de Tecnologia em Análise e Desenvolvimento de Sistemas</div>
    <div style="font-size:10.5pt;color:#475569;">Disciplina: Laboratório de Engenharia de Software (MA5 — Entrega N2)</div>
  </div>

  <div>
    <div class="cover-logo">{logo_svg}</div>
    <div class="cover-title">Website Vevey — Vevey Doces</div>
    <div class="cover-sub">Plataforma E-commerce, Estoque Integrado com Ledger e PDV Balcão</div>
    <div class="cover-team">Equipe: <strong>Vevey Co.</strong></div>

    <div class="table-wrap" style="max-width:520px;margin:0 auto;">
      <div class="table-caption">Tabela 1 — Integrantes da equipe Vevey Co.</div>
      <table>
        <thead><tr><th>Nome Completo</th><th style="width:150px;">RA</th></tr></thead>
        <tbody>
          <tr><td>Barbara Vitoria da Silva</td><td>1680482421022</td></tr>
          <tr><td>Matheus da Silva Araújo</td><td>1680482421029</td></tr>
          <tr><td>Nathan Costa Santos</td><td>1680482421021</td></tr>
          <tr><td>Ricardo Martins Pighin</td><td>1680482421013</td></tr>
        </tbody>
      </table>
      <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
    </div>
  </div>

  <div style="font-size:10.5pt;color:#475569;">
    São Paulo — SP<br/>Novembro de 2026
  </div>
</div>

<!-- ==================== RELATÓRIOS DE ANDAMENTO ==================== -->
<div class="page-break"></div>
<h1>Relatórios de Andamento do Projeto</h1>
<p>Os quadros a seguir consolidam o registro atualizado de atividades executadas por cada integrante da equipe Vevey Co. desde a concepção inicial até a entrega final da documentação e do software na N2 (novembro de 2026).</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 2 — Relatório de andamento: Barbara Vitoria da Silva (RA 1680482421022)</div>
  <table>
    <thead><tr><th style="width:82px;">Data inicial</th><th style="width:82px;">Data final</th><th style="width:58px;">Horas</th><th>Descrição sucinta das atividades</th></tr></thead>
    <tbody>
      <tr><td>12/08/2026</td><td>19/08/2026</td><td>3h30</td><td>Reunião de alinhamento, levantamento com stakeholder e consolidação da Visão e Escopo v2.0.</td></tr>
      <tr><td>25/08/2026</td><td>26/08/2026</td><td>3h30</td><td>Documentação de atores, casos de uso e levantamento de requisitos funcionais e não funcionais.</td></tr>
      <tr><td>14/10/2026</td><td>28/10/2026</td><td>5h00</td><td>Revisão dos fluxos de atendimento WhatsApp, personas, cenários de compra e protótipos de tela.</td></tr>
      <tr><td>10/11/2026</td><td>24/11/2026</td><td>6h00</td><td>Validação dos requisitos na aplicação Laravel, revisão ortográfica/ABNT e fechamento da documentação N2.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="table-wrap">
  <div class="table-caption">Tabela 3 — Relatório de andamento: Matheus da Silva Araújo (RA 1680482421029)</div>
  <table>
    <thead><tr><th style="width:82px;">Data inicial</th><th style="width:82px;">Data final</th><th style="width:58px;">Horas</th><th>Descrição sucinta das atividades</th></tr></thead>
    <tbody>
      <tr><td>12/08/2026</td><td>19/08/2026</td><td>3h30</td><td>Reunião de alinhamento, entrevistas com a loja e finalização da Visão e Escopo v2.0.</td></tr>
      <tr><td>09/09/2026</td><td>30/09/2026</td><td>4h30</td><td>Levantamento de requisitos funcionais e não funcionais e elaboração da apresentação preliminar.</td></tr>
      <tr><td>04/11/2026</td><td>18/11/2026</td><td>6h00</td><td>Apoio à modelagem de estoque, testes de fluxo de checkout, cupons e validação de regras de negócio.</td></tr>
      <tr><td>19/11/2026</td><td>24/11/2026</td><td>5h00</td><td>Testes ponta a ponta do painel administrativo, PDV Balcão e revisão do manual do usuário para N2.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="table-wrap">
  <div class="table-caption">Tabela 4 — Relatório de andamento: Nathan Costa Santos (RA 1680482421021)</div>
  <table>
    <thead><tr><th style="width:82px;">Data inicial</th><th style="width:82px;">Data final</th><th style="width:58px;">Horas</th><th>Descrição sucinta das atividades</th></tr></thead>
    <tbody>
      <tr><td>12/08/2026</td><td>09/09/2026</td><td>7h30</td><td>Alinhamento com proprietários, modelagem de banco v1.0, levantamento de telas e documentação v2.0.</td></tr>
      <tr><td>02/11/2026</td><td>15/11/2026</td><td>14h00</td><td>Migração arquitetural para Laravel 13 + Filament 5 + PostgreSQL (ADRs 1–9), RBAC e módulo Catálogo.</td></tr>
      <tr><td>16/11/2026</td><td>23/11/2026</td><td>18h00</td><td>Implementação dos módulos Orders, Inventory (Ledger), Customers (2FA), PDV Balcão, Auditoria, Loja e APIs.</td></tr>
      <tr><td>24/11/2026</td><td>24/11/2026</td><td>4h00</td><td>Configuração de deploy Docker/Render, testes finais, geração de diagramas e compilação da entrega N2.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="table-wrap">
  <div class="table-caption">Tabela 5 — Relatório de andamento: Ricardo Martins Pighin (RA 1680482421013)</div>
  <table>
    <thead><tr><th style="width:82px;">Data inicial</th><th style="width:82px;">Data final</th><th style="width:58px;">Horas</th><th>Descrição sucinta das atividades</th></tr></thead>
    <tbody>
      <tr><td>12/08/2026</td><td>19/08/2026</td><td>3h30</td><td>Reunião de alinhamento, levantamento com stakeholder e consolidação da documentação v2.0.</td></tr>
      <tr><td>09/09/2026</td><td>14/10/2026</td><td>5h00</td><td>Especificação de requisitos funcionais e não funcionais, regras de segurança e modelagem de dados.</td></tr>
      <tr><td>03/11/2026</td><td>18/11/2026</td><td>6h00</td><td>Revisão das ADRs de banco de dados, políticas RBAC, integridade referencial e cenários de teste.</td></tr>
      <tr><td>19/11/2026</td><td>24/11/2026</td><td>5h00</td><td>Conferência de diagramas UML/DER, documentação técnica, testes de arquitetura e revisão final N2.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== SUMÁRIO E ÍNDICES ==================== -->
<div class="page-break"></div>
<h1>Sumário</h1>
<ul class="toc-list">
  <li class="toc-l1"><span>Relatórios de Andamento do Projeto</span><span>2</span></li>
  <li class="toc-l1"><span>Índice de Figuras</span><span>5</span></li>
  <li class="toc-l1"><span>Índice de Tabelas</span><span>5</span></li>
  <li class="toc-l1"><span>1. Visão e Escopo do Projeto</span><span>6</span></li>
  <li class="toc-l2"><span>1.1 Descrição do problema (Contexto, Stakeholders, Usuários, Riscos e Premissas)</span><span>6</span></li>
  <li class="toc-l2"><span>1.2 Visão da solução (Declaração de visão, Cenários de utilização e Features)</span><span>8</span></li>
  <li class="toc-l1"><span>2. Requisitos de Software</span><span>10</span></li>
  <li class="toc-l2"><span>2.1 Casos de uso (Diagrama, Especificação detalhada e Documentação dos atores)</span><span>10</span></li>
  <li class="toc-l2"><span>2.2 Requisitos funcionais</span><span>14</span></li>
  <li class="toc-l2"><span>2.3 Requisitos não funcionais</span><span>15</span></li>
  <li class="toc-l2"><span>2.4 Protótipos do software (Telas implementadas e Modelo de navegação)</span><span>16</span></li>
  <li class="toc-l1"><span>3. Projeto do Sistema</span><span>19</span></li>
  <li class="toc-l2"><span>3.1 Modelo estrutural do sistema (Diagrama de pacotes e Diagrama de classes)</span><span>19</span></li>
  <li class="toc-l2"><span>3.2 Modelo de banco de dados do sistema (DER e Dicionário de tabelas)</span><span>20</span></li>
  <li class="toc-l2"><span>3.3 Modelo de comportamentos do sistema (Diagrama de sequência)</span><span>21</span></li>
  <li class="toc-l1"><span>4. Documentação Técnica do Sistema</span><span>23</span></li>
  <li class="toc-l2"><span>4.1 Modelo de implementação do sistema</span><span>23</span></li>
  <li class="toc-l2"><span>4.2 Modelos de testes do sistema</span><span>23</span></li>
  <li class="toc-l2"><span>4.3 Modelos de implantação do sistema</span><span>23</span></li>
  <li class="toc-l2"><span>4.4 Procedimento de instalação</span><span>24</span></li>
  <li class="toc-l2"><span>4.5 Documentação técnica do sistema (ADRs e Integrações)</span><span>24</span></li>
  <li class="toc-l2"><span>4.6 Manual do usuário</span><span>25</span></li>
  <li class="toc-l1"><span>5. Referências Bibliográficas (ABNT)</span><span>27</span></li>
</ul>

<h2>Índice de Figuras</h2>
<ul class="toc-list">
  <li><span>Figura 1 — Diagrama de Casos de Uso do Sistema Vevey Doces</span><span>10</span></li>
  <li><span>Figura 2 — Tela Inicial da Loja Vevey Doces (Hero editorial, busca em pílula e departamentos)</span><span>16</span></li>
  <li><span>Figura 3 — Catálogo de Produtos com filtros por atributo (ADR 6) e estoque real</span><span>17</span></li>
  <li><span>Figura 4 — Página de Produto (PDP) com seletor de cores irmãs e disponibilidade em estoque</span><span>17</span></li>
  <li><span>Figura 5 — Tela de Cadastro de Cliente com validação de CPF e verificação em 2 etapas (2FA)</span><span>18</span></li>
  <li><span>Figura 6 — Modelo de Navegação da Loja Pública e do Painel Administrativo</span><span>18</span></li>
  <li><span>Figura 7 — Diagrama de Pacotes e Camadas do Monólito Modular (ADR 8)</span><span>19</span></li>
  <li><span>Figura 8 — Diagrama de Classes de Domínio (Catálogo, Pedidos, Estoque e Clientes)</span><span>20</span></li>
  <li><span>Figura 9 — Diagrama de Sequência: Checkout Web, Reserva de Estoque, Pagamento e Baixa no Ledger</span><span>22</span></li>
  <li><span>Figura 10 — Arquitetura de Implantação (Docker, Render, PostgreSQL e APIs Externas)</span><span>24</span></li>
</ul>

<h2>Índice de Tabelas</h2>
<ul class="toc-list">
  <li><span>Tabela 1 — Integrantes da equipe Vevey Co.</span><span>1</span></li>
  <li><span>Tabela 2 — Relatório de andamento: Barbara Vitoria da Silva</span><span>2</span></li>
  <li><span>Tabela 3 — Relatório de andamento: Matheus da Silva Araújo</span><span>2</span></li>
  <li><span>Tabela 4 — Relatório de andamento: Nathan Costa Santos</span><span>3</span></li>
  <li><span>Tabela 5 — Relatório de andamento: Ricardo Martins Pighin</span><span>3</span></li>
  <li><span>Tabela 6 — Stakeholders do projeto e suas necessidades</span><span>6</span></li>
  <li><span>Tabela 7 — Usuários do sistema, papéis RBAC e personas</span><span>7</span></li>
  <li><span>Tabela 8 — Matriz de riscos e ações de mitigação</span><span>7</span></li>
  <li><span>Tabela 9 — Documentação dos atores do sistema</span><span>13</span></li>
  <li><span>Tabela 10 — Requisitos Funcionais (RF01 a RF16)</span><span>14</span></li>
  <li><span>Tabela 11 — Requisitos Não Funcionais (RNF01 a RNF11)</span><span>15</span></li>
  <li><span>Tabela 12 — Especificação dos pacotes do sistema (responsabilidades e elementos principais)</span><span>20</span></li>
  <li><span>Tabela 13 — Dicionário das tabelas do banco de dados PostgreSQL</span><span>22</span></li>
  <li><span>Tabela 14 — Matriz de Rastreabilidade de Casos de Teste do Sistema</span><span>24</span></li>
  <li><span>Tabela 15 — Resumo das Decisões Arquiteturais (ADRs 1 a 9)</span><span>26</span></li>
  <li><span>Tabela 16 — Credenciais de homologação para testes e demonstração</span><span>28</span></li>
  <li><span>Tabela 17 — Guia rápido de resolução de problemas</span><span>28</span></li>
</ul>

<!-- ==================== CAPÍTULO 1 ==================== -->
<div class="page-break"></div>
<h1>1. Visão e Escopo do Projeto</h1>

<h2>1.1 Descrição do problema</h2>

<h3>1.1.1 Contexto do projeto</h3>
<p>A <strong>Vevey Doces</strong> é uma loja especializada em chocolates em caixa, balas, embalagens de papelão e artigos para festas, atuando no varejo e em pacotes fechados por meio de loja física, Instagram e WhatsApp Business. Antes da implantação do sistema, a operação comercial era fragmentada e dependente de controles manuais: o Instagram gerava interesse mas redirecionava todo o atendimento para o WhatsApp; a equipe precisava consultar preços e disponibilidade manualmente em planilhas; e as vendas presenciais na loja física não eram sincronizadas em tempo real com os pedidos remotos, provocando conflitos de estoque e retrabalho operacional.</p>
<p>Embora existam plataformas de e-commerce de prateleira (como Nuvemshop ou Loja Integrada), a decisão pelo desenvolvimento próprio justificou-se por três fatores estratégicos: (i) integração direta do estoque único entre loja física (PDV Balcão) e loja virtual com rastreabilidade via ledger; (ii) controle integral sobre identidade visual, catálogo plano por atributos (cores e tipos) e regras comerciais (desconto Pix, cupons e caixas fechadas); e (iii) propriedade total sobre os dados de clientes, identidades de contato e histórico de pedidos.</p>

<h3>1.1.2 Stakeholders</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 6 — Stakeholders do projeto e suas necessidades</div>
  <table>
    <thead><tr><th style="width:190px;">Stakeholder</th><th>Necessidades principais</th></tr></thead>
    <tbody>
      <tr><td><strong>Proprietária da Vevey Doces</strong></td><td>Aumentar as vendas, reduzir trabalho manual, profissionalizar a marca e obter indicadores confiáveis (faturamento, ticket médio, vendas por canal e produtos mais vendidos).</td></tr>
      <tr><td><strong>Gerência de Vendas</strong></td><td>Gerenciar catálogo, promoções, cupons e acompanhar todos os pedidos (Site, PDV Balcão e WhatsApp) com controle de estoque unificado e auditável.</td></tr>
      <tr><td><strong>Equipe de Atendimento</strong></td><td>Registrar vendas presenciais rapidamente no PDV Balcão, localizar clientes por telefone (incluindo números antigos) e consultar status de pedidos sem depender de planilhas.</td></tr>
      <tr><td><strong>Equipe de Logística</strong></td><td>Visualizar pedidos pagos, atualizar status de separação/envio/retirada e realizar entradas de fornecedores ou contagens físicas de estoque.</td></tr>
      <tr><td><strong>Cliente Final / Revendedor</strong></td><td>Pesquisar produtos por nome, SKU ou cor da festa, conferir disponibilidade real, calcular frete com data prevista e acompanhar pedidos em sua conta.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>1.1.3 Usuários e Personas</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 7 — Usuários do sistema, papéis RBAC e personas</div>
  <table>
    <thead><tr><th style="width:145px;">Papel (Guard)</th><th style="width:225px;">Necessidades no produto</th><th>Persona representativa</th></tr></thead>
    <tbody>
      <tr><td><strong>Cliente</strong><br/>(Guard <code>customers</code>)</td><td>Navegar no catálogo, filtrar por cor/tipo, comprar com Pix ou cartão, aplicar cupom, verificar conta em 2 etapas e acompanhar pedidos.</td><td><strong>Carla, 34 anos</strong>, organizadora de eventos. Planeja festas infantis e corporativas; valoriza busca rápida, estoque confiável e prazo de entrega claro.</td></tr>
      <tr><td><strong>Atendente</strong><br/>(RBAC <code>attendant</code>)</td><td>Registrar vendas no PDV Balcão, consultar estoque em tempo real, atender pedidos e localizar clientes por telefone.</td><td><strong>Juliana, 30 anos</strong>, atendente. Atende clientes no balcão e no WhatsApp diariamente e precisa de agilidade sem consultar planilhas.</td></tr>
      <tr><td><strong>Logística</strong><br/>(RBAC <code>logistics</code>)</td><td>Separar pedidos pagos, atualizar entrega, lançar notas de entrada e realizar contagens físicas no ledger de estoque.</td><td><strong>Equipe de Estoque</strong>. Responsável por manter a acuracidade entre prateleira física e loja virtual.</td></tr>
      <tr><td><strong>Administrador</strong><br/>(RBAC <code>admin</code> / <code>super_admin</code>)</td><td>Gerir catálogo, cupons, usuários/papéis, indicadores gerenciais no Dashboard e auditoria completa de movimentações.</td><td><strong>Miriam, 60 anos</strong>, sócia-gerente. Necessita de visão gerencial clara, relatórios por canal e controle seguro da operação.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>1.1.4 Riscos e Mitigações</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 8 — Matriz de riscos e ações de mitigação</div>
  <table>
    <thead><tr><th style="width:185px;">Risco</th><th style="width:70px;">Prob.</th><th style="width:75px;">Impacto</th><th>Ação de Mitigação Adotada</th></tr></thead>
    <tbody>
      <tr><td>Divergência entre estoque físico e online</td><td>Alta</td><td>Alto</td><td>Adoção de ledger append-only (<code>stock_movements</code>), reservas de 30 min com <code>lockForUpdate</code> ordenado e venda presencial como pedido <code>pos</code> (ADRs 2 e 5).</td></tr>
      <tr><td>Erro de cálculo monetário (ponto flutuante)</td><td>Média</td><td>Alto</td><td>Armazenamento exclusivo em centavos inteiros (<code>bigint</code>) com <code>brick/money</code>, rateio exato por linha e <code>CHECK</code> no PostgreSQL (ADR 1).</td></tr>
      <tr><td>Vazamento ou escalação de acesso</td><td>Baixa</td><td>Muito Alto</td><td>Guards separados (<code>web</code> vs. <code>customers</code>), RBAC restrito no Filament, 2FA por código OTP, rate limit de login e validação de CPF (LGPD).</td></tr>
      <tr><td>Indisponibilidade de APIs externas</td><td>Média</td><td>Médio</td><td>Cache local, timeouts curtos e fallback automático (BrasilAPI CEP v2 → ViaCEP; Resend → modo demonstração; frete local).</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>1.1.5 Premissas</h3>
<ul>
  <li><strong>Arquitetura e Stack:</strong> Após revisão técnica documentada nas ADRs (novembro de 2026), o sistema foi consolidado em <strong>Laravel 13 + PHP 8.4 + Filament 5 + PostgreSQL + Tailwind CSS v4</strong>, adotando arquitetura de monólito modular com 7 módulos desacoplados.</li>
  <li><strong>Precisão financeira:</strong> Todas as operações ocorrem em Reais (BRL) calculados em centavos no servidor, preservando snapshot histórico nos itens vendidos.</li>
  <li><strong>Operação multicanal:</strong> Todo pedido nasce pelo mesmo serviço central (<code>PlaceOrder</code>), diferenciando-se pelo atributo de canal (<code>web</code>, <code>pos</code> ou <code>whatsapp</code>).</li>
</ul>

<h2>1.2 Visão da solução</h2>

<h3>1.2.1 Declaração de visão</h3>
<p>Para a equipe da Vevey Doces e seus clientes de varejo e eventos que precisam comprar e gerenciar doces, embalagens e artigos de festa sem conflitos de estoque, a plataforma <strong>Website Vevey</strong> é um sistema web modular de e-commerce e gestão operacional que unifica vitrine online, PDV de balcão, controle de estoque auditável por ledger, gestão de clientes com verificação em duas etapas e painel gerencial em tempo real.</p>

<h3>1.2.2 Cenários de utilização do sistema</h3>
<ul>
  <li><strong>Cenário 1 — Compra online pela loja:</strong> Carla encontra um link no Instagram, acessa a loja, filtra balões pela paleta de cores (Azul + Metalizado), adiciona caixas de chocolates ao carrinho, aplica o cupom <code>VEVEY10</code>, calcula SEDEX/PAC com data prevista de entrega (pulando feriados), finaliza via Pix (5% OFF) e acompanha o pedido em <em>Minha Conta</em>.</li>
  <li><strong>Cenário 2 — Venda presencial no PDV Balcão (ADR 5):</strong> Um cliente compra pessoalmente na loja física. Juliana acessa <code>/admin/pdv</code>, seleciona os produtos por nome ou SKU, escolhe a forma de pagamento e conclui a venda; o sistema cria um pedido <code>pos</code> já pago e entregue, dando baixa automática no ledger (permitindo saldo negativo com alerta visual caso uma entrada ainda não tenha sido lançada).</li>
  <li><strong>Cenário 3 — Gestão, reposição e auditoria:</strong> A equipe de logística lança uma entrada de fornecedor ou realiza contagem física em <code>/admin/stock-levels</code> (nunca editando o saldo diretamente). Miriam acompanha o faturamento, ticket médio, vendas por canal e próximas datas sazonais no Dashboard e audita cada ação em <code>/admin/audit-log</code>.</li>
</ul>

<h3>1.2.3 Features do produto</h3>
<ul>
  <li>Vitrine editorial responsiva com busca preditiva ao vivo, filtros combinados por atributos (OU no mesmo atributo, E entre atributos diferentes — ADR 6) e seletor de cores irmãs na página do produto.</li>
  <li>Carrinho lateral com barra de progresso para frete grátis (R$ 150,00) e checkout com ViaCEP/BrasilAPI, SEDEX/PAC com dias úteis reais, cupom de desconto ao vivo e pagamento Sandbox (QR Code Pix + simulação de aprovação).</li>
  <li>Área do cliente autenticada em guard isolado (<code>customers</code>) com cadastro protegido por verificação em 2 etapas (OTP de 6 dígitos), validação oficial de CPF, histórico de pedidos e endereços salvos.</li>
  <li>Painel Administrativo Filament 5 com RBAC (4 papéis), Dashboard gerencial, PDV Balcão, CRUD de Catálogo e Cupons, Controle de Estoque com Ledger e Reservas, Gestão de Clientes com identidades aposentáveis e Logs de Auditoria unificados.</li>
</ul>

<!-- ==================== CAPÍTULO 2 ==================== -->
<div class="page-break"></div>
<h1>2. Requisitos de Software</h1>

<h2>2.1 Casos de uso</h2>
<p>A Figura 1 apresenta a visão geral dos principais casos de uso do sistema e os quatro atores que interagem com a plataforma.</p>

<div class="figure-wrap">
  {SVG_USE_CASES}
  <div class="figure-caption">Figura 1 — Diagrama de Casos de Uso do Sistema Vevey Doces</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>2.1.1 Documentação dos casos de uso</h3>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Realizar Compra (Checkout Online) — ID: UC02</div>
  <p><strong>Resumo:</strong> O cliente revisa os itens do carrinho, informa dados de contato e entrega (ou retirada), aplica cupom opcional, escolhe Pix ou cartão e finaliza o pedido com reserva automática de estoque.</p>
  <p><strong>Atores primários:</strong> Cliente. <strong>Atores secundários:</strong> BrasilAPI/ViaCEP, Módulo de Estoque.</p>
  <p><strong>Pré-condições:</strong> O carrinho possui ao menos um produto ativo com saldo disponível em <code>stock_levels</code>.</p>
  <p><strong>Fluxo principal:</strong></p>
  <ol>
    <li>O cliente acessa <code>/checkout</code> e preenche (ou confirma autopreenchimento) de nome, e-mail e telefone.</li>
    <li>Seleciona retirada na loja (grátis) ou entrega pelos Correios, informando o CEP para cotação de SEDEX/PAC com previsão em dias úteis reais.</li>
    <li>Opcionalmente informa um código de cupom (ex.: <code>VEVEY10</code>) validado em tempo real no servidor.</li>
    <li>Escolhe a forma de pagamento (Pix com 5% OFF ou Cartão) e confirma o pedido.</li>
    <li>O serviço <code>PlaceOrder</code> grava <code>orders</code>, <code>order_items</code> (snapshot), <code>order_adjustments</code>, <code>payments</code> e cria reservas de 30 minutos em <code>stock_reservations</code>.</li>
    <li>O sistema exibe o código do pedido (<code>VV-2026-XXXXXX</code>), o QR Code Pix e a opção de aprovação Sandbox.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Pedido persistido com status <code>pending</code> e estoque reservado; após aprovação do pagamento, a reserva é convertida em movimento <code>sale</code> no ledger.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Estoque insuficiente bloqueia a finalização retornando HTTP 422; (A2) Cupom expirado ou abaixo do mínimo exibe aviso sem travar o checkout.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Cadastrar Conta com Verificação em 2 Etapas (2FA) — ID: UC03</div>
  <p><strong>Resumo:</strong> Um visitante ou cliente que já comprou pelo WhatsApp/site cria ou ativa sua conta pessoal mediante confirmação de um código OTP de 6 dígitos.</p>
  <p><strong>Atores primários:</strong> Cliente. <strong>Atores secundários:</strong> TransactionalMailer (Resend API / SMTP).</p>
  <p><strong>Pré-condições:</strong> Não existe conta verificada com o mesmo e-mail.</p>
  <p><strong>Fluxo principal:</strong></p>
  <ol>
    <li>O usuário preenche nome, e-mail, WhatsApp, CPF opcional e senha em <code>/cadastro</code>.</li>
    <li>O sistema valida o CPF (algoritmo Módulo 11), cria ou vincula o registro em <code>customers</code> e normaliza os contatos em <code>customer_identities</code>.</li>
    <li>Gera um código OTP de 6 dígitos (validade de 15 minutos), dispara o envio por e-mail e redireciona para <code>/verificar-conta</code>.</li>
    <li>O usuário informa o código de 6 dígitos; o sistema marca <code>email_verified_at</code>, autentica no guard <code>customers</code> e abre <code>/conta</code>.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Conta verificada e autenticada com acesso exclusivo aos próprios pedidos e endereços.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Código inválido ou expirado é rejeitado com opção de reenvio; (A2) CPF inválido bloqueia o envio antes da gravação.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Registrar Venda Presencial no PDV Balcão — ID: UC04</div>
  <p><strong>Resumo:</strong> A atendente registra uma venda presencial na loja física como um pedido de canal <code>pos</code>, dando baixa direta e rastreável no estoque.</p>
  <p><strong>Atores primários:</strong> Atendente / Administrador. <strong>Atores secundários:</strong> Módulo de Estoque.</p>
  <p><strong>Pré-condições:</strong> Usuário autenticado no painel <code>/admin</code> com permissão sobre pedidos.</p>
  <p><strong>Fluxo principal:</strong></p>
  <ol>
    <li>A atendente abre <code>/admin/pdv</code>, pesquisa produtos por nome, SKU ou código de barras e adiciona quantidades ao carrinho do balcão.</li>
    <li>Seleciona a forma de pagamento (Dinheiro, Pix, Débito ou Crédito) e conclui a venda.</li>
    <li>O serviço <code>PlaceOrder</code> cria o pedido com <code>channel = pos</code>, <code>payment_status = paid</code>, <code>fulfillment_status = delivered</code> e <code>seller_id</code> preenchido.</li>
    <li><code>RecordDirectSale</code> registra a saída <code>sale</code> no ledger <code>stock_movements</code>; caso algum item fique com saldo negativo, o sistema conclui a venda e exibe alerta de reposição.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Venda contabilizada nos relatórios por canal, estoque atualizado e evento gravado na auditoria.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Ajustar ou Receber Estoque via Ledger — ID: UC07</div>
  <p><strong>Resumo:</strong> A equipe de logística ou administração registra entradas de fornecedores ou contagens físicas de inventário sem jamais sobrescrever o saldo manualmente sem histórico.</p>
  <p><strong>Atores primários:</strong> Logística / Administrador.</p>
  <p><strong>Fluxo principal:</strong></p>
  <ol>
    <li>O usuário acessa <code>/admin/stock-levels</code> e aciona <em>Lançar entrada</em> ou <em>Contagem / Ajustar</em> no produto desejado.</li>
    <li>Informa a quantidade recebida (ou a quantidade contada na prateleira) e o motivo obrigatório.</li>
    <li>A action transacional trava a linha com <code>lockForUpdate</code>, calcula a diferença, insere uma linha imutável em <code>stock_movements</code> e atualiza <code>stock_levels</code>.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Saldo recalculado de forma atômica e auditável em <code>/admin/audit-log</code>.</p>
</div>

<h3>2.1.2 Documentação dos atores</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 9 — Documentação dos atores do sistema</div>
  <table>
    <thead><tr><th style="width:155px;">Ator</th><th style="width:105px;">Tipo</th><th>Descrição e responsabilidades</th></tr></thead>
    <tbody>
      <tr><td><strong>Cliente</strong></td><td>Primário</td><td>Comprador final ou revendedor que navega na loja pública, realiza pedidos e gerencia sua conta em <code>/conta</code>.</td></tr>
      <tr><td><strong>Atendente</strong></td><td>Primário</td><td>Opera o PDV Balcão (<code>/admin/pdv</code>), acompanha pedidos e consulta clientes por telefone/identidade.</td></tr>
      <tr><td><strong>Logística</strong></td><td>Primário</td><td>Executa separação/entrega de pedidos e movimentações de entrada/contagem no ledger de estoque.</td></tr>
      <tr><td><strong>Administrador / SuperAdmin</strong></td><td>Primário</td><td>Gerencia catálogo, cupons, papéis RBAC, acompanha o Dashboard e audita os logs de atividade.</td></tr>
      <tr><td><strong>BrasilAPI / ViaCEP / Resend</strong></td><td>Secundário</td><td>Serviços externos HTTPS para consulta de CEP, feriados nacionais (dias úteis) e disparo de e-mails 2FA.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>2.2 Requisitos funcionais</h2>
<div class="table-wrap">
  <div class="table-caption">Tabela 10 — Requisitos Funcionais (RF01 a RF16)</div>
  <table>
    <thead><tr><th style="width:65px;">Código</th><th>Descrição do Requisito Funcional</th><th style="width:95px;">Módulo</th></tr></thead>
    <tbody>
      <tr><td><strong>RF01</strong></td><td>Permitir cadastro, edição, duplicação e inativação (soft delete) de produtos planos com SKU único, preço e preço promocional.</td><td><code>catalog</code></td></tr>
      <tr><td><strong>RF02</strong></td><td>Organizar produtos em categorias hierárquicas (até 2 níveis) e atributos dinâmicos (ex.: Cor, Tipo) para filtros combinados.</td><td><code>catalog</code></td></tr>
      <tr><td><strong>RF03</strong></td><td>Fornecer busca full-text em português e busca preditiva ao vivo por nome, descrição, SKU e código de barras.</td><td><code>catalog</code> / <code>panel-site</code></td></tr>
      <tr><td><strong>RF04</strong></td><td>Exibir disponibilidade real de estoque nos cards e na página do produto, bloqueando compra online quando esgotado.</td><td><code>inventory</code> / <code>panel-site</code></td></tr>
      <tr><td><strong>RF05</strong></td><td>Gerenciar carrinho de compras com barra de progresso para frete grátis (R$ 150,00) e ajuste rápido de quantidades.</td><td><code>panel-site</code></td></tr>
      <tr><td><strong>RF06</strong></td><td>Calcular frete (Retirada, SEDEX e PAC) via CEP com autopreenchimento (BrasilAPI/ViaCEP) e previsão em dias úteis reais (feriados nacionais).</td><td><code>panel-site</code></td></tr>
      <tr><td><strong>RF07</strong></td><td>Validar e aplicar cupons de desconto (percentual ou valor fixo, pedido mínimo, limite de usos e vigência) acumulados com desconto Pix de 5%.</td><td><code>orders</code></td></tr>
      <tr><td><strong>RF08</strong></td><td>Persistir pedidos com snapshot imutável dos itens, ajustes, pagamentos 1:N e rateio exato de desconto por linha (<code>Money::allocate</code>).</td><td><code>orders</code></td></tr>
      <tr><td><strong>RF09</strong></td><td>Criar reservas de estoque de 30 minutos no checkout, convertendo em baixa <code>sale</code> na confirmação do pagamento ou liberando no cancelamento.</td><td><code>inventory</code></td></tr>
      <tr><td><strong>RF10</strong></td><td>Disponibilizar tela de PDV Balcão (<code>/admin/pdv</code>) que registra vendas presenciais como pedidos <code>pos</code> já pagos e entregues.</td><td><code>panel-admin</code> / <code>orders</code></td></tr>
      <tr><td><strong>RF11</strong></td><td>Permitir entradas de fornecedor e contagens físicas de estoque exclusivamente por ações no ledger, com alerta de saldo negativo/mínimo.</td><td><code>inventory</code> / <code>panel-admin</code></td></tr>
      <tr><td><strong>RF12</strong></td><td>Permitir cadastro e login de clientes em guard separado com validação de CPF e verificação em 2 etapas via código OTP de 6 dígitos.</td><td><code>customers</code> / <code>panel-site</code></td></tr>
      <tr><td><strong>RF13</strong></td><td>Armazenar identidades de contato normalizadas (telefone E.164, WhatsApp, e-mail) com aposentadoria (<code>retired_at</code>) e busca por números antigos.</td><td><code>customers</code></td></tr>
      <tr><td><strong>RF14</strong></td><td>Disponibilizar área <em>Minha Conta</em> (<code>/conta</code>) exibindo estritamente os pedidos do cliente autenticado e gerenciamento de endereços salvos.</td><td><code>panel-site</code></td></tr>
      <tr><td><strong>RF15</strong></td><td>Apresentar Dashboard gerencial com faturamento, ticket médio, vendas por canal, top produtos, alertas de estoque e próximos feriados.</td><td><code>panel-admin</code></td></tr>
      <tr><td><strong>RF16</strong></td><td>Fornecer painel unificado de Logs de Auditoria (<code>/admin/audit-log</code>) rastreando responsável, data/hora e motivo em alterações de estoque e pedidos.</td><td><code>panel-admin</code></td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>2.3 Requisitos não funcionais</h2>
<div class="table-wrap">
  <div class="table-caption">Tabela 11 — Requisitos Não Funcionais (RNF01 a RNF11)</div>
  <table>
    <thead><tr><th style="width:72px;">Código</th><th style="width:125px;">Categoria</th><th>Especificação</th></tr></thead>
    <tbody>
      <tr><td><strong>RNF01</strong></td><td>Integridade financeira</td><td>Todos os valores monetários armazenados em centavos (<code>bigint</code>) via <code>brick/money</code> com <code>CHECK</code> de totais no PostgreSQL.</td></tr>
      <tr><td><strong>RNF02</strong></td><td>Concorrência</td><td>Operações de estoque protegidas por <code>DB::transaction</code> e <code>lockForUpdate</code> ordenado por <code>product_id</code> para prevenção de deadlocks.</td></tr>
      <tr><td><strong>RNF03</strong></td><td>Idempotência</td><td>Índice único composto em <code>stock_movements (reference_type, reference_id, reason, product_id)</code> impedindo baixa duplicada.</td></tr>
      <tr><td><strong>RNF04</strong></td><td>Segurança / Auth</td><td>Separação estrita entre guard da equipe (<code>web</code>) e guard de clientes (<code>customers</code>); senhas com hash bcrypt/Argon2.</td></tr>
      <tr><td><strong>RNF05</strong></td><td>Autorização</td><td>Controle de acesso baseado em papéis (RBAC) com modo <code>strictAuthorization</code> no Filament e policies em todos os models.</td></tr>
      <tr><td><strong>RNF06</strong></td><td>Proteção CSRF/Brute</td><td>Proteção CSRF ativa em todas as rotas web e bloqueio por <code>RateLimiter</code> após 5 tentativas falhas de login (RNF007 original).</td></tr>
      <tr><td><strong>RNF07</strong></td><td>Privacidade (LGPD)</td><td>Validação matemática local de CPF sem consulta indevida de dados pessoais em APIs de terceiros; soft delete em dados sensíveis.</td></tr>
      <tr><td><strong>RNF08</strong></td><td>Desempenho</td><td>Índice GIN sobre coluna gerada <code>tsvector</code> para busca full-text e cache de 24h/7d nas consultas às APIs externas.</td></tr>
      <tr><td><strong>RNF09</strong></td><td>Manutenibilidade</td><td>Fronteiras entre módulos garantidas automaticamente por testes de arquitetura Pest (<code>ModuleDependenciesTest</code>).</td></tr>
      <tr><td><strong>RNF10</strong></td><td>Usabilidade</td><td>Interface responsiva (mobile e desktop) seguindo o <code>DESIGN.md</code> próprio da marca com fonte tabular (<code>tabular-nums</code>) em preços.</td></tr>
      <tr><td><strong>RNF11</strong></td><td>Rastreabilidade</td><td>Histórico completo de transições de status e movimentações de estoque com identificação do usuário responsável e observação.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>2.4 Protótipos do software</h2>

<h3>2.4.1 Telas implementadas do sistema</h3>
<p>As Figuras 2 a 5 apresentam capturas reais da aplicação em execução, evidenciando a identidade visual <strong>Selo Confeitaria Vevey</strong>, o Hero fotográfico editorial, os filtros por atributos (ADR 6), a exibição de estoque real e o formulário de cadastro com CPF e preparação para verificação em duas etapas.</p>

<div class="figure-wrap">
  <img src="{shot_home}" alt="Tela inicial da loja Vevey Doces"/>
  <div class="figure-caption">Figura 2 — Tela Inicial da Loja Vevey Doces (Hero editorial, busca em pílula e departamentos)</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>

<div class="figure-wrap">
  <img src="{shot_catalog}" alt="Catálogo de produtos da Vevey Doces"/>
  <div class="figure-caption">Figura 3 — Catálogo de Produtos com filtros por atributo (ADR 6) e estoque real</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>

<div class="figure-wrap">
  <img src="{shot_product}" alt="Página de detalhes do produto"/>
  <div class="figure-caption">Figura 4 — Página de Produto (PDP) com seletor de cores irmãs e disponibilidade em estoque</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>

<div class="figure-wrap">
  <img src="{shot_register}" alt="Tela de cadastro de cliente com 2FA"/>
  <div class="figure-caption">Figura 5 — Tela de Cadastro de Cliente com validação de CPF e verificação em 2 etapas (2FA)</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>

<h3>2.4.2 Modelo de navegação</h3>
<p>A Figura 6 ilustra o fluxo de navegação entre as telas da loja pública (<code>panel-site</code>) e os recursos do painel administrativo (<code>panel-admin</code>).</p>

<div class="figure-wrap">
  {SVG_NAVIGATION}
  <div class="figure-caption">Figura 6 — Modelo de Navegação da Loja Pública e do Painel Administrativo</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== CAPÍTULO 3 ==================== -->
<div class="page-break"></div>
<h1>3. Projeto do Sistema</h1>

<h2>3.1 Modelo estrutural do sistema</h2>

<h3>3.1.1 Diagrama de pacotes (Monólito Modular — ADR 8)</h3>
<p>O sistema adota a arquitetura de <strong>monólito modular</strong> (<code>internachi/modular</code>), particionado em sete módulos independentes organizados em quatro camadas estritas. Chamadas síncronas descem sempre por meio de <em>Actions</em> e <em>DTOs</em> imutáveis (sufixo <code>Data</code>), enquanto modelos Eloquent não atravessam fronteiras entre módulos de domínio. A Figura 7 detalha essa divisão.</p>

<div class="figure-wrap">
  {SVG_PACKAGES}
  <div class="figure-caption">Figura 7 — Diagrama de Pacotes e Camadas do Monólito Modular (ADR 8)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="table-wrap">
  <div class="table-caption">Tabela 12 — Especificação dos pacotes do sistema (responsabilidades e elementos principais)</div>
  <table>
    <thead><tr><th style="width:120px;">Pacote</th><th style="width:240px;">Responsabilidades</th><th>Elementos principais</th></tr></thead>
    <tbody>
      <tr><td><code>Vevey\Catalog</code></td><td>Gestão do catálogo plano de produtos, categorias, atributos dinâmicos, mídias WebP e busca full-text.</td><td><code>Product</code>, <code>Category</code>, <code>Attribute</code>, <code>AttributeValue</code>, <code>FindProductsForSale</code>, <code>ProductData</code>.</td></tr>
      <tr><td><code>Vevey\Inventory</code></td><td>Controle de estoque via ledger append-only, reservas de 30 min com lock ordenado e cache de saldo.</td><td><code>StockMovement</code>, <code>StockReservation</code>, <code>StockLevel</code>, <code>ReserveStock</code>, <code>FulfillOrderReservations</code>, <code>RecordDirectSale</code>, <code>AdjustStock</code>.</td></tr>
      <tr><td><code>Vevey\Customers</code></td><td>Cadastro autenticável em guard isolado, identidades de contato aposentáveis e endereços salvos.</td><td><code>Customer</code>, <code>CustomerIdentity</code>, <code>CustomerAddress</code>, <code>FindOrCreateCustomerByContact</code>, <code>RetireAndReplaceIdentity</code>.</td></tr>
      <tr><td><code>Vevey\Orders</code></td><td>Orquestração de vendas multicanal (Site, PDV, WhatsApp), snapshots de linha, cupons, pagamentos e histórico.</td><td><code>Order</code>, <code>OrderItem</code>, <code>OrderAdjustment</code>, <code>Coupon</code>, <code>Payment</code>, <code>PlaceOrder</code>, <code>UpdateOrderStatus</code>.</td></tr>
      <tr><td><code>Vevey\Permissions</code></td><td>Autorização RBAC baseada em morph map e sincronização automática dos 4 papéis operacionais.</td><td><code>Role</code>, <code>Permission</code>, <code>Roles</code>, <code>PermissionsEnum</code>, <code>ModelPolicy</code>, <code>SyncPermissionsCommand</code>.</td></tr>
      <tr><td><code>Vevey\PanelAdmin</code></td><td>Interface administrativa Filament 5: Dashboard, PDV Balcão, CRUDs operacionais e Logs de Auditoria.</td><td><code>OrderResource</code>, <code>StockLevelResource</code>, <code>CustomerResource</code>, <code>CouponResource</code>, <code>PosSale</code>, <code>AuditLog</code>, Widgets.</td></tr>
      <tr><td><code>Vevey\PanelSite</code></td><td>Interface pública da loja: vitrine, busca preditiva, filtros ADR 6, checkout Sandbox e área do cliente 2FA.</td><td><code>StorefrontController</code>, <code>CheckoutController</code>, <code>CustomerAuthController</code>, <code>CustomerAccountController</code>.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>3.1.2 Diagrama de classes de domínio</h3>
<p>A Figura 8 resume as principais classes de domínio, seus atributos tipados (<code>Money</code>, enums PHP e UUIDs) e os relacionamentos estruturais entre os módulos.</p>

<div class="figure-wrap">
  {SVG_CLASSES}
  <div class="figure-caption">Figura 8 — Diagrama de Classes de Domínio (Catálogo, Pedidos, Estoque e Clientes)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>3.2 Modelo de banco de dados do sistema</h2>
<p>O banco de dados relacional é gerenciado em <strong>PostgreSQL</strong> com chaves primárias em UUID v7, colunas monetárias em centavos inteiros, colunas geradas <code>STORED</code> (<code>products.search_vector</code>, <code>stock_movements.direction</code> e <code>stock_levels.available</code>), índices parciais únicos para soft deletes e identidades ativas, e ausência total de triggers (toda lógica de negócio reside na aplicação). A Tabela 12 consolida o dicionário de tabelas implementado.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 13 — Dicionário das tabelas do banco de dados PostgreSQL</div>
  <table>
    <thead><tr><th style="width:125px;">Módulo</th><th style="width:190px;">Tabela</th><th>Finalidade e principais restrições</th></tr></thead>
    <tbody>
      <tr><td><code>catalog</code></td><td><code>categories</code>, <code>products</code>, <code>category_product</code></td><td>Catálogo plano (soft delete), <code>CHECK</code> de preços positivos e coluna gerada <code>search_vector</code> com índice GIN.</td></tr>
      <tr><td><code>catalog</code></td><td><code>attributes</code>, <code>attribute_values</code>, <code>attribute_value_product</code></td><td>Atributos dinâmicos (Cor, Tipo) para filtros combinados (ADR 6) sem colunas esparsas.</td></tr>
      <tr><td><code>inventory</code></td><td><code>stock_movements</code></td><td>Ledger append-only (<code>qty &lt;&gt; 0</code>, <code>direction</code> stored) com unique de idempotência.</td></tr>
      <tr><td><code>inventory</code></td><td><code>stock_reservations</code>, <code>stock_levels</code></td><td>Reservas de 30 min (lazy expiration) e cache de saldo com <code>available = on_hand - reserved</code> (permite negativo no PDV).</td></tr>
      <tr><td><code>orders</code></td><td><code>orders</code>, <code>order_items</code></td><td>Pedidos multicanal (<code>web</code>, <code>pos</code>, <code>whatsapp</code>), status separados, <code>CHECK</code> de totais e snapshot de linha (ADR 3).</td></tr>
      <tr><td><code>orders</code></td><td><code>order_adjustments</code>, <code>coupons</code>, <code>payments</code>, <code>order_status_histories</code></td><td>Detalhamento de descontos (cupom/Pix), pagamentos 1:N e trilha de auditoria de transições de status.</td></tr>
      <tr><td><code>customers</code></td><td><code>customers</code>, <code>customer_identities</code>, <code>customer_addresses</code></td><td>Clientes autenticáveis (guard separado, código OTP 2FA), identidades com aposentadoria (<code>retired_at</code>) e endereços.</td></tr>
      <tr><td><code>permissions</code></td><td><code>rbac_roles</code>, <code>rbac_permissions</code> e pivôs</td><td>RBAC com UUIDs sincronizado automaticamente via <code>php artisan sync:permissions</code> (140 permissões).</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>3.3 Modelo de comportamentos do sistema</h2>
<p>A Figura 9 representa o diagrama de sequência do fluxo crítico de compra online: desde a submissão do checkout até a reserva transacional de estoque e a posterior baixa definitiva no ledger após a confirmação do pagamento.</p>

<div class="figure-wrap">
  {SVG_SEQUENCE}
  <div class="figure-caption">Figura 9 — Diagrama de Sequência: Checkout Web, Reserva de Estoque, Pagamento e Baixa no Ledger</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== CAPÍTULO 4 ==================== -->
<div class="page-break"></div>
<h1>4. Documentação Técnica do Sistema</h1>

<h2>4.1 Modelo de implementação do sistema</h2>
<p>O software foi implementado em <strong>PHP 8.4</strong> com <strong>Laravel 13</strong>, <strong>Filament 5</strong> (painel administrativo), <strong>Tailwind CSS v4</strong> e <strong>Vite 8</strong> (loja pública), utilizando <strong>PostgreSQL</strong> como SGBD principal. A base de código emprega <code>declare(strict_types=1)</code>, DTOs <code>final readonly</code>, casts customizados (<code>MoneyCast</code> sobre <code>brick/money</code>), enums tipados e <code>spatie/laravel-medialibrary</code> para gestão de imagens com conversão WebP.</p>

<h2>4.2 Modelos de testes do sistema</h2>
<p>A estratégia de verificação contempla três níveis complementares:</p>
<ul>
  <li><strong>Testes de Arquitetura (Pest Arch):</strong> o arquivo <code>tests/Arch/ModuleDependenciesTest.php</code> valida automaticamente que <code>app/</code> não importa nenhum módulo <code>Vevey\*</code> e que cada módulo respeita estritamente a hierarquia de camadas da ADR 8.</li>
  <li><strong>Testes Funcionais de Domínio (Pest Feature):</strong> suítes em <code>app-modules/catalog/tests</code>, <code>app-modules/orders/tests/Feature/PlaceOrderTest.php</code> (rateio de desconto Pix, snapshot imutável, cupom e canal <code>pos</code>), <code>app-modules/inventory/tests/Feature/InventoryLedgerTest.php</code> (simulação completa de 2 dias da ADR 2 + saldo negativo no balcão + <code>stock:rebuild</code>) e <code>app-modules/customers/tests/Feature/CustomerIdentityTest.php</code> (aposentadoria de telefone e busca por número antigo).</li>
  <li><strong>Validação E2E em PostgreSQL:</strong> verificação direta de migrations, constraints <code>CHECK</code>, colunas geradas <code>STORED</code>, fluxo 2FA com código OTP de 6 dígitos, checkout com pagamento Sandbox e rotas HTTP da loja e do Filament.</li>
</ul>

<div class="table-wrap">
  <div class="table-caption">Tabela 14 — Matriz de Rastreabilidade de Casos de Teste do Sistema</div>
  <table>
    <thead><tr><th style="width:46px;">ID</th><th style="width:48px;">UC</th><th style="width:125px;">Título do Teste</th><th style="width:110px;">Pré-condição</th><th>Passos principais</th><th style="width:135px;">Resultado Esperado</th><th style="width:58px;">Status</th></tr></thead>
    <tbody>
      <tr><td><strong>TC01</strong></td><td>UC01</td><td>Filtro combinado ADR 6</td><td>Produtos ativos com atributos Cor e Tipo</td><td>1. Abrir <code>/catalogo</code><br/>2. Marcar Azul + Metalizado</td><td>Lista apenas produtos que atendem Cor=Azul E Tipo=Metalizado</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC02</strong></td><td>UC02</td><td>Checkout Web com Cupom e Pix</td><td>Carrinho com itens e saldo disponível</td><td>1. Informar CEP<br/>2. Aplicar <code>VEVEY10</code><br/>3. Finalizar no Pix</td><td>Pedido persistido, desconto rateado e reserva de 30 min criada</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC03</strong></td><td>UC02</td><td>Bloqueio de venda sem saldo</td><td>Produto com saldo zero</td><td>1. Tentar finalizar pedido web acima do saldo</td><td>Servidor retorna HTTP 422 impedindo venda sem lastro</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC04</strong></td><td>UC03</td><td>Cadastro com CPF e 2FA</td><td>E-mail ainda não verificado</td><td>1. Cadastrar com CPF válido<br/>2. Inserir OTP incorreto e depois correto</td><td>OTP errado rejeitado; OTP correto ativa conta e abre <code>/conta</code></td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC05</strong></td><td>UC04</td><td>Venda presencial PDV Balcão</td><td>Atendente logado em <code>/admin/pdv</code></td><td>1. Selecionar itens<br/>2. Escolher pagamento<br/>3. Concluir venda</td><td>Pedido <code>pos</code> pago/entregue e baixa <code>sale</code> gravada no ledger</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC06</strong></td><td>UC07</td><td>Contagem física e ajuste</td><td>Logística logada em <code>/admin/stock-levels</code></td><td>1. Acionar Contagem/Ajustar<br/>2. Informar saldo contado e motivo</td><td>Diferença gravada como <code>adjustment</code> no ledger e saldo atualizado</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC07</strong></td><td>UC06</td><td>Auditoria de ações (RNF011)</td><td>SuperAdmin logado em <code>/admin/audit-log</code></td><td>1. Filtrar por módulo e responsável</td><td>Exibe linha do tempo com usuário, data/hora, ação e motivo</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC08</strong></td><td>UC08</td><td>Arquitetura modular (ADR 8)</td><td>Repositório com 7 módulos</td><td>1. Verificar imports entre camadas</td><td>Zero imports de <code>Vevey\*</code> em <code>app/</code>; fronteiras respeitadas</td><td><strong>Passou</strong></td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>4.3 Modelos de implantação do sistema</h2>
<p>O projeto está empacotado em container <strong>Docker</strong> (<code>Dockerfile</code> + <code>docker/start-render.sh</code>) com blueprint automatizado em <code>render.yaml</code> para implantação na nuvem (Render.com + PostgreSQL gerenciado), conforme ilustrado na Figura 10.</p>

<div class="figure-wrap">
  {SVG_DEPLOY}
  <div class="figure-caption">Figura 10 — Arquitetura de Implantação (Docker, Render, PostgreSQL e APIs Externas)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>4.4 Procedimento de instalação</h2>
<p>Para executar o sistema localmente em ambiente de desenvolvimento ou homologação (requisitos: PHP 8.4+, Composer 2, Node.js 22+ e PostgreSQL 16+):</p>
<pre>git clone https://github.com/nathocosta/vevey.git &amp;&amp; cd vevey
composer install
npm install &amp;&amp; npm run build
cp .env.example .env &amp;&amp; php artisan key:generate
# Configurar credenciais do PostgreSQL no arquivo .env
php artisan migrate
php artisan db:seed --class="Vevey\Catalog\Database\Seeders\CatalogSeeder"
php artisan db:seed --class="Vevey\Inventory\Database\Seeders\InitialStockSeeder"
php artisan db:seed --class="Database\Seeders\StaffUsersSeeder"
php artisan sync:permissions
php -S 127.0.0.1:8000 -t public</pre>

<h2>4.5 Documentação técnica do sistema (ADRs e Integrações)</h2>
<p>As decisões arquiteturais do projeto foram registradas formalmente em <code>docs/MODELAGEM_LARAVEL_ADR.md</code> e sintetizadas na Tabela 13. Além disso, o sistema expõe os comandos operacionais <code>php artisan stock:rebuild</code> (reconstrução do cache de saldo a partir do ledger) e <code>php artisan stock:expire-reservations</code> (agendado a cada 5 minutos em <code>routes/console.php</code>), bem como integrações seguras com <strong>BrasilAPI</strong> (CEP v2 e Feriados Nacionais), <strong>ViaCEP</strong> e <strong>Resend HTTP API</strong> para envio de e-mails 2FA.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 15 — Resumo das Decisões Arquiteturais (ADRs 1 a 9)</div>
  <table>
    <thead><tr><th style="width:72px;">ADR</th><th style="width:175px;">Tema</th><th>Decisão implementada</th></tr></thead>
    <tbody>
      <tr><td><strong>ADR 1</strong></td><td>Dinheiro</td><td>Centavos inteiros (<code>bigint</code>), <code>MoneyCast</code>, <code>order_adjustments</code>, rateio via <code>Money::allocate()</code> e <code>CHECK</code> de totais.</td></tr>
      <tr><td><strong>ADR 2</strong></td><td>Estoque (Ledger)</td><td>Ledger de entrada única append-only (<code>stock_movements</code>) + reservas de 30 min + cache <code>stock_levels</code> sob lock.</td></tr>
      <tr><td><strong>ADR 3</strong></td><td>Snapshot de Pedido</td><td>Snapshot de SKU, nome, preço e atributos em <code>order_items</code> sem tabela de versionamento de produto.</td></tr>
      <tr><td><strong>ADR 4</strong></td><td>Status de Pedido</td><td>Separação entre <code>payment_status</code> e <code>fulfillment_status</code>, timestamps de transição e <code>order_status_histories</code>.</td></tr>
      <tr><td><strong>ADR 5</strong></td><td>Venda Presencial (PDV)</td><td>Venda de balcão registrada como pedido <code>pos</code> (pago e entregue), permitindo saldo negativo com alerta.</td></tr>
      <tr><td><strong>ADR 6</strong></td><td>Catálogo e Busca</td><td>Catálogo plano (cada cor = produto), atributos dinâmicos (OU/E) e busca full-text GIN em português.</td></tr>
      <tr><td><strong>ADR 7</strong></td><td>Identidades de Cliente</td><td>Guard separado <code>customers</code>, login padrão com 2FA e <code>customer_identities</code> com aposentadoria (<code>retired_at</code>).</td></tr>
      <tr><td><strong>ADR 8</strong></td><td>Monólito Modular</td><td>7 módulos com camadas verificadas por Pest Arch, comunicação por Actions+DTOs e RBAC por morph map.</td></tr>
      <tr><td><strong>ADR 9</strong></td><td>Evolução da Busca</td><td>Documentação das limitações do radicalizador português e plano futuro de adoção de <code>unaccent</code> + <code>pg_trgm</code>.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>4.6 Manual do usuário</h2>

<h3>4.6.1 Introdução</h3>
<p>O <strong>Website Vevey</strong> oferece duas interfaces integradas: a <strong>Loja Virtual</strong> (<code>/</code>), voltada para clientes finais e organizadores de festas comprarem no varejo ou em caixas fechadas, e o <strong>Painel Administrativo</strong> (<code>/admin</code>), voltado para a equipe da Vevey Doces operar o PDV Balcão, controlar estoque via ledger, gerenciar pedidos/cupons e auditar atividades.</p>

<h3>4.6.2 Requisitos do sistema</h3>
<ul>
  <li><strong>Para uso via navegador (Cliente e Equipe):</strong> navegador moderno atualizado (Google Chrome, Microsoft Edge, Mozilla Firefox ou Safari) em computador ou smartphone com acesso à internet.</li>
  <li><strong>Para execução local (Homologação):</strong> PHP 8.4+, Composer 2, Node.js 22+ e PostgreSQL 16+.</li>
</ul>

<h3>4.6.3 Instalação e configuração</h3>
<p>A instalação local segue exatamente os comandos descritos na Seção 4.4. Para implantação em nuvem no Render.com, basta conectar o repositório GitHub (<code>nathocosta/vevey</code>) utilizando o arquivo <code>render.yaml</code>, que provisiona automaticamente o banco PostgreSQL, executa as migrations, cria os usuários da equipe e ativa o cupom demonstrativo <code>VEVEY10</code>. Para envio real de e-mails 2FA, configure a variável <code>RESEND_API_KEY</code>.</p>

<h3>4.6.4 Utilização do sistema</h3>
<p><strong>Loja Virtual (<code>/</code>):</strong> utilize a barra de busca em pílula no topo ou os filtros de categoria e cor em <code>/catalogo</code>; adicione itens pelos cards (ajustando a quantidade em <code>- 1 +</code>) ou na página do produto; clique em <em>Carrinho → Finalizar pedido</em>; informe o CEP para cotar SEDEX/PAC com previsão em dias úteis reais (ou escolha Retirada na loja), aplique o cupom <code>VEVEY10</code> e conclua via Pix ou Cartão. Em <em>Minha conta → Criar conta</em>, valide seu cadastro inserindo o código de 6 dígitos na tela de verificação em duas etapas.</p>
<p><strong>Painel Administrativo (<code>/admin</code>):</strong> acesse com uma das credenciais da Tabela 16. Utilize <strong>Vendas → PDV Balcão</strong> para vendas presenciais rápidas; <strong>Vendas → Pedidos</strong> para confirmar pagamentos e avançar entregas; <strong>Vendas → Cupons</strong> para criar campanhas; <strong>Estoque → Saldos de estoque</strong> para lançar entradas ou contagens físicas (<em>Aviso importante:</em> nunca tente alterar o saldo diretamente no banco, utilize sempre as ações do ledger); e <strong>Auditoria → Logs de auditoria</strong> para rastrear o histórico completo.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 16 — Credenciais de homologação para testes e demonstração</div>
  <table>
    <thead><tr><th>Perfil / Papel RBAC</th><th>E-mail de acesso</th><th>Senha</th><th>Escopo principal</th></tr></thead>
    <tbody>
      <tr><td>Super Admin</td><td><code>admin@vevey.com</code></td><td><code>password</code></td><td>Acesso total, papéis RBAC e Logs de auditoria.</td></tr>
      <tr><td>Administrador (Dona)</td><td><code>dona@vevey.com</code></td><td><code>password</code></td><td>Dashboard, catálogo, cupons, estoque, pedidos, clientes e auditoria.</td></tr>
      <tr><td>Atendente</td><td><code>atendente@vevey.com</code></td><td><code>password</code></td><td>PDV Balcão, gestão de pedidos e consulta de clientes.</td></tr>
      <tr><td>Logística</td><td><code>logistica@vevey.com</code></td><td><code>password</code></td><td>Separação/entrega de pedidos e movimentações de estoque.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>4.6.5 Resolução de problemas</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 17 — Guia rápido de resolução de problemas</div>
  <table>
    <thead><tr><th style="width:175px;">Problema observado</th><th style="width:190px;">Causa provável</th><th>Solução recomendada</th></tr></thead>
    <tbody>
      <tr><td>Produto aparece como <em>Esgotado</em> na loja</td><td>Saldo disponível (<code>on_hand - reserved</code>) igual ou menor que zero.</td><td>Acessar <code>/admin/stock-levels</code> e acionar <em>Lançar entrada</em> ou <em>Contagem / Ajustar</em>.</td></tr>
      <tr><td>Alerta de <em>Estoque negativo</em> no painel</td><td>Venda presencial realizada no PDV antes do lançamento da nota de entrada.</td><td>Lançar a entrada de mercadoria correspondente ou executar contagem física no ledger.</td></tr>
      <tr><td>Código 2FA não chegou por e-mail</td><td>Ambiente local/homologação sem chave <code>RESEND_API_KEY</code> ou SMTP configurado.</td><td>Utilizar o código exibido no card <em>Modo demonstração</em> em <code>/verificar-conta</code> ou configurar a chave Resend.</td></tr>
      <tr><td>Login bloqueado temporariamente</td><td>Mais de 5 tentativas consecutivas com senha incorreta (<code>RateLimiter</code>).</td><td>Aguardar o tempo indicado na mensagem (5 minutos) e tentar novamente com as credenciais corretas.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== CAPÍTULO 5 ==================== -->
<div class="page-break"></div>
<h1>5. Referências Bibliográficas</h1>
<div class="abnt-ref">
  <p>ASSOCIAÇÃO BRASILEIRA DE NORMAS TÉCNICAS. <strong>ABNT NBR 6023</strong>: informação e documentação: referências: elaboração. 2. ed. Rio de Janeiro: ABNT, 2018.</p>
  <p>BRASILAPI. <strong>Documentação oficial BrasilAPI (CEP v2 e Feriados Nacionais)</strong>. Disponível em: https://brasilapi.com.br/docs. Acesso em: 23 nov. 2026.</p>
  <p>FILAMENT. <strong>Filament v5 Documentation</strong>: accelerated Laravel development framework. Disponível em: https://filamentphp.com/docs. Acesso em: 23 nov. 2026.</p>
  <p>FOWLER, Martin. <strong>Patterns of Enterprise Application Architecture</strong>. Boston: Addison-Wesley, 2002.</p>
  <p>LARAVEL. <strong>Laravel 13.x Documentation</strong>: the PHP framework for web artisans. Disponível em: https://laravel.com/docs. Acesso em: 23 nov. 2026.</p>
  <p>POSTGRESQL GLOBAL DEVELOPMENT GROUP. <strong>PostgreSQL 16 Documentation</strong>: full text search and generated columns. Disponível em: https://www.postgresql.org/docs/. Acesso em: 23 nov. 2026.</p>
  <p>PRESSMAN, Roger S.; MAXIM, Bruce R. <strong>Engenharia de Software</strong>: uma abordagem profissional. 9. ed. Porto Alegre: AMGH, 2021.</p>
  <p>SOMMERVILLE, Ian. <strong>Engenharia de Software</strong>. 10. ed. São Paulo: Pearson Education do Brasil, 2019.</p>
  <p>VIACEP. <strong>ViaCEP — Webservice gratuito de consulta de CEP</strong>. Disponível em: https://viacep.com.br/. Acesso em: 23 nov. 2026.</p>
</div>

</body>
</html>
"""


def main() -> None:
    os.makedirs(BASE_DIR, exist_ok=True)
    html = build_html()
    with open(HTML_OUT, "w", encoding="utf-8") as f:
        f.write(html)

    prof = tempfile.mkdtemp(prefix="vevey-pdf-")
    try:
        file_url = "file:///" + HTML_OUT.replace("\\", "/").replace(" ", "%20")
        cmd = [
            EDGE_BIN,
            "--headless=new",
            f"--user-data-dir={prof}",
            "--disable-gpu",
            "--no-pdf-header-footer",
            f"--print-to-pdf={PDF_OUT}",
            file_url,
        ]
        subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        shutil.copy2(PDF_OUT, PDF_DOWNLOADS)
        size_kb = os.path.getsize(PDF_OUT) // 1024
        print(f"PDF GENERATED OK: {PDF_OUT} ({size_kb} KB)")
        print(f"COPIED TO DOWNLOADS: {PDF_DOWNLOADS}")
    finally:
        shutil.rmtree(prof, ignore_errors=True)


if __name__ == "__main__":
    main()
