# -*- coding: utf-8 -*-
"""
Gera a documentação completa N2 da equipe Vevey Co. (Laboratório de Engenharia de Software)
em total conformidade com os modelos oficiais 01, 02, 03 e de Atores/Casos de Uso da FATEC,
exportando para PDF via Microsoft Edge Headless.
"""
import base64
import os
import shutil
import subprocess
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


# ---------- DIAGRAMAS VETORIAIS EM SVG (FUNDO CLARO, LEGÍVEIS, PADRÃO ACADÊMICO UML/DER) ----------

SVG_CANVAS = r"""
<svg viewBox="0 0 820 430" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <!-- Cabeçalho -->
  <rect x="10" y="10" width="800" height="34" rx="6" fill="#1F1B16"/>
  <text x="24" y="31" font-family="Arial" font-size="12" font-weight="bold" fill="#FFFFFF">PROJECT MODEL CANVAS — WEBSITE VEVEY (VEVEY DOCES)</text>
  <text x="796" y="31" text-anchor="end" font-family="Arial" font-size="10" fill="#FDA4AF">Equipe Vevey Co. · FATEC 2026</text>

  <!-- Coluna 1: POR QUÊ? -->
  <rect x="10" y="52" width="154" height="120" rx="5" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.2"/>
  <text x="18" y="69" font-family="Arial" font-size="9.5" font-weight="bold" fill="#9F1239">JUSTIFICATIVAS</text>
  <text x="18" y="86" font-family="Arial" font-size="8.7" fill="#1F1B16">• Vendas fragmentadas em</text>
  <text x="24" y="99" font-family="Arial" font-size="8.7" fill="#1F1B16">Instagram e WhatsApp.</text>
  <text x="18" y="116" font-family="Arial" font-size="8.7" fill="#1F1B16">• Conflitos de estoque entre</text>
  <text x="24" y="129" font-family="Arial" font-size="8.7" fill="#1F1B16">balcão físico e pedidos.</text>
  <text x="18" y="146" font-family="Arial" font-size="8.7" fill="#1F1B16">• Falta de KPIs e auditoria</text>
  <text x="24" y="159" font-family="Arial" font-size="8.7" fill="#1F1B16">em planilhas manuais.</text>

  <rect x="10" y="178" width="154" height="118" rx="5" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.2"/>
  <text x="18" y="195" font-family="Arial" font-size="9.5" font-weight="bold" fill="#9F1239">OBJETIVO SMART</text>
  <text x="18" y="212" font-family="Arial" font-size="8.7" fill="#1F1B16">Entregar plataforma web de</text>
  <text x="18" y="225" font-family="Arial" font-size="8.7" fill="#1F1B16">e-commerce, PDV e estoque</text>
  <text x="18" y="238" font-family="Arial" font-size="8.7" fill="#1F1B16">unificado até nov/2026,</text>
  <text x="18" y="251" font-family="Arial" font-size="8.7" fill="#1F1B16">eliminando divergência de</text>
  <text x="18" y="264" font-family="Arial" font-size="8.7" fill="#1F1B16">saldo e agilizando pedidos</text>
  <text x="18" y="277" font-family="Arial" font-size="8.7" fill="#1F1B16">varejo e caixas fechadas.</text>

  <rect x="10" y="302" width="154" height="118" rx="5" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.2"/>
  <text x="18" y="319" font-family="Arial" font-size="9.5" font-weight="bold" fill="#9F1239">BENEFÍCIOS</text>
  <text x="18" y="336" font-family="Arial" font-size="8.7" fill="#1F1B16">• Estoque único auditável.</text>
  <text x="18" y="352" font-family="Arial" font-size="8.7" fill="#1F1B16">• Autoatendimento 24h.</text>
  <text x="18" y="368" font-family="Arial" font-size="8.7" fill="#1F1B16">• Redução de retrabalho.</text>
  <text x="18" y="384" font-family="Arial" font-size="8.7" fill="#1F1B16">• Dados e marca próprios.</text>
  <text x="18" y="400" font-family="Arial" font-size="8.7" fill="#1F1B16">• Decisão via Dashboard.</text>

  <!-- Coluna 2: O QUÊ? -->
  <rect x="171" y="52" width="154" height="120" rx="5" fill="#FFFBEB" stroke="#D97706" stroke-width="1.2"/>
  <text x="179" y="69" font-family="Arial" font-size="9.5" font-weight="bold" fill="#92400E">PRODUTO</text>
  <text x="179" y="86" font-family="Arial" font-size="8.7" fill="#1F1B16">Website Vevey:</text>
  <text x="179" y="102" font-family="Arial" font-size="8.7" fill="#1F1B16">• Loja pública responsiva</text>
  <text x="179" y="117" font-family="Arial" font-size="8.7" fill="#1F1B16">• Área do cliente com 2FA</text>
  <text x="179" y="132" font-family="Arial" font-size="8.7" fill="#1F1B16">• Painel Filament + PDV</text>
  <text x="179" y="147" font-family="Arial" font-size="8.7" fill="#1F1B16">• Ledger de estoque e</text>
  <text x="185" y="160" font-family="Arial" font-size="8.7" fill="#1F1B16">Auditoria (RNF11).</text>

  <rect x="171" y="178" width="154" height="242" rx="5" fill="#FFFBEB" stroke="#D97706" stroke-width="1.2"/>
  <text x="179" y="195" font-family="Arial" font-size="9.5" font-weight="bold" fill="#92400E">REQUISITOS CHAVE</text>
  <text x="179" y="214" font-family="Arial" font-size="8.7" fill="#1F1B16">• Catálogo plano por cor e</text>
  <text x="185" y="227" font-family="Arial" font-size="8.7" fill="#1F1B16">tipo com busca full-text.</text>
  <text x="179" y="245" font-family="Arial" font-size="8.7" fill="#1F1B16">• Frete CEP + dias úteis</text>
  <text x="185" y="258" font-family="Arial" font-size="8.7" fill="#1F1B16">reais (BrasilAPI/ViaCEP).</text>
  <text x="179" y="276" font-family="Arial" font-size="8.7" fill="#1F1B16">• Cupons + desconto Pix.</text>
  <text x="179" y="294" font-family="Arial" font-size="8.7" fill="#1F1B16">• Reservas de 30 min e</text>
  <text x="185" y="307" font-family="Arial" font-size="8.7" fill="#1F1B16">baixa atômica no ledger.</text>
  <text x="179" y="325" font-family="Arial" font-size="8.7" fill="#1F1B16">• RBAC com 4 papéis e</text>
  <text x="185" y="338" font-family="Arial" font-size="8.7" fill="#1F1B16">guards separados.</text>
  <text x="179" y="356" font-family="Arial" font-size="8.7" fill="#1F1B16">• Validação CPF (Mód. 11)</text>
  <text x="185" y="369" font-family="Arial" font-size="8.7" fill="#1F1B16">e OTP de 6 dígitos (2FA).</text>
  <text x="179" y="387" font-family="Arial" font-size="8.7" fill="#1F1B16">• Dinheiro em centavos</text>
  <text x="185" y="400" font-family="Arial" font-size="8.7" fill="#1F1B16">com snapshot imutável.</text>

  <!-- Coluna 3: QUEM? -->
  <rect x="332" y="52" width="154" height="178" rx="5" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.2"/>
  <text x="340" y="69" font-family="Arial" font-size="9.5" font-weight="bold" fill="#1E3A8A">STAKEHOLDERS</text>
  <text x="340" y="88" font-family="Arial" font-size="8.7" fill="#1F1B16">• Proprietária da Vevey</text>
  <text x="346" y="101" font-family="Arial" font-size="8.7" fill="#1F1B16">Doces (Miriam).</text>
  <text x="340" y="119" font-family="Arial" font-size="8.7" fill="#1F1B16">• Gerência de Vendas.</text>
  <text x="340" y="137" font-family="Arial" font-size="8.7" fill="#1F1B16">• Equipe de Atendimento</text>
  <text x="346" y="150" font-family="Arial" font-size="8.7" fill="#1F1B16">e Logística / Estoque.</text>
  <text x="340" y="168" font-family="Arial" font-size="8.7" fill="#1F1B16">• Clientes finais e</text>
  <text x="346" y="181" font-family="Arial" font-size="8.7" fill="#1F1B16">organizadores de festas.</text>
  <text x="340" y="199" font-family="Arial" font-size="8.7" fill="#1F1B16">• Docente orientador</text>
  <text x="346" y="212" font-family="Arial" font-size="8.7" fill="#1F1B16">(FATEC — LabEngSW).</text>

  <rect x="332" y="236" width="154" height="184" rx="5" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.2"/>
  <text x="340" y="253" font-family="Arial" font-size="9.5" font-weight="bold" fill="#1E3A8A">EQUIPE (VEVEY CO.)</text>
  <text x="340" y="274" font-family="Arial" font-size="8.7" font-weight="bold" fill="#1F1B16">• Matheus da Silva Araújo</text>
  <text x="346" y="287" font-family="Arial" font-size="8.4" fill="#475569">Requisitos, testes e manual</text>
  <text x="340" y="309" font-family="Arial" font-size="8.7" font-weight="bold" fill="#1F1B16">• Nathan Costa Santos</text>
  <text x="346" y="322" font-family="Arial" font-size="8.4" fill="#475569">Arquitetura, código e deploy</text>
  <text x="340" y="344" font-family="Arial" font-size="8.7" font-weight="bold" fill="#1F1B16">• Ricardo Martins Pighin</text>
  <text x="346" y="357" font-family="Arial" font-size="8.4" fill="#475569">Modelagem, RBAC e revisão</text>
  <text x="340" y="384" font-family="Arial" font-size="8.5" fill="#1E3A8A">Repositório: GitHub</text>
  <text x="340" y="397" font-family="Arial" font-size="8.5" fill="#1E3A8A">nathocosta/vevey</text>

  <!-- Coluna 4: COMO? -->
  <rect x="493" y="52" width="154" height="120" rx="5" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.2"/>
  <text x="501" y="69" font-family="Arial" font-size="9.5" font-weight="bold" fill="#166534">PREMISSAS</text>
  <text x="501" y="86" font-family="Arial" font-size="8.7" fill="#1F1B16">• Stack PHP 8.4 + Laravel 13</text>
  <text x="507" y="99" font-family="Arial" font-size="8.7" fill="#1F1B16">+ Filament 5 + PostgreSQL.</text>
  <text x="501" y="116" font-family="Arial" font-size="8.7" fill="#1F1B16">• Monólito modular em 7</text>
  <text x="507" y="129" font-family="Arial" font-size="8.7" fill="#1F1B16">módulos (ADR 8).</text>
  <text x="501" y="146" font-family="Arial" font-size="8.7" fill="#1F1B16">• Pagamento Sandbox na</text>
  <text x="507" y="159" font-family="Arial" font-size="8.7" fill="#1F1B16">fase acadêmica.</text>

  <rect x="493" y="178" width="154" height="118" rx="5" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.2"/>
  <text x="501" y="195" font-family="Arial" font-size="9.5" font-weight="bold" fill="#166534">GRUPO DE ENTREGAS</text>
  <text x="501" y="212" font-family="Arial" font-size="8.7" fill="#1F1B16">1. Visão, Escopo e Requisitos</text>
  <text x="501" y="228" font-family="Arial" font-size="8.7" fill="#1F1B16">2. Modelagem (ADRs + DER)</text>
  <text x="501" y="244" font-family="Arial" font-size="8.7" fill="#1F1B16">3. Loja + Checkout + 2FA</text>
  <text x="501" y="260" font-family="Arial" font-size="8.7" fill="#1F1B16">4. Admin + PDV + Ledger</text>
  <text x="501" y="276" font-family="Arial" font-size="8.7" fill="#1F1B16">5. Docker/Render + Doc N2</text>

  <rect x="493" y="302" width="154" height="118" rx="5" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.2"/>
  <text x="501" y="319" font-family="Arial" font-size="9.5" font-weight="bold" fill="#166534">RESTRIÇÕES</text>
  <text x="501" y="336" font-family="Arial" font-size="8.7" fill="#1F1B16">• Prazo fixo: N2 em nov/2026.</text>
  <text x="501" y="352" font-family="Arial" font-size="8.7" fill="#1F1B16">• Orçamento zero (software</text>
  <text x="507" y="365" font-family="Arial" font-size="8.7" fill="#1F1B16">livre e APIs gratuitas).</text>
  <text x="501" y="382" font-family="Arial" font-size="8.7" fill="#1F1B16">• Conformidade LGPD no</text>
  <text x="507" y="395" font-family="Arial" font-size="8.7" fill="#1F1B16">tratamento de CPF/dados.</text>

  <!-- Coluna 5: QUANDO / QUANTO? -->
  <rect x="654" y="52" width="156" height="120" rx="5" fill="#F8FAFC" stroke="#475569" stroke-width="1.2"/>
  <text x="662" y="69" font-family="Arial" font-size="9.5" font-weight="bold" fill="#334155">RISCOS PRINCIPAIS</text>
  <text x="662" y="86" font-family="Arial" font-size="8.7" fill="#1F1B16">• Divergência de estoque</text>
  <text x="668" y="99" font-family="Arial" font-size="8.7" fill="#1F1B16">entre loja física e site.</text>
  <text x="662" y="116" font-family="Arial" font-size="8.7" fill="#1F1B16">• Erro de arredondamento</text>
  <text x="668" y="129" font-family="Arial" font-size="8.7" fill="#1F1B16">financeiro em descontos.</text>
  <text x="662" y="146" font-family="Arial" font-size="8.7" fill="#1F1B16">• Indisponibilidade de</text>
  <text x="668" y="159" font-family="Arial" font-size="8.7" fill="#1F1B16">APIs externas (CEP/e-mail).</text>

  <rect x="654" y="178" width="156" height="118" rx="5" fill="#F8FAFC" stroke="#475569" stroke-width="1.2"/>
  <text x="662" y="195" font-family="Arial" font-size="9.5" font-weight="bold" fill="#334155">LINHA DO TEMPO</text>
  <text x="662" y="212" font-family="Arial" font-size="8.7" fill="#1F1B16">• Ago/26: Visão e Escopo</text>
  <text x="662" y="228" font-family="Arial" font-size="8.7" fill="#1F1B16">• Set/26: Requisitos e UCs</text>
  <text x="662" y="244" font-family="Arial" font-size="8.7" fill="#1F1B16">• Out/26: Modelagem/ADRs</text>
  <text x="662" y="260" font-family="Arial" font-size="8.7" fill="#1F1B16">• Nov/26: Implementação</text>
  <text x="662" y="276" font-family="Arial" font-size="8.7" fill="#1F1B16">• 25–30/11: Entrega N2</text>

  <rect x="654" y="302" width="156" height="118" rx="5" fill="#F8FAFC" stroke="#475569" stroke-width="1.2"/>
  <text x="662" y="319" font-family="Arial" font-size="9.5" font-weight="bold" fill="#334155">CUSTOS</text>
  <text x="662" y="336" font-family="Arial" font-size="8.7" fill="#1F1B16">• Ferramentas open-source:</text>
  <text x="668" y="349" font-family="Arial" font-size="8.7" fill="#1F1B16">R$ 0,00 (Laravel/Postgres).</text>
  <text x="662" y="366" font-family="Arial" font-size="8.7" fill="#1F1B16">• Hospedagem (Render Free</text>
  <text x="668" y="379" font-family="Arial" font-size="8.7" fill="#1F1B16">+ APIs públicas): R$ 0,00.</text>
  <text x="662" y="396" font-family="Arial" font-size="8.7" fill="#1F1B16">• Esforço equipe: ~82h.</text>
</svg>
"""

