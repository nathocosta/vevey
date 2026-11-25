# -*- coding: utf-8 -*-
"""
Gera a documentação final N2 da equipe Vevey Co. (Laboratório de Engenharia de Software - FATEC)
seguindo FIELMENTE o padrão visual, tipografia (Calibri), margens, capa (logo FATEC),
relatórios de andamento (1 página por aluno), tabelas (bordas pretas finas, fundo branco)
e todas as seções exigidas nos modelos oficiais 01, 02, 03 e de Atores/Casos de Uso.
"""
import base64
import os
import shutil
import subprocess
import tempfile

BASE_DIR = r"C:\Users\Nathan\Desktop\Vevey - tcc\docs\n2"
SHOTS_DIR = os.path.join(BASE_DIR, "screenshots")
FATEC_LOGO = os.path.join(BASE_DIR, "assets", "fatec_image1.jpeg")
HTML_OUT = os.path.join(BASE_DIR, "VeveyCo-LABENGSW-MA5-N1.html")
PDF_OUT = os.path.join(BASE_DIR, "VeveyCo-LABENGSW-MA5-N1.pdf")
PDF_DOWNLOADS = r"C:\Users\Nathan\Downloads\VeveyCo-LABENGSW-MA5-N1.pdf"
EDGE_BIN = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"


def file_b64(path: str, mime: str) -> str:
    with open(path, "rb") as f:
        return f"data:{mime};base64," + base64.b64encode(f.read()).decode("ascii")


def shot_b64(filename: str) -> str:
    return file_b64(os.path.join(SHOTS_DIR, filename), "image/png")


# ---------- HELPERS PARA TABELAS IDÊNTICAS AO MODELO OFICIAL DO PROFESSOR ----------

def report_page(student_name: str, ra: str, table_num: int, rows: list[tuple[str, str, str, str]], min_rows: int = 16) -> str:
    trs = []
    for d1, d2, hrs, desc in rows:
        trs.append(f"<tr><td>{d1}</td><td>{d2}</td><td style='text-align:center;'>{hrs}</td><td>{desc}</td></tr>")
    for _ in range(max(0, min_rows - len(rows))):
        trs.append("<tr><td style='height:19pt;'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>")
    return f"""
<div class="page-break"></div>
<h1 style="margin-top:0;">Relatório de andamento do projeto</h1>
<div class="report-meta">
  <span><strong>Aluno:</strong> &nbsp;{student_name}</span>
  <span><strong>RA:</strong> &nbsp;{ra}</span>
</div>
<div class="table-caption">Tabela {table_num} — Relatório de andamento: {student_name} (RA {ra})</div>
<table class="grid-table">
  <thead>
    <tr>
      <th style="width:82px;">Data inicial</th>
      <th style="width:82px;">Data final</th>
      <th style="width:58px;">Horas</th>
      <th>Descrição sucinta das atividades</th>
    </tr>
  </thead>
  <tbody>
    {''.join(trs)}
  </tbody>
</table>
<div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
"""


def uc_box(num: int, uc_id: str, name: str, desc: str, prim: str, sec: str,
           pre: list[str], flow: list[str], post: list[str], alts: list[tuple[str, str]]) -> str:
    pre_html = "".join(f"<p>({i+1}) {p}</p>" for i, p in enumerate(pre))
    flow_html = "".join(f"<p>({i+1}) {f}</p>" for i, f in enumerate(flow))
    post_html = "".join(f"<p>({i+1}) {p}</p>" for i, p in enumerate(post))
    alt_html = "".join(f"<p><strong>{code}.</strong> {text}</p>" for code, text in alts)
    return f"""
<div class="doc-box">
  <p class="box-head">{num}. Caso de uso {name} ({uc_id})</p>
  <p class="box-sub">{num}.1. Descrição</p>
  <p>{desc}</p>
  <p><strong>Atores primários:</strong> {prim} &nbsp;|&nbsp; <strong>Atores secundários:</strong> {sec}</p>
  <p class="box-sub">{num}.2. Pré-condições</p>
  {pre_html}
  <p class="box-sub">{num}.3. Fluxo de eventos</p>
  {flow_html}
  <p class="box-sub">{num}.4. Pós-condições</p>
  {post_html}
  <p class="box-sub">{num}.5. Fluxos alternativos</p>
  {alt_html}
</div>
"""


def actor_box(num: int, actor_id: str, name: str, desc: str, resps: list[str],
              env: str, num_type: str, freq: str) -> str:
    resp_html = "".join(f"<p>({i+1}) {r}</p>" for i, r in enumerate(resps))
    return f"""
<div class="doc-box">
  <p class="box-head">{num}. Ator {name} (ID: {actor_id})</p>
  <p class="box-sub">{num}.1. Descrição</p>
  <p>{desc}</p>
  <p class="box-sub">{num}.2. Responsabilidades</p>
  {resp_html}
  <p class="box-sub">{num}.3. Ambiente Físico</p>
  <p>{env}</p>
  <p class="box-sub">{num}.4. Número e Tipo</p>
  <p>{num_type}</p>
  <p class="box-sub">{num}.5. Frequência de uso</p>
  <p>{freq}</p>
</div>
"""


def pkg_box(subsec: str, name: str, resp: str, elements: str) -> str:
    return f"""
<div class="table-wrap">
  <table class="grid-table">
    <tbody>
      <tr><td><strong>{subsec} Pacote <code>{name}</code></strong></td></tr>
      <tr><td><strong>Responsabilidades:</strong> {resp}</td></tr>
      <tr><td><strong>Elementos principais:</strong> {elements}</td></tr>
    </tbody>
  </table>
</div>
"""


def class_box(subsec: str, cls_name: str, desc: str, supercls: str, subcls: str,
              attrs: list[tuple[str, str, str, str]],
              op_name: str, op_desc: str, params: list[tuple[str, str, str]],
              pre: list[str], spec: list[str], post: list[str]) -> str:
    attr_rows = []
    for a_name, a_desc, a_type, a_dom in attrs:
        attr_rows.append(f"""
        <tr><td colspan="3"><strong>Nome:</strong> <code>{a_name}</code></td></tr>
        <tr><td style="width:110px;"><strong>Descrição:</strong></td><td colspan="2">{a_desc}</td></tr>
        <tr><td><strong>Tipo:</strong></td><td colspan="2"><code>{a_type}</code></td></tr>
        <tr><td><strong>Domínio:</strong></td><td colspan="2">{a_dom}</td></tr>
        """)
    if params:
        param_rows = "".join(
            f"<tr><td><code>{pn}</code></td><td><code>{pt}</code></td><td>{pp}</td></tr>"
            for pn, pt, pp in params
        )
    else:
        param_rows = "<tr><td colspan='3'>Nenhum parâmetro (opera sobre os atributos da própria instância).</td></tr>"
    pre_li = "".join(f"<li>{x}</li>" for x in pre)
    spec_li = "".join(f"<li>{x}</li>" for x in spec)
    post_li = "".join(f"<li>{x}</li>" for x in post)
    return f"""
<div class="table-wrap">
  <table class="grid-table spec-table">
    <tbody>
      <tr><td colspan="3"><strong>{subsec} Classe <code>{cls_name}</code></strong></td></tr>
      <tr><td colspan="3"><strong>Descrição:</strong> {desc}</td></tr>
      <tr><td colspan="3"><strong>Superclasse(s):</strong> {supercls}</td></tr>
      <tr><td colspan="3"><strong>Subclasse(s):</strong> {subcls}</td></tr>
      <tr><td colspan="3"><strong>Atributos:</strong></td></tr>
      {''.join(attr_rows)}
      <tr><td colspan="3"><strong>Operações:</strong></td></tr>
      <tr><td colspan="3"><strong>Nome:</strong> <code>{op_name}</code></td></tr>
      <tr><td><strong>Descrição:</strong></td><td colspan="2">{op_desc}</td></tr>
      <tr><td colspan="3"><strong>Parâmetros:</strong></td></tr>
      <tr><th style="width:110px;">Nome</th><th style="width:130px;">Tipo</th><th>Propósito</th></tr>
      {param_rows}
      <tr><td colspan="3"><strong>Pré-condições:</strong><ol class="tight-ol">{pre_li}</ol></td></tr>
      <tr><td colspan="3"><strong>Especificação (pseudocódigo):</strong><ol class="tight-ol">{spec_li}</ol></td></tr>
      <tr><td colspan="3"><strong>Pós-condições:</strong><ol class="tight-ol">{post_li}</ol></td></tr>
    </tbody>
  </table>
</div>
"""


def db_table_box(table_num: int, tbl_name: str, desc: str, cols: list[tuple[str, str, str, str]]) -> str:
    rows = "".join(
        f"<tr><td><code>{c}</code></td><td>{d}</td><td style='text-align:center;'>{k}</td><td><code>{t}</code></td></tr>"
        for c, d, k, t in cols
    )
    return f"""
<div class="table-wrap">
  <div class="table-caption">Tabela {table_num} — Especificação da tabela <code>{tbl_name}</code></div>
  <table class="grid-table">
    <tbody>
      <tr><td colspan="4"><strong>Tabela:</strong> <code>{tbl_name}</code></td></tr>
      <tr><td colspan="4"><strong>Descrição:</strong> {desc}</td></tr>
      <tr><th style="width:155px;">Atributo</th><th>Descrição</th><th style="width:55px;text-align:center;">Chave</th><th style="width:140px;">Tipo</th></tr>
      {rows}
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>
"""


# ---------- DIAGRAMAS VETORIAIS EM SVG (FUNDO BRANCO/CLARO, PADRÃO UML/DER ACADÊMICO) ----------

SVG_CANVAS = r"""
<svg viewBox="0 0 820 430" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <rect x="8" y="8" width="804" height="32" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
  <text x="20" y="29" font-family="Calibri, Arial" font-size="13" font-weight="bold" fill="#000000">PROJECT MODEL CANVAS — WEBSITE VEVEY (VEVEY DOCES)</text>
  <text x="798" y="29" text-anchor="end" font-family="Calibri, Arial" font-size="10.5" fill="#333333">Equipe Vevey Co. · FATEC 2026</text>

  <!-- Col 1 -->
  <rect x="8" y="46" width="156" height="122" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="15" y="62" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">JUSTIFICATIVAS</text>
  <text x="15" y="79" font-family="Calibri, Arial" font-size="9" fill="#000000">• Vendas fragmentadas em</text>
  <text x="21" y="92" font-family="Calibri, Arial" font-size="9" fill="#000000">Instagram e WhatsApp.</text>
  <text x="15" y="109" font-family="Calibri, Arial" font-size="9" fill="#000000">• Conflitos de estoque entre</text>
  <text x="21" y="122" font-family="Calibri, Arial" font-size="9" fill="#000000">balcão físico e pedidos.</text>
  <text x="15" y="139" font-family="Calibri, Arial" font-size="9" fill="#000000">• Falta de KPIs e auditoria</text>
  <text x="21" y="152" font-family="Calibri, Arial" font-size="9" fill="#000000">em planilhas manuais.</text>

  <rect x="8" y="174" width="156" height="122" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="15" y="190" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">OBJETIVO SMART</text>
  <text x="15" y="207" font-family="Calibri, Arial" font-size="9" fill="#000000">Entregar plataforma web de</text>
  <text x="15" y="220" font-family="Calibri, Arial" font-size="9" fill="#000000">e-commerce, PDV e estoque</text>
  <text x="15" y="233" font-family="Calibri, Arial" font-size="9" fill="#000000">unificado até nov/2026,</text>
  <text x="15" y="246" font-family="Calibri, Arial" font-size="9" fill="#000000">eliminando divergência de</text>
  <text x="15" y="259" font-family="Calibri, Arial" font-size="9" fill="#000000">saldo e agilizando vendas</text>
  <text x="15" y="272" font-family="Calibri, Arial" font-size="9" fill="#000000">no varejo e caixas fechadas.</text>

  <rect x="8" y="302" width="156" height="120" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="15" y="318" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">BENEFÍCIOS</text>
  <text x="15" y="335" font-family="Calibri, Arial" font-size="9" fill="#000000">• Estoque único auditável.</text>
  <text x="15" y="351" font-family="Calibri, Arial" font-size="9" fill="#000000">• Autoatendimento 24h.</text>
  <text x="15" y="367" font-family="Calibri, Arial" font-size="9" fill="#000000">• Redução de retrabalho.</text>
  <text x="15" y="383" font-family="Calibri, Arial" font-size="9" fill="#000000">• Dados e marca próprios.</text>
  <text x="15" y="399" font-family="Calibri, Arial" font-size="9" fill="#000000">• Decisão via Dashboard.</text>

  <!-- Col 2 -->
  <rect x="170" y="46" width="156" height="122" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="177" y="62" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">PRODUTO</text>
  <text x="177" y="79" font-family="Calibri, Arial" font-size="9" fill="#000000">Website Vevey:</text>
  <text x="177" y="95" font-family="Calibri, Arial" font-size="9" fill="#000000">• Loja pública responsiva</text>
  <text x="177" y="110" font-family="Calibri, Arial" font-size="9" fill="#000000">• Área do cliente com 2FA</text>
  <text x="177" y="125" font-family="Calibri, Arial" font-size="9" fill="#000000">• Painel Filament + PDV</text>
  <text x="177" y="140" font-family="Calibri, Arial" font-size="9" fill="#000000">• Estoque Ledger + Logs</text>
  <text x="183" y="153" font-family="Calibri, Arial" font-size="9" fill="#000000">de Auditoria unificados.</text>

  <rect x="170" y="174" width="156" height="248" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="177" y="190" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">REQUISITOS CHAVE</text>
  <text x="177" y="209" font-family="Calibri, Arial" font-size="9" fill="#000000">• Catálogo plano por cor e</text>
  <text x="183" y="222" font-family="Calibri, Arial" font-size="9" fill="#000000">tipo com busca full-text.</text>
  <text x="177" y="240" font-family="Calibri, Arial" font-size="9" fill="#000000">• Frete CEP + dias úteis</text>
  <text x="183" y="253" font-family="Calibri, Arial" font-size="9" fill="#000000">reais (BrasilAPI/ViaCEP).</text>
  <text x="177" y="271" font-family="Calibri, Arial" font-size="9" fill="#000000">• Cupons + desconto Pix.</text>
  <text x="177" y="289" font-family="Calibri, Arial" font-size="9" fill="#000000">• Reservas de 30 min e</text>
  <text x="183" y="302" font-family="Calibri, Arial" font-size="9" fill="#000000">baixa atômica no ledger.</text>
  <text x="177" y="320" font-family="Calibri, Arial" font-size="9" fill="#000000">• RBAC com 4 papéis e</text>
  <text x="183" y="333" font-family="Calibri, Arial" font-size="9" fill="#000000">guards separados.</text>
  <text x="177" y="351" font-family="Calibri, Arial" font-size="9" fill="#000000">• Validação CPF (Mód. 11)</text>
  <text x="183" y="364" font-family="Calibri, Arial" font-size="9" fill="#000000">e OTP de 6 dígitos (2FA).</text>
  <text x="177" y="382" font-family="Calibri, Arial" font-size="9" fill="#000000">• Valores em centavos</text>
  <text x="183" y="395" font-family="Calibri, Arial" font-size="9" fill="#000000">com snapshot imutável.</text>

  <!-- Col 3 -->
  <rect x="332" y="46" width="156" height="184" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="339" y="62" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">STAKEHOLDERS</text>
  <text x="339" y="81" font-family="Calibri, Arial" font-size="9" fill="#000000">• Proprietária da Vevey</text>
  <text x="345" y="94" font-family="Calibri, Arial" font-size="9" fill="#000000">Doces (Miriam).</text>
  <text x="339" y="112" font-family="Calibri, Arial" font-size="9" fill="#000000">• Gerência de Vendas.</text>
  <text x="339" y="130" font-family="Calibri, Arial" font-size="9" fill="#000000">• Equipe de Atendimento</text>
  <text x="345" y="143" font-family="Calibri, Arial" font-size="9" fill="#000000">e Logística / Estoque.</text>
  <text x="339" y="161" font-family="Calibri, Arial" font-size="9" fill="#000000">• Clientes finais e</text>
  <text x="345" y="174" font-family="Calibri, Arial" font-size="9" fill="#000000">organizadores de festas.</text>
  <text x="339" y="192" font-family="Calibri, Arial" font-size="9" fill="#000000">• Professor orientador</text>
  <text x="345" y="205" font-family="Calibri, Arial" font-size="9" fill="#000000">(FATEC — LabEngSW).</text>

  <rect x="332" y="236" width="156" height="186" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="339" y="252" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">EQUIPE (VEVEY CO.)</text>
  <text x="339" y="274" font-family="Calibri, Arial" font-size="9" font-weight="bold" fill="#000000">• Matheus da Silva Araújo</text>
  <text x="345" y="287" font-family="Calibri, Arial" font-size="8.6" fill="#333333">Requisitos, testes e manual</text>
  <text x="339" y="309" font-family="Calibri, Arial" font-size="9" font-weight="bold" fill="#000000">• Nathan Costa Santos</text>
  <text x="345" y="322" font-family="Calibri, Arial" font-size="8.6" fill="#333333">Arquitetura, código e deploy</text>
  <text x="339" y="344" font-family="Calibri, Arial" font-size="9" font-weight="bold" fill="#000000">• Ricardo Martins Pighin</text>
  <text x="345" y="357" font-family="Calibri, Arial" font-size="8.6" fill="#333333">Modelagem, RBAC e revisão</text>
  <text x="339" y="384" font-family="Calibri, Arial" font-size="8.8" fill="#000000">Repositório: GitHub</text>
  <text x="339" y="397" font-family="Calibri, Arial" font-size="8.8" fill="#000000">nathocosta/vevey</text>

  <!-- Col 4 -->
  <rect x="494" y="46" width="156" height="122" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="501" y="62" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">PREMISSAS</text>
  <text x="501" y="79" font-family="Calibri, Arial" font-size="9" fill="#000000">• PHP 8.4 + Laravel 13 +</text>
  <text x="507" y="92" font-family="Calibri, Arial" font-size="9" fill="#000000">Filament 5 + PostgreSQL.</text>
  <text x="501" y="109" font-family="Calibri, Arial" font-size="9" fill="#000000">• Monólito modular em 7</text>
  <text x="507" y="122" font-family="Calibri, Arial" font-size="9" fill="#000000">pacotes (ADR 8).</text>
  <text x="501" y="139" font-family="Calibri, Arial" font-size="9" fill="#000000">• Pagamento Sandbox na</text>
  <text x="507" y="152" font-family="Calibri, Arial" font-size="9" fill="#000000">fase acadêmica.</text>

  <rect x="494" y="174" width="156" height="122" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="501" y="190" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">GRUPO DE ENTREGAS</text>
  <text x="501" y="207" font-family="Calibri, Arial" font-size="9" fill="#000000">1. Visão, Escopo e Requisitos</text>
  <text x="501" y="223" font-family="Calibri, Arial" font-size="9" fill="#000000">2. Modelagem (UML + DER)</text>
  <text x="501" y="239" font-family="Calibri, Arial" font-size="9" fill="#000000">3. Loja + Checkout + 2FA</text>
  <text x="501" y="255" font-family="Calibri, Arial" font-size="9" fill="#000000">4. Admin + PDV + Ledger</text>
  <text x="501" y="271" font-family="Calibri, Arial" font-size="9" fill="#000000">5. Docker/Render + Doc N2</text>

  <rect x="494" y="302" width="156" height="120" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="501" y="318" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">RESTRIÇÕES</text>
  <text x="501" y="335" font-family="Calibri, Arial" font-size="9" fill="#000000">• Prazo fixo: N2 nov/2026.</text>
  <text x="501" y="351" font-family="Calibri, Arial" font-size="9" fill="#000000">• Orçamento zero (software</text>
  <text x="507" y="364" font-family="Calibri, Arial" font-size="9" fill="#000000">livre e APIs gratuitas).</text>
  <text x="501" y="381" font-family="Calibri, Arial" font-size="9" fill="#000000">• Conformidade LGPD no</text>
  <text x="507" y="394" font-family="Calibri, Arial" font-size="9" fill="#000000">tratamento de CPF/dados.</text>

  <!-- Col 5 -->
  <rect x="656" y="46" width="156" height="122" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="663" y="62" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">RISCOS PRINCIPAIS</text>
  <text x="663" y="79" font-family="Calibri, Arial" font-size="9" fill="#000000">• Divergência de estoque</text>
  <text x="669" y="92" font-family="Calibri, Arial" font-size="9" fill="#000000">entre loja física e site.</text>
  <text x="663" y="109" font-family="Calibri, Arial" font-size="9" fill="#000000">• Erro de arredondamento</text>
  <text x="669" y="122" font-family="Calibri, Arial" font-size="9" fill="#000000">financeiro em descontos.</text>
  <text x="663" y="139" font-family="Calibri, Arial" font-size="9" fill="#000000">• Indisponibilidade de</text>
  <text x="669" y="152" font-family="Calibri, Arial" font-size="9" fill="#000000">APIs externas (CEP/e-mail).</text>

  <rect x="656" y="174" width="156" height="122" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="663" y="190" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">LINHA DO TEMPO</text>
  <text x="663" y="207" font-family="Calibri, Arial" font-size="9" fill="#000000">• Ago/26: Visão e Escopo</text>
  <text x="663" y="223" font-family="Calibri, Arial" font-size="9" fill="#000000">• Set/26: Requisitos e UCs</text>
  <text x="663" y="239" font-family="Calibri, Arial" font-size="9" fill="#000000">• Out/26: Modelagem/ADRs</text>
  <text x="663" y="255" font-family="Calibri, Arial" font-size="9" fill="#000000">• Nov/26: Implementação</text>
  <text x="663" y="271" font-family="Calibri, Arial" font-size="9" fill="#000000">• 25–30/11: Entrega N2</text>

  <rect x="656" y="302" width="156" height="120" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <text x="663" y="318" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">CUSTOS</text>
  <text x="663" y="335" font-family="Calibri, Arial" font-size="9" fill="#000000">• Ferramentas open-source:</text>
  <text x="669" y="348" font-family="Calibri, Arial" font-size="9" fill="#000000">R$ 0,00 (Laravel/Postgres).</text>
  <text x="663" y="365" font-family="Calibri, Arial" font-size="9" fill="#000000">• Hospedagem (Render Free</text>
  <text x="669" y="378" font-family="Calibri, Arial" font-size="9" fill="#000000">+ APIs públicas): R$ 0,00.</text>
  <text x="663" y="395" font-family="Calibri, Arial" font-size="9" fill="#000000">• Esforço equipe: ~82h.</text>
</svg>
"""

