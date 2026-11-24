# -*- coding: utf-8 -*-
"""
Gera a apresentação N2 (10 slides widescreen 16:9 objetivos, ~15 min) em PDF pronto para projetor/pendrive
focando no problema, arquitetura modular, ferramentas e roteiro de demonstração da Vevey Doces.
"""
import base64
import os
import shutil
import subprocess
import tempfile

BASE_DIR = r"C:\Users\Nathan\Desktop\Vevey - tcc\docs\n2"
SHOTS_DIR = os.path.join(BASE_DIR, "screenshots")
LOGO_PATH = r"C:\Users\Nathan\Desktop\Vevey - tcc\public\images\brand\vevey-symbol.svg"
HTML_OUT = os.path.join(BASE_DIR, "VeveyCo-Apresentacao-N2.html")
PDF_OUT = os.path.join(BASE_DIR, "VeveyCo-Apresentacao-N2.pdf")
PDF_DOWNLOADS = r"C:\Users\Nathan\Downloads\VeveyCo-Apresentacao-N2.pdf"
EDGE_BIN = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"


def img_b64(filename: str) -> str:
    with open(os.path.join(SHOTS_DIR, filename), "rb") as f:
        return "data:image/png;base64," + base64.b64encode(f.read()).decode("ascii")


def main() -> None:
    with open(LOGO_PATH, "r", encoding="utf-8") as f:
        logo_svg = f.read()

    home_img = img_b64("home.png")
    catalog_img = img_b64("catalog.png")
    product_img = img_b64("product.png")

    html = f"""<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8" />
<title>Vevey Co. — Apresentação N2 (30/11/2026)</title>
<style>
  @page {{ size: 297mm 167mm; margin: 0; }}
  * {{ box-sizing: border-box; }}
  body {{ margin: 0; font-family: Arial, Helvetica, sans-serif; color: #1F1B16; background: #F7F4EE; }}
  .slide {{
    width: 297mm;
    height: 167mm;
    padding: 16mm 20mm;
    background: #FDFBF7;
    position: relative;
    page-break-after: always;
    break-after: page;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
  }}
  .slide-dark {{
    background: #1C1915;
    color: #FDFBF7;
  }}
  .tag {{
    display: inline-block;
    font-size: 10pt;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #E11D48;
    margin-bottom: 6mm;
  }}
  h1 {{
    font-size: 32pt;
    line-height: 1.12;
    margin: 0 0 6mm 0;
    letter-spacing: -0.02em;
  }}
  h2 {{
    font-size: 24pt;
    line-height: 1.18;
    margin: 0 0 7mm 0;
    letter-spacing: -0.015em;
  }}
  .lead {{ font-size: 14pt; color: #5E554A; line-height: 1.45; max-width: 210mm; }}
  .slide-dark .lead {{ color: #C8C0B4; }}
  .grid-2 {{ display: grid; grid-template-columns: 1fr 1fr; gap: 10mm; align-items: start; }}
  .grid-3 {{ display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 7mm; }}
  .grid-4 {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 6mm; }}
  .card {{
    background: #FFFFFF;
    border: 1px solid #E5DDD0;
    border-radius: 10px;
    padding: 6mm;
  }}
  .card h3 {{ font-size: 13pt; margin: 0 0 3mm 0; color: #1F1B16; }}
  .card p, .card li {{ font-size: 10.8pt; color: #4A4137; line-height: 1.42; margin: 0 0 2mm 0; }}
  .card-accent {{ border-top: 4px solid #E11D48; }}
  .card-green {{ border-top: 4px solid #16A34A; }}
  .card-amber {{ border-top: 4px solid #D97706; }}
  .card-blue {{ border-top: 4px solid #2563EB; }}
  .shot {{ width: 100%; border-radius: 8px; border: 1px solid #E2E8F0; }}
  .footer {{
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 9pt;
    color: #7B7266;
    border-top: 1px solid rgba(31,27,22,0.1);
    padding-top: 3mm;
  }}
  .slide-dark .footer {{ color: #9CA3AF; border-color: rgba(255,255,255,0.12); }}
  .pill {{
    display: inline-block;
    padding: 2mm 4mm;
    border-radius: 999px;
    background: #FFF1F2;
    color: #9F1239;
    font-size: 10pt;
    font-weight: 700;
    margin: 0 2mm 2mm 0;
  }}
  .step-num {{
    display: inline-flex;
    width: 24px;
    height: 24px;
    border-radius: 999px;
    background: #E11D48;
    color: #FFF;
    font-weight: 700;
    font-size: 11pt;
    align-items: center;
    justify-content: center;
    margin-right: 6px;
  }}
</style>
</head>
<body>

<!-- SLIDE 1: CAPA -->
<section class="slide slide-dark">
  <div style="display:flex;align-items:center;gap:14px;">
    <div style="width:48px;height:48px;">{logo_svg}</div>
    <span style="font-size:12pt;color:#AAA398;letter-spacing:0.05em;">FATEC · LABORATÓRIO DE ENGENHARIA DE SOFTWARE (N2)</span>
  </div>
  <div>
    <div class="tag" style="color:#FB7185;">EQUIPE VEVEY CO. · 30 DE NOVEMBRO DE 2026</div>
    <h1 style="font-size:38pt;color:#FDFBF7;">Vevey Doces</h1>
    <p class="lead" style="font-size:16pt;">Plataforma modular de E-commerce, Estoque Integrado com Ledger, PDV Balcão e Auditoria Operacional.</p>
    <p style="margin-top:8mm;font-size:11pt;color:#C8C0B4;">Matheus da Silva Araújo · Nathan Costa Santos · Ricardo Martins Pighin</p>
  </div>
  <div class="footer"><span>github.com/nathocosta/vevey</span><span>Slide 1 / 10</span></div>
</section>

<!-- SLIDE 2: O PROBLEMA -->
<section class="slide">
  <div>
    <div class="tag">1. O Problema</div>
    <h2>Vendas multicanal descentralizadas e estoque fora de sincronia</h2>
    <div class="grid-3">
      <div class="card card-accent">
        <h3>Atendimento manual</h3>
        <p>Instagram gerava demanda, mas todo orçamento, preço e conferência ocorria manualmente no WhatsApp.</p>
      </div>
      <div class="card card-amber">
        <h3>Conflito de estoque</h3>
        <p>Vendas na loja física não davam baixa integrada, provocando venda online de itens já esgotados na prateleira.</p>
      </div>
      <div class="card card-blue">
        <h3>Sem visão gerencial</h3>
        <p>Falta de indicadores consolidados (faturamento por canal, ticket médio, campeões de venda e rastreabilidade).</p>
      </div>
    </div>
  </div>
  <div class="footer"><span>Vevey Co. — Problema e Motivação</span><span>Slide 2 / 10</span></div>
</section>

<!-- SLIDE 3: A SOLUÇÃO -->
<section class="slide">
  <div>
    <div class="tag">2. A Solução</div>
    <h2>Um único sistema unificando loja virtual, balcão físico e gestão</h2>
    <div class="grid-2" style="align-items:center;">
      <div style="display:flex;flex-direction:column;gap:5mm;">
        <div class="card">
          <h3>Vitrine Online (panel-site)</h3>
          <p>Catálogo plano por cores/tipos, busca preditiva, carrinho, frete SEDEX/PAC com dias úteis reais, cupom e conta do cliente com 2FA.</p>
        </div>
        <div class="card">
          <h3>Painel Operacional &amp; PDV (panel-admin)</h3>
          <p>PDV Balcão integrado, estoque auditável por ledger, gestão de pedidos/cupons, Dashboard de KPIs e Logs de Auditoria.</p>
        </div>
      </div>
      <img class="shot" src="{home_img}" alt="Home Vevey Doces"/>
    </div>
  </div>
  <div class="footer"><span>Vevey Co. — Visão da Solução</span><span>Slide 3 / 10</span></div>
</section>

<!-- SLIDE 4: ARQUITETURA MODULAR -->
<section class="slide">
  <div>
    <div class="tag">3. Arquitetura de Software (ADR 8)</div>
    <h2>Monólito Modular em 4 camadas com fronteiras testadas</h2>
    <div class="grid-4">
      <div class="card card-accent">
        <h3>Interface</h3>
        <p><strong>panel-admin</strong> (Filament 5)</p>
        <p><strong>panel-site</strong> (Loja Blade + Tailwind v4)</p>
      </div>
      <div class="card card-amber">
        <h3>Orquestração</h3>
        <p><strong>orders</strong></p>
        <p>PlaceOrder único para Site, PDV e WhatsApp + Cupons e Pagamentos 1:N.</p>
      </div>
      <div class="card card-green">
        <h3>Domínio Base</h3>
        <p><strong>catalog</strong> · <strong>inventory</strong> · <strong>customers</strong></p>
        <p>Isolados entre si; comunicação via Actions + DTOs imutáveis.</p>
      </div>
      <div class="card card-blue">
        <h3>Compartilhado</h3>
        <p><strong>permissions</strong> (RBAC)</p>
        <p><strong>App\</strong> (MoneyCast, BrasilAPI, Mailer)</p>
      </div>
    </div>
    <p class="lead" style="margin-top:6mm;font-size:11.5pt;">Garantia automática: <strong>Pest Arch (ModuleDependenciesTest)</strong> bloqueia qualquer import proibido entre camadas na CI.</p>
  </div>
  <div class="footer"><span>Vevey Co. — Arquitetura Modular</span><span>Slide 4 / 10</span></div>
</section>

<!-- SLIDE 5: STACK E FERRAMENTAS -->
<section class="slide">
  <div>
    <div class="tag">4. Ferramentas e Stack Tecnológica</div>
    <h2>Tecnologias modernas escolhidas por produtividade e rigor</h2>
    <div class="grid-3">
      <div class="card">
        <h3>Back-end &amp; Painel</h3>
        <p>• PHP 8.4 (strict types) + Laravel 13</p>
        <p>• Filament 5 (Painel + PDV + Widgets)</p>
        <p>• internachi/modular · brick/money</p>
      </div>
      <div class="card">
        <h3>Banco &amp; Front-end</h3>
        <p>• PostgreSQL 16 (UUID v7, GIN, CHECK)</p>
        <p>• Blade + Tailwind CSS v4 + Vite 8</p>
        <p>• spatie/medialibrary · spatie/permission</p>
      </div>
      <div class="card">
        <h3>APIs &amp; Implantação</h3>
        <p>• BrasilAPI (CEP v2 + Feriados) &amp; ViaCEP</p>
        <p>• Resend HTTP API (E-mails 2FA)</p>
        <p>• Docker + Render.com Blueprint</p>
      </div>
    </div>
  </div>
  <div class="footer"><span>Vevey Co. — Stack e Integrações</span><span>Slide 5 / 10</span></div>
</section>

<!-- SLIDE 6: DECISÕES DE ENGENHARIA (ADRs) -->
<section class="slide">
  <div>
    <div class="tag">5. Engenharia &amp; Decisões Críticas</div>
    <h2>Problemas reais resolvidos na modelagem (ADRs 1 a 7)</h2>
    <div class="grid-2">
      <div class="card card-green">
        <h3>Estoque com Ledger + Reservas (ADR 2 &amp; 5)</h3>
        <p>• <strong>stock_movements</strong> append-only (nunca edita saldo na mão).</p>
        <p>• Reservas de 30 min no checkout sob <code>lockForUpdate</code> ordenado.</p>
        <p>• Venda física vira pedido <code>pos</code> e permite negativo com alerta.</p>
      </div>
      <div class="card card-accent">
        <h3>Dinheiro Exato &amp; Snapshot (ADR 1 &amp; 3)</h3>
        <p>• Centavos inteiros (<code>bigint</code>) + <code>CHECK</code> de totais no Postgres.</p>
        <p>• Rateio proporcional de cupom/Pix via <code>Money::allocate()</code>.</p>
        <p>• Snapshot completo em <code>order_items</code> preserva histórico.</p>
      </div>
    </div>
  </div>
  <div class="footer"><span>Vevey Co. — Decisões Arquiteturais</span><span>Slide 6 / 10</span></div>
</section>

<!-- SLIDE 7: SEGURANÇA, RBAC E 2FA -->
<section class="slide">
  <div>
    <div class="tag">6. Segurança, LGPD e Rastreabilidade</div>
    <h2>Guards isolados, RBAC granular, 2FA e Auditoria</h2>
    <div class="grid-3">
      <div class="card card-blue">
        <h3>Autenticação &amp; 2FA</h3>
        <p>Guards separados (<code>web</code> equipe vs. <code>customers</code> loja), código OTP de 6 dígitos no cadastro, rate limit (5 tentativas) e validação Módulo 11 de CPF.</p>
      </div>
      <div class="card card-amber">
        <h3>RBAC (4 Papéis)</h3>
        <p>140 permissões sincronizadas automaticamente para <strong>SuperAdmin</strong>, <strong>Admin</strong>, <strong>Atendente</strong> e <strong>Logística</strong>.</p>
      </div>
      <div class="card card-accent">
        <h3>Auditoria (RNF011)</h3>
        <p>Painel unificado <code>/admin/audit-log</code> mostra <strong>quem</strong> alterou estoque ou status de pedido, <strong>quando</strong> e o <strong>motivo</strong>.</p>
      </div>
    </div>
  </div>
  <div class="footer"><span>Vevey Co. — Segurança e Governança</span><span>Slide 7 / 10</span></div>
</section>

<!-- SLIDE 8: EXPERIÊNCIA DA LOJA -->
<section class="slide">
  <div>
    <div class="tag">7. Experiência de Compra</div>
    <h2>Catálogo plano por cores, estoque em tempo real e checkout inteligente</h2>
    <div class="grid-2">
      <img class="shot" src="{catalog_img}" alt="Catálogo Vevey"/>
      <img class="shot" src="{product_img}" alt="PDP Vevey"/>
    </div>
  </div>
  <div class="footer"><span>Vevey Co. — Storefront (panel-site)</span><span>Slide 8 / 10</span></div>
</section>

<!-- SLIDE 9: ROTEIRO DE DEMONSTRAÇÃO AO VIVO -->
<section class="slide">
  <div>
    <div class="tag">8. Demonstração do Software (5 a 10 min)</div>
    <h2>Roteiro dos cenários relevantes ao vivo</h2>
    <div class="grid-2">
      <div class="card">
        <p><span class="step-num">1</span><strong>Loja &amp; Filtros ADR 6:</strong> busca preditiva, filtro por cor/tipo e estoque real na PDP.</p>
        <p><span class="step-num">2</span><strong>Cadastro 2FA:</strong> validação de CPF e confirmação do código OTP de 6 dígitos.</p>
        <p><span class="step-num">3</span><strong>Checkout Completo:</strong> CEP (BrasilAPI + dias úteis sem feriados), cupom <code>VEVEY10</code>, QR Code Pix e aprovação Sandbox.</p>
      </div>
      <div class="card">
        <p><span class="step-num">4</span><strong>PDV Balcão (/admin/pdv):</strong> venda presencial rápida nascendo paga/entregue com baixa imediata no ledger.</p>
        <p><span class="step-num">5</span><strong>Estoque (/admin/stock-levels):</strong> lançamento de entrada/contagem sem edição direta de saldo.</p>
        <p><span class="step-num">6</span><strong>Dashboard &amp; Auditoria:</strong> KPIs atualizados, próximos feriados e trilha em <code>/admin/audit-log</code>.</p>
      </div>
    </div>
  </div>
  <div class="footer"><span>Vevey Co. — Roteiro de Demonstração</span><span>Slide 9 / 10</span></div>
</section>

<!-- SLIDE 10: ENCERRAMENTO -->
<section class="slide slide-dark" style="justify-content:center;align-items:center;text-align:center;">
  <div style="width:68px;height:68px;margin-bottom:6mm;">{logo_svg}</div>
  <h1 style="font-size:34pt;color:#FDFBF7;margin-bottom:4mm;">Obrigado!</h1>
  <p class="lead" style="text-align:center;max-width:170mm;">Vamos à demonstração ao vivo do sistema Vevey Doces.</p>
  <p style="margin-top:8mm;font-size:11pt;color:#FB7185;">Equipe Vevey Co. · Laboratório de Engenharia de Software · FATEC 2026</p>
</section>

</body>
</html>
"""

    with open(HTML_OUT, "w", encoding="utf-8") as f:
        f.write(html)

    prof = tempfile.mkdtemp(prefix="vevey-slides-")
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
        print(f"SLIDES PDF OK: {PDF_OUT} ({size_kb} KB)")
        print(f"COPIED TO DOWNLOADS: {PDF_DOWNLOADS}")
    finally:
        shutil.rmtree(prof, ignore_errors=True)


if __name__ == "__main__":
    main()