SVG_USE_CASES = r"""
<svg viewBox="0 0 820 390" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <rect x="195" y="16" width="430" height="358" rx="10" fill="#FDFBF7" stroke="#334155" stroke-width="1.6"/>
  <text x="410" y="38" text-anchor="middle" font-family="Arial" font-size="13" font-weight="bold" fill="#1F1B16">Sistema E-commerce &amp; Gestão Vevey Doces</text>

  <!-- Atores Esquerda -->
  <g stroke="#1F1B16" stroke-width="1.6" fill="none">
    <circle cx="70" cy="95" r="13"/>
    <line x1="70" y1="108" x2="70" y2="144"/>
    <line x1="50" y1="120" x2="90" y2="120"/>
    <line x1="70" y1="144" x2="54" y2="168"/>
    <line x1="70" y1="144" x2="86" y2="168"/>

    <circle cx="70" cy="245" r="13"/>
    <line x1="70" y1="258" x2="70" y2="294"/>
    <line x1="50" y1="270" x2="90" y2="270"/>
    <line x1="70" y1="294" x2="54" y2="318"/>
    <line x1="70" y1="294" x2="86" y2="318"/>
  </g>
  <text x="70" y="185" text-anchor="middle" font-family="Arial" font-size="11.5" font-weight="bold" fill="#1F1B16">Cliente (AT01)</text>
  <text x="70" y="335" text-anchor="middle" font-family="Arial" font-size="11.5" font-weight="bold" fill="#1F1B16">Atendente (AT02)</text>

  <!-- Atores Direita -->
  <g stroke="#1F1B16" stroke-width="1.6" fill="none">
    <circle cx="745" cy="115" r="13"/>
    <line x1="745" y1="128" x2="745" y2="164"/>
    <line x1="725" y1="140" x2="765" y2="140"/>
    <line x1="745" y1="164" x2="729" y2="188"/>
    <line x1="745" y1="164" x2="761" y2="188"/>

    <circle cx="745" cy="255" r="13"/>
    <line x1="745" y1="268" x2="745" y2="304"/>
    <line x1="725" y1="280" x2="765" y2="280"/>
    <line x1="745" y1="304" x2="729" y2="328"/>
    <line x1="745" y1="304" x2="761" y2="328"/>
  </g>
  <text x="745" y="205" text-anchor="middle" font-family="Arial" font-size="11.5" font-weight="bold" fill="#1F1B16">Administrador (AT04)</text>
  <text x="745" y="345" text-anchor="middle" font-family="Arial" font-size="11.5" font-weight="bold" fill="#1F1B16">Logística (AT03)</text>

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

SVG_NAVIGATION = r"""
<svg viewBox="0 0 820 400" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="arrNav" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#334155"/>
    </marker>
  </defs>

  <!-- Nó inicial UML -->
  <circle cx="42" cy="196" r="10" fill="#1F1B16"/>
  <line x1="52" y1="196" x2="94" y2="196" stroke="#334155" stroke-width="1.4" marker-end="url(#arrNav)"/>

  <!-- Decisão inicial (Perfil de acesso) -->
  <polygon points="116,178 138,196 116,214 94,196" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.5"/>

  <!-- Ramo Superior: Loja Pública (Cliente) -->
  <rect x="160" y="14" width="644" height="196" rx="8" fill="#FDFBF7" stroke="#E2E8F0" stroke-width="1.2"/>
  <text x="174" y="32" font-family="Arial" font-size="10" font-weight="bold" fill="#E11D48">FLUXO CLIENTE — LOJA PÚBLICA (panel-site)</text>

  <path d="M 116 178 L 116 74 L 174 74" fill="none" stroke="#334155" stroke-width="1.3" marker-end="url(#arrNav)"/>
  <text x="110" y="68" text-anchor="end" font-family="Arial" font-size="8.2" fill="#475569">[acessa "/"]</text>

  <g font-family="Arial" font-size="9.5" text-anchor="middle" fill="#1F1B16">
    <!-- T01 -->
    <rect x="176" y="52" width="106" height="44" rx="8" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.4"/>
    <text x="229" y="71" font-weight="bold">T01: Home</text>
    <text x="229" y="85" font-size="8.3" fill="#64748B">Hero + Busca (/)</text>

    <!-- Decisão T01 -->
    <polygon points="322,58 338,74 322,90 306,74" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.4"/>

    <!-- T02 -->
    <rect x="394" y="42" width="114" height="42" rx="8" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.4"/>
    <text x="451" y="60" font-weight="bold">T02: Catálogo</text>
    <text x="451" y="73" font-size="8.3" fill="#64748B">Filtros ADR 6 (/catalogo)</text>

    <!-- T03 -->
    <rect x="556" y="42" width="106" height="42" rx="8" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.4"/>
    <text x="609" y="60" font-weight="bold">T03: Produto</text>
    <text x="609" y="73" font-size="8.3" fill="#64748B">PDP + Cores (/produto)</text>

    <!-- T05 Checkout -->
    <rect x="688" y="42" width="102" height="44" rx="8" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.5"/>
    <text x="739" y="60" font-weight="bold" fill="#9F1239">T05: Checkout</text>
    <text x="739" y="74" font-size="8.3" fill="#9F1239">CEP + Cupom + Pix</text>

    <!-- T04 Cadastro / 2FA -->
    <rect x="394" y="138" width="136" height="46" rx="8" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <text x="462" y="157" font-weight="bold" fill="#1E3A8A">T04: Cadastro / 2FA</text>
    <text x="462" y="171" font-size="8.3" fill="#1D4ED8">/cadastro → /verificar-conta</text>

    <!-- T06 Minha Conta -->
    <rect x="598" y="138" width="118" height="46" rx="8" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <text x="657" y="157" font-weight="bold" fill="#1E3A8A">T06: Minha Conta</text>
    <text x="657" y="171" font-size="8.3" fill="#1D4ED8">Pedidos + Endereços</text>
  </g>

  <!-- Transições Loja -->
  <g stroke="#334155" stroke-width="1.3" fill="none" marker-end="url(#arrNav)">
    <line x1="282" y1="74" x2="305" y2="74"/>
    <path d="M 338 66 L 392 63"/>
    <path d="M 322 90 L 322 161 L 392 161"/>
    <line x1="508" y1="63" x2="554" y2="63"/>
    <line x1="662" y1="63" x2="686" y2="63"/>
    <line x1="530" y1="161" x2="596" y2="161"/>
    <path d="M 739 86 L 739 161 L 718 161"/>
  </g>
  <g font-family="Arial" font-size="8" fill="#475569">
    <text x="366" y="55" text-anchor="middle">[Catálogo]</text>
    <text x="357" y="153" text-anchor="middle">[Minha conta]</text>
    <text x="531" y="56" text-anchor="middle">clica item</text>
    <text x="674" y="56" text-anchor="middle">pagar</text>
    <text x="563" y="154" text-anchor="middle">valida OTP</text>
    <text x="769" y="128" text-anchor="middle">acompanhar</text>
  </g>

  <!-- Ramo Inferior: Painel Admin (Equipe) -->
  <rect x="160" y="224" width="644" height="162" rx="8" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.2"/>
  <text x="174" y="242" font-family="Arial" font-size="10" font-weight="bold" fill="#334155">FLUXO EQUIPE — PAINEL ADMINISTRATIVO FILAMENT (/admin · RBAC)</text>

  <path d="M 116 214 L 116 300 L 174 300" fill="none" stroke="#334155" stroke-width="1.3" marker-end="url(#arrNav)"/>
  <text x="110" y="304" text-anchor="end" font-family="Arial" font-size="8.2" fill="#475569">[acessa "/admin"]</text>

  <g font-family="Arial" font-size="9.5" text-anchor="middle" fill="#1F1B16">
    <!-- T07 Login + Dashboard -->
    <rect x="176" y="278" width="122" height="44" rx="8" fill="#FFFFFF" stroke="#334155" stroke-width="1.4"/>
    <text x="237" y="297" font-weight="bold">T07: Dashboard</text>
    <text x="237" y="311" font-size="8.3" fill="#64748B">Login RBAC → KPIs</text>

    <!-- Decisão Admin -->
    <polygon points="342,284 358,300 342,316 326,300" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.4"/>

    <!-- T08 PDV -->
    <rect x="430" y="254" width="128" height="38" rx="8" fill="#FFFBEB" stroke="#D97706" stroke-width="1.4"/>
    <text x="494" y="271" font-weight="bold" fill="#92400E">T08: PDV Balcão</text>
    <text x="494" y="284" font-size="8.2" fill="#92400E">Venda pos (/admin/pdv)</text>

    <!-- Estoque -->
    <rect x="430" y="302" width="128" height="38" rx="8" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <text x="494" y="319" font-weight="bold" fill="#166534">Estoque &amp; Pedidos</text>
    <text x="494" y="332" font-size="8.2" fill="#166534">Ledger · Entregas</text>

    <!-- Auditoria -->
    <rect x="614" y="278" width="118" height="44" rx="8" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <text x="673" y="297" font-weight="bold" fill="#1E3A8A">Auditoria (Logs)</text>
    <text x="673" y="311" font-size="8.2" fill="#1D4ED8">/admin/audit-log</text>
  </g>

  <g stroke="#334155" stroke-width="1.3" fill="none" marker-end="url(#arrNav)">
    <line x1="298" y1="300" x2="325" y2="300"/>
    <path d="M 358 292 L 392 273 L 428 273"/>
    <path d="M 358 308 L 392 321 L 428 321"/>
    <path d="M 558 273 L 586 273 L 586 294 L 612 294"/>
    <path d="M 558 321 L 586 321 L 586 306 L 612 306"/>
    <line x1="732" y1="300" x2="762" y2="300"/>
  </g>
  <g font-family="Arial" font-size="8.2" fill="#475569">
    <text x="394" y="266" text-anchor="middle">[escolhe "PDV"]</text>
    <text x="392" y="336" text-anchor="middle">[escolhe "Estoque"]</text>
    <text x="586" y="266" text-anchor="middle">registra</text>
  </g>

  <!-- Nó final UML (bullseye) -->
  <circle cx="776" cy="300" r="10" fill="#FFFFFF" stroke="#1F1B16" stroke-width="1.6"/>
  <circle cx="776" cy="300" r="6" fill="#1F1B16"/>
</svg>
"""

SVG_PACKAGES = r"""
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

  <!-- Setas de dependência <<use>> -->
  <g stroke="#475569" stroke-width="1.4" stroke-dasharray="5 3" marker-end="url(#arr2)">
    <line x1="410" y1="76" x2="410" y2="95"/>
    <line x1="410" y1="154" x2="410" y2="173"/>
    <line x1="410" y1="234" x2="410" y2="251"/>
  </g>
  <g font-family="Arial" font-size="8.5" fill="#475569">
    <text x="418" y="89">«use»</text>
    <text x="418" y="167">«use»</text>
    <text x="418" y="246">«use»</text>
  </g>
</svg>
"""

SVG_CLASSES = r"""
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
    <text x="232" y="247">+ qty: int (!= 0)</text>
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

  <!-- Linhas de relacionamento + multiplicidade -->
  <g stroke="#64748B" stroke-width="1.3">
    <line x1="106" y1="148" x2="106" y2="192"/>
    <line x1="196" y1="80" x2="224" y2="80" stroke-dasharray="4 3"/>
    <line x1="419" y1="80" x2="448" y2="80"/>
    <line x1="419" y1="130" x2="448" y2="230"/>
    <line x1="726" y1="148" x2="726" y2="192"/>
    <line x1="196" y1="256" x2="224" y2="256"/>
  </g>
  <g font-family="Arial" font-size="8.5" fill="#475569">
    <text x="112" y="174">1 : 1</text>
    <text x="424" y="75">1 : 1..*</text>
    <text x="424" y="180">1 : 1..*</text>
    <text x="732" y="174">1 : 0..*</text>
    <text x="200" y="249">1 : 0..*</text>
  </g>