SVG_USE_CASES = r"""
<svg viewBox="0 0 820 390" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="openArr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto">
      <path d="M 1 1 L 9 5 L 1 9" fill="none" stroke="#000000" stroke-width="1.4"/>
    </marker>
  </defs>
  <rect x="180" y="14" width="460" height="362" fill="#FFFFFF" stroke="#000000" stroke-width="1.4"/>
  <polygon points="180,14 235,14 235,30 225,36 180,36" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
  <text x="204" y="29" text-anchor="middle" font-family="Calibri, Arial" font-size="10.5" font-weight="bold">uc</text>
  <text x="415" y="33" text-anchor="middle" font-family="Calibri, Arial" font-size="12.5" font-weight="bold">Sistema E-commerce &amp; Gestão Vevey Doces</text>

  <!-- Atores Esquerda -->
  <g stroke="#000000" stroke-width="1.5" fill="none">
    <circle cx="68" cy="95" r="13"/>
    <line x1="68" y1="108" x2="68" y2="144"/>
    <line x1="48" y1="120" x2="88" y2="120"/>
    <line x1="68" y1="144" x2="52" y2="168"/>
    <line x1="68" y1="144" x2="84" y2="168"/>

    <circle cx="68" cy="245" r="13"/>
    <line x1="68" y1="258" x2="68" y2="294"/>
    <line x1="48" y1="270" x2="88" y2="270"/>
    <line x1="68" y1="294" x2="52" y2="318"/>
    <line x1="68" y1="294" x2="84" y2="318"/>
  </g>
  <text x="68" y="184" text-anchor="middle" font-family="Calibri, Arial" font-size="11" font-weight="bold">Cliente (AT01)</text>
  <text x="68" y="334" text-anchor="middle" font-family="Calibri, Arial" font-size="11" font-weight="bold">Atendente (AT02)</text>

  <!-- Atores Direita -->
  <g stroke="#000000" stroke-width="1.5" fill="none">
    <circle cx="748" cy="115" r="13"/>
    <line x1="748" y1="128" x2="748" y2="164"/>
    <line x1="728" y1="140" x2="768" y2="140"/>
    <line x1="748" y1="164" x2="732" y2="188"/>
    <line x1="748" y1="164" x2="764" y2="188"/>

    <circle cx="748" cy="255" r="13"/>
    <line x1="748" y1="268" x2="748" y2="304"/>
    <line x1="728" y1="280" x2="768" y2="280"/>
    <line x1="748" y1="304" x2="732" y2="328"/>
    <line x1="748" y1="304" x2="764" y2="328"/>
  </g>
  <text x="748" y="204" text-anchor="middle" font-family="Calibri, Arial" font-size="11" font-weight="bold">Administrador (AT04)</text>
  <text x="748" y="344" text-anchor="middle" font-family="Calibri, Arial" font-size="11" font-weight="bold">Logística (AT03)</text>

  <!-- Casos de Uso (elipses padrão UML branco/amarelo suave) -->
  <g font-family="Calibri, Arial" font-size="10.2" fill="#000000" text-anchor="middle">
    <ellipse cx="315" cy="78" rx="112" ry="21" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <text x="315" y="82">UC01: Explorar Catálogo e Buscar</text>

    <ellipse cx="315" cy="144" rx="112" ry="21" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <text x="315" y="148">UC02: Realizar Compra (Checkout)</text>

    <ellipse cx="315" cy="210" rx="112" ry="21" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <text x="315" y="214">UC03: Cadastrar Conta (2FA)</text>

    <ellipse cx="315" cy="280" rx="112" ry="21" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <text x="315" y="284">UC04: Registrar Venda PDV</text>

    <ellipse cx="525" cy="108" rx="102" ry="21" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <text x="525" y="112">UC05: Gerenciar Catálogo/Cupons</text>

    <ellipse cx="525" cy="182" rx="102" ry="21" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <text x="525" y="186">UC06: Consultar Dashboard/Logs</text>

    <ellipse cx="525" cy="256" rx="102" ry="21" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <text x="525" y="260">UC07: Ajustar Estoque (Ledger)</text>

    <ellipse cx="525" cy="330" rx="102" ry="21" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <text x="525" y="334">UC08: Separar e Entregar Pedido</text>
  </g>

  <!-- Associações -->
  <g stroke="#000000" stroke-width="1.1">
    <line x1="94" y1="120" x2="203" y2="78"/>
    <line x1="94" y1="128" x2="203" y2="144"/>
    <line x1="94" y1="136" x2="203" y2="210"/>
    <line x1="94" y1="274" x2="203" y2="280"/>
    <line x1="94" y1="286" x2="423" y2="330"/>

    <line x1="722" y1="140" x2="627" y2="108"/>
    <line x1="722" y1="150" x2="627" y2="182"/>
    <line x1="722" y1="160" x2="627" y2="256"/>
    <line x1="722" y1="282" x2="627" y2="256"/>
    <line x1="722" y1="292" x2="627" y2="330"/>
  </g>

  <!-- Relacionamentos <<include>> / <<extend>> -->
  <line x1="315" y1="123" x2="315" y2="101" stroke="#000000" stroke-width="1" stroke-dasharray="4 3" marker-end="url(#openArr)"/>
  <text x="348" y="114" font-family="Calibri, Arial" font-size="8.5">«include»</text>
  <line x1="315" y1="189" x2="315" y2="167" stroke="#000000" stroke-width="1" stroke-dasharray="4 3" marker-end="url(#openArr)"/>
  <text x="348" y="180" font-family="Calibri, Arial" font-size="8.5">«extend»</text>
</svg>
"""

SVG_NAVIGATION = r"""
<svg viewBox="0 0 820 400" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="arrNav" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 1 1 L 9 5 L 1 9" fill="none" stroke="#000000" stroke-width="1.5"/>
    </marker>
  </defs>

  <!-- Nó inicial UML -->
  <circle cx="38" cy="196" r="9" fill="#000000"/>
  <line x1="47" y1="196" x2="90" y2="196" stroke="#000000" stroke-width="1.2" marker-end="url(#arrNav)"/>

  <!-- Decisão inicial -->
  <polygon points="112,178 134,196 112,214 90,196" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>

  <!-- Ramo Loja Pública -->
  <rect x="158" y="14" width="648" height="196" fill="#FFFFFF" stroke="#666666" stroke-dasharray="4 3" stroke-width="1"/>
  <text x="170" y="31" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">LOJA PÚBLICA (panel-site)</text>

  <path d="M 112 178 L 112 74 L 172 74" fill="none" stroke="#000000" stroke-width="1.2" marker-end="url(#arrNav)"/>
  <text x="106" y="68" text-anchor="end" font-family="Calibri, Arial" font-size="8.5">[acessa "/"]</text>

  <g font-family="Calibri, Arial" font-size="9.5" text-anchor="middle" fill="#000000">
    <rect x="174" y="52" width="108" height="44" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="228" y="71" font-weight="bold">Tela 01: Home</text>
    <text x="228" y="85" font-size="8.3">Hero + Busca (/)</text>

    <polygon points="322,58 338,74 322,90 306,74" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>

    <rect x="394" y="42" width="114" height="42" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="451" y="60" font-weight="bold">Tela 02: Catálogo</text>
    <text x="451" y="73" font-size="8.3">Filtros ADR 6</text>

    <rect x="556" y="42" width="106" height="42" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="609" y="60" font-weight="bold">Tela 03: Produto</text>
    <text x="609" y="73" font-size="8.3">PDP + Cores</text>

    <rect x="688" y="42" width="104" height="44" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="740" y="60" font-weight="bold">Tela 05: Checkout</text>
    <text x="740" y="74" font-size="8.3">CEP + Cupom + Pix</text>

    <rect x="394" y="138" width="136" height="46" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="462" y="157" font-weight="bold">Tela 04: Cadastro / 2FA</text>
    <text x="462" y="171" font-size="8.3">/cadastro → /verificar-conta</text>

    <rect x="598" y="138" width="118" height="46" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="657" y="157" font-weight="bold">Tela 06: Minha Conta</text>
    <text x="657" y="171" font-size="8.3">Pedidos + Endereços</text>
  </g>

  <g stroke="#000000" stroke-width="1.2" fill="none" marker-end="url(#arrNav)">
    <line x1="282" y1="74" x2="305" y2="74"/>
    <path d="M 338 66 L 392 63"/>
    <path d="M 322 90 L 322 161 L 392 161"/>
    <line x1="508" y1="63" x2="554" y2="63"/>
    <line x1="662" y1="63" x2="686" y2="63"/>
    <line x1="530" y1="161" x2="596" y2="161"/>
    <path d="M 740 86 L 740 161 L 718 161"/>
  </g>
  <g font-family="Calibri, Arial" font-size="8.2" fill="#000000">
    <text x="366" y="55" text-anchor="middle">[Catálogo]</text>
    <text x="357" y="153" text-anchor="middle">[Minha conta]</text>
    <text x="531" y="56" text-anchor="middle">clica item</text>
    <text x="674" y="56" text-anchor="middle">pagar</text>
    <text x="563" y="154" text-anchor="middle">valida OTP</text>
    <text x="771" y="128" text-anchor="middle">acompanhar</text>
  </g>

  <!-- Ramo Painel Admin -->
  <rect x="158" y="224" width="648" height="162" fill="#FFFFFF" stroke="#666666" stroke-dasharray="4 3" stroke-width="1"/>
  <text x="170" y="241" font-family="Calibri, Arial" font-size="10" font-weight="bold" fill="#000000">PAINEL ADMINISTRATIVO FILAMENT (/admin · RBAC)</text>

  <path d="M 112 214 L 112 300 L 172 300" fill="none" stroke="#000000" stroke-width="1.2" marker-end="url(#arrNav)"/>
  <text x="106" y="304" text-anchor="end" font-family="Calibri, Arial" font-size="8.5">[acessa "/admin"]</text>

  <g font-family="Calibri, Arial" font-size="9.5" text-anchor="middle" fill="#000000">
    <rect x="174" y="278" width="124" height="44" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="236" y="297" font-weight="bold">Tela 07: Dashboard</text>
    <text x="236" y="311" font-size="8.3">Login RBAC → KPIs</text>

    <polygon points="342,284 358,300 342,316 326,300" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>

    <rect x="430" y="254" width="128" height="38" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="494" y="271" font-weight="bold">Tela 08: PDV Balcão</text>
    <text x="494" y="284" font-size="8.2">Venda pos (/admin/pdv)</text>

    <rect x="430" y="302" width="128" height="38" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="494" y="319" font-weight="bold">Estoque &amp; Pedidos</text>
    <text x="494" y="332" font-size="8.2">Ledger · Entregas</text>

    <rect x="614" y="278" width="118" height="44" rx="8" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <text x="673" y="297" font-weight="bold">Auditoria (Logs)</text>
    <text x="673" y="311" font-size="8.2">/admin/audit-log</text>
  </g>

  <g stroke="#000000" stroke-width="1.2" fill="none" marker-end="url(#arrNav)">
    <line x1="298" y1="300" x2="325" y2="300"/>
    <path d="M 358 292 L 392 273 L 428 273"/>
    <path d="M 358 308 L 392 321 L 428 321"/>
    <path d="M 558 273 L 586 273 L 586 294 L 612 294"/>
    <path d="M 558 321 L 586 321 L 586 306 L 612 306"/>
    <line x1="732" y1="300" x2="762" y2="300"/>
  </g>
  <g font-family="Calibri, Arial" font-size="8.2" fill="#000000">
    <text x="394" y="266" text-anchor="middle">[escolhe "PDV"]</text>
    <text x="392" y="336" text-anchor="middle">[escolhe "Estoque"]</text>
    <text x="586" y="266" text-anchor="middle">registra</text>
  </g>

  <circle cx="776" cy="300" r="10" fill="#FFFFFF" stroke="#000000" stroke-width="1.4"/>
  <circle cx="776" cy="300" r="6" fill="#000000"/>
</svg>
"""

SVG_PACKAGES = r"""
<svg viewBox="0 0 820 330" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="pkgArr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto">
      <path d="M 1 1 L 9 5 L 1 9" fill="none" stroke="#000000" stroke-width="1.4"/>
    </marker>
  </defs>

  <!-- Pacote PanelAdmin -->
  <rect x="90" y="14" width="120" height="18" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
  <text x="98" y="27" font-family="Calibri, Arial" font-size="9.5" font-weight="bold">Vevey\PanelAdmin</text>
  <rect x="90" y="32" width="260" height="54" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
  <text x="220" y="53" text-anchor="middle" font-family="Calibri, Arial" font-size="9.5">«boundary» Filament Resources &amp; Widgets</text>
  <text x="220" y="70" text-anchor="middle" font-family="Calibri, Arial" font-size="9">PosSale · AuditLog · OrderResource · StockLevelResource</text>

  <!-- Pacote PanelSite -->
  <rect x="470" y="14" width="115" height="18" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
  <text x="478" y="27" font-family="Calibri, Arial" font-size="9.5" font-weight="bold">Vevey\PanelSite</text>
  <rect x="470" y="32" width="260" height="54" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
  <text x="600" y="53" text-anchor="middle" font-family="Calibri, Arial" font-size="9.5">«boundary» Storefront Controllers &amp; Blade</text>
  <text x="600" y="70" text-anchor="middle" font-family="Calibri, Arial" font-size="9">Storefront · Checkout · CustomerAuth · CustomerAccount</text>

  <!-- Pacote Orders -->
  <rect x="260" y="114" width="105" height="18" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
  <text x="268" y="127" font-family="Calibri, Arial" font-size="9.5" font-weight="bold">Vevey\Orders</text>
  <rect x="260" y="132" width="300" height="50" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
  <text x="410" y="152" text-anchor="middle" font-family="Calibri, Arial" font-size="9.5">«control / entity» Orquestrador Multicanal</text>
  <text x="410" y="168" text-anchor="middle" font-family="Calibri, Arial" font-size="9">Order · OrderItem · Coupon · Payment · PlaceOrder</text>

  <!-- Base: Catalog, Inventory, Customers -->
  <rect x="24" y="208" width="100" height="18" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
  <text x="32" y="221" font-family="Calibri, Arial" font-size="9.5" font-weight="bold">Vevey\Catalog</text>
  <rect x="24" y="226" width="234" height="44" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
  <text x="141" y="246" text-anchor="middle" font-family="Calibri, Arial" font-size="9">Product · Category · Attribute</text>
  <text x="141" y="260" text-anchor="middle" font-family="Calibri, Arial" font-size="8.6">FindProductsForSale · ProductData</text>

  <rect x="293" y="208" width="110" height="18" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
  <text x="301" y="221" font-family="Calibri, Arial" font-size="9.5" font-weight="bold">Vevey\Inventory</text>
  <rect x="293" y="226" width="234" height="44" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
  <text x="410" y="246" text-anchor="middle" font-family="Calibri, Arial" font-size="9">StockLevel · StockMovement · Reservation</text>
  <text x="410" y="260" text-anchor="middle" font-family="Calibri, Arial" font-size="8.6">ReserveStock · RecordDirectSale · AdjustStock</text>

  <rect x="562" y="208" width="118" height="18" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
  <text x="570" y="221" font-family="Calibri, Arial" font-size="9.5" font-weight="bold">Vevey\Customers</text>
  <rect x="562" y="226" width="234" height="44" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
  <text x="679" y="246" text-anchor="middle" font-family="Calibri, Arial" font-size="9">Customer · Identity · Address</text>
  <text x="679" y="260" text-anchor="middle" font-family="Calibri, Arial" font-size="8.6">FindOrCreateCustomer · RetireIdentity</text>

  <!-- Compartilhado -->
  <rect x="24" y="290" width="772" height="30" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
  <text x="410" y="309" text-anchor="middle" font-family="Calibri, Arial" font-size="9.5" font-weight="bold">Camada Compartilhada: Vevey\Permissions (RBAC)  +  App\ (User, MoneyCast, BrasilApiService, TransactionalMailer)</text>

  <!-- Setas tracejadas <<use>> -->
  <g stroke="#000000" stroke-width="1.1" stroke-dasharray="4 3" fill="none" marker-end="url(#pkgArr)">
    <path d="M 220 86 L 220 100 L 360 100 L 360 130"/>
    <path d="M 600 86 L 600 100 L 460 100 L 460 130"/>
    <path d="M 330 182 L 330 195 L 141 195 L 141 224"/>
    <line x1="410" y1="182" x2="410" y2="224"/>
    <path d="M 490 182 L 490 195 L 679 195 L 679 224"/>
    <line x1="410" y1="270" x2="410" y2="288"/>
  </g>
  <g font-family="Calibri, Arial" font-size="8.3" fill="#000000">
    <text x="366" y="116">«use»</text>
    <text x="416" y="204">«use»</text>
    <text x="416" y="284">«use»</text>
  </g>
</svg>
"""

SVG_CLASSES = r"""
<svg viewBox="0 0 820 340" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <g font-family="Calibri, Arial" font-size="9.6" fill="#000000">
    <!-- Product -->
    <rect x="16" y="16" width="180" height="132" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <line x1="16" y1="40" x2="196" y2="40" stroke="#000000" stroke-width="1"/>
    <line x1="16" y1="118" x2="196" y2="118" stroke="#000000" stroke-width="1"/>
    <text x="106" y="32" text-anchor="middle" font-weight="bold">Product</text>
    <text x="23" y="54">+ id: uuid</text>
    <text x="23" y="68">+ sku, barcode: string</text>
    <text x="23" y="82">+ name, slug: string</text>
    <text x="23" y="96">+ price_cents: Money</text>
    <text x="23" y="110">+ promo_price_cents: ?Money</text>
    <text x="23" y="134">+ scopeSearch(term), active()</text>

    <!-- Order -->
    <rect x="224" y="16" width="195" height="148" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <line x1="224" y1="40" x2="419" y2="40" stroke="#000000" stroke-width="1"/>
    <line x1="224" y1="134" x2="419" y2="134" stroke="#000000" stroke-width="1"/>
    <text x="321" y="32" text-anchor="middle" font-weight="bold">Order</text>
    <text x="231" y="54">+ id: uuid · number: string</text>
    <text x="231" y="68">+ channel: OrderChannel</text>
    <text x="231" y="82">+ payment_status: PaymentStatus</text>
    <text x="231" y="96">+ fulfillment_status: Fulfillment</text>
    <text x="231" y="110">+ subtotal/discount/total: Money</text>
    <text x="231" y="124">+ customer_id, seller_id: ?uuid</text>
    <text x="231" y="150">+ items(), payments(), histories()</text>

    <!-- OrderItem -->
    <rect x="448" y="16" width="175" height="132" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <line x1="448" y1="40" x2="623" y2="40" stroke="#000000" stroke-width="1"/>
    <line x1="448" y1="120" x2="623" y2="120" stroke="#000000" stroke-width="1"/>
    <text x="535" y="32" text-anchor="middle" font-weight="bold">OrderItem</text>
    <text x="455" y="54">+ order_id, product_id: uuid</text>
    <text x="455" y="68">+ sku, name: string</text>
    <text x="455" y="82">+ unit_price_cents: Money</text>
    <text x="455" y="96">+ quantity: int</text>
    <text x="455" y="110">+ product_snapshot: jsonb</text>
    <text x="455" y="135">+ lineTotal(): Money</text>

    <!-- Customer -->
    <rect x="646" y="16" width="160" height="132" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <line x1="646" y1="40" x2="806" y2="40" stroke="#000000" stroke-width="1"/>
    <line x1="646" y1="120" x2="806" y2="120" stroke="#000000" stroke-width="1"/>
    <text x="726" y="32" text-anchor="middle" font-weight="bold">Customer</text>
    <text x="653" y="54">+ id: uuid</text>
    <text x="653" y="68">+ name, email, cpf</text>
    <text x="653" y="82">+ password: ?string</text>
    <text x="653" y="96">+ verification_code</text>
    <text x="653" y="110">+ email_verified_at</text>
    <text x="653" y="135">+ identities(), addresses()</text>

    <!-- StockLevel -->
    <rect x="16" y="194" width="180" height="126" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <line x1="16" y1="218" x2="196" y2="218" stroke="#000000" stroke-width="1"/>
    <line x1="16" y1="296" x2="196" y2="296" stroke="#000000" stroke-width="1"/>
    <text x="106" y="210" text-anchor="middle" font-weight="bold">StockLevel</text>
    <text x="23" y="232">+ product_id: uuid (PK)</text>
    <text x="23" y="246">+ on_hand: int (signed)</text>
    <text x="23" y="260">+ reserved: int (&gt;= 0)</text>
    <text x="23" y="274">+ available: generated</text>
    <text x="23" y="288">+ reorder_point: int</text>
    <text x="23" y="311">+ isLowStock(): bool</text>

    <!-- StockMovement -->
    <rect x="224" y="194" width="195" height="126" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <line x1="224" y1="218" x2="419" y2="218" stroke="#000000" stroke-width="1"/>
    <line x1="224" y1="296" x2="419" y2="296" stroke="#000000" stroke-width="1"/>
    <text x="321" y="210" text-anchor="middle" font-weight="bold">StockMovement</text>
    <text x="231" y="232">+ id, product_id: uuid</text>
    <text x="231" y="246">+ qty: int (!= 0)</text>
    <text x="231" y="260">+ direction: stored (in/out)</text>
    <text x="231" y="274">+ reason: MovementReason</text>
    <text x="231" y="288">+ balance_after, user_id</text>
    <text x="231" y="311">+ record(dto): self</text>

    <!-- Payment -->
    <rect x="448" y="194" width="175" height="126" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <line x1="448" y1="218" x2="623" y2="218" stroke="#000000" stroke-width="1"/>
    <line x1="448" y1="296" x2="623" y2="296" stroke="#000000" stroke-width="1"/>
    <text x="535" y="210" text-anchor="middle" font-weight="bold">Payment</text>
    <text x="455" y="232">+ id, order_id: uuid</text>
    <text x="455" y="246">+ method: PaymentMethod</text>
    <text x="455" y="260">+ amount_cents: Money</text>
    <text x="455" y="274">+ status: PaymentStatus</text>
    <text x="455" y="288">+ expires_at, paid_at</text>
    <text x="455" y="311">+ markPaid(): void</text>

    <!-- CustomerIdentity -->
    <rect x="646" y="194" width="160" height="126" fill="#FFFDF2" stroke="#000000" stroke-width="1.2"/>
    <line x1="646" y1="218" x2="806" y2="218" stroke="#000000" stroke-width="1"/>
    <line x1="646" y1="296" x2="806" y2="296" stroke="#000000" stroke-width="1"/>
    <text x="726" y="210" text-anchor="middle" font-weight="bold">CustomerIdentity</text>
    <text x="653" y="232">+ id, customer_id: uuid</text>
    <text x="653" y="246">+ type: IdentityType</text>
    <text x="653" y="260">+ value: E.164 / email</text>
    <text x="653" y="274">+ is_primary: bool</text>
    <text x="653" y="288">+ retired_at: ?timestamp</text>
    <text x="653" y="311">+ retire(): void</text>
  </g>

  <g stroke="#000000" stroke-width="1.1">
    <line x1="106" y1="148" x2="106" y2="194"/>
    <line x1="196" y1="80" x2="224" y2="80" stroke-dasharray="4 3"/>
    <line x1="419" y1="80" x2="448" y2="80"/>
    <line x1="419" y1="130" x2="448" y2="230"/>
    <line x1="726" y1="148" x2="726" y2="194"/>
    <line x1="196" y1="256" x2="224" y2="256"/>
  </g>
  <g font-family="Calibri, Arial" font-size="8.5" fill="#000000">
    <text x="112" y="174">1 : 1</text>
    <text x="423" y="75">1 : 1..*</text>
    <text x="424" y="180">1 : 1..*</text>
    <text x="732" y="174">1 : 0..*</text>
    <text x="200" y="249">1 : 0..*</text>
  </g>
</svg>
"""

SVG_DER = r"""
<svg viewBox="0 0 820 395" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <g font-family="Calibri, Arial" font-size="9.2" fill="#000000">
    <!-- categories -->
    <rect x="14" y="14" width="154" height="82" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="14" y="14" width="154" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="91" y="28" text-anchor="middle" font-weight="bold">categories</text>
    <text x="21" y="48"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="21" y="62"><tspan font-weight="bold">FK</tspan> parent_id (uuid)</text>
    <text x="21" y="76">name, slug, sort_order</text>
    <text x="21" y="89">N:M ↔ category_product</text>

    <!-- products -->
    <rect x="14" y="128" width="168" height="114" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <rect x="14" y="128" width="168" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.3"/>
    <text x="98" y="142" text-anchor="middle" font-weight="bold">products</text>
    <text x="21" y="162"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="21" y="176">sku (unique), barcode</text>
    <text x="21" y="190">name, slug (unique)</text>
    <text x="21" y="204">price_cents, promo_cents</text>
    <text x="21" y="218">search_vector (tsvector)</text>
    <text x="21" y="232">is_active, deleted_at</text>

    <!-- attributes / values -->
    <rect x="14" y="274" width="168" height="106" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="14" y="274" width="168" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="98" y="288" text-anchor="middle" font-weight="bold">attributes / values</text>
    <text x="21" y="308"><tspan font-weight="bold">PK</tspan> attribute_values.id</text>
    <text x="21" y="322"><tspan font-weight="bold">FK</tspan> attribute_id → attributes</text>
    <text x="21" y="336">value, slug, hex_color</text>
    <text x="21" y="352">Pivô: attribute_value_product</text>
    <text x="21" y="366">(filtros combinados ADR 6)</text>

    <!-- stock_levels -->
    <rect x="218" y="14" width="176" height="96" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="218" y="14" width="176" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="306" y="28" text-anchor="middle" font-weight="bold">stock_levels</text>
    <text x="225" y="48"><tspan font-weight="bold">PK/FK</tspan> product_id (uuid)</text>
    <text x="225" y="62">on_hand (int signed)</text>
    <text x="225" y="76">reserved (int &gt;= 0)</text>
    <text x="225" y="90">available (STORED)</text>
    <text x="225" y="103">reorder_point (int)</text>

    <!-- stock_movements -->
    <rect x="218" y="136" width="176" height="106" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="218" y="136" width="176" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="306" y="150" text-anchor="middle" font-weight="bold">stock_movements</text>
    <text x="225" y="170"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="225" y="184"><tspan font-weight="bold">FK</tspan> product_id, user_id</text>
    <text x="225" y="198">qty (CHECK != 0), direction</text>
    <text x="225" y="212">reason, balance_after</text>
    <text x="225" y="226">reference_type, reference_id</text>

    <!-- order_items -->
    <rect x="218" y="274" width="176" height="106" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="218" y="274" width="176" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="306" y="288" text-anchor="middle" font-weight="bold">order_items</text>
    <text x="225" y="308"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="225" y="322"><tspan font-weight="bold">FK</tspan> order_id, product_id</text>
    <text x="225" y="336">sku, name, quantity</text>
    <text x="225" y="350">unit_price_cents, discount</text>
    <text x="225" y="364">product_snapshot (jsonb)</text>

    <!-- orders -->
    <rect x="432" y="128" width="178" height="132" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
    <rect x="432" y="128" width="178" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.3"/>
    <text x="521" y="142" text-anchor="middle" font-weight="bold">orders</text>
    <text x="439" y="162"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="439" y="176"><tspan font-weight="bold">FK</tspan> customer_id, seller_id</text>
    <text x="439" y="190"><tspan font-weight="bold">FK</tspan> coupon_id (nullable)</text>
    <text x="439" y="204">number (unique), channel</text>
    <text x="439" y="218">payment_status, fulfillment</text>
    <text x="439" y="232">subtotal, discount, shipping</text>
    <text x="439" y="246">total_cents (CHECK exato)</text>

    <!-- payments & adjustments -->
    <rect x="432" y="14" width="178" height="96" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="432" y="14" width="178" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="521" y="28" text-anchor="middle" font-weight="bold">payments / adjustments</text>
    <text x="439" y="48"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="439" y="62"><tspan font-weight="bold">FK</tspan> order_id (uuid)</text>
    <text x="439" y="76">method, amount_cents, status</text>
    <text x="439" y="90">order_status_histories (1:N)</text>
    <text x="439" y="103">stock_reservations (30m)</text>

    <!-- coupons -->
    <rect x="432" y="286" width="178" height="94" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="432" y="286" width="178" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="521" y="300" text-anchor="middle" font-weight="bold">coupons</text>
    <text x="439" y="320"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="439" y="334">code (unique), type, value</text>
    <text x="439" y="348">min_order_cents, max_uses</text>
    <text x="439" y="362">used_count, starts/ends_at</text>

    <!-- customers -->
    <rect x="646" y="128" width="160" height="114" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="646" y="128" width="160" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="726" y="142" text-anchor="middle" font-weight="bold">customers</text>
    <text x="653" y="162"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="653" y="176">name, email (unique)</text>
    <text x="653" y="190">cpf, password</text>
    <text x="653" y="204">verification_code (2FA)</text>
    <text x="653" y="218">email_verified_at</text>
    <text x="653" y="232">deleted_at</text>

    <!-- customer_identities -->
    <rect x="646" y="14" width="160" height="96" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="646" y="14" width="160" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="726" y="28" text-anchor="middle" font-weight="bold">customer_identities</text>
    <text x="653" y="48"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="653" y="62"><tspan font-weight="bold">FK</tspan> customer_id (uuid)</text>
    <text x="653" y="76">type, value (E.164/email)</text>
    <text x="653" y="90">is_primary, retired_at</text>

    <!-- customer_addresses -->
    <rect x="646" y="274" width="160" height="106" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <rect x="646" y="274" width="160" height="20" fill="#E5E7EB" stroke="#000000" stroke-width="1.2"/>
    <text x="726" y="288" text-anchor="middle" font-weight="bold">customer_addresses</text>
    <text x="653" y="308"><tspan font-weight="bold">PK</tspan> id (uuid)</text>
    <text x="653" y="322"><tspan font-weight="bold">FK</tspan> customer_id (uuid)</text>
    <text x="653" y="336">label, cep, street, number</text>
    <text x="653" y="350">neighborhood, city, state</text>
    <text x="653" y="364">is_default (bool)</text>
  </g>

  <g stroke="#000000" stroke-width="1.1" fill="none">
    <line x1="91" y1="96" x2="91" y2="128"/>
    <line x1="91" y1="242" x2="91" y2="274"/>
    <path d="M 182 156 L 200 156 L 200 62 L 218 62"/>
    <line x1="182" y1="185" x2="218" y2="185"/>
    <path d="M 182 218 L 200 218 L 200 326 L 218 326"/>
    <line x1="394" y1="326" x2="412" y2="326"/>
    <path d="M 412 326 L 412 206 L 432 206"/>
    <line x1="521" y1="110" x2="521" y2="128"/>
    <line x1="521" y1="260" x2="521" y2="286"/>
    <line x1="610" y1="185" x2="646" y2="185"/>
    <line x1="726" y1="110" x2="726" y2="128"/>
    <line x1="726" y1="242" x2="726" y2="274"/>
  </g>
  <g font-family="Calibri, Arial" font-size="8.2" font-weight="bold" fill="#000000">
    <text x="96" y="116">N:M</text>
    <text x="96" y="262">N:M</text>
    <text x="186" y="56">1:1</text>
    <text x="187" y="180">1:N</text>
    <text x="186" y="320">1:N</text>
    <text x="415" y="198">1:N</text>
    <text x="526" y="122">1:N</text>
    <text x="526" y="278">0..1:N</text>
    <text x="614" y="179">0..1:N</text>
    <text x="731" y="122">1:N</text>
    <text x="731" y="262">1:N</text>
  </g>
</svg>
"""

SVG_SEQ_CHECKOUT = r"""
<svg viewBox="0 0 820 320" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="seqSolid" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#000000"/>
    </marker>
    <marker id="seqOpen" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 1 1 L 9 5 L 1 9" fill="none" stroke="#000000" stroke-width="1.5"/>
    </marker>
  </defs>
  <g font-family="Calibri, Arial" font-size="10" font-weight="bold" text-anchor="middle" fill="#000000">
    <rect x="20" y="14" width="110" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="75" y="33">umCliente : Cliente</text>
    <line x1="75" y1="44" x2="75" y2="306" stroke="#666666" stroke-dasharray="4 3"/>

    <rect x="172" y="14" width="130" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="237" y="33">: CheckoutController</text>
    <line x1="237" y1="44" x2="237" y2="306" stroke="#666666" stroke-dasharray="4 3"/>

    <rect x="342" y="14" width="130" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="407" y="33">: PlaceOrder</text>
    <line x1="407" y1="44" x2="407" y2="306" stroke="#666666" stroke-dasharray="4 3"/>

    <rect x="512" y="14" width="130" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="577" y="33">: InventoryService</text>
    <line x1="577" y1="44" x2="577" y2="306" stroke="#666666" stroke-dasharray="4 3"/>

    <rect x="680" y="14" width="120" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="740" y="33">: PostgreSQL</text>
    <line x1="740" y1="44" x2="740" y2="306" stroke="#666666" stroke-dasharray="4 3"/>
  </g>

  <rect x="233" y="74" width="8" height="156" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <rect x="403" y="104" width="8" height="96" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <rect x="573" y="164" width="8" height="36" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>

  <g font-family="Calibri, Arial" font-size="9.2" fill="#000000">
    <line x1="75" y1="76" x2="231" y2="76" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid)"/>
    <text x="154" y="70" text-anchor="middle">1: POST /checkout(itens, cep, cupom)</text>

    <line x1="241" y1="106" x2="401" y2="106" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid)"/>
    <text x="321" y="100" text-anchor="middle">2: handle(PlaceOrderData)</text>

    <line x1="411" y1="136" x2="736" y2="136" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid)"/>
    <text x="574" y="130" text-anchor="middle">3: INSERT orders, order_items (snapshot), payments</text>

    <line x1="411" y1="166" x2="571" y2="166" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid)"/>
    <text x="491" y="160" text-anchor="middle">4: reserveStock(lockForUpdate)</text>

    <line x1="581" y1="194" x2="736" y2="194" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid)"/>
    <text x="658" y="188" text-anchor="middle">5: INSERT stock_reservations (30m)</text>

    <line x1="233" y1="224" x2="79" y2="224" stroke="#000000" stroke-width="1.1" stroke-dasharray="4 3" marker-end="url(#seqOpen)"/>
    <text x="156" y="218" text-anchor="middle">6: 201 Created + QR Code Pix</text>

    <line x1="75" y1="256" x2="233" y2="256" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid)"/>
    <text x="154" y="250" text-anchor="middle">7: confirmarPagamento(orderId)</text>

    <line x1="237" y1="286" x2="573" y2="286" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid)"/>
    <text x="405" y="280" text-anchor="middle">8: fulfillOrderReservations() → INSERT stock_movements (sale)</text>
  </g>
</svg>
"""

SVG_SEQ_PDV = r"""
<svg viewBox="0 0 820 260" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="seqSolid2" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#000000"/>
    </marker>
    <marker id="seqOpen2" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 1 1 L 9 5 L 1 9" fill="none" stroke="#000000" stroke-width="1.5"/>
    </marker>
  </defs>
  <g font-family="Calibri, Arial" font-size="10" font-weight="bold" text-anchor="middle" fill="#000000">
    <rect x="20" y="14" width="120" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="80" y="33">atendente : Atendente</text>
    <line x1="80" y1="44" x2="80" y2="246" stroke="#666666" stroke-dasharray="4 3"/>

    <rect x="190" y="14" width="120" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="250" y="33">: PosSale (PDV)</text>
    <line x1="250" y1="44" x2="250" y2="246" stroke="#666666" stroke-dasharray="4 3"/>

    <rect x="360" y="14" width="125" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="422" y="33">: PlaceOrder</text>
    <line x1="422" y1="44" x2="422" y2="246" stroke="#666666" stroke-dasharray="4 3"/>

    <rect x="530" y="14" width="130" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="595" y="33">: RecordDirectSale</text>
    <line x1="595" y1="44" x2="595" y2="246" stroke="#666666" stroke-dasharray="4 3"/>

    <rect x="690" y="14" width="110" height="30" fill="#FFFDF2" stroke="#000000"/>
    <text x="745" y="33">: PostgreSQL</text>
    <line x1="745" y1="44" x2="745" y2="246" stroke="#666666" stroke-dasharray="4 3"/>
  </g>

  <rect x="246" y="72" width="8" height="152" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <rect x="418" y="102" width="8" height="92" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
  <rect x="591" y="132" width="8" height="58" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>

  <g font-family="Calibri, Arial" font-size="9.2" fill="#000000">
    <line x1="80" y1="74" x2="244" y2="74" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid2)"/>
    <text x="162" y="68" text-anchor="middle">1: concluirVenda(itens, metodoPagamento)</text>

    <line x1="254" y1="104" x2="416" y2="104" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid2)"/>
    <text x="335" y="98" text-anchor="middle">2: handle(channel=pos, paid, delivered)</text>

    <line x1="426" y1="134" x2="589" y2="134" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid2)"/>
    <text x="507" y="128" text-anchor="middle">3: handle(orderId, itens, sellerId)</text>

    <line x1="599" y1="164" x2="741" y2="164" stroke="#000000" stroke-width="1.1" marker-end="url(#seqSolid2)"/>
    <text x="670" y="158" text-anchor="middle">4: INSERT stock_movements + UPDATE stock_levels</text>

    <line x1="591" y1="188" x2="428" y2="188" stroke="#000000" stroke-width="1.1" stroke-dasharray="4 3" marker-end="url(#seqOpen2)"/>
    <text x="509" y="182" text-anchor="middle">5: alertaSaldoNegativo (se on_hand &lt; 0)</text>

    <line x1="246" y1="220" x2="84" y2="220" stroke="#000000" stroke-width="1.1" stroke-dasharray="4 3" marker-end="url(#seqOpen2)"/>
    <text x="165" y="214" text-anchor="middle">6: reciboPedido(VV-2026-XXXXXX)</text>
  </g>
</svg>
"""

SVG_ARTIFACTS = r"""
<svg viewBox="0 0 820 275" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <defs>
    <marker id="artArr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
      <path d="M 1 1 L 9 5 L 1 9" fill="none" stroke="#000000" stroke-width="1.5"/>
    </marker>
  </defs>

  <rect x="12" y="12" width="796" height="252" fill="#FFFFFF" stroke="#000000" stroke-width="1.3"/>
  <text x="410" y="30" text-anchor="middle" font-family="Calibri, Arial" font-size="10.5" font-weight="bold">«artifact» vevey-app (Repositório Monólito Modular Laravel 13)</text>

  <g font-family="Calibri, Arial" font-size="9.2" fill="#000000">
    <rect x="26" y="44" width="236" height="106" fill="#FFFFFF" stroke="#000000" stroke-width="1.1"/>
    <text x="144" y="60" text-anchor="middle" font-size="8.5">«artifact»</text>
    <text x="144" y="74" text-anchor="middle" font-weight="bold" font-size="10">app-modules/* (7 pacotes PHP)</text>
    <text x="36" y="93">• catalog/ · inventory/ · customers/</text>
    <text x="36" y="108">• orders/ · permissions/</text>
    <text x="36" y="123">• panel-admin/ (Filament 5)</text>
    <text x="36" y="138">• panel-site/ (Controllers Loja)</text>

    <rect x="292" y="44" width="236" height="106" fill="#FFFFFF" stroke="#000000" stroke-width="1.1"/>
    <text x="410" y="60" text-anchor="middle" font-size="8.5">«artifact»</text>
    <text x="410" y="74" text-anchor="middle" font-weight="bold" font-size="10">resources/ + public/build/*</text>
    <text x="302" y="93">• views/storefront/*.blade.php</text>
    <text x="302" y="108">• views/components/brand/logo.blade.php</text>
    <text x="302" y="123">• css/app.css (Tailwind CSS v4)</text>
    <text x="302" y="138">• js/app.js (Bundle Vite 8)</text>

    <rect x="558" y="44" width="236" height="106" fill="#FFFFFF" stroke="#000000" stroke-width="1.1"/>
    <text x="676" y="60" text-anchor="middle" font-size="8.5">«artifact»</text>
    <text x="676" y="74" text-anchor="middle" font-weight="bold" font-size="10">app/ + routes/ + database/</text>
    <text x="568" y="93">• Services/BrasilApiService.php</text>
    <text x="568" y="108">• Services/TransactionalMailer.php</text>
    <text x="568" y="123">• Casts/MoneyCast.php · routes/web.php</text>
    <text x="568" y="138">• migrations/ · StaffUsersSeeder.php</text>

    <rect x="26" y="178" width="236" height="72" fill="#FFFFFF" stroke="#000000" stroke-width="1.1"/>
    <text x="144" y="194" text-anchor="middle" font-size="8.5">«artifact»</text>
    <text x="144" y="208" text-anchor="middle" font-weight="bold" font-size="10">composer.json (vendor/)</text>
    <text x="36" y="224">• laravel/framework 13 · filament 5</text>
    <text x="36" y="238">• internachi/modular · brick/money · spatie</text>

    <rect x="292" y="178" width="236" height="72" fill="#FFFFFF" stroke="#000000" stroke-width="1.1"/>
    <text x="410" y="194" text-anchor="middle" font-size="8.5">«artifact»</text>
    <text x="410" y="208" text-anchor="middle" font-weight="bold" font-size="10">tests/ (Pest Arch &amp; Feature)</text>
    <text x="302" y="224">• Arch/ModuleDependenciesTest.php</text>
    <text x="302" y="238">• Feature: Orders, Inventory, Customers</text>

    <rect x="558" y="178" width="236" height="72" fill="#FFFFFF" stroke="#000000" stroke-width="1.1"/>
    <text x="676" y="194" text-anchor="middle" font-size="8.5">«artifact»</text>
    <text x="676" y="208" text-anchor="middle" font-weight="bold" font-size="10">Dockerfile · render.yaml</text>
    <text x="568" y="224">• docker/start-render.sh (bootstrap)</text>
    <text x="568" y="238">• package.json · vite.config.js</text>
  </g>

  <g stroke="#000000" stroke-width="1.1" stroke-dasharray="4 3" fill="none" marker-end="url(#artArr)">
    <line x1="144" y1="150" x2="144" y2="176"/>
    <line x1="292" y1="97" x2="264" y2="97"/>
    <path d="M 262 138 L 278 138 L 278 164 L 542 164 L 542 118 L 556 118"/>
    <line x1="410" y1="178" x2="410" y2="152"/>
    <line x1="676" y1="178" x2="676" y2="152"/>
  </g>
</svg>
"""