</svg>
"""

SVG_DER = r"""
<svg viewBox="0 0 820 395" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <g font-family="Arial" font-size="9.2" fill="#1F1B16">
    <!-- categories -->
    <rect x="14" y="14" width="150" height="82" rx="5" fill="#FFFFFF" stroke="#334155" stroke-width="1.3"/>
    <rect x="14" y="14" width="150" height="20" rx="5" fill="#1F1B16"/>
    <text x="89" y="28" text-anchor="middle" font-weight="bold" fill="#FFFFFF">categories</text>
    <text x="22" y="48"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="22" y="62"><tspan font-weight="bold" fill="#2563EB">FK</tspan> parent_id (uuid)</text>
    <text x="22" y="76">name, slug, sort_order</text>
    <text x="22" y="89" fill="#64748B">N:M ↔ category_product</text>

    <!-- products -->
    <rect x="14" y="128" width="166" height="114" rx="5" fill="#FDFBF7" stroke="#1F1B16" stroke-width="1.5"/>
    <rect x="14" y="128" width="166" height="20" rx="5" fill="#1F1B16"/>
    <text x="97" y="142" text-anchor="middle" font-weight="bold" fill="#FFFFFF">products</text>
    <text x="22" y="162"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="22" y="176">sku (unique), barcode</text>
    <text x="22" y="190">name, slug (unique)</text>
    <text x="22" y="204">price_cents, promo_cents</text>
    <text x="22" y="218">search_vector (tsvector GIN)</text>
    <text x="22" y="232">is_active, deleted_at</text>

    <!-- attributes / values -->
    <rect x="14" y="274" width="166" height="106" rx="5" fill="#FFFFFF" stroke="#334155" stroke-width="1.3"/>
    <rect x="14" y="274" width="166" height="20" rx="5" fill="#334155"/>
    <text x="97" y="288" text-anchor="middle" font-weight="bold" fill="#FFFFFF">attributes / values</text>
    <text x="22" y="308"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> attribute_values.id</text>
    <text x="22" y="322"><tspan font-weight="bold" fill="#2563EB">FK</tspan> attribute_id → attributes</text>
    <text x="22" y="336">value, slug, hex_color</text>
    <text x="22" y="352" fill="#64748B">Pivô: attribute_value_product</text>
    <text x="22" y="366" fill="#64748B">(filtros ADR 6)</text>

    <!-- stock_levels -->
    <rect x="218" y="14" width="176" height="96" rx="5" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <rect x="218" y="14" width="176" height="20" rx="5" fill="#166534"/>
    <text x="306" y="28" text-anchor="middle" font-weight="bold" fill="#FFFFFF">stock_levels</text>
    <text x="226" y="48"><tspan font-weight="bold" fill="#B91C1C">PK/FK</tspan> product_id (uuid)</text>
    <text x="226" y="62">on_hand (int signed)</text>
    <text x="226" y="76">reserved (int &gt;= 0)</text>
    <text x="226" y="90">available (STORED)</text>
    <text x="226" y="103">reorder_point (int)</text>

    <!-- stock_movements + reservations -->
    <rect x="218" y="136" width="176" height="106" rx="5" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <rect x="218" y="136" width="176" height="20" rx="5" fill="#166534"/>
    <text x="306" y="150" text-anchor="middle" font-weight="bold" fill="#FFFFFF">stock_movements</text>
    <text x="226" y="170"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="226" y="184"><tspan font-weight="bold" fill="#2563EB">FK</tspan> product_id, user_id</text>
    <text x="226" y="198">qty (CHECK != 0), direction</text>
    <text x="226" y="212">reason, balance_after</text>
    <text x="226" y="226">reference_type, reference_id</text>

    <!-- order_items -->
    <rect x="218" y="274" width="176" height="106" rx="5" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.4"/>
    <rect x="218" y="274" width="176" height="20" rx="5" fill="#9F1239"/>
    <text x="306" y="288" text-anchor="middle" font-weight="bold" fill="#FFFFFF">order_items</text>
    <text x="226" y="308"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="226" y="322"><tspan font-weight="bold" fill="#2563EB">FK</tspan> order_id, product_id</text>
    <text x="226" y="336">sku, name, quantity</text>
    <text x="226" y="350">unit_price_cents, discount</text>
    <text x="226" y="364">product_snapshot (jsonb)</text>

    <!-- orders -->
    <rect x="432" y="128" width="178" height="132" rx="5" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.6"/>
    <rect x="432" y="128" width="178" height="20" rx="5" fill="#E11D48"/>
    <text x="521" y="142" text-anchor="middle" font-weight="bold" fill="#FFFFFF">orders</text>
    <text x="440" y="162"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="440" y="176"><tspan font-weight="bold" fill="#2563EB">FK</tspan> customer_id, seller_id</text>
    <text x="440" y="190"><tspan font-weight="bold" fill="#2563EB">FK</tspan> coupon_id (nullable)</text>
    <text x="440" y="204">number (unique), channel</text>
    <text x="440" y="218">payment_status, fulfillment</text>
    <text x="440" y="232">subtotal, discount, shipping</text>
    <text x="440" y="246">total_cents (CHECK exato)</text>

    <!-- payments & adjustments -->
    <rect x="432" y="14" width="178" height="96" rx="5" fill="#FFFBEB" stroke="#D97706" stroke-width="1.3"/>
    <rect x="432" y="14" width="178" height="20" rx="5" fill="#B45309"/>
    <text x="521" y="28" text-anchor="middle" font-weight="bold" fill="#FFFFFF">payments / adjustments</text>
    <text x="440" y="48"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="440" y="62"><tspan font-weight="bold" fill="#2563EB">FK</tspan> order_id (uuid)</text>
    <text x="440" y="76">method, amount_cents, status</text>
    <text x="440" y="90">order_status_histories (1:N)</text>
    <text x="440" y="103">stock_reservations (30m)</text>

    <!-- coupons -->
    <rect x="432" y="286" width="178" height="94" rx="5" fill="#FFFBEB" stroke="#D97706" stroke-width="1.3"/>
    <rect x="432" y="286" width="178" height="20" rx="5" fill="#B45309"/>
    <text x="521" y="300" text-anchor="middle" font-weight="bold" fill="#FFFFFF">coupons</text>
    <text x="440" y="320"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="440" y="334">code (unique), type, value</text>
    <text x="440" y="348">min_order_cents, max_uses</text>
    <text x="440" y="362">used_count, starts/ends_at</text>

    <!-- customers -->
    <rect x="646" y="128" width="160" height="114" rx="5" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <rect x="646" y="128" width="160" height="20" rx="5" fill="#1E3A8A"/>
    <text x="726" y="142" text-anchor="middle" font-weight="bold" fill="#FFFFFF">customers</text>
    <text x="654" y="162"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="654" y="176">name, email (unique)</text>
    <text x="654" y="190">cpf, password</text>
    <text x="654" y="204">verification_code (2FA)</text>
    <text x="654" y="218">email_verified_at</text>
    <text x="654" y="232">deleted_at</text>

    <!-- customer_identities -->
    <rect x="646" y="14" width="160" height="96" rx="5" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.3"/>
    <rect x="646" y="14" width="160" height="20" rx="5" fill="#2563EB"/>
    <text x="726" y="28" text-anchor="middle" font-weight="bold" fill="#FFFFFF">customer_identities</text>
    <text x="654" y="48"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="654" y="62"><tspan font-weight="bold" fill="#2563EB">FK</tspan> customer_id (uuid)</text>
    <text x="654" y="76">type, value (E.164/email)</text>
    <text x="654" y="90">is_primary, retired_at</text>

    <!-- customer_addresses -->
    <rect x="646" y="274" width="160" height="106" rx="5" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.3"/>
    <rect x="646" y="274" width="160" height="20" rx="5" fill="#2563EB"/>
    <text x="726" y="288" text-anchor="middle" font-weight="bold" fill="#FFFFFF">customer_addresses</text>
    <text x="654" y="308"><tspan font-weight="bold" fill="#B91C1C">PK</tspan> id (uuid)</text>
    <text x="654" y="322"><tspan font-weight="bold" fill="#2563EB">FK</tspan> customer_id (uuid)</text>
    <text x="654" y="336">label, cep, street, number</text>
    <text x="654" y="350">neighborhood, city, state</text>
    <text x="654" y="364">is_default (bool)</text>
  </g>

  <!-- Relações + Cardinalidades -->
  <g stroke="#475569" stroke-width="1.3" fill="none">
    <line x1="89" y1="96" x2="89" y2="128"/>
    <line x1="89" y1="242" x2="89" y2="274"/>
    <path d="M 180 156 L 200 156 L 200 62 L 218 62"/>
    <line x1="180" y1="185" x2="218" y2="185"/>
    <path d="M 180 218 L 200 218 L 200 326 L 218 326"/>
    <line x1="394" y1="326" x2="412" y2="326"/>
    <path d="M 412 326 L 412 206 L 432 206"/>
    <line x1="521" y1="110" x2="521" y2="128"/>
    <line x1="521" y1="260" x2="521" y2="286"/>
    <line x1="610" y1="185" x2="646" y2="185"/>
    <line x1="726" y1="110" x2="726" y2="128"/>
    <line x1="726" y1="242" x2="726" y2="274"/>
  </g>
  <g font-family="Arial" font-size="8.2" font-weight="bold" fill="#334155">
    <text x="94" y="116">N:M</text>
    <text x="94" y="262">N:M</text>
    <text x="184" y="56">1:1</text>
    <text x="186" y="180">1:N</text>
    <text x="184" y="320">1:N</text>
    <text x="415" y="198">1:N</text>
    <text x="526" y="122">1:N</text>
    <text x="526" y="278">0..1:N</text>
    <text x="615" y="179">0..1:N</text>
    <text x="731" y="122">1:N</text>
    <text x="731" y="262">1:N</text>
  </g>
</svg>
"""

SVG_SEQUENCE = r"""
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

  <!-- Barras de ativação -->
  <rect x="233" y="76" width="8" height="156" fill="#E2E8F0" stroke="#475569" stroke-width="0.8"/>
  <rect x="403" y="106" width="8" height="96" fill="#FEF3C7" stroke="#D97706" stroke-width="0.8"/>
  <rect x="573" y="166" width="8" height="36" fill="#DCFCE7" stroke="#16A34A" stroke-width="0.8"/>

  <!-- Mensagens -->
  <g font-family="Arial" font-size="9.5" fill="#1F1B16">
    <line x1="75" y1="78" x2="231" y2="78" stroke="#1F1B16" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="154" y="72" text-anchor="middle">1. POST /checkout (itens, CEP, cupom)</text>

    <line x1="241" y1="108" x2="401" y2="108" stroke="#1F1B16" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="321" y="102" text-anchor="middle">2. PlaceOrder::handle(PlaceOrderData)</text>

    <line x1="411" y1="138" x2="736" y2="138" stroke="#1F1B16" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="574" y="132" text-anchor="middle">3. Grava order + order_items (snapshot) + adjustments + payment</text>

    <line x1="411" y1="168" x2="571" y2="168" stroke="#1F1B16" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="491" y="162" text-anchor="middle">4. ReserveStock (lockForUpdate)</text>

    <line x1="581" y1="196" x2="736" y2="196" stroke="#16A34A" stroke-width="1.3" marker-end="url(#seqArr)"/>
    <text x="658" y="190" text-anchor="middle">5. INSERT stock_reservations (30m)</text>

    <line x1="233" y1="226" x2="79" y2="226" stroke="#475569" stroke-width="1.2" stroke-dasharray="4 3" marker-end="url(#seqArr)"/>
    <text x="156" y="220" text-anchor="middle">6. 201 Created + QR Code Pix</text>

    <line x1="75" y1="258" x2="233" y2="258" stroke="#E11D48" stroke-width="1.4" marker-end="url(#seqArr)"/>
    <text x="154" y="252" text-anchor="middle" fill="#9F1239">7. Confirmação Pagamento (Sandbox/Admin)</text>

    <line x1="237" y1="288" x2="573" y2="288" stroke="#16A34A" stroke-width="1.4" marker-end="url(#seqArr)"/>
    <text x="405" y="282" text-anchor="middle" fill="#166534">8. FulfillOrderReservations → INSERT stock_movements (sale -qty)</text>
  </g>