SVG_DEPLOY = r"""
<svg viewBox="0 0 820 235" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:740px;height:auto;background:#ffffff;">
  <g font-family="Calibri, Arial" font-size="9.5" text-anchor="middle" fill="#000000">
    <!-- Cubo 3D Cliente -->
    <polygon points="18,74 30,62 166,62 154,74" fill="#F3F4F6" stroke="#000000" stroke-width="1.1"/>
    <polygon points="154,74 166,62 166,148 154,160" fill="#E5E7EB" stroke="#000000" stroke-width="1.1"/>
    <rect x="18" y="74" width="136" height="86" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <text x="86" y="92" font-size="8.5">«device»</text>
    <text x="86" y="106" font-weight="bold">client: Navegador Web</text>
    <rect x="30" y="116" width="112" height="32" fill="#FFFFFF" stroke="#000000" stroke-width="0.9"/>
    <text x="86" y="130" font-size="8.3">«artifact»</text>
    <text x="86" y="142" font-size="8.5">Loja &amp; Painel /admin</text>

    <!-- Cubo 3D Servidor Docker -->
    <polygon points="214,34 228,20 478,20 464,34" fill="#F3F4F6" stroke="#000000" stroke-width="1.1"/>
    <polygon points="464,34 478,20 478,196 464,210" fill="#E5E7EB" stroke="#000000" stroke-width="1.1"/>
    <rect x="214" y="34" width="250" height="176" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <text x="339" y="51" font-size="8.5">«execution environment»</text>
    <text x="339" y="65" font-weight="bold">server: Container Docker (Render)</text>

    <rect x="230" y="76" width="218" height="52" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
    <text x="339" y="91" font-size="8.3">«artifact»</text>
    <text x="339" y="104" font-weight="bold">Laravel 13 + PHP 8.4</text>
    <text x="339" y="118" font-size="8.5">7 Módulos (internachi/modular)</text>

    <rect x="230" y="140" width="218" height="54" fill="#FFFFFF" stroke="#000000" stroke-width="1"/>
    <text x="339" y="155" font-size="8.3">«artifact»</text>
    <text x="339" y="168" font-weight="bold">Assets Vite + Filament 5</text>
    <text x="339" y="182" font-size="8.5">Tailwind v4 + Scheduler (5 min)</text>

    <!-- Cubo 3D PostgreSQL -->
    <polygon points="528,34 540,22 684,22 672,34" fill="#F3F4F6" stroke="#000000" stroke-width="1.1"/>
    <polygon points="672,34 684,22 684,102 672,114" fill="#E5E7EB" stroke="#000000" stroke-width="1.1"/>
    <rect x="528" y="34" width="144" height="80" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <text x="600" y="52" font-size="8.5">«database node»</text>
    <text x="600" y="66" font-weight="bold">PostgreSQL 16+</text>
    <text x="600" y="84" font-size="8.5">20 Tabelas · UUID v7</text>
    <text x="600" y="98" font-size="8.5">GIN Full-Text · CHECK</text>

    <!-- Nó APIs Externas -->
    <rect x="528" y="136" width="270" height="74" fill="#FFFFFF" stroke="#000000" stroke-width="1.2"/>
    <text x="663" y="154" font-size="8.5">«external services»</text>
    <text x="663" y="168" font-weight="bold">APIs Públicas &amp; Transacionais</text>
    <text x="663" y="184" font-size="8.6">BrasilAPI (CEP v2 + Feriados) · ViaCEP</text>
    <text x="663" y="198" font-size="8.6">Resend API (E-mails 2FA) · Melhor Envio</text>
  </g>

  <!-- Caminhos de comunicação (linhas sólidas com protocolo) -->
  <g stroke="#000000" stroke-width="1.2">
    <line x1="166" y1="112" x2="214" y2="112"/>
    <line x1="478" y1="78" x2="528" y2="78"/>
    <line x1="478" y1="170" x2="528" y2="170"/>
  </g>
  <g font-family="Calibri, Arial" font-size="8.2" fill="#000000" text-anchor="middle">
    <text x="190" y="106">«HTTPS»</text>
    <text x="503" y="72">«TCP/5432»</text>
    <text x="503" y="164">«HTTPS/JSON»</text>
  </g>
</svg>
"""