</svg>
"""

SVG_ARTIFACTS = r"""
<svg viewBox="0 0 820 275" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="artArr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#475569"/>
    </marker>
  </defs>

  <!-- Repositório Raiz -->
  <rect x="12" y="12" width="796" height="252" rx="8" fill="#FDFBF7" stroke="#334155" stroke-width="1.4"/>
  <text x="26" y="31" font-family="Arial" font-size="10.5" font-weight="bold" fill="#1F1B16">Repositório Aplicação Web — vevey/ (Monólito Modular Laravel 13)</text>

  <g font-family="Arial" font-size="9.2" fill="#1F1B16">
    <!-- Artefato: app-modules -->
    <rect x="26" y="46" width="236" height="106" rx="5" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.3"/>
    <text x="144" y="62" text-anchor="middle" font-size="8.5" fill="#9F1239">«artifact»</text>
    <text x="144" y="76" text-anchor="middle" font-weight="bold" font-size="10">app-modules/* (7 pacotes PHP)</text>
    <text x="36" y="95">• catalog/ · inventory/ · customers/</text>
    <text x="36" y="110">• orders/ · permissions/</text>
    <text x="36" y="125">• panel-admin/ (Filament 5)</text>
    <text x="36" y="140">• panel-site/ (Controllers Loja)</text>

    <!-- Artefato: resources + Vite -->
    <rect x="292" y="46" width="236" height="106" rx="5" fill="#FFFBEB" stroke="#D97706" stroke-width="1.3"/>
    <text x="410" y="62" text-anchor="middle" font-size="8.5" fill="#92400E">«artifact»</text>
    <text x="410" y="76" text-anchor="middle" font-weight="bold" font-size="10">resources/ + public/build/*</text>
    <text x="302" y="95">• views/storefront/*.blade.php</text>
    <text x="302" y="110">• views/components/brand/logo.blade.php</text>
    <text x="302" y="125">• css/app.css (Tailwind CSS v4)</text>
    <text x="302" y="140">• js/app.js (Bundle Vite 8)</text>

    <!-- Artefato: app/ + routes/ -->
    <rect x="558" y="46" width="236" height="106" rx="5" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.3"/>
    <text x="676" y="62" text-anchor="middle" font-size="8.5" fill="#1E3A8A">«artifact»</text>
    <text x="676" y="76" text-anchor="middle" font-weight="bold" font-size="10">app/ + routes/ + database/</text>
    <text x="568" y="95">• Services/BrasilApiService.php</text>
    <text x="568" y="110">• Services/TransactionalMailer.php</text>
    <text x="568" y="125">• Casts/MoneyCast.php · routes/web.php</text>
    <text x="568" y="140">• migrations/ · StaffUsersSeeder.php</text>

    <!-- Artefato: composer.json -->
    <rect x="26" y="178" width="236" height="72" rx="5" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.3"/>
    <text x="144" y="194" text-anchor="middle" font-size="8.5" fill="#166534">«artifact»</text>
    <text x="144" y="208" text-anchor="middle" font-weight="bold" font-size="10">composer.json (vendor/)</text>
    <text x="36" y="224">• laravel/framework 13 · filament 5</text>
    <text x="36" y="238">• internachi/modular · brick/money · spatie</text>

    <!-- Artefato: tests/ -->
    <rect x="292" y="178" width="236" height="72" rx="5" fill="#F8FAFC" stroke="#475569" stroke-width="1.3"/>
    <text x="410" y="194" text-anchor="middle" font-size="8.5" fill="#334155">«artifact»</text>
    <text x="410" y="208" text-anchor="middle" font-weight="bold" font-size="10">tests/ (Pest Arch &amp; Feature)</text>
    <text x="302" y="224">• Arch/ModuleDependenciesTest.php</text>
    <text x="302" y="238">• Feature: Orders, Inventory, Customers</text>

    <!-- Artefato: Docker / Render -->
    <rect x="558" y="178" width="236" height="72" rx="5" fill="#F8FAFC" stroke="#475569" stroke-width="1.3"/>
    <text x="676" y="194" text-anchor="middle" font-size="8.5" fill="#334155">«artifact»</text>
    <text x="676" y="208" text-anchor="middle" font-weight="bold" font-size="10">Dockerfile · render.yaml</text>
    <text x="568" y="224">• docker/start-render.sh (bootstrap)</text>
    <text x="568" y="238">• package.json · vite.config.js</text>
  </g>

  <!-- Dependências tracejadas <<use>> -->
  <g stroke="#475569" stroke-width="1.2" stroke-dasharray="5 3" fill="none" marker-end="url(#artArr)">
    <line x1="144" y1="152" x2="144" y2="176"/>
    <line x1="292" y1="99" x2="264" y2="99"/>
    <path d="M 262 140 L 278 140 L 278 165 L 542 165 L 542 120 L 556 120"/>
    <line x1="410" y1="178" x2="410" y2="154"/>
    <line x1="676" y1="178" x2="676" y2="154"/>
  </g>
</svg>
"""

SVG_DEPLOY = r"""
<svg viewBox="0 0 820 220" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="depArr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#475569"/>
    </marker>
  </defs>
  <g font-family="Arial" font-size="10.5" text-anchor="middle" fill="#1F1B16">
    <!-- Navegador -->
    <rect x="18" y="68" width="136" height="76" rx="8" fill="#FDFBF7" stroke="#1F1B16" stroke-width="1.4"/>
    <text x="86" y="88" font-size="8.5" fill="#64748B">«device»</text>
    <text x="86" y="104" font-weight="bold">Navegador Web</text>
    <text x="86" y="119" font-size="9" fill="#64748B">Cliente (Loja)</text>
    <text x="86" y="133" font-size="9" fill="#64748B">Equipe (/admin · PDV)</text>

    <!-- Render Web Service -->
    <rect x="206" y="22" width="256" height="170" rx="10" fill="#FFF1F2" stroke="#E11D48" stroke-width="1.5"/>
    <text x="334" y="42" font-weight="bold" fill="#9F1239">«execution environment» Docker (Render)</text>
    <rect x="224" y="56" width="220" height="48" rx="6" fill="#FFFFFF" stroke="#E11D48"/>
    <text x="334" y="72" font-size="8.5" fill="#9F1239">«artifact»</text>
    <text x="334" y="86" font-weight="bold">Laravel 13 + PHP 8.4</text>
    <text x="334" y="99" font-size="8.8" fill="#64748B">7 Módulos (internachi/modular)</text>
    <rect x="224" y="116" width="220" height="58" rx="6" fill="#FFFFFF" stroke="#334155"/>
    <text x="334" y="132" font-size="8.5" fill="#475569">«artifact»</text>
    <text x="334" y="146" font-weight="bold">Assets Vite + Filament 5</text>
    <text x="334" y="161" font-size="8.8" fill="#64748B">Tailwind v4 + Scheduler (5 min)</text>

    <!-- PostgreSQL -->
    <rect x="518" y="22" width="134" height="80" rx="8" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.4"/>
    <text x="585" y="42" font-size="8.5" fill="#1D4ED8">«database node»</text>
    <text x="585" y="58" font-weight="bold" fill="#1E3A8A">PostgreSQL 16+</text>
    <text x="585" y="74" font-size="9" fill="#475569">20 Tabelas · UUIDs</text>
    <text x="585" y="88" font-size="9" fill="#475569">GIN Full-Text · CHECK</text>

    <!-- APIs Externas -->
    <rect x="518" y="118" width="280" height="74" rx="8" fill="#F0FDF4" stroke="#16A34A" stroke-width="1.4"/>
    <text x="658" y="136" font-size="8.5" fill="#166534">«external services · HTTPS»</text>
    <text x="658" y="152" font-weight="bold" fill="#166534">Integrações Externas</text>
    <text x="658" y="168" font-size="9.2" fill="#1F1B16">BrasilAPI (CEP v2 + Feriados) · ViaCEP</text>
    <text x="658" y="182" font-size="9.2" fill="#1F1B16">Resend API (E-mails 2FA) · Melhor Envio</text>
  </g>

  <g stroke="#475569" stroke-width="1.5" marker-end="url(#depArr)">
    <line x1="154" y1="106" x2="204" y2="106"/>
    <line x1="462" y1="76" x2="516" y2="64"/>
    <line x1="462" y1="146" x2="516" y2="152"/>
  </g>
  <g font-family="Arial" font-size="8" fill="#475569" text-anchor="middle">
    <text x="179" y="99">HTTPS</text>
    <text x="489" y="61">TCP/5432</text>
    <text x="489" y="142">JSON/REST</text>
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
    margin: 0 0 9pt 0;
    orphans: 3;
    widows: 3;
  }}
  ul, ol {{
    margin: 0 0 9pt 0;
    padding-left: 18pt;
  }}
  li {{
    text-align: justify;
    margin-bottom: 3.5pt;
  }}
  h1 {{
    font-size: 16pt;
    font-weight: 700;
    color: #1F1B16;
    border-bottom: 2px solid #E11D48;
    padding-bottom: 4pt;
    margin: 20pt 0 11pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  h2 {{
    font-size: 13pt;
    font-weight: 700;
    color: #1F1B16;
    margin: 15pt 0 8pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  h3 {{
    font-size: 11.2pt;
    font-weight: 700;
    color: #334155;
    margin: 11pt 0 6pt 0;
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
    margin: 10pt 0 13pt 0;
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
    font-size: 9pt;
    line-height: 1.36;
  }}
  thead {{ display: table-header-group; }}
  tr {{ page-break-inside: avoid; break-inside: avoid; }}
  th {{
    background: #1F1B16;
    color: #FFFFFF;
    font-weight: 700;
    text-align: left;
    padding: 5.5pt 6.5pt;
    border: 1px solid #1F1B16;
  }}
  td {{
    padding: 3.8pt 6pt;
    border: 1px solid #CBD5E1;
    vertical-align: top;
    text-align: left;
  }}
  tbody tr:nth-child(even) td {{
    background: #F8FAFC;
  }}
  /* Figuras */
  .figure-wrap {{
    margin: 12pt 0 14pt 0;
    padding: 9pt;
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
    margin-top: 6pt;
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
    margin: 6pt 0 12pt 0;
  }}
  .toc-list li {{
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dotted #CBD5E1;
    padding: 1.5pt 0;
    font-size: 9pt;
    text-align: left;
  }}
  .compact-table td {{
    padding: 2.9pt 5.5pt;
    font-size: 8.6pt;
    line-height: 1.3;
  }}
  .compact-table th {{
    padding: 4.5pt 5.5pt;
    font-size: 8.8pt;
  }}
  .toc-l1 {{ font-weight: 700; margin-top: 4pt; }}
  .toc-l2 {{ padding-left: 14pt !important; }}
  /* Blocos estruturados (Casos de Uso, Atores e Classes) */
  .uc-card {{
    border: 1px solid #CBD5E1;
    border-radius: 6pt;
    padding: 8pt 10.5pt;
    margin: 8pt 0 10pt 0;
    background: #FFFFFF;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .uc-title {{
    font-size: 10.2pt;
    font-weight: 700;
    color: #9F1239;
    margin-bottom: 4.5pt;
    border-bottom: 1px solid #FFE4E6;
    padding-bottom: 2.5pt;
  }}
  .actor-title {{
    font-size: 10.2pt;
    font-weight: 700;
    color: #1E3A8A;
    margin-bottom: 4.5pt;
    border-bottom: 1px solid #DBEAFE;
    padding-bottom: 2.5pt;
  }}
  .class-title {{
    font-size: 10.2pt;
    font-weight: 700;
    color: #166534;
    margin-bottom: 4.5pt;
    border-bottom: 1px solid #DCFCE7;
    padding-bottom: 2.5pt;
  }}
  .uc-card p {{ margin-bottom: 3.5pt; font-size: 9.2pt; line-height: 1.42; }}
  .uc-card ol, .uc-card ul {{ margin: 2pt 0 4.5pt 0; font-size: 9pt; line-height: 1.38; }}
  code {{
    font-family: Consolas, "Courier New", monospace;
    font-size: 8.8pt;
    background: #F1F5F9;
    padding: 0.8pt 3pt;
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
  <div class="table-caption">Tabela 2 — Relatório de andamento: Matheus da Silva Araújo (RA 1680482421029)</div>
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
  <div class="table-caption">Tabela 3 — Relatório de andamento: Nathan Costa Santos (RA 1680482421021)</div>
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
  <div class="table-caption">Tabela 4 — Relatório de andamento: Ricardo Martins Pighin (RA 1680482421013)</div>
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
  <li class="toc-l1"><span>Índice de Figuras</span><span>4</span></li>
  <li class="toc-l1"><span>Índice de Tabelas</span><span>4</span></li>
  <li class="toc-l1"><span>1. Visão e Escopo do Projeto</span><span>5</span></li>
  <li class="toc-l2"><span>1.1 Descrição do problema (Contexto, Canvas, Stakeholders, Usuários, Riscos e Premissas)</span><span>5</span></li>
  <li class="toc-l2"><span>1.2 Visão da solução (Declaração de visão, Cenários de utilização e Features)</span><span>7</span></li>
  <li class="toc-l1"><span>2. Requisitos de Software</span><span>9</span></li>
  <li class="toc-l2"><span>2.1 Casos de uso (Diagrama, Especificação UC01–UC08 e Documentação dos atores AT01–AT05)</span><span>9</span></li>
  <li class="toc-l2"><span>2.2 Requisitos funcionais</span><span>15</span></li>
  <li class="toc-l2"><span>2.3 Requisitos não funcionais</span><span>16</span></li>
  <li class="toc-l2"><span>2.4 Protótipos do software (Telas implementadas e Modelo de navegação UML)</span><span>18</span></li>
  <li class="toc-l1"><span>3. Projeto do Sistema</span><span>22</span></li>
  <li class="toc-l2"><span>3.1 Modelo estrutural do sistema (Diagrama de pacotes, Classes e Especificações)</span><span>22</span></li>
  <li class="toc-l2"><span>3.2 Modelo de banco de dados do sistema (DER e Dicionário de tabelas)</span><span>25</span></li>
  <li class="toc-l2"><span>3.3 Modelo de comportamentos do sistema (Diagrama de sequência)</span><span>27</span></li>
  <li class="toc-l1"><span>4. Documentação Técnica do Sistema</span><span>28</span></li>
  <li class="toc-l2"><span>4.1 Modelo de implementação do sistema (Diagrama de artefatos e estrutura)</span><span>28</span></li>
  <li class="toc-l2"><span>4.2 Modelos de testes do sistema</span><span>29</span></li>
  <li class="toc-l2"><span>4.3 Modelos de implantação do sistema</span><span>30</span></li>
  <li class="toc-l2"><span>4.4 Procedimento de instalação</span><span>30</span></li>
  <li class="toc-l2"><span>4.5 Documentação técnica do sistema (ADRs e Integrações)</span><span>30</span></li>
  <li class="toc-l2"><span>4.6 Manual do usuário</span><span>31</span></li>
  <li class="toc-l1"><span>5. Referências Bibliográficas (ABNT)</span><span>34</span></li>
</ul>

<div class="page-break"></div>
<h2>Índice de Figuras</h2>
<ul class="toc-list">
  <li><span>Figura 1 — Formulário Project Model Canvas do projeto Website Vevey</span><span>5</span></li>
  <li><span>Figura 2 — Diagrama de Casos de Uso do Sistema Vevey Doces</span><span>9</span></li>
  <li><span>Figura 3 — Tela T01 — Página Inicial da Loja Vevey Doces (Hero editorial, busca e departamentos)</span><span>18</span></li>
  <li><span>Figura 4 — Tela T02 — Catálogo de Produtos com filtros por atributo (ADR 6) e estoque real</span><span>19</span></li>
  <li><span>Figura 5 — Tela T03 — Página de Produto (PDP) com seletor de cores irmãs e estoque</span><span>19</span></li>
  <li><span>Figura 6 — Tela T04 — Cadastro de Cliente com validação de CPF e verificação em 2 etapas (2FA)</span><span>20</span></li>
  <li><span>Figura 7 — Diagrama UML de Atividade: Modelo de Navegação da Loja e do Painel Administrativo</span><span>21</span></li>
  <li><span>Figura 8 — Diagrama de Pacotes e Camadas do Monólito Modular (ADR 8)</span><span>22</span></li>
  <li><span>Figura 9 — Diagrama de Classes de Domínio (Catálogo, Pedidos, Estoque e Clientes)</span><span>24</span></li>
  <li><span>Figura 10 — Diagrama Entidade-Relacionamento (DER) do Banco de Dados PostgreSQL</span><span>26</span></li>
  <li><span>Figura 11 — Diagrama de Sequência: Checkout Web, Reserva, Pagamento e Baixa no Ledger</span><span>27</span></li>
  <li><span>Figura 12 — Diagrama de Componentes e Artefatos («artifact») da Arquitetura de Implementação</span><span>28</span></li>
  <li><span>Figura 13 — Diagrama de Implantação (Docker, Render, PostgreSQL e APIs Externas)</span><span>30</span></li>
</ul>

<h2>Índice de Tabelas</h2>
<ul class="toc-list">
  <li><span>Tabela 1 — Integrantes da equipe Vevey Co.</span><span>1</span></li>
  <li><span>Tabela 2 — Relatório de andamento: Matheus da Silva Araújo</span><span>2</span></li>
  <li><span>Tabela 3 — Relatório de andamento: Nathan Costa Santos</span><span>2</span></li>
  <li><span>Tabela 4 — Relatório de andamento: Ricardo Martins Pighin</span><span>2</span></li>
  <li><span>Tabela 5 — Stakeholders do projeto e suas necessidades</span><span>6</span></li>
  <li><span>Tabela 6 — Usuários do sistema, papéis RBAC e personas</span><span>6</span></li>
  <li><span>Tabela 7 — Matriz de riscos e ações de mitigação</span><span>7</span></li>
  <li><span>Tabela 8 — Resumo dos atores do sistema (ID, tipo, ambiente e frequência)</span><span>14</span></li>
  <li><span>Tabela 9 — Requisitos Funcionais (RF01 a RF16) e casos de uso relacionados</span><span>16</span></li>
  <li><span>Tabela 10 — Requisitos Não Funcionais (RNF01 a RNF11) e casos de uso relacionados</span><span>17</span></li>
  <li><span>Tabela 11 — Especificação dos pacotes do sistema (responsabilidades e elementos principais)</span><span>23</span></li>
  <li><span>Tabela 12 — Dicionário das tabelas do banco de dados PostgreSQL</span><span>26</span></li>
  <li><span>Tabela 13 — Organização dos diretórios e artefatos de implementação</span><span>28</span></li>
  <li><span>Tabela 14 — Matriz de Rastreabilidade de Casos de Teste do Sistema</span><span>29</span></li>
  <li><span>Tabela 15 — Resumo das Decisões Arquiteturais (ADRs 1 a 9)</span><span>31</span></li>
  <li><span>Tabela 16 — Credenciais de homologação para testes e demonstração</span><span>32</span></li>
  <li><span>Tabela 17 — Guia rápido de resolução de problemas</span><span>33</span></li>
</ul>

<!-- ==================== CAPÍTULO 1 ==================== -->
<div class="page-break"></div>
<h1>1. Visão e Escopo do Projeto</h1>

<h2>1.1 Descrição do problema</h2>

<h3>1.1.1 Contexto do projeto</h3>
<p>A <strong>Vevey Doces</strong> é uma loja especializada em chocolates em caixa, balas, embalagens de papelão e artigos para festas, atuando no varejo e em pacotes fechados por meio de loja física, Instagram e WhatsApp Business. Antes da implantação do sistema, a operação comercial era fragmentada e dependente de controles manuais: o Instagram gerava interesse, mas redirecionava todo o atendimento para o WhatsApp; a equipe precisava consultar preços e disponibilidade manualmente em planilhas; e as vendas presenciais na loja física não eram sincronizadas em tempo real com os pedidos remotos, provocando conflitos de estoque e retrabalho operacional.</p>
<p>Embora existam plataformas de e-commerce de prateleira (como Nuvemshop ou Loja Integrada), a decisão pelo desenvolvimento próprio justificou-se por três fatores estratégicos: (i) integração direta do estoque único entre loja física (PDV Balcão) e loja virtual com rastreabilidade via ledger; (ii) controle integral sobre identidade visual, catálogo plano por atributos (cores e tipos) e regras comerciais (desconto Pix, cupons e caixas fechadas); e (iii) propriedade total sobre os dados de clientes, identidades de contato e histórico de pedidos. A Figura 1 sintetiza a justificativa do projeto em formato Project Model Canvas.</p>

<div class="figure-wrap">
  {SVG_CANVAS}
  <div class="figure-caption">Figura 1 — Formulário Project Model Canvas do projeto Website Vevey</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>1.1.2 Stakeholders</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 5 — Stakeholders do projeto e suas necessidades</div>
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
  <div class="table-caption">Tabela 6 — Usuários do sistema, papéis RBAC e personas</div>
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
  <div class="table-caption">Tabela 7 — Matriz de riscos e ações de mitigação</div>
  <table>
    <thead><tr><th style="width:185px;">Risco</th><th style="width:65px;">Prob.</th><th style="width:72px;">Impacto</th><th>Ação de Mitigação Adotada</th></tr></thead>
    <tbody>
      <tr><td>Divergência entre estoque físico e online</td><td>Alta</td><td>Alto</td><td>Adoção de ledger append-only (<code>stock_movements</code>), reservas de 30 min com <code>lockForUpdate</code> ordenado e venda presencial como pedido <code>pos</code> (ADRs 2 e 5).</td></tr>
      <tr><td>Erro de cálculo monetário (ponto flutuante)</td><td>Média</td><td>Alto</td><td>Armazenamento exclusivo em centavos inteiros (<code>bigint</code>) com <code>brick/money</code>, rateio exato por linha e <code>CHECK</code> no PostgreSQL (ADR 1).</td></tr>
      <tr><td>Vazamento ou escalação de acesso</td><td>Baixa</td><td>Muito Alto</td><td>Guards separados (<code>web</code> vs. <code>customers</code>), RBAC restrito no Filament, 2FA por código OTP, rate limit de login e validação de CPF (LGPD).</td></tr>
      <tr><td>Indisponibilidade de APIs externas</td><td>Média</td><td>Médio</td><td>Cache local, timeouts curtos e fallback automático (BrasilAPI CEP v2 para ViaCEP; Resend para modo demonstração; frete local).</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>1.1.5 Premissas</h3>
<ul>
  <li><strong>Arquitetura e Stack:</strong> Após revisão técnica documentada nas ADRs (novembro de 2026), o sistema foi consolidado em <strong>Laravel 13</strong>, <strong>PHP 8.4</strong>, <strong>Filament 5</strong>, <strong>PostgreSQL 16</strong> e <strong>Tailwind CSS v4</strong>, adotando arquitetura de monólito modular com 7 módulos desacoplados.</li>
  <li><strong>Precisão financeira:</strong> Todas as operações ocorrem em Reais (BRL) calculados em centavos no servidor, preservando snapshot histórico nos itens vendidos.</li>
  <li><strong>Operação multicanal:</strong> Todo pedido nasce pelo mesmo serviço central (<code>PlaceOrder</code>), diferenciando-se pelo atributo de canal (<code>web</code>, <code>pos</code> ou <code>whatsapp</code>).</li>
</ul>

<h2>1.2 Visão da solução</h2>

<h3>1.2.1 Declaração de visão</h3>
<p>Para a equipe da Vevey Doces e seus clientes de varejo e eventos que precisam comprar e gerenciar doces, embalagens e artigos de festa sem conflitos de estoque, a plataforma <strong>Website Vevey</strong> é um sistema web modular de e-commerce e gestão operacional que unifica vitrine online, PDV de balcão, controle de estoque auditável por ledger, gestão de clientes com verificação em duas etapas e painel gerencial em tempo real.</p>

<h3>1.2.2 Cenários de utilização do sistema</h3>
<ul>
  <li><strong>Cenário 1 — Compra online pela loja:</strong> Carla encontra um link no Instagram, acessa a loja, filtra balões pela paleta de cores (Azul e Metalizado), adiciona caixas de chocolates ao carrinho, aplica o cupom <code>VEVEY10</code>, calcula SEDEX/PAC com data prevista de entrega (pulando feriados), finaliza via Pix (5% OFF) e acompanha o pedido em <em>Minha Conta</em>.</li>
  <li><strong>Cenário 2 — Venda presencial no PDV Balcão (ADR 5):</strong> Um cliente compra pessoalmente na loja física. Juliana acessa <code>/admin/pdv</code>, seleciona os produtos por nome ou SKU, escolhe a forma de pagamento e conclui a venda; o sistema cria um pedido <code>pos</code> já pago e entregue, dando baixa automática no ledger (permitindo saldo negativo com alerta visual caso uma entrada ainda não tenha sido lançada).</li>
  <li><strong>Cenário 3 — Gestão, reposição e auditoria:</strong> A equipe de logística lança uma entrada de fornecedor ou realiza contagem física em <code>/admin/stock-levels</code> (nunca editando o saldo diretamente). Miriam acompanha o faturamento, ticket médio, vendas por canal e próximas datas sazonais no Dashboard e audita cada ação em <code>/admin/audit-log</code>.</li>
</ul>

<h3>1.2.3 Features do produto</h3>
<ul>
  <li>Vitrine editorial responsiva com busca preditiva ao vivo, filtros combinados por atributos (OU no mesmo atributo, E entre atributos diferentes — ADR 6) e seletor de cores irmãs na página do produto.</li>
  <li>Carrinho lateral com barra de progresso para frete grátis (R$ 150,00) e checkout com ViaCEP/BrasilAPI, SEDEX/PAC com dias úteis reais, cupom de desconto ao vivo e pagamento Sandbox (QR Code Pix e simulação de aprovação).</li>
  <li>Área do cliente autenticada em guard isolado (<code>customers</code>) com cadastro protegido por verificação em 2 etapas (OTP de 6 dígitos), validação oficial de CPF, histórico de pedidos e endereços salvos.</li>
  <li>Painel Administrativo Filament 5 com RBAC (4 papéis), Dashboard gerencial, PDV Balcão, Catálogo, Cupons, Estoque (Ledger/Reservas), Clientes e Auditoria unificada.</li>
</ul>

<!-- ==================== CAPÍTULO 2 ==================== -->
<div class="page-break"></div>
<h1>2. Requisitos de Software</h1>

<h2>2.1 Casos de uso</h2>
<p>A Figura 2 apresenta a visão geral dos oito casos de uso do sistema (UC01 a UC08) e os quatro atores humanos que interagem diretamente com a plataforma.</p>

<div class="figure-wrap">
  {SVG_USE_CASES}
  <div class="figure-caption">Figura 2 — Diagrama de Casos de Uso do Sistema Vevey Doces</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>2.1.1 Documentação dos casos de uso</h3>
<p>A seguir, os oito casos de uso representados na Figura 2 são especificados integralmente conforme o modelo oficial da disciplina.</p>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Explorar Catálogo e Buscar — ID: UC01</div>
  <p><strong>Resumo:</strong> O cliente navega pelas categorias, utiliza a busca preditiva ou full-text em português e combina filtros dinâmicos de atributos (Cor, Tipo) para localizar produtos com saldo real.</p>
  <p><strong>Atores primários:</strong> Cliente (AT01). <strong>Atores secundários:</strong> Nenhum.</p>
  <p><strong>Pré-condições:</strong> Existem produtos ativos cadastrados no módulo <code>catalog</code> com categorias e atributos associados.</p>
  <p><strong>Fluxo de eventos principal:</strong></p>
  <ol>
    <li>O cliente acessa a página inicial (<code>/</code>) ou o catálogo (<code>/catalogo</code>).</li>
    <li>Digita um termo na busca em pílula (nome, SKU ou código de barras) ou seleciona filtros de categoria, cor e tipo.</li>
    <li>O sistema executa a busca full-text (<code>tsvector</code> GIN) e aplica a regra da ADR 6 (OU dentro do mesmo atributo, E entre atributos diferentes).</li>
    <li>Exibe os cards com preço, preço Pix, selo de caixa fechada e disponibilidade real em estoque.</li>
    <li>Ao clicar em um produto, abre a PDP (<code>/produto/{{slug}}</code>) exibindo seletor de cores irmãs e botão de adicionar ao carrinho.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Produto localizado e pronto para inclusão no carrinho de compras.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Nenhum produto encontrado exibe estado vazio com botão de limpar filtros; (A2) Item com estoque disponível menor ou igual a zero aparece como <em>Esgotado</em> e bloqueia adição ao carrinho.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Realizar Compra (Checkout Online) — ID: UC02</div>
  <p><strong>Resumo:</strong> O cliente revisa os itens do carrinho, informa dados de contato e entrega (ou retirada), aplica cupom opcional, escolhe Pix ou cartão e finaliza o pedido com reserva automática de estoque.</p>
  <p><strong>Atores primários:</strong> Cliente (AT01). <strong>Atores secundários:</strong> Serviços Externos — BrasilAPI/ViaCEP (AT05).</p>
  <p><strong>Pré-condições:</strong> O carrinho possui ao menos um produto ativo com saldo disponível em <code>stock_levels</code>.</p>
  <p><strong>Fluxo de eventos principal:</strong></p>
  <ol>
    <li>O cliente acessa <code>/checkout</code> e preenche (ou confirma autopreenchimento) de nome, e-mail e telefone.</li>
    <li>Seleciona retirada na loja (grátis) ou entrega pelos Correios, informando o CEP para cotação de SEDEX/PAC com previsão em dias úteis reais.</li>
    <li>Opcionalmente informa um código de cupom (ex.: <code>VEVEY10</code>) validado em tempo real no servidor.</li>
    <li>Escolhe a forma de pagamento (Pix com 5% OFF ou Cartão) e confirma o pedido.</li>
    <li>O serviço <code>PlaceOrder</code> grava <code>orders</code>, <code>order_items</code> (snapshot), <code>order_adjustments</code>, <code>payments</code> e cria reservas de 30 minutos em <code>stock_reservations</code>.</li>
    <li>O sistema exibe o código do pedido (<code>VV-2026-XXXXXX</code>), o QR Code Pix e a opção de aprovação Sandbox.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Pedido persistido com status <code>pending</code> e estoque reservado; após aprovação do pagamento, a reserva é convertida em movimento <code>sale</code> no ledger.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Estoque insuficiente bloqueia a finalização retornando HTTP 422; (A2) Cupom expirado ou abaixo do valor mínimo exibe aviso sem travar o checkout.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Cadastrar Conta com Verificação em 2 Etapas (2FA) — ID: UC03</div>
  <p><strong>Resumo:</strong> Um visitante ou cliente que já comprou pelo WhatsApp/site cria ou ativa sua conta pessoal mediante confirmação de um código OTP de 6 dígitos.</p>
  <p><strong>Atores primários:</strong> Cliente (AT01). <strong>Atores secundários:</strong> Serviços Externos — Resend API / SMTP (AT05).</p>
  <p><strong>Pré-condições:</strong> Não existe conta verificada com o mesmo endereço de e-mail.</p>
  <p><strong>Fluxo de eventos principal:</strong></p>
  <ol>
    <li>O usuário preenche nome, e-mail, WhatsApp, CPF opcional e senha em <code>/cadastro</code>.</li>
    <li>O sistema valida o CPF (algoritmo Módulo 11), cria ou vincula o registro em <code>customers</code> e normaliza os contatos em <code>customer_identities</code>.</li>
    <li>Gera um código OTP de 6 dígitos (validade de 15 minutos), dispara o envio por e-mail e redireciona para <code>/verificar-conta</code>.</li>
    <li>O usuário informa o código de 6 dígitos; o sistema marca <code>email_verified_at</code>, autentica no guard <code>customers</code> e abre <code>/conta</code>.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Conta verificada e autenticada com acesso exclusivo aos próprios pedidos e endereços salvos.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Código inválido ou expirado é rejeitado com opção de reenvio; (A2) CPF inválido bloqueia o envio antes da gravação; (A3) Sem chave SMTP/Resend em homologação, exibe card demonstrativo do código.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Registrar Venda Presencial no PDV Balcão — ID: UC04</div>
  <p><strong>Resumo:</strong> A atendente registra uma venda presencial na loja física como um pedido de canal <code>pos</code>, dando baixa direta e rastreável no estoque.</p>
  <p><strong>Atores primários:</strong> Atendente (AT02) ou Administrador (AT04). <strong>Atores secundários:</strong> Nenhum.</p>
  <p><strong>Pré-condições:</strong> Usuário autenticado no painel <code>/admin</code> (guard <code>web</code>) com permissão RBAC sobre pedidos.</p>
  <p><strong>Fluxo de eventos principal:</strong></p>
  <ol>
    <li>A atendente abre <code>/admin/pdv</code>, pesquisa produtos por nome, SKU ou código de barras e adiciona quantidades ao carrinho do balcão.</li>
    <li>Seleciona a forma de pagamento (Dinheiro, Pix, Débito ou Crédito) e conclui a venda.</li>
    <li>O serviço <code>PlaceOrder</code> cria o pedido com <code>channel = pos</code>, <code>payment_status = paid</code>, <code>fulfillment_status = delivered</code> e <code>seller_id</code> preenchido.</li>
    <li><code>RecordDirectSale</code> registra a saída <code>sale</code> no ledger <code>stock_movements</code>; caso algum item fique com saldo negativo, o sistema conclui a venda e exibe alerta de reposição.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Venda contabilizada nos relatórios por canal, estoque atualizado e evento gravado na auditoria.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Carrinho vazio impede conclusão da venda; (A2) Produto com saldo negativo gera notificação visual imediata sem travar a fila do caixa (ADR 5).</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Gerenciar Catálogo e Cupons — ID: UC05</div>
  <p><strong>Resumo:</strong> O administrador cadastra, edita, duplica ou inativa produtos, categorias, atributos (Cor, Tipo) e campanhas de cupons promocionais.</p>
  <p><strong>Atores primários:</strong> Administrador / SuperAdmin (AT04). <strong>Atores secundários:</strong> Nenhum.</p>
  <p><strong>Pré-condições:</strong> Usuário autenticado em <code>/admin</code> com papel <code>admin</code> ou <code>super_admin</code>.</p>
  <p><strong>Fluxo de eventos principal:</strong></p>
  <ol>
    <li>O administrador acessa <em>Catálogo → Produtos</em> (ou <em>Vendas → Cupons</em>) no painel Filament.</li>
    <li>Preenche os campos obrigatórios (SKU único, nome, preço em reais, preço promocional opcional, categoria, atributos e imagens WebP).</li>
    <li>Para cupons, define código (ex.: <code>VEVEY10</code>), tipo (percentual ou valor fixo), pedido mínimo, limite de usos e vigência.</li>
    <li>Salva o registro; o sistema valida unicidade e constraints <code>CHECK</code> e publica imediatamente na vitrine.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Produto ou cupom persistido no PostgreSQL, indexado no <code>search_vector</code> e refletido em tempo real na loja.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) SKU ou código de cupom duplicado é rejeitado com mensagem de validação; (A2) Preço promocional maior ou igual ao preço cheio é bloqueado por validação e constraint de banco.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Consultar Dashboard e Logs de Auditoria — ID: UC06</div>
  <p><strong>Resumo:</strong> O administrador acompanha indicadores gerenciais no Dashboard e audita o histórico completo de alterações de estoque e transições de status de pedidos.</p>
  <p><strong>Atores primários:</strong> Administrador / SuperAdmin (AT04). <strong>Atores secundários:</strong> Serviços Externos — BrasilAPI Feriados (AT05).</p>
  <p><strong>Pré-condições:</strong> Usuário autenticado em <code>/admin</code> com permissão de acesso ao Dashboard e à página <code>/admin/audit-log</code>.</p>
  <p><strong>Fluxo de eventos principal:</strong></p>
  <ol>
    <li>O administrador acessa <code>/admin</code> e visualiza os widgets de faturamento, ticket médio, vendas por canal, top produtos, alertas de estoque baixo e próximos feriados sazonais.</li>
    <li>Navega até <em>Auditoria → Logs de auditoria</em> (<code>/admin/audit-log</code>).</li>
    <li>Filtra os eventos por módulo (Estoque ou Pedidos), usuário responsável ou termo de busca.</li>
    <li>Inspeciona a linha do tempo unificada com data/hora, ator responsável, ação executada e justificativa.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Indicadores gerenciais e trilha de auditoria (RNF11) consultados sem alteração de estado.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Papéis <code>attendant</code> e <code>logistics</code> recebem HTTP 403 ao tentar acessar <code>/admin/audit-log</code>; (A2) Falha na BrasilAPI exibe cache local de feriados.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Ajustar ou Receber Estoque via Ledger — ID: UC07</div>
  <p><strong>Resumo:</strong> A equipe de logística ou administração registra entradas de fornecedores ou contagens físicas de inventário sem jamais sobrescrever o saldo manualmente sem histórico.</p>
  <p><strong>Atores primários:</strong> Logística (AT03) ou Administrador (AT04). <strong>Atores secundários:</strong> Nenhum.</p>
  <p><strong>Pré-condições:</strong> Usuário autenticado no painel <code>/admin</code> com permissão sobre <code>StockLevel</code> e produto cadastrado no catálogo.</p>
  <p><strong>Fluxo de eventos principal:</strong></p>
  <ol>
    <li>O usuário acessa <code>/admin/stock-levels</code> e aciona <em>Lançar entrada</em> ou <em>Contagem / Ajustar</em> no produto desejado.</li>
    <li>Informa a quantidade recebida (ou a quantidade física contada na prateleira) e o motivo obrigatório.</li>
    <li>A action transacional (<code>AdjustStock</code>) trava a linha com <code>lockForUpdate</code>, calcula o delta, insere uma linha imutável em <code>stock_movements</code> e atualiza <code>stock_levels</code>.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Saldo recalculado de forma atômica, refletido na vitrine e auditável em <code>/admin/audit-log</code>.</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Quantidade informada igual ao saldo atual cancela a inserção (constraint <code>qty != 0</code>); (A2) Motivo em branco bloqueia o envio do modal.</p>
</div>

<div class="uc-card">
  <div class="uc-title">Caso de Uso: Separar e Entregar Pedido — ID: UC08</div>
  <p><strong>Resumo:</strong> A equipe de logística ou atendimento acompanha pedidos pagos, registra a separação/envio (ou retirada) e atualiza o status de entrega com histórico auditável.</p>
  <p><strong>Atores primários:</strong> Logística (AT03) ou Atendente (AT02). <strong>Atores secundários:</strong> Nenhum.</p>
  <p><strong>Pré-condições:</strong> Usuário autenticado em <code>/admin</code> e existência de pedido com <code>payment_status = paid</code> pendente de separação.</p>
  <p><strong>Fluxo de eventos principal:</strong></p>
  <ol>
    <li>O operador abre <em>Vendas → Pedidos</em> (<code>/admin/orders</code>) e filtra os pedidos pagos aguardando expedição.</li>
    <li>Abre o detalhe do pedido, confere os itens em <code>order_items</code> e o endereço de entrega (ou retirada em loja).</li>
    <li>Aciona <code>UpdateOrderStatus</code> para avançar <code>fulfillment_status</code> (<code>separating</code> → <code>shipped</code> → <code>delivered</code>), registrando observação opcional.</li>
    <li>O sistema atualiza os timestamps do pedido e insere o evento em <code>order_status_histories</code>.</li>
  </ol>
  <p><strong>Pós-condições:</strong> Status de entrega atualizado no banco e visível ao cliente em <em>Minha Conta</em> (<code>/conta</code>).</p>
  <p><strong>Fluxos alternativos:</strong> (A1) Cancelamento de pedido pago aciona estorno de estoque gerando movimento positivo de devolução no ledger; (A2) Cancelamento de pedido pendente libera imediatamente as reservas em <code>stock_reservations</code>.</p>
</div>

<h3>2.1.2 Documentação dos atores</h3>
<p>A Tabela 8 consolida os cinco atores do sistema, seguidos pela especificação individual completa conforme o modelo oficial da disciplina (ID, Resumo, Responsabilidades, Ambiente Físico, Número/Tipo e Frequência de Uso).</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 8 — Resumo dos atores do sistema (ID, tipo, ambiente e frequência)</div>
  <table>
    <thead><tr><th style="width:48px;">ID</th><th style="width:135px;">Ator</th><th style="width:76px;">Tipo</th><th style="width:140px;">Ambiente Físico</th><th>Frequência de uso</th></tr></thead>
    <tbody>
      <tr><td><strong>AT01</strong></td><td>Cliente</td><td>Primário</td><td>Residência, trabalho ou celular (Web)</td><td>Esporádica (eventos) a semanal (revendedores)</td></tr>
      <tr><td><strong>AT02</strong></td><td>Atendente</td><td>Primário</td><td>Balcão da loja física Vevey Doces</td><td>Contínua durante o expediente comercial (seg–sáb)</td></tr>
      <tr><td><strong>AT03</strong></td><td>Logística</td><td>Primário</td><td>Depósito e área de expedição da loja</td><td>Diária (recebimentos, contagens e separações)</td></tr>
      <tr><td><strong>AT04</strong></td><td>Administrador / SuperAdmin</td><td>Primário</td><td>Escritório da loja ou acesso remoto</td><td>Diária para KPIs e semanal para gestão/auditoria</td></tr>
      <tr><td><strong>AT05</strong></td><td>BrasilAPI / ViaCEP / Resend</td><td>Secundário</td><td>Infraestrutura em nuvem (APIs HTTPS)</td><td>Sob demanda em cadastros, fretes e Dashboard</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="uc-card">
  <div class="actor-title">Ator: Cliente — ID: AT01</div>
  <p><strong>Resumo:</strong> Representa qualquer consumidor final, organizador de festas ou revendedor que acessa a loja virtual da Vevey Doces para consultar catálogo e realizar compras.</p>
  <p><strong>Responsabilidades:</strong> Pesquisar e filtrar produtos; gerenciar carrinho; calcular frete; aplicar cupons; finalizar pedidos; cadastrar e verificar conta (2FA); acompanhar entregas em <code>/conta</code>.</p>
  <p><strong>Ambiente Físico:</strong> Acesso remoto via navegador desktop ou smartphone conectado à internet.</p>
  <p><strong>Número e Tipo:</strong> Ilimitado (centenas a milhares de usuários humanos concorrentes na vitrine pública).</p>
  <p><strong>Frequência com que usa o sistema:</strong> Variável — desde compras pontuais para festas (1 a 3 vezes por ano) até compras semanais por revendedores e confeiteiros.</p>
</div>

<div class="uc-card">
  <div class="actor-title">Ator: Atendente — ID: AT02</div>
  <p><strong>Resumo:</strong> Colaborador da loja física responsável pelo atendimento no balcão e pelo suporte comercial a clientes via WhatsApp.</p>
  <p><strong>Responsabilidades:</strong> Registrar vendas presenciais no PDV Balcão (<code>/admin/pdv</code>); localizar clientes por telefone (inclusive números aposentados); consultar disponibilidade de estoque e acompanhar status de pedidos.</p>
  <p><strong>Ambiente Físico:</strong> Terminal de computador ou tablet instalado no balcão de atendimento da Vevey Doces.</p>
  <p><strong>Número e Tipo:</strong> De 1 a 4 usuários humanos simultâneos com papel RBAC <code>attendant</code>.</p>
  <p><strong>Frequência com que usa o sistema:</strong> Contínua durante todo o horário comercial (segunda a sábado, das 09h às 19h).</p>
</div>

<div class="uc-card">
  <div class="actor-title">Ator: Logística — ID: AT03</div>
  <p><strong>Resumo:</strong> Colaborador encarregado do estoque físico, recebimento de mercadorias de fornecedores e expedição/retirada de pedidos.</p>
  <p><strong>Responsabilidades:</strong> Lançar entradas de fornecedores; executar contagens físicas via ledger (<code>/admin/stock-levels</code>); separar pedidos pagos e atualizar status de expedição/entrega.</p>
  <p><strong>Ambiente Físico:</strong> Área de estoque, depósito e bancada de separação da loja física Vevey Doces.</p>
  <p><strong>Número e Tipo:</strong> De 1 a 3 usuários humanos com papel RBAC <code>logistics</code>.</p>
  <p><strong>Frequência com que usa o sistema:</strong> Diária, a cada recebimento de fornecedor, contagem periódica e lote de separação de pedidos.</p>
</div>

<div class="uc-card">
  <div class="actor-title">Ator: Administrador / SuperAdmin — ID: AT04</div>
  <p><strong>Resumo:</strong> Sócia-gerente ou responsável técnico com acesso gerencial pleno à plataforma e governança de segurança.</p>
  <p><strong>Responsabilidades:</strong> Cadastrar/editar produtos, preços e cupons; gerir contas da equipe e papéis RBAC; monitorar KPIs no Dashboard e auditar movimentações em <code>/admin/audit-log</code>.</p>
  <p><strong>Ambiente Físico:</strong> Escritório administrativo da Vevey Doces ou acesso remoto seguro via HTTPS.</p>
  <p><strong>Número e Tipo:</strong> De 1 a 2 usuários humanos com papéis RBAC <code>admin</code> ou <code>super_admin</code>.</p>
  <p><strong>Frequência com que usa o sistema:</strong> Diária para acompanhamento financeiro/estoque e semanal para campanhas, preços e auditoria.</p>
</div>

<div class="uc-card">
  <div class="actor-title">Ator: Serviços Externos (BrasilAPI / ViaCEP / Resend) — ID: AT05</div>
  <p><strong>Resumo:</strong> Serviços web de terceiros integrados via HTTP/JSON para enriquecimento de endereço por CEP, calendário oficial de feriados nacionais e envio de e-mails transacionais 2FA.</p>
  <p><strong>Responsabilidades:</strong> Retornar logradouro/cidade/UF a partir do CEP; listar feriados nacionais para cálculo de dias úteis de entrega; entregar e-mails de verificação OTP de 6 dígitos.</p>
  <p><strong>Ambiente Físico:</strong> Servidores em nuvem externos acessados pela aplicação via HTTPS com cache local e fallback.</p>
  <p><strong>Número e Tipo:</strong> 3 serviços sistêmicos externos (atores não humanos / secundários).</p>
  <p><strong>Frequência com que usa o sistema:</strong> Acionados sob demanda durante o checkout, cadastro de cliente e carregamento do Dashboard (mitigado por cache de 24h a 7 dias).</p>
</div>

<h2>2.2 Requisitos funcionais</h2>
<p>A Tabela 9 lista os 16 requisitos funcionais implementados, vinculando cada requisito ao seu respectivo caso de uso (rastreabilidade) e módulo responsável.</p>

<div class="table-wrap compact-table">
  <div class="table-caption">Tabela 9 — Requisitos Funcionais (RF01 a RF16) e casos de uso relacionados</div>
  <table>
    <thead><tr><th style="width:56px;">ID</th><th>Descrição do Requisito Funcional</th><th style="width:82px;">Caso de Uso</th><th style="width:86px;">Módulo</th></tr></thead>
    <tbody>
      <tr><td><strong>RF01</strong></td><td>Permitir cadastro, edição, duplicação e inativação (soft delete) de produtos planos com SKU único, preço e preço promocional.</td><td>UC05</td><td><code>catalog</code></td></tr>
      <tr><td><strong>RF02</strong></td><td>Organizar produtos em categorias hierárquicas (até 2 níveis) e atributos dinâmicos (Cor, Tipo) para filtros combinados.</td><td>UC01, UC05</td><td><code>catalog</code></td></tr>
      <tr><td><strong>RF03</strong></td><td>Fornecer busca full-text em português e busca preditiva ao vivo por nome, descrição, SKU e código de barras.</td><td>UC01</td><td><code>catalog</code> / <code>panel-site</code></td></tr>
      <tr><td><strong>RF04</strong></td><td>Exibir disponibilidade real de estoque nos cards e na página do produto, bloqueando compra online quando esgotado.</td><td>UC01, UC02</td><td><code>inventory</code> / <code>panel-site</code></td></tr>
      <tr><td><strong>RF05</strong></td><td>Gerenciar carrinho de compras com barra de progresso para frete grátis (R$ 150,00) e ajuste rápido de quantidades.</td><td>UC01, UC02</td><td><code>panel-site</code></td></tr>
      <tr><td><strong>RF06</strong></td><td>Calcular frete (Retirada, SEDEX e PAC) via CEP com autopreenchimento (BrasilAPI/ViaCEP) e previsão em dias úteis reais.</td><td>UC02</td><td><code>panel-site</code></td></tr>
      <tr><td><strong>RF07</strong></td><td>Validar e aplicar cupons de desconto (percentual ou valor fixo, pedido mínimo, limite de usos e vigência) acumulados com 5% Pix.</td><td>UC02, UC05</td><td><code>orders</code></td></tr>
      <tr><td><strong>RF08</strong></td><td>Persistir pedidos com snapshot imutável dos itens, ajustes, pagamentos 1:N e rateio exato de desconto por linha (<code>Money::allocate</code>).</td><td>UC02, UC04</td><td><code>orders</code></td></tr>
      <tr><td><strong>RF09</strong></td><td>Criar reservas de estoque de 30 minutos no checkout, convertendo em baixa <code>sale</code> na confirmação do pagamento ou liberando no cancelamento.</td><td>UC02, UC08</td><td><code>inventory</code></td></tr>
      <tr><td><strong>RF10</strong></td><td>Disponibilizar tela de PDV Balcão (<code>/admin/pdv</code>) que registra vendas presenciais como pedidos <code>pos</code> já pagos e entregues.</td><td>UC04</td><td><code>panel-admin</code> / <code>orders</code></td></tr>
      <tr><td><strong>RF11</strong></td><td>Permitir entradas de fornecedor e contagens físicas de estoque exclusivamente por ações no ledger, com alerta de saldo negativo/mínimo.</td><td>UC07</td><td><code>inventory</code> / <code>panel-admin</code></td></tr>
      <tr><td><strong>RF12</strong></td><td>Permitir cadastro e login de clientes em guard separado com validação de CPF e verificação em 2 etapas via código OTP de 6 dígitos.</td><td>UC03</td><td><code>customers</code> / <code>panel-site</code></td></tr>
      <tr><td><strong>RF13</strong></td><td>Armazenar identidades de contato normalizadas (telefone E.164, WhatsApp, e-mail) com aposentadoria (<code>retired_at</code>) e busca por números antigos.</td><td>UC03, UC04</td><td><code>customers</code></td></tr>
      <tr><td><strong>RF14</strong></td><td>Disponibilizar área <em>Minha Conta</em> (<code>/conta</code>) exibindo estritamente os pedidos do cliente autenticado e gerenciamento de endereços salvos.</td><td>UC02, UC03</td><td><code>panel-site</code></td></tr>
      <tr><td><strong>RF15</strong></td><td>Apresentar Dashboard gerencial com faturamento, ticket médio, vendas por canal, top produtos, alertas de estoque e próximos feriados.</td><td>UC06</td><td><code>panel-admin</code></td></tr>
      <tr><td><strong>RF16</strong></td><td>Fornecer painel unificado de Logs de Auditoria (<code>/admin/audit-log</code>) rastreando responsável, data/hora e motivo em alterações de estoque e pedidos.</td><td>UC06, UC07, UC08</td><td><code>panel-admin</code></td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>2.3 Requisitos não funcionais</h2>
<p>A Tabela 10 documenta os requisitos de qualidade, segurança, desempenho e arquitetura aplicáveis ao sistema e os casos de uso impactados.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 10 — Requisitos Não Funcionais (RNF01 a RNF11) e casos de uso relacionados</div>
  <table>
    <thead><tr><th style="width:62px;">ID</th><th style="width:112px;">Categoria</th><th>Especificação</th><th style="width:86px;">Caso de Uso</th></tr></thead>
    <tbody>
      <tr><td><strong>RNF01</strong></td><td>Integridade financeira</td><td>Todos os valores monetários armazenados em centavos (<code>bigint</code>) via <code>brick/money</code> com <code>CHECK</code> de totais no PostgreSQL.</td><td>UC02, UC04, UC05</td></tr>
      <tr><td><strong>RNF02</strong></td><td>Concorrência</td><td>Operações de estoque protegidas por <code>DB::transaction</code> e <code>lockForUpdate</code> ordenado por <code>product_id</code> para prevenção de deadlocks.</td><td>UC02, UC04, UC07</td></tr>
      <tr><td><strong>RNF03</strong></td><td>Idempotência</td><td>Índice único composto em <code>stock_movements (reference_type, reference_id, reason, product_id)</code> impedindo baixa duplicada.</td><td>UC02, UC04, UC08</td></tr>
      <tr><td><strong>RNF04</strong></td><td>Segurança / Auth</td><td>Separação estrita entre guard da equipe (<code>web</code>) e guard de clientes (<code>customers</code>); senhas com hash bcrypt/Argon2.</td><td>UC03, UC04, UC05, UC06</td></tr>
      <tr><td><strong>RNF05</strong></td><td>Autorização</td><td>Controle de acesso baseado em papéis (RBAC) com modo <code>strictAuthorization</code> no Filament e policies em todos os models.</td><td>UC04, UC05, UC06, UC07, UC08</td></tr>
      <tr><td><strong>RNF06</strong></td><td>Proteção CSRF/Brute</td><td>Proteção CSRF ativa em todas as rotas web e bloqueio por <code>RateLimiter</code> após 5 tentativas falhas de login.</td><td>UC02, UC03</td></tr>
      <tr><td><strong>RNF07</strong></td><td>Privacidade (LGPD)</td><td>Validação matemática local de CPF sem consulta indevida de dados pessoais em APIs de terceiros; soft delete em dados sensíveis.</td><td>UC03, UC05</td></tr>
      <tr><td><strong>RNF08</strong></td><td>Desempenho</td><td>Índice GIN sobre coluna gerada <code>tsvector</code> para busca full-text e cache de 24h a 7d nas consultas às APIs externas.</td><td>UC01, UC02, UC06</td></tr>
      <tr><td><strong>RNF09</strong></td><td>Manutenibilidade</td><td>Fronteiras entre módulos garantidas automaticamente por testes de arquitetura Pest (<code>ModuleDependenciesTest</code>).</td><td>Todos (UC01–UC08)</td></tr>
      <tr><td><strong>RNF10</strong></td><td>Usabilidade</td><td>Interface responsiva (mobile e desktop) seguindo o <code>DESIGN.md</code> próprio da marca com fonte tabular (<code>tabular-nums</code>) em preços.</td><td>UC01, UC02, UC03</td></tr>
      <tr><td><strong>RNF11</strong></td><td>Rastreabilidade</td><td>Histórico completo de transições de status e movimentações de estoque com identificação do usuário responsável e observação.</td><td>UC04, UC06, UC07, UC08</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>2.4 Protótipos do software</h2>

<h3>2.4.1 Telas implementadas do sistema</h3>
<p>As Figuras 3 a 6 apresentam capturas reais das principais telas da aplicação em execução, numeradas de <strong>T01</strong> a <strong>T04</strong> para associação direta ao diagrama de navegação da Seção 2.4.2 e ao Manual do Usuário na Seção 4.6.4.</p>

<div class="figure-wrap">
  <img src="{shot_home}" alt="Tela inicial da loja Vevey Doces"/>
  <div class="figure-caption">Figura 3 — Tela T01 — Página Inicial da Loja Vevey Doces (Hero editorial, busca e departamentos)</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>

<div class="figure-wrap">
  <img src="{shot_catalog}" alt="Catálogo de produtos da Vevey Doces"/>
  <div class="figure-caption">Figura 4 — Tela T02 — Catálogo de Produtos com filtros por atributo (ADR 6) e estoque real</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>

<div class="figure-wrap">
  <img src="{shot_product}" alt="Página de detalhes do produto"/>
  <div class="figure-caption">Figura 5 — Tela T03 — Página de Produto (PDP) com seletor de cores irmãs e estoque</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>

<div class="figure-wrap">
  <img src="{shot_register}" alt="Tela de cadastro de cliente com 2FA"/>
  <div class="figure-caption">Figura 6 — Tela T04 — Cadastro de Cliente com validação de CPF e verificação em 2 etapas (2FA)</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>

<h3>2.4.2 Modelo de navegação</h3>
<p>A Figura 7 apresenta o modelo de navegação estruturado como um <strong>Diagrama UML de Atividade</strong>, evidenciando o nó inicial, os pontos de decisão do usuário (<code>[acessa "/"]</code>, <code>[acessa "/admin"]</code>, <code>[escolhe "Catálogo"]</code>, <code>[escolhe "PDV"]</code>), as telas numeradas (<strong>T01</strong> a <strong>T08</strong>), os eventos disparados nas transições e o nó final.</p>

<div class="figure-wrap">
  {SVG_NAVIGATION}
  <div class="figure-caption">Figura 7 — Diagrama UML de Atividade: Modelo de Navegação da Loja e do Painel Administrativo</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== CAPÍTULO 3 ==================== -->
<div class="page-break"></div>
<h1>3. Projeto do Sistema</h1>

<h2>3.1 Modelo estrutural do sistema</h2>

<h3>3.1.1 Diagrama de pacotes (Monólito Modular — ADR 8)</h3>
<p>O sistema adota a arquitetura de <strong>monólito modular</strong> (<code>internachi/modular</code>), particionado em sete pacotes independentes organizados em quatro camadas estritas. Chamadas síncronas descem sempre por meio de <em>Actions</em> e <em>DTOs</em> imutáveis (sufixo <code>Data</code>), enquanto modelos Eloquent não atravessam fronteiras entre módulos de domínio. A Figura 8 e a Tabela 11 detalham essa divisão.</p>

<div class="figure-wrap">
  {SVG_PACKAGES}
  <div class="figure-caption">Figura 8 — Diagrama de Pacotes e Camadas do Monólito Modular (ADR 8)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="table-wrap">
  <div class="table-caption">Tabela 11 — Especificação dos pacotes do sistema (responsabilidades e elementos principais)</div>
  <table>
    <thead><tr><th style="width:120px;">Pacote</th><th style="width:240px;">Responsabilidades</th><th>Elementos principais</th></tr></thead>
    <tbody>
      <tr><td><code>Vevey\\Catalog</code></td><td>Gestão do catálogo plano de produtos, categorias, atributos dinâmicos, mídias WebP e busca full-text.</td><td><code>Product</code>, <code>Category</code>, <code>Attribute</code>, <code>AttributeValue</code>, <code>FindProductsForSale</code>, <code>ProductData</code>.</td></tr>
      <tr><td><code>Vevey\\Inventory</code></td><td>Controle de estoque via ledger append-only, reservas de 30 min com lock ordenado e cache de saldo.</td><td><code>StockMovement</code>, <code>StockReservation</code>, <code>StockLevel</code>, <code>ReserveStock</code>, <code>FulfillOrderReservations</code>, <code>RecordDirectSale</code>, <code>AdjustStock</code>.</td></tr>
      <tr><td><code>Vevey\\Customers</code></td><td>Cadastro autenticável em guard isolado, identidades de contato aposentáveis e endereços salvos.</td><td><code>Customer</code>, <code>CustomerIdentity</code>, <code>CustomerAddress</code>, <code>FindOrCreateCustomerByContact</code>, <code>RetireAndReplaceIdentity</code>.</td></tr>
      <tr><td><code>Vevey\\Orders</code></td><td>Orquestração de vendas multicanal (Site, PDV, WhatsApp), snapshots de linha, cupons, pagamentos e histórico.</td><td><code>Order</code>, <code>OrderItem</code>, <code>OrderAdjustment</code>, <code>Coupon</code>, <code>Payment</code>, <code>PlaceOrder</code>, <code>UpdateOrderStatus</code>.</td></tr>
      <tr><td><code>Vevey\\Permissions</code></td><td>Autorização RBAC baseada em morph map e sincronização automática dos 4 papéis operacionais.</td><td><code>Role</code>, <code>Permission</code>, <code>Roles</code>, <code>PermissionsEnum</code>, <code>ModelPolicy</code>, <code>SyncPermissionsCommand</code>.</td></tr>
      <tr><td><code>Vevey\\PanelAdmin</code></td><td>Interface administrativa Filament 5: Dashboard, PDV Balcão, CRUDs operacionais e Logs de Auditoria.</td><td><code>OrderResource</code>, <code>StockLevelResource</code>, <code>CustomerResource</code>, <code>CouponResource</code>, <code>PosSale</code>, <code>AuditLog</code>, Widgets.</td></tr>
      <tr><td><code>Vevey\\PanelSite</code></td><td>Interface pública da loja: vitrine, busca preditiva, filtros ADR 6, checkout Sandbox e área do cliente 2FA.</td><td><code>StorefrontController</code>, <code>CheckoutController</code>, <code>CustomerAuthController</code>, <code>CustomerAccountController</code>.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>3.1.2 Diagrama de classes de domínio e especificações</h3>
<p>A Figura 9 resume as principais classes de domínio, seus atributos tipados (<code>Money</code>, enums PHP e UUIDs) e os relacionamentos estruturais entre os módulos. Na sequência, as quatro classes centrais do negócio são especificadas textualmente conforme o modelo oficial da disciplina.</p>

<div class="figure-wrap">
  {SVG_CLASSES}
  <div class="figure-caption">Figura 9 — Diagrama de Classes de Domínio (Catálogo, Pedidos, Estoque e Clientes)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="uc-card">
  <div class="class-title">3.1.2.1 Classe Product (Vevey\\Catalog\\Models\\Product)</div>
  <p><strong>Descrição:</strong> Representa um item comercializável do catálogo plano da Vevey Doces (cada cor ou formato de caixa constitui um registro próprio com SKU exclusivo).</p>
  <p><strong>Superclasse(s):</strong> <code>Illuminate\\Database\\Eloquent\\Model</code> (com traits <code>HasUuids</code>, <code>SoftDeletes</code>, <code>InteractsWithMedia</code>). <strong>Subclasse(s):</strong> Nenhuma.</p>
  <p><strong>Atributos principais:</strong> <code>id</code> (UUID v7, identificador único); <code>sku</code> (string única, até 64 caracteres); <code>name</code> (string, nome comercial); <code>price_cents</code> (<code>Money</code> BRL, inteiro positivo maior que zero); <code>promo_price_cents</code> (<code>?Money</code>, nulo ou menor que <code>price_cents</code>); <code>is_active</code> (boolean).</p>
  <p><strong>Operação principal — <code>scopeSearch(Builder $query, string $term)</code>:</strong> Filtra produtos usando o índice GIN <code>search_vector</code> (português) combinado com correspondência de SKU e código de barras. <em>Pré-condição:</em> termo não vazio. <em>Pós-condição:</em> retorna consulta ordenada por relevância sem alterar estado.</p>
</div>

<div class="uc-card">
  <div class="class-title">3.1.2.2 Classe Order (Vevey\\Orders\\Models\\Order)</div>
  <p><strong>Descrição:</strong> Orquestra uma venda multicanal (<code>web</code>, <code>pos</code> ou <code>whatsapp</code>), mantendo totais monetários validados em centavos e separando status financeiro de status logístico.</p>
  <p><strong>Superclasse(s):</strong> <code>Illuminate\\Database\\Eloquent\\Model</code> (<code>HasUuids</code>). <strong>Subclasse(s):</strong> Nenhuma.</p>
  <p><strong>Atributos principais:</strong> <code>id</code> (UUID v7); <code>number</code> (string única, padrão <code>VV-AAAA-XXXXXX</code>); <code>channel</code> (enum <code>OrderChannel</code>: web, pos, whatsapp); <code>payment_status</code> (enum <code>PaymentStatus</code>); <code>fulfillment_status</code> (enum <code>FulfillmentStatus</code>); <code>subtotal_cents</code>, <code>discount_cents</code>, <code>shipping_cents</code>, <code>total_cents</code> (<code>Money</code> BRL com <code>CHECK</code> de soma exata).</p>
  <p><strong>Operação principal — <code>PlaceOrder::handle(PlaceOrderData $data): Order</code>:</strong> Valida itens, calcula descontos (cupom e 5% Pix) via <code>Money::allocate()</code>, grava pedido, snapshots em <code>order_items</code> e aciona reserva ou baixa direta no estoque. <em>Pré-condição:</em> lista não vazia de itens válidos. <em>Pós-condição:</em> pedido e movimentações de estoque persistidos atomicamente.</p>
</div>

<div class="uc-card">
  <div class="class-title">3.1.2.3 Classe StockMovement (Vevey\\Inventory\\Models\\StockMovement)</div>
  <p><strong>Descrição:</strong> Registro imutável (append-only) de cada entrada, saída de venda, devolução ou contagem física no ledger de estoque (ADR 2).</p>
  <p><strong>Superclasse(s):</strong> <code>Illuminate\\Database\\Eloquent\\Model</code> (<code>HasUuids</code>). <strong>Subclasse(s):</strong> Nenhuma.</p>
  <p><strong>Atributos principais:</strong> <code>id</code> (UUID v7); <code>product_id</code> (UUID, FK para <code>products</code>); <code>qty</code> (int assinado, domínio <code>qty != 0</code>); <code>direction</code> (coluna gerada <code>STORED</code>: <code>in</code> se positivo, <code>out</code> se negativo); <code>reason</code> (enum <code>MovementReason</code>: purchase, sale, adjustment, return); <code>balance_after</code> (int); <code>user_id</code> (UUID do responsável).</p>
  <p><strong>Operação principal — <code>AdjustStock::handle(productId, targetQty, reason, userId)</code>:</strong> Adquire <code>lockForUpdate</code> sobre <code>stock_levels</code>, calcula a diferença entre saldo contado e atual, grava o lançamento no ledger e atualiza o saldo. <em>Pré-condição:</em> motivo informado e delta diferente de zero. <em>Pós-condição:</em> novo registro imutável gravado e cache sincronizado.</p>
</div>

<div class="uc-card">
  <div class="class-title">3.1.2.4 Classe Customer (Vevey\\Customers\\Models\\Customer)</div>
  <p><strong>Descrição:</strong> Representa o comprador autenticável no guard isolado <code>customers</code>, vinculando pedidos, endereços salvos e múltiplas identidades de contato aposentáveis (ADR 7).</p>
  <p><strong>Superclasse(s):</strong> <code>Illuminate\\Foundation\\Auth\\User</code> (<code>Authenticatable</code>, <code>HasUuids</code>, <code>SoftDeletes</code>). <strong>Subclasse(s):</strong> Nenhuma.</p>
  <p><strong>Atributos principais:</strong> <code>id</code> (UUID v7); <code>name</code> (string); <code>email</code> (string única); <code>cpf</code> (string opcional de 11 dígitos validada por Módulo 11); <code>password</code> (hash bcrypt/Argon2); <code>verification_code</code> (OTP de 6 dígitos); <code>email_verified_at</code> (?timestamp).</p>
  <p><strong>Operação principal — <code>RetireAndReplaceIdentity::handle(customer, oldValue, newValue)</code>:</strong> Marca <code>retired_at</code> no contato antigo e registra o novo telefone/e-mail como primário. <em>Pré-condição:</em> novo valor normalizado (E.164/e-mail) sem conflito ativo. <em>Pós-condição:</em> contato novo ativo e número antigo preservado para buscas históricas.</p>
</div>

<h2>3.2 Modelo de banco de dados do sistema</h2>
<p>O banco de dados relacional é gerenciado em <strong>PostgreSQL 16</strong> com chaves primárias em UUID v7, colunas monetárias em centavos inteiros, colunas geradas <code>STORED</code> (<code>products.search_vector</code>, <code>stock_movements.direction</code> e <code>stock_levels.available</code>), índices parciais únicos para soft deletes e identidades ativas, e ausência total de triggers (toda lógica de negócio reside na aplicação). A Figura 10 apresenta o Diagrama Entidade-Relacionamento (DER) com chaves primárias (PK), chaves estrangeiras (FK) e cardinalidades, seguido pelo dicionário de tabelas na Tabela 12.</p>

<div class="figure-wrap">
  {SVG_DER}
  <div class="figure-caption">Figura 10 — Diagrama Entidade-Relacionamento (DER) do Banco de Dados PostgreSQL</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="table-wrap">
  <div class="table-caption">Tabela 12 — Dicionário das tabelas do banco de dados PostgreSQL</div>
  <table>
    <thead><tr><th style="width:110px;">Módulo</th><th style="width:195px;">Tabela</th><th>Finalidade e principais restrições</th></tr></thead>
    <tbody>
      <tr><td><code>catalog</code></td><td><code>categories</code>, <code>products</code>, <code>category_product</code></td><td>Catálogo plano (soft delete), <code>CHECK</code> de preços positivos e coluna gerada <code>search_vector</code> com índice GIN.</td></tr>
      <tr><td><code>catalog</code></td><td><code>attributes</code>, <code>attribute_values</code>, <code>attribute_value_product</code></td><td>Atributos dinâmicos (Cor, Tipo) para filtros combinados (ADR 6) sem colunas esparsas.</td></tr>
      <tr><td><code>inventory</code></td><td><code>stock_movements</code></td><td>Ledger append-only (<code>qty != 0</code>, <code>direction</code> stored) com índice único de idempotência.</td></tr>
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
<p>A Figura 11 representa o diagrama de sequência do fluxo crítico de compra online (UC02): desde a submissão do checkout até a reserva transacional de estoque sob <code>lockForUpdate</code> e a posterior baixa definitiva no ledger após a confirmação do pagamento.</p>

<div class="figure-wrap">
  {SVG_SEQUENCE}
  <div class="figure-caption">Figura 11 — Diagrama de Sequência: Checkout Web, Reserva, Pagamento e Baixa no Ledger</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== CAPÍTULO 4 ==================== -->
<div class="page-break"></div>
<h1>4. Documentação Técnica do Sistema</h1>

<h2>4.1 Modelo de implementação do sistema</h2>
<p>O software foi implementado em <strong>PHP 8.4</strong> com <strong>Laravel 13</strong>, <strong>Filament 5</strong> (painel administrativo), <strong>Tailwind CSS v4</strong> e <strong>Vite 8</strong> (loja pública), utilizando <strong>PostgreSQL 16</strong> como SGBD principal. A base de código emprega <code>declare(strict_types=1)</code>, DTOs <code>final readonly</code>, casts customizados (<code>MoneyCast</code> sobre <code>brick/money</code>), enums tipados e <code>spatie/laravel-medialibrary</code> para gestão de imagens com conversão WebP. A Figura 12 ilustra o Diagrama de Componentes e Artefatos (<code>«artifact»</code>), detalhado na Tabela 13.</p>

<div class="figure-wrap">
  {SVG_ARTIFACTS}
  <div class="figure-caption">Figura 12 — Diagrama de Componentes e Artefatos («artifact») da Arquitetura de Implementação</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="table-wrap">
  <div class="table-caption">Tabela 13 — Organização dos diretórios e artefatos de implementação</div>
  <table>
    <thead><tr><th style="width:165px;">Diretório / Artefato</th><th>Descrição e papel na arquitetura</th></tr></thead>
    <tbody>
      <tr><td><code>app-modules/*</code></td><td>Contém os 7 módulos independentes (<code>catalog</code>, <code>inventory</code>, <code>customers</code>, <code>orders</code>, <code>permissions</code>, <code>panel-admin</code>, <code>panel-site</code>), cada um com seus próprios <code>src/</code>, <code>database/migrations/</code>, <code>tests/</code> e <code>composer.json</code>.</td></tr>
      <tr><td><code>app/</code> e <code>routes/</code></td><td>Camada compartilhada com <code>User</code>, <code>MoneyCast</code>, <code>BrasilApiService</code>, <code>TransactionalMailer</code>, rotas HTTP (<code>web.php</code>) e agendamento de tarefas (<code>console.php</code>).</td></tr>
      <tr><td><code>resources/</code> e <code>public/build/</code></td><td>Templates Blade da vitrine pública, identidade visual SVG (<code>logo.blade.php</code>), estilos Tailwind CSS v4 e bundle compilado pelo Vite 8.</td></tr>
      <tr><td><code>tests/Arch/</code></td><td>Suíte Pest Arch (<code>ModuleDependenciesTest.php</code>) que valida automaticamente as fronteiras entre módulos da ADR 8.</td></tr>
      <tr><td><code>Dockerfile</code> e <code>render.yaml</code></td><td>Artefatos de empacotamento em container e blueprint declarativo para implantação automatizada na nuvem (Render.com + PostgreSQL).</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>4.2 Modelos de testes do sistema</h2>
<p>A estratégia de verificação contempla três níveis complementares: <strong>Testes de Arquitetura (Pest Arch)</strong> em <code>tests/Arch/ModuleDependenciesTest.php</code>, <strong>Testes Funcionais de Domínio (Pest Feature)</strong> cobrindo pedidos, ledger de estoque e identidades de clientes, e <strong>Validação E2E em PostgreSQL</strong> validando migrations, constraints <code>CHECK</code>, colunas geradas <code>STORED</code>, fluxo 2FA e checkout Sandbox. A Tabela 14 consolida a Matriz de Rastreabilidade dos casos de teste executados.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 14 — Matriz de Rastreabilidade de Casos de Teste do Sistema</div>
  <table>
    <thead><tr><th style="width:44px;">ID</th><th style="width:46px;">UC</th><th style="width:120px;">Título do Teste</th><th style="width:108px;">Pré-condição</th><th>Passos principais</th><th style="width:132px;">Resultado Esperado</th><th style="width:54px;">Status</th></tr></thead>
    <tbody>
      <tr><td><strong>TC01</strong></td><td>UC01</td><td>Filtro combinado ADR 6</td><td>Produtos ativos com atributos Cor e Tipo</td><td>1. Abrir <code>/catalogo</code><br/>2. Marcar Azul e Metalizado</td><td>Lista apenas produtos com Cor igual a Azul e Tipo igual a Metalizado</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC02</strong></td><td>UC02</td><td>Checkout Web com Cupom e Pix</td><td>Carrinho com itens e saldo disponível</td><td>1. Informar CEP<br/>2. Aplicar <code>VEVEY10</code><br/>3. Finalizar no Pix</td><td>Pedido persistido, desconto rateado e reserva de 30 min criada</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC03</strong></td><td>UC02</td><td>Bloqueio de venda sem saldo</td><td>Produto com saldo zero</td><td>1. Tentar finalizar pedido web acima do saldo</td><td>Servidor retorna HTTP 422 impedindo venda sem lastro</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC04</strong></td><td>UC03</td><td>Cadastro com CPF e 2FA</td><td>E-mail ainda não verificado</td><td>1. Cadastrar com CPF válido<br/>2. Inserir OTP errado e depois correto</td><td>OTP errado rejeitado; OTP correto ativa conta e abre <code>/conta</code></td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC05</strong></td><td>UC04</td><td>Venda presencial PDV Balcão</td><td>Atendente logado em <code>/admin/pdv</code></td><td>1. Selecionar itens<br/>2. Escolher pagamento<br/>3. Concluir venda</td><td>Pedido <code>pos</code> pago/entregue e baixa <code>sale</code> gravada no ledger</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC06</strong></td><td>UC07</td><td>Contagem física e ajuste</td><td>Logística logada em <code>/admin/stock-levels</code></td><td>1. Acionar Contagem/Ajustar<br/>2. Informar saldo contado e motivo</td><td>Diferença gravada como <code>adjustment</code> no ledger e saldo atualizado</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC07</strong></td><td>UC06</td><td>Auditoria de ações (RNF11)</td><td>SuperAdmin logado em <code>/admin/audit-log</code></td><td>1. Filtrar por módulo e responsável</td><td>Exibe linha do tempo com usuário, data/hora, ação e motivo</td><td><strong>Passou</strong></td></tr>
      <tr><td><strong>TC08</strong></td><td>UC08</td><td>Arquitetura modular (ADR 8)</td><td>Repositório com 7 módulos</td><td>1. Verificar imports entre camadas</td><td>Zero imports de <code>Vevey\\*</code> em <code>app/</code>; fronteiras respeitadas</td><td><strong>Passou</strong></td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>4.3 Modelos de implantação do sistema</h2>
<p>O projeto está empacotado em container <strong>Docker</strong> (<code>Dockerfile</code> e <code>docker/start-render.sh</code>) com blueprint automatizado em <code>render.yaml</code> para implantação na nuvem (Render.com com PostgreSQL gerenciado), conforme ilustrado no Diagrama de Implantação da Figura 13.</p>

<div class="figure-wrap">
  {SVG_DEPLOY}
  <div class="figure-caption">Figura 13 — Diagrama de Implantação (Docker, Render, PostgreSQL e APIs Externas)</div>
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
php artisan db:seed --class="Vevey\\Catalog\\Database\\Seeders\\CatalogSeeder"
php artisan db:seed --class="Vevey\\Inventory\\Database\\Seeders\\InitialStockSeeder"
php artisan db:seed --class="Database\\Seeders\\StaffUsersSeeder"
php artisan sync:permissions
php -S 127.0.0.1:8000 -t public</pre>

<h2>4.5 Documentação técnica do sistema (ADRs e Integrações)</h2>
<p>As decisões arquiteturais do projeto foram registradas formalmente em <code>docs/MODELAGEM_LARAVEL_ADR.md</code> e sintetizadas na Tabela 15. Além disso, o sistema expõe os comandos operacionais <code>php artisan stock:rebuild</code> (reconstrução do cache de saldo a partir do ledger) e <code>php artisan stock:expire-reservations</code> (agendado a cada 5 minutos em <code>routes/console.php</code>), bem como integrações seguras com <strong>BrasilAPI</strong> (CEP v2 e Feriados Nacionais), <strong>ViaCEP</strong> e <strong>Resend HTTP API</strong> para envio de e-mails 2FA.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 15 — Resumo das Decisões Arquiteturais (ADRs 1 a 9)</div>
  <table>
    <thead><tr><th style="width:72px;">ADR</th><th style="width:175px;">Tema</th><th>Decisão implementada</th></tr></thead>
    <tbody>
      <tr><td><strong>ADR 1</strong></td><td>Dinheiro</td><td>Centavos inteiros (<code>bigint</code>), <code>MoneyCast</code>, <code>order_adjustments</code>, rateio via <code>Money::allocate()</code> e <code>CHECK</code> de totais.</td></tr>
      <tr><td><strong>ADR 2</strong></td><td>Estoque (Ledger)</td><td>Ledger de entrada única append-only (<code>stock_movements</code>), reservas de 30 min e cache <code>stock_levels</code> sob lock.</td></tr>
      <tr><td><strong>ADR 3</strong></td><td>Snapshot de Pedido</td><td>Snapshot de SKU, nome, preço e atributos em <code>order_items</code> sem tabela de versionamento de produto.</td></tr>
      <tr><td><strong>ADR 4</strong></td><td>Status de Pedido</td><td>Separação entre <code>payment_status</code> e <code>fulfillment_status</code>, timestamps de transição e <code>order_status_histories</code>.</td></tr>
      <tr><td><strong>ADR 5</strong></td><td>Venda Presencial (PDV)</td><td>Venda de balcão registrada como pedido <code>pos</code> (pago e entregue), permitindo saldo negativo com alerta.</td></tr>
      <tr><td><strong>ADR 6</strong></td><td>Catálogo e Busca</td><td>Catálogo plano (cada cor equivale a um produto), atributos dinâmicos (OU/E) e busca full-text GIN em português.</td></tr>
      <tr><td><strong>ADR 7</strong></td><td>Identidades de Cliente</td><td>Guard separado <code>customers</code>, login padrão com 2FA e <code>customer_identities</code> com aposentadoria (<code>retired_at</code>).</td></tr>
      <tr><td><strong>ADR 8</strong></td><td>Monólito Modular</td><td>7 módulos com camadas verificadas por Pest Arch, comunicação por Actions e DTOs e RBAC por morph map.</td></tr>
      <tr><td><strong>ADR 9</strong></td><td>Evolução da Busca</td><td>Documentação das limitações do radicalizador português e plano futuro de adoção de <code>unaccent</code> e <code>pg_trgm</code>.</td></tr>
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
<p><strong>Loja Virtual (<code>/</code> — Telas T01 a T04, ilustradas nas Figuras 3 a 6):</strong> na Tela T01 (Figura 3), utilize a barra de busca em pílula no topo ou escolha um departamento; na Tela T02 (Figura 4), combine filtros de categoria e cor em <code>/catalogo</code> e adicione itens ajustando a quantidade (<code>- 1 +</code>); na Tela T03 (Figura 5), alterne entre cores irmãs e confira a disponibilidade real antes de clicar em <em>Adicionar ao carrinho</em>. Em seguida, clique em <em>Carrinho → Finalizar pedido</em>, informe o CEP para cotar SEDEX/PAC com previsão em dias úteis reais (ou escolha Retirada na loja), aplique o cupom <code>VEVEY10</code> e conclua via Pix ou Cartão. Para criar conta, acesse <em>Minha conta → Criar conta</em> (Tela T04, Figura 6) e confirme o código de 6 dígitos na tela de verificação em duas etapas, seguindo o fluxo da Figura 7.</p>
<p><strong>Painel Administrativo (<code>/admin</code> — Telas T07 e T08, fluxo na Figura 7):</strong> autentique-se com uma das credenciais da Tabela 16. Utilize <strong>Vendas → PDV Balcão</strong> (<code>/admin/pdv</code>) para vendas presenciais rápidas; <strong>Vendas → Pedidos</strong> para confirmar pagamentos e avançar entregas; <strong>Vendas → Cupons</strong> para criar campanhas promocionais; <strong>Estoque → Saldos de estoque</strong> para lançar entradas de fornecedor ou contagens físicas (<em>Aviso importante:</em> nunca altere saldos diretamente no banco de dados — utilize sempre as ações do ledger); e <strong>Auditoria → Logs de auditoria</strong> para rastrear o histórico completo de operações.</p>

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