def build_html() -> str:
    fatec_b64 = file_b64(FATEC_LOGO, "image/jpeg")
    shot_home = shot_b64("home.png")
    shot_catalog = shot_b64("catalog.png")
    shot_product = shot_b64("product.png")
    shot_register = shot_b64("register.png")

    rep_matheus = report_page(
        "Matheus da Silva Araújo", "1680482421029", 2,
        [
            ("12/08/2026", "19/08/2026", "3h30", "Reunião de alinhamento com a equipe, entrevista com a proprietária da Vevey Doces e consolidação da Visão e Escopo v2.0."),
            ("25/08/2026", "02/09/2026", "3h00", "Elaboração de personas, stakeholders e cenários de utilização da loja virtual e do balcão físico."),
            ("09/09/2026", "30/09/2026", "4h30", "Levantamento de requisitos funcionais (RF001–RF018), requisitos não funcionais e apresentação preliminar."),
            ("07/10/2026", "21/10/2026", "4h00", "Detalhamento de fluxos alternativos dos casos de uso e validação dos protótipos de tela."),
            ("04/11/2026", "12/11/2026", "5h00", "Apoio à modelagem de estoque com ledger, validação das regras de cupom e cálculo de frete com feriados."),
            ("13/11/2026", "18/11/2026", "4h30", "Execução de testes funcionais de checkout online, reserva de estoque e área Minha Conta (2FA)."),
            ("19/11/2026", "24/11/2026", "5h00", "Testes ponta a ponta do painel administrativo, PDV Balcão, revisão do Manual do Usuário e validação da entrega N2."),
        ],
    )

    rep_nathan = report_page(
        "Nathan Costa Santos", "1680482421021", 3,
        [
            ("12/08/2026", "19/08/2026", "4h00", "Alinhamento com proprietários, definição do Project Model Canvas e levantamento inicial de escopo."),
            ("26/08/2026", "09/09/2026", "4h30", "Modelagem inicial de banco de dados v1.0, definição das telas e revisão da especificação de requisitos."),
            ("02/11/2026", "08/11/2026", "9h00", "Definição das ADRs 1–9 e migração arquitetural para monólito modular Laravel 13 + Filament 5 + PostgreSQL."),
            ("09/11/2026", "15/11/2026", "8h00", "Implementação dos módulos Vevey\\Permissions (RBAC 140 permissões) e Vevey\\Catalog (atributos ADR 6 e busca GIN)."),
            ("16/11/2026", "19/11/2026", "9h00", "Implementação dos módulos Vevey\\Orders (PlaceOrder, snapshots, cupons) e Vevey\\Inventory (ledger e reservas 30 min)."),
            ("20/11/2026", "22/11/2026", "8h00", "Implementação de Vevey\\Customers (2FA OTP, CPF Módulo 11), PDV Balcão (/admin/pdv) e Logs de Auditoria."),
            ("23/11/2026", "24/11/2026", "6h00", "Integração BrasilAPI/ViaCEP/Resend, Docker/Render, identidade visual, testes E2E e geração da documentação final N2."),
        ],
    )

    rep_ricardo = report_page(
        "Ricardo Martins Pighin", "1680482421013", 4,
        [
            ("12/08/2026", "19/08/2026", "3h30", "Reunião de alinhamento, levantamento de requisitos com stakeholder e consolidação da documentação v2.0."),
            ("09/09/2026", "23/09/2026", "4h00", "Especificação de requisitos não funcionais, regras de segurança (LGPD, CSRF, Rate Limit) e atores."),
            ("30/09/2026", "14/10/2026", "4h00", "Modelagem de dados relacional (DER), chaves estrangeiras, integridade referencial e dicionário de tabelas."),
            ("03/11/2026", "12/11/2026", "5h00", "Revisão das ADRs de banco de dados (CHECK monetário, colunas STORED) e matriz de papéis RBAC."),
            ("13/11/2026", "18/11/2026", "4h30", "Elaboração da Matriz de Rastreabilidade de casos de teste (TC01–TC10) e validação de testes Pest Arch."),
            ("19/11/2026", "24/11/2026", "5h00", "Conferência de diagramas UML (Pacotes, Classes, Sequência, Artefatos, Implantação), referências ABNT e revisão N2."),
        ],
    )

    # 8 Casos de Uso completos no formato oficial em caixa
    ucs_html = "".join([
        uc_box(
            1, "UC01", "Explorar Catálogo e Buscar",
            "O cliente navega pelos departamentos da loja virtual, utiliza a busca preditiva ao vivo ou full-text em português e combina filtros dinâmicos de atributos (Cor, Tipo) para localizar produtos com disponibilidade real em estoque.",
            "Cliente (AT01)", "Nenhum",
            ["Existem produtos ativos cadastrados no módulo <code>catalog</code> com categorias e atributos vinculados."],
            [
                "O cliente acessa a página inicial (<code>/</code> — Tela 01) ou o catálogo completo (<code>/catalogo</code> — Tela 02).",
                "O cliente digita um termo na busca (nome, SKU ou código de barras) ou seleciona filtros de categoria, cor e tipo.",
                "O sistema executa a busca full-text (<code>tsvector</code> GIN) e aplica a regra da ADR 6 (OU dentro do mesmo atributo, E entre atributos diferentes).",
                "O sistema apresenta os cards de produtos com preço, valor Pix, selo de caixa fechada e disponibilidade real em <code>stock_levels</code>.",
                "Ao clicar em um produto, o sistema abre a Página de Produto (Tela 03 — <code>/produto/{slug}</code>) com seletor de cores irmãs e botão para adicionar ao carrinho."
            ],
            ["Produto localizado e pronto para inclusão no carrinho de compras."],
            [
                ("A1. Nenhum produto encontrado", "O sistema exibe mensagem informativa e botão para limpar os filtros aplicados."),
                ("A2. Produto esgotado", "Quando o saldo disponível (<code>on_hand - reserved</code>) for menor ou igual a zero, o sistema exibe o selo <em>Esgotado</em> e bloqueia a adição ao carrinho.")
            ]
        ),
        uc_box(
            2, "UC02", "Realizar Compra (Checkout Online)",
            "O cliente revisa os itens do carrinho, informa dados de contato e entrega (ou retirada em loja), aplica cupom opcional, seleciona pagamento (Pix ou Cartão) e finaliza o pedido com reserva transacional de estoque.",
            "Cliente (AT01)", "Serviços Externos — BrasilAPI/ViaCEP (AT05)",
            [
                "O carrinho possui ao menos um produto ativo.",
                "Todos os itens do carrinho possuem saldo disponível suficiente em <code>stock_levels</code>."
            ],
            [
                "O cliente acessa a Tela 05 (<code>/checkout</code>) e informa (ou confirma autopreenchimento) de nome, e-mail e telefone.",
                "O cliente escolhe retirada na loja (grátis) ou entrega via Correios, informando o CEP para autopreenchimento e cotação SEDEX/PAC com previsão em dias úteis reais (pulando feriados nacionais via BrasilAPI).",
                "Opcionalmente, o cliente informa um cupom de desconto (ex.: <code>VEVEY10</code>) validado em tempo real no servidor.",
                "O cliente escolhe a forma de pagamento (Pix com 5% OFF ou Cartão) e confirma o pedido.",
                "O serviço <code>PlaceOrder</code> persiste <code>orders</code>, <code>order_items</code> (snapshot), <code>order_adjustments</code>, <code>payments</code> e cria reservas de 30 minutos em <code>stock_reservations</code> sob <code>lockForUpdate</code>.",
                "O sistema exibe o número do pedido (<code>VV-2026-XXXXXX</code>), o QR Code Pix e a confirmação Sandbox; após aprovação, converte as reservas em movimento <code>sale</code> no ledger."
            ],
            [
                "Pedido gravado com totais validados em centavos (<code>CHECK</code>) e snapshot imutável dos itens.",
                "Estoque reservado (30 minutos) ou baixado definitivamente no ledger após pagamento confirmado."
            ],
            [
                ("A1. Estoque insuficiente no envio", "Se outro comprador consumir o saldo antes da confirmação, o servidor aborta a transação e retorna HTTP 422."),
                ("A2. Cupom inválido ou expirado", "O sistema exibe mensagem de validação sem bloquear a finalização sem desconto.")
            ]
        ),
        uc_box(
            3, "UC03", "Cadastrar Conta com Verificação em 2 Etapas (2FA)",
            "Um visitante ou cliente que já comprou pelo WhatsApp/site cria ou ativa sua conta pessoal no guard isolado <code>customers</code> mediante validação de CPF e confirmação de código OTP de 6 dígitos.",
            "Cliente (AT01)", "Serviços Externos — Resend API / SMTP (AT05)",
            ["Não existe conta verificada com o mesmo endereço de e-mail na tabela <code>customers</code>."],
            [
                "O cliente acessa a Tela 04 (<code>/cadastro</code>) e preenche nome completo, e-mail, WhatsApp, CPF opcional e senha.",
                "O sistema valida o CPF matematicamente (algoritmo Módulo 11) e normaliza os contatos em <code>customer_identities</code>.",
                "O sistema gera um código OTP de 6 dígitos (validade de 15 minutos), dispara o envio via <code>TransactionalMailer</code> (Resend API / SMTP) e redireciona para <code>/verificar-conta</code>.",
                "O cliente informa o código de 6 dígitos recebido; o sistema grava <code>email_verified_at</code>, autentica no guard <code>customers</code> e abre a Tela 06 (<code>/conta</code>)."
            ],
            ["Conta de cliente verificada, autenticada e vinculada ao histórico de pedidos e endereços salvos."],
            [
                ("A1. CPF inválido", "O sistema rejeita o formulário antes da persistência indicando erro de dígito verificador."),
                ("A2. Código OTP incorreto ou expirado", "O sistema rejeita a verificação e disponibiliza botão para reenvio de novo código."),
                ("A3. Ambiente local/homologação sem chave Resend", "O sistema exibe o código no card <em>Modo demonstração</em> para validação.")
            ]
        ),
        uc_box(
            4, "UC04", "Registrar Venda Presencial no PDV Balcão",
            "A atendente registra uma venda presencial realizada na loja física como um pedido de canal <code>pos</code>, dando baixa imediata e rastreável no estoque único da loja.",
            "Atendente (AT02) ou Administrador (AT04)", "Nenhum",
            ["Usuário autenticado no painel <code>/admin</code> (guard <code>web</code>) com permissão RBAC sobre pedidos."],
            [
                "A atendente acessa a Tela 08 (<code>/admin/pdv</code>) e pesquisa produtos por nome, SKU ou código de barras.",
                "Adiciona os produtos e respectivas quantidades ao carrinho do balcão.",
                "Seleciona a forma de pagamento (Dinheiro, Pix, Débito ou Crédito) e aciona <em>Concluir Venda</em>.",
                "O serviço <code>PlaceOrder</code> cria o pedido com <code>channel = pos</code>, <code>payment_status = paid</code>, <code>fulfillment_status = delivered</code> e <code>seller_id</code> preenchido.",
                "<code>RecordDirectSale</code> registra a saída <code>sale</code> no ledger <code>stock_movements</code> e atualiza <code>stock_levels</code>."
            ],
            [
                "Venda contabilizada no faturamento do canal <code>pos</code>.",
                "Saldo de estoque atualizado atomicamente e evento registrado na auditoria."
            ],
            [
                ("A1. Carrinho vazio", "O sistema impede a finalização até que ao menos um item seja incluído."),
                ("A2. Saldo negativo na prateleira (ADR 5)", "Caso uma nota de entrada ainda não tenha sido lançada, o sistema conclui a venda sem travar o caixa e exibe alerta visual de estoque negativo para reposição.")
            ]
        ),
        uc_box(
            5, "UC05", "Gerenciar Catálogo e Cupons",
            "O administrador cadastra, edita, duplica ou inativa produtos planos, categorias hierárquicas, atributos dinâmicos (Cor, Tipo) e campanhas de cupons promocionais.",
            "Administrador / SuperAdmin (AT04)", "Nenhum",
            ["Usuário autenticado em <code>/admin</code> com papel RBAC <code>admin</code> ou <code>super_admin</code>."],
            [
                "O administrador acessa <em>Catálogo → Produtos</em> ou <em>Vendas → Cupons</em> no painel Filament.",
                "Informa SKU único, nome, preço em reais, preço promocional opcional, categoria, atributos e imagens (convertidas para WebP).",
                "Para cupons, define código (ex.: <code>VEVEY10</code>), tipo (percentual ou fixo), pedido mínimo, limite de usos e período de vigência.",
                "O sistema valida unicidade e constraints <code>CHECK</code>, salva no PostgreSQL e atualiza o índice <code>search_vector</code>."
            ],
            ["Produto ou cupom publicado em tempo real na vitrine pública."],
            [
                ("A1. SKU ou código duplicado", "O sistema bloqueia o salvamento exibindo mensagem de conflito de unicidade."),
                ("A2. Preço promocional inválido", "Preço promocional maior ou igual ao preço regular é bloqueado por validação e constraint de banco.")
            ]
        ),
        uc_box(
            6, "UC06", "Consultar Dashboard e Logs de Auditoria",
            "O administrador acompanha indicadores gerenciais em tempo real na Tela 07 (Dashboard) e consulta a linha do tempo unificada de auditoria (RNF011).",
            "Administrador / SuperAdmin (AT04)", "Serviços Externos — BrasilAPI Feriados (AT05)",
            ["Usuário autenticado em <code>/admin</code> com papel <code>admin</code> ou <code>super_admin</code>."],
            [
                "O administrador acessa <code>/admin</code> (Tela 07) e visualiza KPIs de faturamento, ticket médio, vendas por canal, top produtos, alertas de estoque baixo e próximos feriados nacionais.",
                "Navega até <em>Auditoria → Logs de auditoria</em> (<code>/admin/audit-log</code>).",
                "Aplica filtros por módulo (Estoque ou Pedidos), responsável ou termo de busca.",
                "Consulta a linha do tempo detalhada contendo data/hora, ator, tipo de evento, referência e justificativa."
            ],
            ["Indicadores gerenciais e trilha de auditoria consultados sem modificação de dados."],
            [
                ("A1. Acesso não autorizado", "Papéis <code>attendant</code> e <code>logistics</code> recebem HTTP 403 ao tentar acessar <code>/admin/audit-log</code>."),
                ("A2. Indisponibilidade da BrasilAPI", "O widget de feriados utiliza cache local de 7 dias sem degradar o Dashboard.")
            ]
        ),
        uc_box(
            7, "UC07", "Ajustar ou Receber Estoque via Ledger",
            "A equipe de logística ou administração registra entradas de fornecedores ou contagens físicas de inventário exclusivamente por movimentações imutáveis no ledger.",
            "Logística (AT03) ou Administrador (AT04)", "Nenhum",
            [
                "Usuário autenticado em <code>/admin</code> com permissão sobre <code>StockLevel</code>.",
                "Produto previamente cadastrado no catálogo."
            ],
            [
                "O usuário acessa <code>/admin/stock-levels</code> e aciona <em>Lançar entrada</em> ou <em>Contagem / Ajustar</em> no produto desejado.",
                "Informa a quantidade recebida (ou a quantidade contada fisicamente na prateleira) e o motivo obrigatório.",
                "A action transacional <code>AdjustStock</code> trava o registro com <code>lockForUpdate</code>, calcula o delta, insere linha imutável em <code>stock_movements</code> e atualiza <code>stock_levels</code>."
            ],
            ["Saldo de estoque recalculado atomicamente, refletido na vitrine e auditável em <code>/admin/audit-log</code>."],
            [
                ("A1. Diferença igual a zero", "O sistema cancela a inserção respeitando a constraint <code>CHECK (qty != 0)</code>."),
                ("A2. Motivo não informado", "O formulário bloqueia o envio exigindo justificativa rastreável.")
            ]
        ),
        uc_box(
            8, "UC08", "Separar e Entregar Pedido",
            "A equipe de logística ou atendimento acompanha pedidos pagos, realiza a conferência dos itens e atualiza o status logístico até a entrega ou retirada.",
            "Logística (AT03) ou Atendente (AT02)", "Nenhum",
            [
                "Usuário autenticado em <code>/admin</code> com permissão sobre pedidos.",
                "Existência de pedido com <code>payment_status = paid</code> aguardando separação."
            ],
            [
                "O operador acessa <em>Vendas → Pedidos</em> (<code>/admin/orders</code>) e filtra os pedidos pagos.",
                "Abre o pedido, confere os itens em <code>order_items</code> e os dados de entrega ou retirada.",
                "Aciona <code>UpdateOrderStatus</code> para avançar <code>fulfillment_status</code> (<code>separating</code> → <code>shipped</code> → <code>delivered</code>), informando observação opcional.",
                "O sistema atualiza os timestamps de transição e registra o histórico em <code>order_status_histories</code>."
            ],
            ["Status logístico atualizado e visível em tempo real ao cliente na Tela 06 (<code>/conta</code>)."],
            [
                ("A1. Cancelamento de pedido pago", "O sistema gera movimento positivo de devolução (<code>return</code>) no ledger restaurando o saldo."),
                ("A2. Cancelamento de pedido pendente", "O sistema libera imediatamente as reservas ativas em <code>stock_reservations</code>.")
            ]
        ),
    ])

    # 5 Atores completos no formato oficial em caixa
    actors_html = "".join([
        actor_box(
            1, "AT01", "Cliente",
            "Representa qualquer consumidor final, organizador de festas ou revendedor que acessa a loja virtual da Vevey Doces para consultar catálogo e efetuar pedidos.",
            [
                "Pesquisar e filtrar produtos por categoria, cor, tipo ou SKU.",
                "Gerenciar carrinho de compras, calcular frete e aplicar cupons promocionais.",
                "Finalizar compras online via Pix ou Cartão.",
                "Cadastrar e verificar conta pessoal (2FA), gerenciar endereços e acompanhar pedidos."
            ],
            "Acesso remoto por meio de navegador web em computador, tablet ou smartphone conectado à internet.",
            "Ilimitado — dezenas a milhares de usuários humanos concorrentes acessando a vitrine pública e o guard <code>customers</code>.",
            "Variável — desde compras esporádicas para festas (1 a 3 vezes por ano) até compras semanais por confeiteiros e revendedores."
        ),
        actor_box(
            2, "AT02", "Atendente",
            "Colaborador da loja física responsável pelo atendimento presencial no balcão e pelo suporte a pedidos recebidos pelo WhatsApp.",
            [
                "Registrar vendas presenciais no PDV Balcão (<code>/admin/pdv</code>).",
                "Localizar clientes por telefone (inclusive números antigos aposentados).",
                "Consultar disponibilidade real de estoque e atualizar status de retirada de pedidos."
            ],
            "Terminal de computador ou tablet instalado no balcão de atendimento da loja física Vevey Doces.",
            "De 1 a 4 usuários humanos simultâneos autenticados no guard <code>web</code> com papel RBAC <code>attendant</code>.",
            "Contínua durante todo o expediente comercial (segunda a sábado, das 09h às 19h)."
        ),
        actor_box(
            3, "AT03", "Logística",
            "Colaborador encarregado do recebimento de mercadorias, acuracidade do estoque físico e expedição de pedidos.",
            [
                "Lançar entradas de mercadorias de fornecedores no ledger de estoque.",
                "Executar contagens físicas e ajustes de inventário em <code>/admin/stock-levels</code>.",
                "Separar pedidos pagos e atualizar status de envio/entrega."
            ],
            "Área de estoque, depósito e bancada de separação da loja física Vevey Doces.",
            "De 1 a 3 usuários humanos autenticados no guard <code>web</code> com papel RBAC <code>logistics</code>.",
            "Diária, a cada recebimento de fornecedor, contagem periódica e lote de expedição."
        ),
        actor_box(
            4, "AT04", "Administrador / SuperAdmin",
            "Sócia-gerente ou responsável técnico com acesso gerencial pleno à plataforma, catálogo, finanças, papéis RBAC e auditoria.",
            [
                "Cadastrar, editar e inativar produtos, categorias, atributos e cupons.",
                "Gerenciar contas da equipe e permissões RBAC.",
                "Acompanhar KPIs no Dashboard e auditar eventos em <code>/admin/audit-log</code>."
            ],
            "Escritório administrativo da Vevey Doces ou acesso remoto seguro via HTTPS.",
            "De 1 a 2 usuários humanos autenticados no guard <code>web</code> com papéis RBAC <code>admin</code> ou <code>super_admin</code>.",
            "Diária para monitoramento de vendas/estoque e semanal para campanhas, precificação e auditoria."
        ),
        actor_box(
            5, "AT05", "Serviços Externos (BrasilAPI / ViaCEP / Resend)",
            "Atores secundários (sistemas externos) integrados via HTTPS/JSON para consulta de CEP, calendário oficial de feriados nacionais e disparo de e-mails 2FA.",
            [
                "Retornar logradouro, bairro, cidade e UF a partir do CEP informado.",
                "Fornecer feriados nacionais oficiais para cálculo de dias úteis reais de entrega e planejamento sazonal.",
                "Enviar e-mails transacionais contendo o código OTP de 6 dígitos."
            ],
            "Servidores em nuvem externos acessados pela aplicação Laravel via HTTPS com cache local e fallback.",
            "3 serviços web externos (atores não humanos / secundários).",
            "Sob demanda durante checkout, cadastro 2FA e carregamento do Dashboard (mitigado por cache de 24h a 7 dias)."
        ),
    ])

    # 7 Pacotes no formato oficial de tabela de 3 linhas
    pkgs_html = "".join([
        pkg_box("3.1.1.1", "Vevey\\Catalog",
                "Contém as classes responsáveis pelo catálogo plano de produtos, categorias hierárquicas, atributos dinâmicos (ADR 6), gestão de mídias WebP e busca full-text em português.",
                "<code>Product</code>, <code>Category</code>, <code>Attribute</code>, <code>AttributeValue</code>, <code>FindProductsForSale</code>, <code>ProductData</code>."),
        pkg_box("3.1.1.2", "Vevey\\Inventory",
                "Contém o ledger append-only de movimentações de estoque (ADR 2), controle de reservas de 30 minutos sob <code>lockForUpdate</code>, cache de saldos e comandos de reconciliação.",
                "<code>StockMovement</code>, <code>StockReservation</code>, <code>StockLevel</code>, <code>ReserveStock</code>, <code>FulfillOrderReservations</code>, <code>RecordDirectSale</code>, <code>AdjustStock</code>."),
        pkg_box("3.1.1.3", "Vevey\\Customers",
                "Contém o modelo autenticável de clientes no guard isolado <code>customers</code>, identidades de contato aposentáveis (ADR 7) e endereços de entrega salvos.",
                "<code>Customer</code>, <code>CustomerIdentity</code>, <code>CustomerAddress</code>, <code>FindOrCreateCustomerByContact</code>, <code>RetireAndReplaceIdentity</code>, <code>FindCustomerByPhone</code>."),
        pkg_box("3.1.1.4", "Vevey\\Orders",
                "Orquestra o ciclo completo de vendas multicanal (Site, PDV Balcão e WhatsApp), snapshots imutáveis de linha (ADR 3), cupons, rateio de descontos, pagamentos 1:N e histórico de status.",
                "<code>Order</code>, <code>OrderItem</code>, <code>OrderAdjustment</code>, <code>Coupon</code>, <code>Payment</code>, <code>OrderStatusHistory</code>, <code>PlaceOrder</code>, <code>UpdateOrderStatus</code>."),
        pkg_box("3.1.1.5", "Vevey\\Permissions",
                "Gerencia a autorização granular baseada em papéis (RBAC) com morph map, policies padronizadas e comando de sincronização automática de permissões.",
                "<code>Role</code>, <code>Permission</code>, <code>Roles</code>, <code>PermissionsEnum</code>, <code>ModelPolicy</code>, <code>SyncPermissionsCommand</code>."),
        pkg_box("3.1.1.6", "Vevey\\PanelAdmin",
                "Provê a interface administrativa em Filament 5: Dashboard gerencial, PDV Balcão (<code>/admin/pdv</code>), recursos CRUD e painel unificado de Logs de Auditoria.",
                "<code>OrderResource</code>, <code>StockLevelResource</code>, <code>CustomerResource</code>, <code>CouponResource</code>, <code>PosSale</code>, <code>AuditLog</code>, Widgets."),
        pkg_box("3.1.1.7", "Vevey\\PanelSite",
                "Provê a interface pública da loja virtual: vitrine editorial, busca preditiva, filtros combinados, checkout com Pix Sandbox e área autenticada do cliente com 2FA.",
                "<code>StorefrontController</code>, <code>CheckoutController</code>, <code>CustomerAuthController</code>, <code>CustomerAccountController</code>."),
    ])

    # 5 Classes especificadas no formato completo da página 20-21 do modelo oficial
    classes_html = "".join([
        class_box(
            "3.1.2.1.", "Product",
            "Representa um produto comercializável no catálogo plano da Vevey Doces (cada cor ou formato de caixa constitui um produto independente).",
            "<code>Illuminate\\Database\\Eloquent\\Model</code> (traits <code>HasUuids</code>, <code>SoftDeletes</code>, <code>InteractsWithMedia</code>)",
            "Nenhuma",
            [
                ("id", "Identificador único universal do produto", "uuid", "UUID v7 gerado automaticamente"),
                ("sku", "Código interno único de estoque do produto", "string(64)", "Texto alfanumérico único (ex.: BAL-MET-9-AZ)"),
                ("price_cents", "Preço regular do produto armazenado em centavos", "Money (bigint)", "Inteiro estritamente positivo (> 0)"),
                ("promo_price_cents", "Preço promocional opcional em centavos", "?Money (bigint)", "Nulo ou inteiro positivo menor que price_cents"),
            ],
            "scopeSearch",
            "Filtra produtos utilizando o índice GIN <code>search_vector</code> (língua portuguesa) combinado com busca exata/parcial por SKU e código de barras.",
            [
                ("query", "Builder", "Instância da consulta Eloquent em construção"),
                ("term", "string", "Termo de busca informado pelo cliente ou atendente"),
            ],
            ["O parâmetro <code>term</code> deve conter ao menos um caractere não vazio."],
            [
                "Normalizar e sanitizar o termo de busca recebido;",
                "Aplicar <code>plainto_tsquery('portuguese', term)</code> sobre <code>search_vector</code> combinado com <code>ILIKE</code> em <code>sku</code> e <code>barcode</code>;",
                "Ordenar resultados por relevância textual (<code>ts_rank</code>)."
            ],
            ["Retorna a consulta filtrada e ordenada por relevância sem alterar o estado do banco."]
        ),
        class_box(
            "3.1.2.2.", "Order",
            "Orquestra uma venda multicanal (Site, PDV ou WhatsApp), mantendo totais monetários validados em centavos e separando status financeiro de logístico.",
            "<code>Illuminate\\Database\\Eloquent\\Model</code> (<code>HasUuids</code>)",
            "Nenhuma",
            [
                ("number", "Código legível único do pedido exibido ao cliente", "string(32)", "Formato VV-AAAA-XXXXXX"),
                ("channel", "Canal de origem da venda", "OrderChannel", "Enum: web, pos, whatsapp"),
                ("payment_status", "Situação financeira atual do pedido", "PaymentStatus", "Enum: pending, paid, refunded, failed, cancelled"),
                ("total_cents", "Valor líquido final do pedido em centavos", "Money (bigint)", "total_cents = subtotal - discount + shipping (>= 0)"),
            ],
            "PlaceOrder::handle",
            "Valida itens, calcula descontos (cupom e 5% Pix) com rateio proporcional por linha, grava o pedido com snapshots e aciona o estoque.",
            [
                ("data", "PlaceOrderData", "DTO imutável contendo canal, itens, cliente, frete, cupom e pagamento"),
            ],
            ["A lista de itens deve conter ao menos um produto ativo.", "Para canal <code>web</code>, todos os itens devem possuir saldo disponível suficiente."],
            [
                "Abrir transação <code>DB::transaction</code>;",
                "Buscar preços vigentes via <code>FindProductsForSale</code> e calcular descontos com <code>Money::allocate()</code>;",
                "Gravar <code>orders</code>, <code>order_items</code> (snapshot), <code>order_adjustments</code> e <code>payments</code>;",
                "Se canal for <code>web</code>, chamar <code>ReserveStock</code> (30 min); se <code>pos</code>, chamar <code>RecordDirectSale</code>."
            ],
            ["Pedido persistido atomicamente com snapshot imutável e estoque reservado ou baixado."]
        ),
        class_box(
            "3.1.2.3.", "OrderItem",
            "Representa uma linha imutável de um pedido contendo snapshot completo de nome, SKU, preço unitário e atributos no momento da compra (ADR 3).",
            "<code>Illuminate\\Database\\Eloquent\\Model</code> (<code>HasUuids</code>)",
            "Nenhuma",
            [
                ("order_id", "Chave estrangeira para o pedido proprietário", "uuid", "UUID existente em orders.id"),
                ("unit_price_cents", "Preço unitário congelado no ato da compra", "Money (bigint)", "Inteiro positivo (> 0)"),
                ("quantity", "Quantidade comprada do item", "integer", "Inteiro positivo (>= 1)"),
                ("product_snapshot", "Cópia JSON dos atributos e categoria do produto", "jsonb", "Objeto JSON válido com SKU, nome, cor e imagem"),
            ],
            "lineTotal",
            "Calcula o valor líquido da linha descontando o rateio proporcional atribuído ao item.",
            [],
            ["O item deve possuir <code>unit_price_cents</code> e <code>quantity</code> válidos."],
            [
                "Multiplicar <code>unit_price_cents</code> por <code>quantity</code> utilizando <code>brick/money</code>;",
                "Subtrair <code>discount_cents</code> rateado para a linha."
            ],
            ["Retorna instância imutável de <code>Money</code> representando o total líquido da linha."]
        ),
        class_box(
            "3.1.2.4.", "StockMovement",
            "Registro append-only (imutável) de toda entrada, saída de venda, devolução ou ajuste físico no ledger de estoque (ADR 2).",
            "<code>Illuminate\\Database\\Eloquent\\Model</code> (<code>HasUuids</code>)",
            "Nenhuma",
            [
                ("product_id", "Produto movimentado", "uuid", "UUID existente em products.id"),
                ("qty", "Quantidade movimentada (positiva para entrada, negativa para saída)", "integer", "Qualquer inteiro diferente de zero (CHECK qty != 0)"),
                ("direction", "Direção da movimentação calculada pelo SGBD", "string (STORED)", "'in' quando qty > 0; 'out' quando qty < 0"),
                ("reason", "Motivo padronizado da movimentação", "MovementReason", "Enum: purchase, sale, adjustment, return"),
            ],
            "AdjustStock::handle",
            "Executa contagem física ou entrada manual registrando a diferença de forma atômica e auditável no ledger.",
            [
                ("productId", "string", "UUID do produto alvo"),
                ("targetQty", "int", "Quantidade física contada na prateleira"),
                ("reason", "string", "Justificativa obrigatória da operação"),
                ("userId", "string", "UUID do operador autenticado"),
            ],
            ["O motivo deve ser informado e o delta (<code>targetQty - on_hand</code>) deve ser diferente de zero."],
            [
                "Iniciar transação e travar <code>stock_levels</code> com <code>lockForUpdate()</code>;",
                "Calcular <code>delta = targetQty - current.on_hand</code>;",
                "Inserir nova linha imutável em <code>stock_movements</code> com <code>balance_after = targetQty</code>;",
                "Atualizar <code>stock_levels.on_hand</code> com <code>targetQty</code>."
            ],
            ["Saldo atualizado atomicamente e nova entrada imutável gravada na trilha de auditoria."]
        ),
        class_box(
            "3.1.2.5.", "Customer",
            "Representa o cliente autenticável no guard isolado <code>customers</code>, vinculando pedidos, endereços salvos e identidades aposentáveis (ADR 7).",
            "<code>Illuminate\\Foundation\\Auth\\User</code> (<code>Authenticatable</code>, <code>HasUuids</code>, <code>SoftDeletes</code>)",
            "Nenhuma",
            [
                ("email", "Endereço de e-mail principal de login", "string(255)", "E-mail válido único entre clientes ativos"),
                ("cpf", "CPF opcional validado localmente pelo algoritmo Módulo 11", "string(11)", "Nulo ou 11 dígitos numéricos válidos"),
                ("verification_code", "Código OTP de 6 dígitos para verificação 2FA", "?string(6)", "6 dígitos numéricos válidos por 15 minutos"),
                ("email_verified_at", "Data e hora da confirmação da conta via 2FA", "?timestamp", "Nulo (pendente) ou timestamp UTC"),
            ],
            "RetireAndReplaceIdentity::handle",
            "Aposenta um telefone ou e-mail antigo marcando <code>retired_at</code> e cadastra o novo contato como primário sem perder buscas históricas.",
            [
                ("customer", "Customer", "Cliente titular da identidade"),
                ("oldValue", "string", "Contato anterior a ser aposentado"),
                ("newValue", "string", "Novo contato normalizado (E.164 ou e-mail)"),
            ],
            ["O novo contato não pode pertencer como ativo a outro cliente."],
            [
                "Localizar a identidade antiga em <code>customer_identities</code> e definir <code>retired_at = now()</code>, <code>is_primary = false</code>;",
                "Inserir o novo registro em <code>customer_identities</code> com <code>is_primary = true</code>."
            ],
            ["Novo contato ativo para comunicação e número antigo preservado para buscas no balcão."]
        ),
    ])

    # 8 Tabelas principais do BD no formato oficial (Atributo | Descrição | Chave | Tipo)
    db_tables_html = "".join([
        db_table_box(9, "products", "Armazena o catálogo plano de produtos comercializados pela Vevey Doces.", [
            ("id", "Identificador único universal (UUID v7)", "SIM", "UUID"),
            ("sku", "Código único de controle de estoque (SKU)", "NÃO", "VARCHAR(64) UNIQUE"),
            ("barcode", "Código de barras EAN/GTIN opcional para leitura no PDV", "NÃO", "VARCHAR(64)"),
            ("name", "Nome comercial exibido na vitrine e nos pedidos", "NÃO", "VARCHAR(255)"),
            ("slug", "Identificador amigável para URL da página do produto", "NÃO", "VARCHAR(255) UNIQUE"),
            ("price_cents", "Preço regular em centavos (CHECK > 0)", "NÃO", "BIGINT"),
            ("promo_price_cents", "Preço promocional opcional em centavos", "NÃO", "BIGINT NULL"),
            ("search_vector", "Vetor full-text gerado (STORED) com índice GIN em português", "NÃO", "TSVECTOR"),
            ("is_active", "Indica se o produto está publicado na loja virtual", "NÃO", "BOOLEAN"),
            ("deleted_at", "Data de inativação lógica (soft delete)", "NÃO", "TIMESTAMP NULL"),
        ]),
        db_table_box(10, "categories e attributes", "Organiza a hierarquia de departamentos (até 2 níveis) e atributos dinâmicos de filtro (ADR 6).", [
            ("categories.id", "Identificador único da categoria", "SIM", "UUID"),
            ("categories.parent_id", "Referência opcional para categoria pai", "SIM", "UUID (FK)"),
            ("categories.name / slug", "Nome e slug da categoria", "NÃO", "VARCHAR(120)"),
            ("attributes.id / name", "Definição do atributo (ex.: Cor, Tipo)", "SIM", "UUID / VARCHAR(80)"),
            ("attribute_values.id", "Valor específico do atributo (ex.: Azul, Metalizado)", "SIM", "UUID"),
            ("attribute_values.hex_color", "Código hexadecimal opcional para swatch de cor", "NÃO", "VARCHAR(7) NULL"),
        ]),
        db_table_box(11, "stock_levels", "Cache transacional de saldo por produto sincronizado com o ledger de movimentações.", [
            ("product_id", "Identificador do produto (chave primária e estrangeira)", "SIM", "UUID (PK/FK)"),
            ("on_hand", "Quantidade física em prateleira (assinado; permite negativo no PDV)", "NÃO", "INTEGER"),
            ("reserved", "Quantidade atualmente reservada por checkouts pendentes (>= 0)", "NÃO", "INTEGER"),
            ("available", "Saldo disponível para venda online (STORED: on_hand - reserved)", "NÃO", "INTEGER"),
            ("reorder_point", "Estoque mínimo que dispara alerta visual no Dashboard", "NÃO", "INTEGER"),
        ]),
        db_table_box(12, "stock_movements", "Ledger imutável (append-only) que registra cada movimentação de estoque com rastreabilidade.", [
            ("id", "Identificador único do lançamento", "SIM", "UUID"),
            ("product_id", "Referência ao produto movimentado", "SIM", "UUID (FK)"),
            ("qty", "Delta movimentado (CHECK qty != 0)", "NÃO", "INTEGER"),
            ("direction", "Coluna gerada STORED ('in' se qty > 0, 'out' se qty < 0)", "NÃO", "VARCHAR(3)"),
            ("reason", "Motivo (purchase, sale, adjustment, return)", "NÃO", "VARCHAR(32)"),
            ("balance_after", "Saldo resultante imediatamente após o lançamento", "NÃO", "INTEGER"),
            ("reference_type / id", "Referência polimórfica de idempotência (ex.: pedido)", "NÃO", "VARCHAR / UUID"),
            ("user_id", "Responsável pela movimentação (auditoria RNF011)", "SIM", "UUID (FK) NULL"),
        ]),
        db_table_box(13, "stock_reservations", "Controla reservas temporárias de 30 minutos criadas no checkout online.", [
            ("id", "Identificador único da reserva", "SIM", "UUID"),
            ("order_id", "Pedido proprietário da reserva", "SIM", "UUID (FK)"),
            ("product_id", "Produto reservado", "SIM", "UUID (FK)"),
            ("quantity", "Quantidade reservada (> 0)", "NÃO", "INTEGER"),
            ("expires_at", "Instante de expiração (lazy expiration / cron 5 min)", "NÃO", "TIMESTAMP"),
            ("released_at", "Instante em que a reserva foi convertida em venda ou liberada", "NÃO", "TIMESTAMP NULL"),
        ]),
        db_table_box(14, "orders e order_items", "Persiste os pedidos multicanal, totais financeiros e snapshots imutáveis dos itens vendidos.", [
            ("orders.id", "Identificador único do pedido", "SIM", "UUID"),
            ("orders.number", "Código sequencial legível (VV-2026-XXXXXX)", "NÃO", "VARCHAR(32) UNIQUE"),
            ("orders.channel", "Canal da venda (web, pos, whatsapp)", "NÃO", "VARCHAR(16)"),
            ("orders.customer_id / seller_id", "Referências opcionais ao cliente e ao vendedor do PDV", "SIM", "UUID (FK) NULL"),
            ("orders.payment_status / fulfillment", "Status financeiro e status logístico independentes (ADR 4)", "NÃO", "VARCHAR(24)"),
            ("orders.total_cents", "Total líquido em centavos (CHECK subtotal - discount + shipping)", "NÃO", "BIGINT"),
            ("order_items.product_snapshot", "Snapshot JSONB imutável do produto no ato da venda (ADR 3)", "NÃO", "JSONB"),
        ]),
        db_table_box(15, "coupons, payments e order_status_histories", "Armazena campanhas de cupom, pagamentos 1:N e histórico auditável de transições de status.", [
            ("coupons.id / code", "Identificador e código único do cupom (ex.: VEVEY10)", "SIM", "UUID / VARCHAR(40)"),
            ("coupons.type / value", "Tipo (percent/fixed), valor, pedido mínimo e limite de usos", "NÃO", "VARCHAR / BIGINT"),
            ("payments.id / order_id", "Registro de tentativa/confirmação de pagamento 1:N", "SIM", "UUID (PK/FK)"),
            ("payments.method / amount_cents", "Método (pix, card, cash) e valor em centavos", "NÃO", "VARCHAR / BIGINT"),
            ("order_status_histories.id", "Registro imutável de cada alteração de status e responsável", "SIM", "UUID"),
        ]),
        db_table_box(16, "customers, customer_identities e customer_addresses", "Gerencia contas autenticáveis de clientes, identidades aposentáveis (ADR 7) e endereços.", [
            ("customers.id", "Identificador único do cliente", "SIM", "UUID"),
            ("customers.name / email / cpf", "Dados cadastrais e CPF opcional validado por Módulo 11", "NÃO", "VARCHAR"),
            ("customers.verification_code", "Código OTP de 6 dígitos para verificação em 2 etapas", "NÃO", "VARCHAR(6) NULL"),
            ("customer_identities.value", "Telefone E.164 ou e-mail normalizado com flag is_primary", "NÃO", "VARCHAR(255)"),
            ("customer_identities.retired_at", "Data de aposentadoria de contato antigo (preserva buscas)", "NÃO", "TIMESTAMP NULL"),
            ("customer_addresses.cep / street", "Endereço salvo preenchido via BrasilAPI/ViaCEP", "NÃO", "VARCHAR"),
        ]),
    ])

    return f"""<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8" />
<title>VeveyCo-LABENGSW-MA5-N1</title>
<style>
  @page {{
    size: A4 portrait;
    margin: 2.0cm 2.0cm 2.3cm 2.0cm;
    @bottom-right {{
      content: counter(page) "/" counter(pages);
      font-family: Calibri, "Segoe UI", Arial, sans-serif;
      font-size: 10.5pt;
      color: #000000;
    }}
  }}
  * {{ box-sizing: border-box; }}
  body {{
    font-family: Calibri, "Segoe UI", Arial, sans-serif;
    font-size: 11.2pt;
    line-height: 1.38;
    color: #000000;
    background: #FFFFFF;
    margin: 0;
  }}
  p {{
    text-align: justify;
    margin: 0 0 8pt 0;
    orphans: 3;
    widows: 3;
  }}
  ul, ol {{
    margin: 0 0 8pt 0;
    padding-left: 20pt;
  }}
  li {{
    text-align: justify;
    margin-bottom: 3pt;
  }}
  h1 {{
    font-size: 17.5pt;
    font-weight: 700;
    color: #000000;
    margin: 16pt 0 10pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  h2 {{
    font-size: 14.5pt;
    font-weight: 700;
    color: #000000;
    margin: 13pt 0 7pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  h3 {{
    font-size: 12.5pt;
    font-weight: 700;
    color: #000000;
    margin: 11pt 0 5pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  h4 {{
    font-size: 11.2pt;
    font-weight: 700;
    color: #000000;
    margin: 9pt 0 4pt 0;
    page-break-after: avoid;
    break-after: avoid;
  }}
  .page-break {{
    page-break-before: always;
    break-before: page;
  }}
  /* Capa fiel ao modelo oficial FATEC */
  .cover {{
    min-height: 24.4cm;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }}
  .cover-logo-fatec {{
    width: 215px;
    height: auto;
    display: block;
    margin-top: 4pt;
  }}
  .cover-center {{
    text-align: center;
    margin: 1.4cm 0 2.0cm 0;
  }}
  .cover-disc {{
    font-size: 18pt;
    font-weight: 700;
    margin-bottom: 24pt;
  }}
  .cover-prod {{
    font-size: 25pt;
    font-weight: 700;
    margin-bottom: 14pt;
  }}
  .cover-team {{
    font-size: 14pt;
    font-weight: 700;
  }}
  .cover-members {{
    width: 48%;
    margin-left: auto;
    margin-bottom: 1.2cm;
  }}
  /* Relatórios de andamento */
  .report-meta {{
    display: flex;
    justify-content: space-between;
    font-size: 11.2pt;
    margin: 8pt 0 12pt 0;
    padding-right: 18%;
  }}
  /* Tabelas oficiais FATEC: borda preta fina, fundo branco, cabeçalho negrito */
  .table-wrap {{
    margin: 9pt 0 11pt 0;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .table-caption {{
    font-size: 10pt;
    font-weight: 700;
    color: #000000;
    margin-bottom: 4pt;
    text-align: left;
  }}
  .source-note {{
    font-size: 9pt;
    color: #333333;
    margin-top: 3pt;
    text-align: left;
  }}
  table.grid-table {{
    width: 100%;
    border-collapse: collapse;
    font-size: 9.8pt;
    line-height: 1.32;
    background: #FFFFFF;
  }}
  table.grid-table thead {{ display: table-header-group; }}
  table.grid-table tr {{ page-break-inside: avoid; break-inside: avoid; }}
  table.grid-table th {{
    background: #FFFFFF;
    color: #000000;
    font-weight: 700;
    text-align: left;
    padding: 4.5pt 6pt;
    border: 1px solid #000000;
  }}
  table.grid-table td {{
    background: #FFFFFF;
    color: #000000;
    padding: 4pt 6pt;
    border: 1px solid #000000;
    vertical-align: top;
    text-align: left;
  }}
  table.compact-table th, table.compact-table td {{
    padding: 3pt 5pt;
    font-size: 9.1pt;
    line-height: 1.28;
  }}
  table.spec-table th, table.spec-table td {{
    padding: 2.6pt 5pt;
    font-size: 9pt;
    line-height: 1.24;
  }}
  .tight-ol {{
    margin: 2pt 0 2pt 0;
    padding-left: 16pt;
  }}
  .tight-ol li {{
    margin-bottom: 1.5pt;
  }}
  /* Caixas oficiais de Caso de Uso e Ator (borda preta retangular, igual págs. 11-12) */
  .doc-box {{
    border: 1px solid #000000;
    padding: 7pt 9pt;
    margin: 8pt 0 10pt 0;
    background: #FFFFFF;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .doc-box p {{
    margin: 0 0 3.5pt 0;
    font-size: 9.8pt;
    line-height: 1.34;
    text-align: justify;
  }}
  .doc-box .box-head {{
    font-size: 10.6pt;
    font-weight: 700;
    margin-bottom: 5pt;
  }}
  .doc-box .box-sub {{
    font-size: 10pt;
    font-weight: 700;
    margin-top: 4.5pt;
    margin-bottom: 2.5pt;
  }}
  /* Figuras */
  .figure-wrap {{
    margin: 10pt 0 12pt 0;
    padding: 6pt;
    border: 1px solid #CCCCCC;
    background: #FFFFFF;
    text-align: center;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .figure-wrap img {{
    max-width: 100%;
    height: auto;
    border: 1px solid #CCCCCC;
  }}
  .figure-caption {{
    font-size: 9.8pt;
    font-weight: 700;
    color: #000000;
    margin-top: 5pt;
    text-align: center;
  }}
  .figure-source {{
    font-size: 8.8pt;
    color: #333333;
    margin-top: 2pt;
    text-align: center;
  }}
  /* Sumário e Índices estilo Word oficial (pág. 8) */
  .toc-list {{
    list-style: none;
    padding: 0;
    margin: 4pt 0 10pt 0;
  }}
  .toc-list li {{
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dotted #000000;
    padding: 1.3pt 0;
    font-size: 9.4pt;
    text-align: left;
  }}
  .index-list li {{
    padding: 0.9pt 0;
    font-size: 8.9pt;
  }}
  .toc-l1 {{ font-weight: 700; margin-top: 3pt; }}
  .toc-l2 {{ padding-left: 14pt !important; }}
  .toc-l3 {{ padding-left: 28pt !important; }}
  code {{
    font-family: Consolas, "Courier New", monospace;
    font-size: 8.9pt;
  }}
  pre {{
    font-family: Consolas, "Courier New", monospace;
    font-size: 8.8pt;
    background: #FFFFFF;
    border: 1px solid #000000;
    padding: 7pt 9pt;
    white-space: pre-wrap;
    page-break-inside: avoid;
    break-inside: avoid;
  }}
  .abnt-ref p {{
    text-align: left;
    margin-bottom: 8pt;
    font-size: 10.5pt;
  }}
</style>
</head>
<body>

<!-- ==================== CAPA (PÁGINA 1 — FIEL AO MODELO FATEC) ==================== -->
<div class="cover">
  <div>
    <img class="cover-logo-fatec" src="{fatec_b64}" alt="Logo FATEC"/>
  </div>

  <div class="cover-center">
    <div class="cover-disc">Laboratório de Engenharia de Software</div>
    <div class="cover-prod">Website Vevey — Vevey Doces</div>
    <div class="cover-team">Vevey Co.</div>
  </div>

  <div class="cover-members">
    <div class="table-caption">Tabela 1 — Integrantes da equipe Vevey Co.</div>
    <table class="grid-table">
      <thead><tr><th>Nome</th><th style="width:125px;">RA</th></tr></thead>
      <tbody>
        <tr><td>Matheus da Silva Araújo</td><td>1680482421029</td></tr>
        <tr><td>Nathan Costa Santos</td><td>1680482421021</td></tr>
        <tr><td>Ricardo Martins Pighin</td><td>1680482421013</td></tr>
      </tbody>
    </table>
    <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
  </div>
</div>

<!-- ==================== RELATÓRIOS DE ANDAMENTO (1 PÁGINA POR ALUNO) ==================== -->
{rep_matheus}
{rep_nathan}
{rep_ricardo}

<!-- ==================== SUMÁRIO ==================== -->
<div class="page-break"></div>
<h1 style="margin-top:0;">Sumário</h1>
<ul class="toc-list">
  <li class="toc-l1"><span>1. Visão e Escopo do Projeto</span><span>7</span></li>
  <li class="toc-l2"><span>1.1. Descrição do problema</span><span>7</span></li>
  <li class="toc-l3"><span>1.1.1. Contexto do projeto</span><span>7</span></li>
  <li class="toc-l3"><span>1.1.2. Stakeholders</span><span>8</span></li>
  <li class="toc-l3"><span>1.1.3. Usuários</span><span>8</span></li>
  <li class="toc-l3"><span>1.1.4. Premissas</span><span>9</span></li>
  <li class="toc-l2"><span>1.2. Visão da solução</span><span>9</span></li>
  <li class="toc-l3"><span>1.2.1. Declaração de visão</span><span>9</span></li>
  <li class="toc-l3"><span>1.2.2. Cenários de utilização do sistema</span><span>9</span></li>
  <li class="toc-l3"><span>1.2.3. Features do produto</span><span>10</span></li>
  <li class="toc-l1"><span>2. Requisitos de software</span><span>11</span></li>
  <li class="toc-l2"><span>2.1. Casos de uso</span><span>11</span></li>
  <li class="toc-l3"><span>2.1.1. Documentação dos casos de uso</span><span>11</span></li>
  <li class="toc-l3"><span>2.1.2. Documentação dos atores</span><span>17</span></li>
  <li class="toc-l2"><span>2.2. Requisitos funcionais</span><span>19</span></li>
  <li class="toc-l2"><span>2.3. Requisitos não funcionais</span><span>20</span></li>
  <li class="toc-l2"><span>2.4. Protótipos do software</span><span>21</span></li>
  <li class="toc-l3"><span>2.4.1. Protótipos de telas</span><span>21</span></li>
  <li class="toc-l3"><span>2.4.2. Modelo de navegação</span><span>25</span></li>
  <li class="toc-l1"><span>3. Projeto do Sistema</span><span>27</span></li>
  <li class="toc-l2"><span>3.1. Modelo estrutural do sistema</span><span>27</span></li>
  <li class="toc-l3"><span>3.1.1. Diagrama de pacotes</span><span>27</span></li>
  <li class="toc-l3"><span>3.1.2. Diagramas de classes</span><span>28</span></li>
  <li class="toc-l2"><span>3.2. Modelo de banco de dados do sistema</span><span>34</span></li>
  <li class="toc-l2"><span>3.3. Modelo de comportamentos do sistema</span><span>39</span></li>
  <li class="toc-l1"><span>4. Documentação técnica do sistema</span><span>41</span></li>
  <li class="toc-l2"><span>4.1. Modelo de implementação do sistema</span><span>41</span></li>
  <li class="toc-l2"><span>4.2. Modelos de testes do sistema</span><span>41</span></li>
  <li class="toc-l2"><span>4.3. Modelos de implantação do sistema</span><span>42</span></li>
  <li class="toc-l2"><span>4.4. Elaboração do procedimento de instalação</span><span>43</span></li>
  <li class="toc-l2"><span>4.5. Elaboração da documentação técnica do sistema</span><span>44</span></li>
  <li class="toc-l2"><span>4.6. Elaboração do manual do usuário</span><span>45</span></li>
  <li class="toc-l1"><span>5. Referências bibliográficas</span><span>48</span></li>
</ul>

<!-- ==================== ÍNDICE DE FIGURAS E TABELAS ==================== -->
<div class="page-break"></div>
<h2 style="margin-top:0;margin-bottom:4pt;">Índice de Figuras</h2>
<ul class="toc-list index-list">
  <li><span>Figura 1 — Formulário Project Model Canvas do projeto Website Vevey</span><span>7</span></li>
  <li><span>Figura 2 — Diagrama de Casos de Uso do Sistema Vevey Doces</span><span>11</span></li>
  <li><span>Figura 3 — Tela 01 — Página Inicial da Loja Vevey Doces</span><span>22</span></li>
  <li><span>Figura 4 — Tela 02 — Catálogo de Produtos com filtros combinados (ADR 6)</span><span>23</span></li>
  <li><span>Figura 5 — Tela 03 — Página de Produto (PDP) com cores irmãs e estoque real</span><span>24</span></li>
  <li><span>Figura 6 — Tela 04 — Cadastro de Cliente com CPF e verificação em 2 etapas (2FA)</span><span>25</span></li>
  <li><span>Figura 7 — Diagrama UML de Atividade: Modelo de Navegação do Sistema</span><span>26</span></li>
  <li><span>Figura 8 — Diagrama de Pacotes do Monólito Modular (ADR 8)</span><span>27</span></li>
  <li><span>Figura 9 — Diagrama de Classes de Domínio do Sistema Vevey Doces</span><span>29</span></li>
  <li><span>Figura 10 — Diagrama Entidade-Relacionamento (DER) do PostgreSQL</span><span>35</span></li>
  <li><span>Figura 11 — Diagrama de Sequência: Cenário Realizar Compra Online (UC02)</span><span>39</span></li>
  <li><span>Figura 12 — Diagrama de Sequência: Cenário Registrar Venda PDV Balcão (UC04)</span><span>40</span></li>
  <li><span>Figura 13 — Diagrama de Artefatos («artifact») da Arquitetura de Implementação</span><span>41</span></li>
  <li><span>Figura 14 — Diagrama de Implantação UML (Docker, Render, PostgreSQL e APIs)</span><span>43</span></li>
</ul>

<h2 style="margin-top:8pt;margin-bottom:4pt;">Índice de Tabelas</h2>
<ul class="toc-list index-list">
  <li><span>Tabela 1 — Integrantes da equipe Vevey Co.</span><span>1</span></li>
  <li><span>Tabela 2 — Relatório de andamento: Matheus da Silva Araújo</span><span>2</span></li>
  <li><span>Tabela 3 — Relatório de andamento: Nathan Costa Santos</span><span>3</span></li>
  <li><span>Tabela 4 — Relatório de andamento: Ricardo Martins Pighin</span><span>4</span></li>
  <li><span>Tabela 5 — Stakeholders do projeto e suas necessidades</span><span>8</span></li>
  <li><span>Tabela 6 — Usuários do sistema, papéis RBAC e necessidades</span><span>8</span></li>
  <li><span>Tabela 7 — Requisitos Funcionais (RF001 a RF018)</span><span>20</span></li>
  <li><span>Tabela 8 — Requisitos Não Funcionais (RNF001 a RNF012)</span><span>21</span></li>
  <li><span>Tabela 9 — Especificação da tabela products</span><span>35</span></li>
  <li><span>Tabela 10 — Especificação da tabela categories e attributes</span><span>36</span></li>
  <li><span>Tabela 11 — Especificação da tabela stock_levels</span><span>36</span></li>
  <li><span>Tabela 12 — Especificação da tabela stock_movements</span><span>37</span></li>
  <li><span>Tabela 13 — Especificação da tabela stock_reservations</span><span>37</span></li>
  <li><span>Tabela 14 — Especificação da tabela orders e order_items</span><span>38</span></li>
  <li><span>Tabela 15 — Especificação da tabela coupons, payments e order_status_histories</span><span>38</span></li>
  <li><span>Tabela 16 — Especificação da tabela customers, customer_identities e addresses</span><span>39</span></li>
  <li><span>Tabela 17 — Organização dos diretórios e artefatos de implementação</span><span>41</span></li>
  <li><span>Tabela 18 — Matriz de Rastreabilidade de Casos de Teste do Sistema (TC01 a TC10)</span><span>42</span></li>
  <li><span>Tabela 19 — Resumo das Decisões Arquiteturais (ADRs 1 a 9)</span><span>45</span></li>
  <li><span>Tabela 20 — Credenciais de homologação para testes e demonstração</span><span>46</span></li>
  <li><span>Tabela 21 — Guia rápido de resolução de problemas</span><span>47</span></li>
</ul>

<!-- ==================== 1. VISÃO E ESCOPO DO PROJETO ==================== -->
<div class="page-break"></div>
<h1 style="margin-top:0;">1. Visão e Escopo do Projeto</h1>

<h2>1.1. Descrição do problema</h2>

<h3>1.1.1. Contexto do projeto</h3>
<p>A <strong>Vevey Doces</strong> é uma loja especializada na comercialização de chocolates em caixa, balas, embalagens de papelão e artigos para festas, atendendo tanto consumidores finais no varejo quanto organizadores de eventos e revendedores em caixas fechadas. Historicamente, a divulgação ocorria pelo Instagram, mas todo o processo de consulta de preço, verificação de disponibilidade e fechamento de pedidos era conduzido manualmente via WhatsApp Business e planilhas eletrônicas. Como as vendas presenciais na loja física não davam baixa integrada em um estoque único, ocorriam vendas simultâneas de itens já esgotados na prateleira, atrasos no atendimento e ausência de indicadores gerenciais confiáveis.</p>
<p>Existem no mercado plataformas de e-commerce de prateleira (como Nuvemshop, Loja Integrada e Tray) que resolvem a publicação de catálogos online. Contudo, decidiu-se pelo desenvolvimento de um sistema próprio sob medida por três razões objetivas: (1) necessidade de um estoque unificado com ledger imutável integrando vendas online e PDV de balcão físico sem custos recorrentes de ERPs externos; (2) domínio total sobre a modelagem de catálogo plano por cores/atributos (ADR 6) e regras comerciais específicas (desconto Pix, cupons e caixas fechadas); e (3) propriedade integral dos dados de clientes, identidades aposentáveis e histórico auditável de operações. A Figura 1 apresenta o formulário Project Model Canvas que justifica o contexto do projeto.</p>

<div class="figure-wrap">
  {SVG_CANVAS}
  <div class="figure-caption">Figura 1 — Formulário Project Model Canvas do projeto Website Vevey</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>1.1.2. Stakeholders</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 5 — Stakeholders do projeto e suas necessidades</div>
  <table class="grid-table">
    <thead><tr><th style="width:185px;">Stakeholder</th><th>Necessidades principais</th></tr></thead>
    <tbody>
      <tr><td><strong>Proprietária da Vevey Doces</strong></td><td>Aumentar as vendas, reduzir retrabalho manual no WhatsApp, profissionalizar a identidade digital da loja e acompanhar faturamento, ticket médio e produtos mais vendidos.</td></tr>
      <tr><td><strong>Gerência de Vendas</strong></td><td>Gerenciar catálogo, promoções, cupons e acompanhar pedidos de todos os canais (Site, PDV Balcão e WhatsApp) com estoque unificado e auditável.</td></tr>
      <tr><td><strong>Equipe de Atendimento</strong></td><td>Registrar vendas presenciais rapidamente no PDV Balcão, localizar clientes por telefone (inclusive números antigos) e consultar disponibilidade em tempo real.</td></tr>
      <tr><td><strong>Equipe de Logística</strong></td><td>Visualizar pedidos pagos para separação, atualizar status de expedição/entrega e lançar entradas de fornecedores ou contagens físicas no ledger.</td></tr>
      <tr><td><strong>Cliente Final / Revendedor</strong></td><td>Pesquisar doces e itens de festa por nome, SKU ou cor, conferir estoque real, calcular frete com data prevista e acompanhar pedidos na sua conta.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>1.1.3. Usuários</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 6 — Usuários do sistema, papéis RBAC e necessidades</div>
  <table class="grid-table">
    <thead><tr><th style="width:165px;">Papel / Guard</th><th>Necessidades em relação ao produto</th></tr></thead>
    <tbody>
      <tr><td><strong>Cliente</strong> (Guard <code>customers</code>)</td><td>Navegar no catálogo, filtrar por cor e tipo, comprar com Pix ou cartão, aplicar cupom, verificar conta em 2 etapas (2FA) e acompanhar entregas.</td></tr>
      <tr><td><strong>Atendente</strong> (RBAC <code>attendant</code>)</td><td>Operar o PDV Balcão (<code>/admin/pdv</code>), consultar estoque em tempo real, localizar clientes por telefone e gerenciar retiradas.</td></tr>
      <tr><td><strong>Logística</strong> (RBAC <code>logistics</code>)</td><td>Separar e expedir pedidos pagos, lançar entradas de mercadorias e executar contagens físicas no ledger de estoque.</td></tr>
      <tr><td><strong>Administrador</strong> (RBAC <code>admin</code> / <code>super_admin</code>)</td><td>Gerir catálogo, preços, cupons, papéis RBAC, monitorar indicadores gerenciais no Dashboard e auditar todas as ações no log unificado.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<p><strong>Personas documentadas para justificativa dos usuários:</strong></p>
<ul>
  <li><strong>Persona 1 — Carla (34 anos, Organizadora de Eventos — Papel: Cliente):</strong> Planeja festas infantis e corporativas semanalmente; utiliza o celular entre reuniões. Precisa encontrar balões e embalagens pela cor exata da decoração, saber se há quantidade real em estoque e receber previsão confiável de entrega.</li>
  <li><strong>Persona 2 — Juliana (30 anos, Atendente — Papel: Atendente):</strong> Atua no balcão da loja física e responde clientes no WhatsApp. Frustra-se ao perder tempo consultando planilhas desatualizadas; precisa registrar vendas presenciais em poucos cliques no PDV.</li>
  <li><strong>Persona 3 — Marcos (28 anos, Auxiliar de Estoque — Papel: Logística):</strong> Recebe caixas de fornecedores e separa pedidos de entrega/retirada. Precisa lançar entradas e contagens físicas de forma simples, garantindo que a loja virtual reflita a prateleira.</li>
  <li><strong>Persona 4 — Miriam (60 anos, Sócia-Gerente — Papel: Administrador):</strong> Responsável financeira e comercial da Vevey Doces. Busca clareza gerencial no Dashboard (faturamento por canal, top produtos, alertas de estoque) e rastreabilidade sobre alterações feitas pela equipe.</li>
</ul>

<h3>1.1.4. Premissas</h3>
<ul>
  <li><strong>Stack tecnológica:</strong> Aplicação construída em PHP 8.4, Laravel 13, Filament 5, Tailwind CSS v4 e PostgreSQL 16 sob arquitetura de monólito modular com 7 pacotes (ADR 8).</li>
  <li><strong>Precisão financeira (ADR 1):</strong> Todos os valores monetários são armazenados em centavos inteiros (<code>bigint</code>) manipulados via <code>brick/money</code> com restrições <code>CHECK</code> no PostgreSQL.</li>
  <li><strong>Operação multicanal unificada:</strong> Todo pedido (Site, PDV Balcão ou WhatsApp) passa pelo serviço central <code>PlaceOrder</code> e compartilha o mesmo ledger de estoque.</li>
  <li><strong>Ambiente acadêmico de pagamentos e mensageria:</strong> O checkout dispõe de fluxo Pix Sandbox funcional para demonstração, enquanto e-mails 2FA utilizam Resend HTTP API com fallback automático de homologação.</li>
</ul>

<h2>1.2. Visão da solução</h2>

<h3>1.2.1. Declaração de visão</h3>
<p>Para a equipe comercial/operacional da Vevey Doces e seus clientes de varejo e eventos que enfrentam conflitos de estoque e lentidão no atendimento manual por WhatsApp, o <strong>Website Vevey</strong> é uma plataforma web modular de e-commerce e gestão operacional que integra vitrine pública, PDV de balcão, controle de estoque auditável por ledger, contas de clientes com verificação em duas etapas e painel gerencial em tempo real. Diferentemente de soluções genéricas de prateleira, o sistema garante sincronia atômica entre prateleira física e loja virtual com propriedade total dos dados e zero custo de licenciamento.</p>

<h3>1.2.2. Cenários de utilização do sistema</h3>

<h4>1.2.2.1. Cenário 1 — Compra online pela loja virtual</h4>
<p>Carla acessa a loja pelo link do Instagram, abre o catálogo, filtra balões pela cor Azul e tipo Metalizado, confere que há 50 unidades em estoque real e adiciona 2 pacotes e 1 caixa fechada de chocolates ao carrinho. No checkout, informa o CEP (autopreenchido via BrasilAPI/ViaCEP), visualiza as opções SEDEX e PAC com data prevista pulando feriados nacionais, aplica o cupom <code>VEVEY10</code> e finaliza via Pix (5% OFF). O sistema reserva o estoque por 30 minutos e exibe o QR Code Pix; após aprovação, a reserva vira baixa definitiva no ledger.</p>

<h4>1.2.2.2. Cenário 2 — Cadastro de cliente com verificação em 2 etapas (2FA)</h4>
<p>Um cliente que já comprou anteriormente pelo WhatsApp decide criar sua conta em <code>/cadastro</code>. Ele informa nome, e-mail, WhatsApp, CPF e senha. O sistema valida o CPF pelo algoritmo Módulo 11, vincula suas identidades de contato e envia um código OTP de 6 dígitos (válido por 15 minutos). Ao confirmar o código em <code>/verificar-conta</code>, sua conta é ativada e ele passa a visualizar todos os seus pedidos e gerenciar endereços salvos em <code>/conta</code>.</p>

<h4>1.2.2.3. Cenário 3 — Venda presencial no PDV Balcão</h4>
<p>Um cliente comparece à loja física para comprar chocolates e embalagens. Juliana abre <code>/admin/pdv</code>, pesquisa os produtos por nome ou código de barras, seleciona pagamento em Dinheiro ou Pix e conclui a venda. O sistema registra um pedido de canal <code>pos</code> já pago e entregue, dando baixa imediata no ledger. Caso alguma mercadoria recém-chegada ainda não tenha sido lançada no estoque, o sistema conclui a venda sem travar a fila e exibe alerta visual de saldo negativo.</p>

<h4>1.2.2.4. Cenário 4 — Reposição de estoque, expedição e auditoria</h4>
<p>Marcos recebe uma entrega de fornecedor, acessa <code>/admin/stock-levels</code> e aciona <em>Lançar entrada</em> informando a quantidade e o motivo; depois separa os pedidos pagos em <code>/admin/orders</code> avançando o status para enviado/entregue. Ao final do dia, Miriam analisa o faturamento por canal e os próximos feriados no Dashboard e consulta em <code>/admin/audit-log</code> quem executou cada movimentação de estoque e alteração de pedido.</p>

<h3>1.2.3. Features do produto</h3>

<h4>1.2.3.1. Catálogo plano com busca full-text e filtros combinados (ADR 6)</h4>
<p>Vitrine responsiva com busca preditiva ao vivo, índice GIN em português, filtros combinados por atributos dinâmicos (OU no mesmo atributo, E entre atributos diferentes) e seletor de cores irmãs na página do produto.</p>

<h4>1.2.3.2. Carrinho e checkout inteligente com frete real e Pix Sandbox</h4>
<p>Carrinho lateral com barra de frete grátis (R$ 150,00), consulta de CEP (BrasilAPI com fallback ViaCEP), cálculo de dias úteis pulando feriados nacionais, validação ao vivo de cupons e pagamento Pix Sandbox com rateio exato em centavos.</p>

<h4>1.2.3.3. Controle de estoque auditável por ledger e reservas (ADR 2)</h4>
<p>Movimentações imutáveis em <code>stock_movements</code>, reservas automáticas de 30 minutos no checkout sob <code>lockForUpdate</code> ordenado e reconciliação via comando <code>php artisan stock:rebuild</code>.</p>

<h4>1.2.3.4. PDV Balcão integrado ao estoque único (ADR 5)</h4>
<p>Tela operacional em <code>/admin/pdv</code> que transforma vendas físicas em pedidos <code>pos</code> pagos e entregues, com baixa direta no ledger e tolerância controlada a saldo negativo com alerta.</p>

<h4>1.2.3.5. Área do cliente com guard isolado, 2FA e identidades aposentáveis (ADR 7)</h4>
<p>Autenticação segregada (guard <code>customers</code>), validação matemática de CPF (LGPD), verificação por OTP de 6 dígitos, histórico de pedidos e preservação de telefones antigos para busca no balcão.</p>

<h4>1.2.3.6. Painel gerencial RBAC e logs de auditoria unificados</h4>
<p>Controle de acesso estrito para 4 papéis (140 permissões sincronizadas), Dashboard com KPIs e feriados sazonais, e linha do tempo de auditoria (<code>/admin/audit-log</code>).</p>

<!-- ==================== 2. REQUISITOS DE SOFTWARE ==================== -->
<div class="page-break"></div>
<h1 style="margin-top:0;">2. Requisitos de software</h1>

<h2>2.1. Casos de uso</h2>
<p>A Figura 2 apresenta o diagrama de casos de uso do sistema Vevey Doces, contemplando os oito casos de uso principais (<code>UC01</code> a <code>UC08</code>), seus quatro atores primários e os relacionamentos de inclusão (<code>«include»</code>) e extensão (<code>«extend»</code>).</p>

<div class="figure-wrap">
  {SVG_USE_CASES}
  <div class="figure-caption">Figura 2 — Diagrama de Casos de Uso do Sistema Vevey Doces</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>2.1.1. Documentação dos casos de uso</h3>
<p>A seguir são detalhados integralmente todos os oito casos de uso representados na Figura 2, seguindo o padrão estruturado da disciplina.</p>
{ucs_html}

<h3>2.1.2. Documentação dos atores</h3>
<p>A seguir são especificados os cinco atores (quatro primários humanos e um ator secundário representando os serviços externos) que interagem com o sistema.</p>
{actors_html}

<h2>2.2. Requisitos funcionais</h2>
<p>A Tabela 7 sintetiza os 18 requisitos funcionais implementados no sistema e sua rastreabilidade direta aos casos de uso documentados na Seção 2.1.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 7 — Requisitos Funcionais (RF001 a RF018)</div>
  <table class="grid-table compact-table">
    <thead><tr><th style="width:92px;">ID do Requisito</th><th>Descrição</th><th style="width:130px;">Caso de Uso Relacionado</th></tr></thead>
    <tbody>
      <tr><td>RF001</td><td>O sistema deverá permitir cadastrar, editar, duplicar e inativar (soft delete) produtos planos com SKU único, preço e preço promocional.</td><td>UC05</td></tr>
      <tr><td>RF002</td><td>O sistema deverá organizar produtos em categorias hierárquicas (até 2 níveis) e atributos dinâmicos (Cor, Tipo) para filtros combinados.</td><td>UC01, UC05</td></tr>
      <tr><td>RF003</td><td>O sistema deverá prover busca full-text em português (GIN) e busca preditiva ao vivo por nome, descrição, SKU e código de barras.</td><td>UC01</td></tr>
      <tr><td>RF004</td><td>O sistema deverá exibir disponibilidade real de estoque nos cards e na página do produto, bloqueando compra online quando esgotado.</td><td>UC01, UC02</td></tr>
      <tr><td>RF005</td><td>O sistema deverá disponibilizar seletor de cores irmãs na página do produto (PDP) vinculando produtos do mesmo grupo.</td><td>UC01</td></tr>
      <tr><td>RF006</td><td>O sistema deverá gerenciar carrinho de compras com ajuste rápido de quantidades e barra de progresso para frete grátis (R$ 150,00).</td><td>UC01, UC02</td></tr>
      <tr><td>RF007</td><td>O sistema deverá consultar CEP (BrasilAPI com fallback ViaCEP) e calcular frete (Retirada, SEDEX e PAC) com previsão em dias úteis reais.</td><td>UC02</td></tr>
      <tr><td>RF008</td><td>O sistema deverá validar e aplicar cupons promocionais (percentual/fixo, pedido mínimo, limite de usos e vigência) acumulados com 5% Pix.</td><td>UC02, UC05</td></tr>
      <tr><td>RF009</td><td>O sistema deverá persistir pedidos com snapshot imutável dos itens, ajustes, pagamentos 1:N e rateio exato de desconto por linha.</td><td>UC02, UC04</td></tr>
      <tr><td>RF010</td><td>O sistema deverá criar reservas de estoque de 30 minutos no checkout, convertendo em baixa <code>sale</code> na confirmação do pagamento ou liberando no cancelamento.</td><td>UC02, UC08</td></tr>
      <tr><td>RF011</td><td>O sistema deverá disponibilizar tela de PDV Balcão (<code>/admin/pdv</code>) registrando vendas presenciais como pedidos <code>pos</code> pagos e entregues.</td><td>UC04</td></tr>
      <tr><td>RF012</td><td>O sistema deverá permitir entradas de fornecedores e contagens físicas exclusivamente por lançamentos imutáveis no ledger de estoque.</td><td>UC07</td></tr>
      <tr><td>RF013</td><td>O sistema deverá permitir cadastro e login de clientes em guard separado com validação de CPF (Módulo 11) e verificação 2FA por código OTP de 6 dígitos.</td><td>UC03</td></tr>
      <tr><td>RF014</td><td>O sistema deverá armazenar identidades de contato normalizadas (E.164 e e-mail) com aposentadoria (<code>retired_at</code>) e busca por números antigos.</td><td>UC03, UC04</td></tr>
      <tr><td>RF015</td><td>O sistema deverá oferecer área <em>Minha Conta</em> (<code>/conta</code>) listando apenas os pedidos do cliente autenticado e seus endereços salvos.</td><td>UC02, UC03</td></tr>
      <tr><td>RF016</td><td>O sistema deverá permitir à equipe de logística e atendimento atualizar status de separação, envio e entrega com histórico auditável.</td><td>UC08</td></tr>
      <tr><td>RF017</td><td>O sistema deverá exibir Dashboard gerencial com faturamento, ticket médio, vendas por canal, top produtos, alertas de estoque e próximos feriados.</td><td>UC06</td></tr>
      <tr><td>RF018</td><td>O sistema deverá prover painel unificado de Logs de Auditoria (<code>/admin/audit-log</code>) registrando responsável, data/hora e motivo das alterações.</td><td>UC06, UC07, UC08</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>2.3. Requisitos não funcionais</h2>
<p>A Tabela 8 especifica as propriedades de qualidade, segurança, desempenho e manutenibilidade exigidas pelo sistema.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 8 — Requisitos Não Funcionais (RNF001 a RNF012)</div>
  <table class="grid-table compact-table">
    <thead><tr><th style="width:92px;">ID do Requisito</th><th>Descrição</th><th style="width:130px;">Caso de Uso Relacionado</th></tr></thead>
    <tbody>
      <tr><td>RNF001</td><td>Todos os valores monetários deverão ser armazenados em centavos inteiros (<code>bigint</code>) via <code>brick/money</code> com restrições <code>CHECK</code> no PostgreSQL.</td><td>UC02, UC04, UC05</td></tr>
      <tr><td>RNF002</td><td>Operações concorrentes de estoque deverão ser protegidas por transações ACID e <code>lockForUpdate</code> ordenado por <code>product_id</code> para evitar deadlocks.</td><td>UC02, UC04, UC07</td></tr>
      <tr><td>RNF003</td><td>A tabela <code>stock_movements</code> deverá garantir idempotência por índice único composto impedindo baixas duplicadas para o mesmo pedido.</td><td>UC02, UC04, UC08</td></tr>
      <tr><td>RNF004</td><td>O sistema deverá segregar estritamente a autenticação da equipe (guard <code>web</code>) e dos clientes (guard <code>customers</code>), com senhas em hash bcrypt/Argon2.</td><td>UC03, UC04, UC05, UC06</td></tr>
      <tr><td>RNF005</td><td>O painel administrativo deverá operar em modo <code>strictAuthorization</code> com controle RBAC para 4 papéis e policies em todos os models.</td><td>UC04, UC05, UC06, UC07, UC08</td></tr>
      <tr><td>RNF006</td><td>Todas as rotas web deverão manter proteção CSRF ativa e bloqueio por <code>RateLimiter</code> após 5 tentativas falhas de login.</td><td>UC02, UC03</td></tr>
      <tr><td>RNF007</td><td>Em conformidade com a LGPD, a validação de CPF deverá ser executada localmente sem consulta a dados pessoais em APIs de terceiros, utilizando soft delete para registros sensíveis.</td><td>UC03, UC05</td></tr>
      <tr><td>RNF008</td><td>A busca no catálogo deverá utilizar índice GIN sobre coluna gerada <code>tsvector</code>, e consultas a APIs externas deverão utilizar cache de 24h a 7 dias.</td><td>UC01, UC02, UC06</td></tr>
      <tr><td>RNF009</td><td>As fronteiras entre os 7 módulos do monólito modular deverão ser verificadas automaticamente por testes de arquitetura Pest (<code>ModuleDependenciesTest</code>).</td><td>UC01 a UC08</td></tr>
      <tr><td>RNF010</td><td>A interface pública deverá ser responsiva (desktop e mobile) com tipografia tabular (<code>tabular-nums</code>) nos valores monetários.</td><td>UC01, UC02, UC03</td></tr>
      <tr><td>RNF011</td><td>Toda alteração de estoque ou transição de status de pedido deverá registrar usuário responsável, data/hora e motivo de forma auditável.</td><td>UC04, UC06, UC07, UC08</td></tr>
      <tr><td>RNF012</td><td>O sistema deverá disponibilizar empacotamento Docker e blueprint de implantação automatizada garantindo reprodutibilidade entre ambientes.</td><td>UC01 a UC08</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>2.4. Protótipos do software</h2>

<h3>2.4.1. Protótipos de telas</h3>
<p>As Figuras 3 a 6 apresentam capturas reais das interfaces implementadas, numeradas de <strong>Tela 01</strong> a <strong>Tela 04</strong> para associação direta ao Modelo de Navegação (Seção 2.4.2) e ao Manual do Usuário (Seção 4.6.4).</p>

<div class="figure-wrap">
  <img src="{shot_home}" alt="Tela 01 — Página Inicial"/>
  <div class="figure-caption">Figura 3 — Tela 01 — Página Inicial da Loja Vevey Doces</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>
<p><strong>Elementos da Tela 01 (Página Inicial — <code>/</code>):</strong> barra superior informativa (frete grátis acima de R$ 150,00, 5% OFF no Pix e retirada no mesmo dia); logotipo oficial Vevey Doces; barra de busca preditiva em pílula por nome, SKU ou código de barras; atalhos de acesso a <em>Ofertas</em>, <em>Painel</em>, <em>Minha conta</em> e <em>Carrinho</em> com contador; barra de departamentos; banner Hero editorial com destaque ao cupom <code>VEVEY10</code> e botões de ação direta para o catálogo.</p>

<div class="figure-wrap">
  <img src="{shot_catalog}" alt="Tela 02 — Catálogo de Produtos"/>
  <div class="figure-caption">Figura 4 — Tela 02 — Catálogo de Produtos com filtros combinados (ADR 6)</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>
<p><strong>Elementos da Tela 02 (Catálogo — <code>/catalogo</code>):</strong> trilha de navegação (breadcrumb); seletor de ordenação (relevância, menor/maior preço); barra lateral de departamentos, filtro <em>Somente ofertas</em> e filtros dinâmicos por atributo (Cor com amostras visuais e contagem); grade de cards exibindo badge de desconto, selo <em>Caixa fechada</em>, preço regular/Pix, seletor rápido de quantidade (<code>- 1 +</code>) e botão <em>Adicionar</em> bloqueado automaticamente se esgotado.</p>

<div class="figure-wrap">
  <img src="{shot_product}" alt="Tela 03 — Página de Produto"/>
  <div class="figure-caption">Figura 5 — Tela 03 — Página de Produto (PDP) com cores irmãs e estoque real</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>
<p><strong>Elementos da Tela 03 (Página de Produto — <code>/produto/{{slug}}</code>):</strong> galeria fotográfica em alta resolução com selo de desconto; categoria e SKU técnico; preço regular, preço promocional e cálculo automático de 5% OFF no Pix; seletor de <em>Outras cores disponíveis</em> (navegando entre produtos irmãos); indicador em tempo real de saldo disponível em estoque; seletor de quantidade, botão <em>Adicionar ao carrinho</em> e calculadora rápida de frete por CEP.</p>

<div class="figure-wrap">
  <img src="{shot_register}" alt="Tela 04 — Cadastro e 2FA"/>
  <div class="figure-caption">Figura 6 — Tela 04 — Cadastro de Cliente com CPF e verificação em 2 etapas (2FA)</div>
  <div class="figure-source">Fonte: Captura real do sistema desenvolvido (2026).</div>
</div>
<p><strong>Elementos da Tela 04 (Cadastro de Cliente — <code>/cadastro</code>):</strong> formulário centralizado com campos de nome completo, e-mail (para vínculo automático com pedidos anteriores do WhatsApp/site), WhatsApp/Telefone com máscara, CPF opcional validado por Módulo 11, senha e confirmação de senha, botão <em>Criar minha conta</em> (que dispara o código OTP de 6 dígitos para <code>/verificar-conta</code>) e link de redirecionamento para login.</p>

<h3>2.4.2. Modelo de navegação</h3>
<p>A Figura 7 ilustra o modelo de navegação elaborado como um <strong>Diagrama UML de Atividade</strong>. Os retângulos arredondados representam as telas numeradas (<strong>Tela 01</strong> a <strong>Tela 08</strong>), os losangos representam decisões de navegação disparadas por eventos do usuário (como <code>[Catálogo]</code>, <code>[Minha conta]</code>, <code>[escolhe "PDV"]</code> e <code>[escolhe "Estoque"]</code>), e os arcos orientados indicam os caminhos percorridos desde o nó inicial até o encerramento do fluxo.</p>

<div class="figure-wrap">
  {SVG_NAVIGATION}
  <div class="figure-caption">Figura 7 — Diagrama UML de Atividade: Modelo de Navegação do Sistema</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== 3. PROJETO DO SISTEMA ==================== -->
<div class="page-break"></div>
<h1 style="margin-top:0;">3. Projeto do Sistema</h1>

<h2>3.1. Modelo estrutural do sistema</h2>

<h3>3.1.1. Diagrama de pacotes</h3>
<p>O sistema foi estruturado segundo o critério combinado de <strong>Camadas</strong> e <strong>Domínio (DDD / Monólito Modular — ADR 8)</strong>, utilizando <code>internachi/modular</code>. A Figura 8 apresenta os sete pacotes lógicos e suas dependências unidirecionais (<code>«use»</code>), seguidos pela especificação individual de cada pacote.</p>

<div class="figure-wrap">
  {SVG_PACKAGES}
  <div class="figure-caption">Figura 8 — Diagrama de Pacotes do Monólito Modular (ADR 8)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

{pkgs_html}

<h3>3.1.2. Diagramas de classes</h3>
<p>A Figura 9 apresenta o Diagrama de Classes UML das principais entidades de domínio do sistema, com visibilidade pública (<code>+</code>), tipos estritos (<code>Money</code>, enums PHP, <code>uuid</code>) e multiplicidades entre os objetos. Na sequência, as cinco classes centrais de negócio são especificadas segundo o modelo completo da disciplina.</p>

<div class="figure-wrap">
  {SVG_CLASSES}
  <div class="figure-caption">Figura 9 — Diagrama de Classes de Domínio do Sistema Vevey Doces</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

{classes_html}

<h2>3.2. Modelo de banco de dados do sistema</h2>
<p>Por adotar o SGBD relacional <strong>PostgreSQL 16</strong>, o modelo de persistência é representado pelo Diagrama Entidade-Relacionamento (DER) da Figura 10, evidenciando chaves primárias (<code>PK</code>), chaves estrangeiras (<code>FK</code>) e cardinalidades. Todas as tabelas empregam chaves primárias UUID v7, restrições <code>CHECK</code> de integridade financeira, colunas geradas <code>STORED</code> e ausência de triggers (lógica concentrada na camada de domínio). As Tabelas 9 a 16 detalham os atributos de cada entidade.</p>

<div class="figure-wrap">
  {SVG_DER}
  <div class="figure-caption">Figura 10 — Diagrama Entidade-Relacionamento (DER) do PostgreSQL</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

{db_tables_html}

<h2>3.3. Modelo de comportamentos do sistema</h2>
<p>Os diagramas de sequência a seguir demonstram dinamicamente como os objetos das classes especificadas na Seção 3.1.2 colaboram para realizar os dois cenários operacionais mais críticos do sistema.</p>

<h3>3.3.1. Cenário: Realizar Compra Online (Checkout e Baixa no Ledger — UC02)</h3>
<p>A Figura 11 detalha a troca de mensagens desde a submissão do checkout pelo cliente até a gravação do pedido com snapshot, criação de reservas temporárias sob <code>lockForUpdate</code> e conversão definitiva em movimento <code>sale</code> no ledger após a confirmação do pagamento.</p>

<div class="figure-wrap">
  {SVG_SEQ_CHECKOUT}
  <div class="figure-caption">Figura 11 — Diagrama de Sequência: Cenário Realizar Compra Online (UC02)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h3>3.3.2. Cenário: Registrar Venda Presencial no PDV Balcão (UC04)</h3>
<p>A Figura 12 ilustra o fluxo síncrono de venda presencial no balcão (<code>/admin/pdv</code>), onde o pedido nasce com status pago e entregue e aciona diretamente <code>RecordDirectSale</code>, emitindo alerta caso algum item fique com saldo negativo (ADR 5).</p>

<div class="figure-wrap">
  {SVG_SEQ_PDV}
  <div class="figure-caption">Figura 12 — Diagrama de Sequência: Cenário Registrar Venda PDV Balcão (UC04)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== 4. DOCUMENTAÇÃO TÉCNICA DO SISTEMA ==================== -->
<div class="page-break"></div>
<h1 style="margin-top:0;">4. Documentação técnica do sistema</h1>

<h2>4.1. Modelo de implementação do sistema</h2>
<p>O código-fonte foi implementado em <strong>PHP 8.4</strong> com <strong>Laravel 13</strong>, <strong>Filament 5</strong>, <strong>Tailwind CSS v4</strong> e <strong>Vite 8</strong>, utilizando <code>declare(strict_types=1)</code>, DTOs imutáveis (<code>final readonly</code>), casts monetários (<code>MoneyCast</code>) e gerenciamento de imagens em formato WebP (<code>spatie/laravel-medialibrary</code>). A Figura 13 apresenta o Diagrama de Artefatos (<code>«artifact»</code>) e a Tabela 17 descreve a organização de diretórios e pacotes.</p>

<div class="figure-wrap">
  {SVG_ARTIFACTS}
  <div class="figure-caption">Figura 13 — Diagrama de Artefatos («artifact») da Arquitetura de Implementação</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<div class="table-wrap">
  <div class="table-caption">Tabela 17 — Organização dos diretórios e artefatos de implementação</div>
  <table class="grid-table">
    <thead><tr><th style="width:165px;">Diretório / Artefato</th><th>Descrição e papel na arquitetura</th></tr></thead>
    <tbody>
      <tr><td><code>app-modules/*</code></td><td>Contém os 7 pacotes independentes (<code>catalog</code>, <code>inventory</code>, <code>customers</code>, <code>orders</code>, <code>permissions</code>, <code>panel-admin</code>, <code>panel-site</code>), cada qual com seus próprios <code>src/</code>, <code>database/migrations/</code>, <code>tests/</code> e <code>composer.json</code>.</td></tr>
      <tr><td><code>app/</code> e <code>routes/</code></td><td>Camada compartilhada com <code>User</code>, <code>MoneyCast</code>, <code>BrasilApiService</code>, <code>TransactionalMailer</code>, rotas HTTP (<code>web.php</code>) e agendador de tarefas (<code>console.php</code>).</td></tr>
      <tr><td><code>resources/</code> e <code>public/build/</code></td><td>Templates Blade da vitrine pública, logotipo SVG (<code>logo.blade.php</code>), estilos Tailwind CSS v4 e ativos compilados pelo Vite 8.</td></tr>
      <tr><td><code>tests/Arch/</code></td><td>Suíte Pest Arch (<code>ModuleDependenciesTest.php</code>) que garante automaticamente as fronteiras de dependência da ADR 8.</td></tr>
      <tr><td><code>Dockerfile</code> e <code>render.yaml</code></td><td>Artefatos de empacotamento em container Linux/PHP e blueprint declarativo para implantação automatizada no Render.com.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>4.2. Modelos de testes do sistema</h2>
<p>A validação do sistema final combinou testes automatizados de arquitetura (Pest Arch), testes funcionais de domínio (Pest Feature) e testes ponta a ponta sobre o banco PostgreSQL. A Tabela 18 apresenta a Matriz de Rastreabilidade mapeando todos os casos de uso (<code>UC01</code> a <code>UC08</code>) aos respectivos casos de teste executados.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 18 — Matriz de Rastreabilidade de Casos de Teste do Sistema (TC01 a TC10)</div>
  <table class="grid-table compact-table">
    <thead>
      <tr>
        <th style="width:44px;">ID Teste</th>
        <th style="width:52px;">Caso de Uso</th>
        <th style="width:115px;">Título do Teste</th>
        <th style="width:105px;">Pré-condição</th>
        <th>Passos</th>
        <th style="width:128px;">Resultado Esperado</th>
        <th style="width:62px;">Status</th>
      </tr>
    </thead>
    <tbody>
      <tr><td>TC01</td><td>UC01</td><td>Filtro combinado ADR 6</td><td>Produtos ativos com Cor e Tipo</td><td>1. Abrir <code>/catalogo</code><br/>2. Marcar Azul e Metalizado</td><td>Exibe apenas itens com Cor=Azul E Tipo=Metalizado</td><td><strong>Passou</strong></td></tr>
      <tr><td>TC02</td><td>UC02</td><td>Checkout Web com Cupom e Pix</td><td>Carrinho com itens e saldo disponível</td><td>1. Informar CEP<br/>2. Aplicar <code>VEVEY10</code><br/>3. Finalizar no Pix</td><td>Pedido persistido, desconto rateado e reserva 30 min criada</td><td><strong>Passou</strong></td></tr>
      <tr><td>TC03</td><td>UC02</td><td>Bloqueio de venda sem saldo</td><td>Produto com saldo zero</td><td>1. Tentar finalizar pedido web acima do saldo</td><td>Servidor retorna HTTP 422 bloqueando venda sem lastro</td><td><strong>Passou</strong></td></tr>
      <tr><td>TC04</td><td>UC03</td><td>Cadastro com CPF e 2FA</td><td>E-mail ainda não verificado</td><td>1. Cadastrar com CPF válido<br/>2. Inserir OTP errado e depois correto</td><td>OTP errado rejeitado; OTP correto ativa conta e abre <code>/conta</code></td><td><strong>Passou</strong></td></tr>
      <tr><td>TC05</td><td>UC04</td><td>Venda presencial PDV Balcão</td><td>Atendente logado em <code>/admin/pdv</code></td><td>1. Selecionar itens<br/>2. Escolher pagamento<br/>3. Concluir venda</td><td>Pedido <code>pos</code> pago/entregue e baixa <code>sale</code> gravada no ledger</td><td><strong>Passou</strong></td></tr>
      <tr><td>TC06</td><td>UC05</td><td>Cadastro de produto e cupom</td><td>Admin logado em <code>/admin</code></td><td>1. Criar produto com SKU único<br/>2. Criar cupom válido</td><td>Produto indexado no <code>search_vector</code> e cupom ativo na loja</td><td><strong>Passou</strong></td></tr>
      <tr><td>TC07</td><td>UC06</td><td>Auditoria e controle RBAC</td><td>SuperAdmin e Atendente cadastrados</td><td>1. Acessar <code>/admin/audit-log</code> como SuperAdmin e como Atendente</td><td>SuperAdmin vê linha do tempo; Atendente recebe HTTP 403</td><td><strong>Passou</strong></td></tr>
      <tr><td>TC08</td><td>UC07</td><td>Contagem física no ledger</td><td>Logística logada em <code>/admin/stock-levels</code></td><td>1. Acionar Contagem/Ajustar<br/>2. Informar saldo contado e motivo</td><td>Delta gravado como <code>adjustment</code> no ledger e saldo atualizado</td><td><strong>Passou</strong></td></tr>
      <tr><td>TC09</td><td>UC08</td><td>Separação e entrega de pedido</td><td>Pedido pago pendente</td><td>1. Abrir pedido em <code>/admin/orders</code><br/>2. Avançar status para entregue</td><td>Status atualizado e registrado em <code>order_status_histories</code></td><td><strong>Passou</strong></td></tr>
      <tr><td>TC10</td><td>UC01–UC08</td><td>Fronteiras modulares (ADR 8)</td><td>Repositório com 7 pacotes</td><td>1. Executar <code>ModuleDependenciesTest</code></td><td>Zero imports de <code>Vevey\\*</code> em <code>app/</code>; camadas íntegras</td><td><strong>Passou</strong></td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>4.3. Modelos de implantação do sistema</h2>
<p>A Figura 14 apresenta o Diagrama de Implantação UML (Deployment Diagram) ilustrando os nós físicos e virtuais de execução, os artefatos implantados em cada ambiente e os protocolos de comunicação utilizados em produção.</p>

<div class="figure-wrap">
  {SVG_DEPLOY}
  <div class="figure-caption">Figura 14 — Diagrama de Implantação UML (Docker, Render, PostgreSQL e APIs)</div>
  <div class="figure-source">Fonte: Elaborado pelos autores (2026).</div>
</div>

<p><strong>Descrição dos nós e artefatos apresentados:</strong></p>
<ul>
  <li><strong>Nó <code>client: Navegador Web</code> (<code>«device»</code>):</strong> computador, tablet ou smartphone utilizado por clientes (loja pública) e equipe (<code>/admin</code> e PDV), comunicando-se exclusivamente via <code>HTTPS</code>.</li>
  <li><strong>Nó <code>server: Container Docker (Render)</code> (<code>«execution environment»</code>):</strong> container Linux baseado em <code>Dockerfile</code> que hospeda o artefato da aplicação <strong>Laravel 13 + PHP 8.4</strong> (7 módulos), os ativos compilados pelo <strong>Vite 8 / Filament 5</strong> e o agendador de tarefas (expiração de reservas a cada 5 minutos).</li>
  <li><strong>Nó <code>PostgreSQL 16+</code> (<code>«database node»</code>):</strong> banco de dados relacional gerenciado acessado via <code>TCP/5432</code>, armazenando as 20 tabelas do sistema, índices GIN e constraints <code>CHECK</code>.</li>
  <li><strong>Nó <code>APIs Públicas &amp; Transacionais</code> (<code>«external services»</code>):</strong> serviços externos consumidos via <code>HTTPS/JSON</code> (BrasilAPI CEP v2 e Feriados, ViaCEP como fallback e Resend API para e-mails 2FA).</li>
</ul>

<h2>4.4. Elaboração do procedimento de instalação</h2>
<p>Para instalar e inicializar o sistema em ambiente local de homologação ou servidor dedicado, devem ser atendidos os seguintes <strong>requisitos mínimos</strong>: PHP 8.4 ou superior (extensões <code>pdo_pgsql</code>, <code>intl</code>, <code>mbstring</code>, <code>gd</code>, <code>zip</code>), Composer 2.x, Node.js 22+, NPM 10+ e PostgreSQL 16+.</p>
<p><strong>Passo a passo de instalação:</strong></p>
<pre>1. Clonar o repositório oficial:
   git clone https://github.com/nathocosta/vevey.git &amp;&amp; cd vevey
2. Instalar dependências PHP e compilar ativos front-end:
   composer install
   npm install &amp;&amp; npm run build
3. Configurar variáveis de ambiente e gerar chave criptográfica:
   cp .env.example .env &amp;&amp; php artisan key:generate
   # Editar .env configurando DB_CONNECTION=pgsql, DB_HOST, DB_DATABASE, DB_USERNAME e DB_PASSWORD
   # Opcional: configurar RESEND_API_KEY para disparo real de e-mails 2FA
4. Executar migrations, carga inicial (catálogo, estoque, equipe) e sincronizar RBAC:
   php artisan migrate
   php artisan db:seed --class="Vevey\\Catalog\\Database\\Seeders\\CatalogSeeder"
   php artisan db:seed --class="Vevey\\Inventory\\Database\\Seeders\\InitialStockSeeder"
   php artisan db:seed --class="Database\\Seeders\\StaffUsersSeeder"
   php artisan sync:permissions
5. Inicializar o servidor HTTP local:
   php -S 127.0.0.1:8000 -t public</pre>
<p><strong>Implantação em nuvem (Render.com):</strong> conectar o repositório GitHub (<code>nathocosta/vevey</code>) utilizando o blueprint <code>render.yaml</code>, que provisiona automaticamente o serviço Docker e o banco PostgreSQL, executando <code>docker/start-render.sh</code> na inicialização.</p>

<h2>4.5. Elaboração da documentação técnica do sistema</h2>
<p>A documentação voltada à manutenção evolutiva está versionada no próprio repositório em <code>docs/MODELAGEM_LARAVEL_ADR.md</code> e resumida na Tabela 19. Adicionalmente, o sistema disponibiliza os comandos de manutenção <code>php artisan stock:rebuild</code> (reconstrução integral de <code>stock_levels</code> a partir do ledger), <code>php artisan stock:expire-reservations</code> (agendado a cada 5 minutos em <code>routes/console.php</code>) e <code>php artisan sync:permissions</code> (reconciliação das 140 permissões RBAC).</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 19 — Resumo das Decisões Arquiteturais (ADRs 1 a 9)</div>
  <table class="grid-table compact-table">
    <thead><tr><th style="width:68px;">ADR</th><th style="width:160px;">Tema</th><th>Decisão técnica implementada</th></tr></thead>
    <tbody>
      <tr><td>ADR 1</td><td>Dinheiro</td><td>Centavos inteiros (<code>bigint</code>), <code>MoneyCast</code>, <code>order_adjustments</code>, rateio via <code>Money::allocate()</code> e <code>CHECK</code> de totais.</td></tr>
      <tr><td>ADR 2</td><td>Estoque (Ledger)</td><td>Ledger append-only (<code>stock_movements</code>), reservas de 30 min sob lock ordenado e cache <code>stock_levels</code>.</td></tr>
      <tr><td>ADR 3</td><td>Snapshot de Pedido</td><td>Cópia imutável de SKU, nome, preço e atributos em <code>order_items</code> preservando o histórico comercial.</td></tr>
      <tr><td>ADR 4</td><td>Status de Pedido</td><td>Separação entre <code>payment_status</code> e <code>fulfillment_status</code> com timestamps e <code>order_status_histories</code>.</td></tr>
      <tr><td>ADR 5</td><td>Venda Presencial (PDV)</td><td>Venda de balcão registrada como pedido <code>pos</code> (pago e entregue), permitindo saldo negativo com alerta.</td></tr>
      <tr><td>ADR 6</td><td>Catálogo e Busca</td><td>Catálogo plano (cada cor equivale a um produto), filtros dinâmicos (OU/E) e busca full-text GIN em português.</td></tr>
      <tr><td>ADR 7</td><td>Identidades de Cliente</td><td>Guard separado <code>customers</code>, verificação 2FA OTP e <code>customer_identities</code> com aposentadoria (<code>retired_at</code>).</td></tr>
      <tr><td>ADR 8</td><td>Monólito Modular</td><td>7 pacotes com fronteiras validadas por Pest Arch, comunicação por Actions/DTOs e RBAC por morph map.</td></tr>
      <tr><td>ADR 9</td><td>Evolução da Busca</td><td>Documentação das limitações do radicalizador português e plano futuro de adoção de <code>unaccent</code> e <code>pg_trgm</code>.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<h2>4.6. Elaboração do manual do usuário</h2>

<h3>4.6.1. Introdução</h3>
<p>O <strong>Website Vevey</strong> unifica duas interfaces complementares: a <strong>Loja Virtual</strong> (<code>/</code>), destinada a clientes finais e organizadores de festas comprarem no varejo ou em caixas fechadas, e o <strong>Painel Administrativo</strong> (<code>/admin</code>), destinado à equipe da Vevey Doces para operar o PDV Balcão, controlar estoque via ledger, gerenciar pedidos/cupons e auditar atividades.</p>

<h3>4.6.2. Requisitos do sistema</h3>
<ul>
  <li><strong>Usuários finais e equipe via navegador:</strong> navegador web atualizado (Google Chrome, Microsoft Edge, Mozilla Firefox ou Safari) em computador, tablet ou smartphone com conexão à internet.</li>
  <li><strong>Ambiente local de homologação:</strong> PHP 8.4+, Composer 2, Node.js 22+ e PostgreSQL 16+ (conforme Seção 4.4).</li>
</ul>

<h3>4.6.3. Instalação e configuração</h3>
<p>A instalação local segue integralmente os passos da Seção 4.4. Em ambiente de nuvem (Render.com), o arquivo <code>render.yaml</code> automatiza a criação do banco PostgreSQL, execução das migrations, carga do catálogo, criação das contas da equipe e ativação do cupom demonstrativo <code>VEVEY10</code>. Para envio real de e-mails de verificação 2FA, basta informar a chave <code>RESEND_API_KEY</code> nas variáveis de ambiente.</p>

<h3>4.6.4. Utilização do sistema</h3>
<p><strong>Operação na Loja Virtual (<code>/</code> — Telas 01 a 04, ilustradas nas Figuras 3 a 6):</strong></p>
<ol>
  <li>Na <strong>Tela 01</strong> (Figura 3), digite o nome, SKU ou código de barras na busca em pílula ou navegue pelos departamentos.</li>
  <li>Na <strong>Tela 02</strong> (Figura 4), combine filtros de categoria e cor no catálogo e adicione produtos ajustando a quantidade (<code>- 1 +</code>).</li>
  <li>Na <strong>Tela 03</strong> (Figura 5), alterne entre cores irmãs, confira o saldo real disponível e clique em <em>Adicionar ao carrinho</em>.</li>
  <li>Abra o carrinho lateral e clique em <em>Finalizar pedido</em>; informe o CEP para cotar SEDEX/PAC com previsão em dias úteis reais (ou escolha Retirada na loja), aplique o cupom <code>VEVEY10</code> e conclua via Pix (5% OFF) ou Cartão.</li>
  <li>Para criar sua conta, acesse <em>Minha conta → Criar conta</em> (<strong>Tela 04</strong>, Figura 6) e informe o código de 6 dígitos na tela de verificação em duas etapas (fluxo ilustrado na Figura 7).</li>
</ol>
<p><strong>Operação no Painel Administrativo (<code>/admin</code> — Telas 07 e 08, fluxo na Figura 7):</strong> autentique-se utilizando uma das credenciais da Tabela 20. Utilize <strong>Vendas → PDV Balcão</strong> (<code>/admin/pdv</code>) para vendas presenciais; <strong>Vendas → Pedidos</strong> para confirmar pagamentos e avançar entregas; <strong>Vendas → Cupons</strong> para gerenciar promoções; <strong>Estoque → Saldos de estoque</strong> para lançar entradas de fornecedor ou contagens físicas; e <strong>Auditoria → Logs de auditoria</strong> para consultar o histórico completo.</p>
<p><strong>Aviso importante:</strong> nunca altere saldos de estoque diretamente no banco de dados. Toda entrada ou contagem física deve ser registrada pelas ações do menu <em>Saldos de estoque</em> para preservar a integridade do ledger e da auditoria.</p>

<div class="table-wrap">
  <div class="table-caption">Tabela 20 — Credenciais de homologação para testes e demonstração</div>
  <table class="grid-table">
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

<h3>4.6.5. Resolução de problemas</h3>
<div class="table-wrap">
  <div class="table-caption">Tabela 21 — Guia rápido de resolução de problemas</div>
  <table class="grid-table">
    <thead><tr><th style="width:170px;">Problema observado</th><th style="width:190px;">Causa provável</th><th>Solução recomendada</th></tr></thead>
    <tbody>
      <tr><td>Produto aparece como <em>Esgotado</em> na loja</td><td>Saldo disponível (<code>on_hand - reserved</code>) igual ou menor que zero.</td><td>Acessar <code>/admin/stock-levels</code> e acionar <em>Lançar entrada</em> ou <em>Contagem / Ajustar</em>.</td></tr>
      <tr><td>Alerta de <em>Estoque negativo</em> no painel</td><td>Venda presencial realizada no PDV antes do lançamento da nota de entrada.</td><td>Lançar a entrada de mercadoria correspondente ou executar contagem física no ledger.</td></tr>
      <tr><td>Código 2FA não chegou por e-mail</td><td>Ambiente local/homologação sem chave <code>RESEND_API_KEY</code> ou SMTP configurado.</td><td>Utilizar o código exibido no card <em>Modo demonstração</em> em <code>/verificar-conta</code> ou configurar a chave Resend.</td></tr>
      <tr><td>Login bloqueado temporariamente</td><td>Mais de 5 tentativas consecutivas com senha incorreta (<code>RateLimiter</code>).</td><td>Aguardar o tempo indicado na mensagem (5 minutos) e tentar novamente com as credenciais corretas.</td></tr>
    </tbody>
  </table>
  <div class="source-note">Fonte: Elaborado pelos autores (2026).</div>
</div>

<!-- ==================== 5. REFERÊNCIAS BIBLIOGRÁFICAS (ABNT) ==================== -->
<div class="page-break"></div>
<h1 style="margin-top:0;">5. Referências bibliográficas</h1>
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
