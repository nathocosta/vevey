# 📋 Roadmap & Backlog — Próxima Sessão (Vevey Doces & Festas)

> **Projeto**: E-commerce & Gestão Omnichannel Vevey Doces e Festas (TCC)  
> **Data do Handover**: 23/10/2026  
> **Localização & Unidade**: R. Sen. Flaquer, 691 - Centro, Santo André - SP, 09010-160  
> **Horário de Atendimento**: Segunda a Sexta das 08h às 18h | Sábado das 08h às 16h  
> **Identidade Visual**: Borgonha Luxury (`#800F2F`), Dourado Nobre (`#B88636`), Off-White (`#FAF7F2`), Neutros Quentes (`#EAE3D9` / `#201516`), Fontes *Playfair Display* e *Plus Jakarta Sans*.

---

## 1. 🧹 Limpeza de Códigos Mortos e Dependências Realizada Nesta Sessão

1. **Dependências Redundantes Podadas**:
   - Remoção de `psycopg[binary]>=3.2.0` do `backend/requirements.txt`. O projeto utiliza SQLAlchemy com driver síncrono `postgresql+psycopg2://` mapeado em `psycopg2-binary>=2.9.9`. A dependência duplicada aumentava o tamanho do build sem utilização.
2. **Sincronização e Limpeza de Seeds SQL**:
   - Sobrescrita de `database/seed.sql` que continha produtos genéricos antigos de apenas chocolates. Agora está rigorosamente alinhado com `backend/seed.sql` contemplando o catálogo completo de doces, artigos de confeitaria artística e balões da Vevey Doces (Santo André - SP).
3. **Remoção de Classes Utilitárias Fantasmas no Frontend**:
   - Limpeza em `frontend/js/app.js` de referências de Tailwind inexistentes (`border-brand-600`, `bg-brand-600`, `text-brand-600`, `ring-brand-500`), substituindo pelos tokens oficiais do Design System da Vevey.
4. **Alinhamento de Horários e Logística**:
   - Atualização do cálculo de frete em `backend/app/api/v1/endpoints/frete.py` para informar a retirada física em Santo André - SP com os horários oficiais (`Seg-Sex 08h-18h | Sáb 08h-16h`).
5. **Segurança e Arquitetura de Permissões**:
   - Criação da tela de bloqueio dedicada `frontend/acesso-negado.html` (HTTP 403 / Restrição de Acesso).
   - Separação estrita com a página de erro não encontrado `frontend/404.html`.
   - Tela de login administrativo protegida em `frontend/admin-login.html` e Gatekeeper no `frontend/admin.html` com validação de JWT.

---

## 2. 🎯 O Que Falta Implementar (Backlog Priorizado para a Próxima Sessão)

### Prioridade Alta (Essencial para Apresentação do TCC & Fluxo Completo)

- [ ] **1. Checkout Expresso / Guest Checkout no Backend**:
  - **Contexto**: O frontend agora permite finalizar pedidos salvando no `localStorage` (`vevey_last_order`) mesmo sem login prévio forçado.
  - **Falta fazer**: Criar um endpoint `POST /api/v1/pedidos/checkout-expresso` ou flexibilizar o endpoint `POST /api/v1/pedidos/` para aceitar pedidos com dados de cliente no próprio payload (nome, telefone, email, endereço) sem exigir token JWT de autenticação prévia de cliente, vinculando o pedido a um cliente convidado ou criando o registro caso não exista.

- [ ] **2. Webhook & Confirmação Real de Pagamento Pix**:
  - **Contexto**: O Pix atualmente gera um payload e QR Code demonstrativo no frontend e na tela de confirmação.
  - **Falta fazer**: Implementar um webhook `POST /api/v1/pagamentos/webhook` (ex: Mercado Pago, Asaas ou Gerencianet/Efí) ou um endpoint de simulação `POST /api/v1/pagamentos/{id}/aprovar-teste` para que o painel do administrador e a tela do cliente mudem automaticamente o status de "Aguardando Pagamento" para "Pago / Em Separação" em tempo real.

- [ ] **3. Conexão Completa do CRUD do Admin com a API REST**:
  - **Contexto**: O `frontend/admin.html` possui uma camada fallback offline/localStorage para testes e conexão com `/api/v1/produtos`.
  - **Falta fazer**: Garantir que as ações de *Cadastrar Produto*, *Atualizar Estoque*, *Excluir Produto* e *Alterar Status do Pedido* enviem sempre o cabeçalho `Authorization: Bearer <TOKEN>` salvo pelo `frontend/admin-login.html`, exibindo mensagens de erro elegantes caso a sessão expire.

---

### Prioridade Média (Robustez & Qualidade de Software)

- [ ] **4. Suíte de Testes Automatizados (Backend)**:
  - Adicionar `pytest` e `pytest-asyncio` às dependências de desenvolvimento.
  - Criar testes unitários e de integração em `backend/tests/`:
    - `test_produtos.py`: Listagem, filtros por categoria, busca textual e validação de estoque.
    - `test_pedidos.py`: Criação de pedido, cálculo de total, cálculo de frete e redução de estoque.
    - `test_auth.py`: Login com credenciais válidas e rejeição com credenciais inválidas (bloqueio 401/403).

- [ ] **5. Upload Real de Fotos no Admin**:
  - Atualmente os produtos novos criados no admin podem usar links diretos de imagens ou ícones de fallback.
  - Implementar upload de arquivos multipart (`POST /api/v1/produtos/upload-imagem`) salvando em armazenamento local ou bucket (Supabase Storage / S3 / Cloudinary).

- [ ] **6. Histórico Completo na Tela "Minha Conta"**:
  - Integrar a aba "Meus Pedidos" em `frontend/minha-conta.html` para disparar `GET /api/v1/pedidos/meus-pedidos` quando o cliente estiver autenticado, além de ler os pedidos salvos localmente no navegador.

---

### Prioridade Baixa / Polimento Final

- [ ] **7. Versionamento de Banco de Dados com Alembic**:
  - Adicionar `alembic` para permitir migrações automáticas de schema sem necessidade de rodar scripts SQL manuais no Supabase.
- [ ] **8. Exportação de Relatórios de Vendas (Admin)**:
  - Implementar botão no Admin para exportar pedidos do mês em formato `.csv` ou `.xlsx` para a gestão contábil da loja.
- [ ] **9. SEO e OpenGraph**:
  - Inserir tags `<meta property="og:image">`, `<meta property="og:title">` e favicon oficial da Vevey em todas as páginas para renderização correta ao compartilhar links no WhatsApp e redes sociais.

---

## 3. 🚀 Guia Rápido de Execução

### Como Rodar o Backend:
```powershell
cd "c:\Users\Nathan\Documents\Vevey - tcc\backend"
# Ativar ambiente virtual (se houver venv):
# .\venv\Scripts\Activate.ps1
uvicorn app.main:app --reload --port 8000
```
- Documentação interativa Swagger: `http://localhost:8000/api/v1/docs`
- Health check: `http://localhost:8000/health`

### Como Rodar o Frontend:
```powershell
cd "c:\Users\Nathan\Documents\Vevey - tcc\frontend"
# Usando Python ou VS Code Live Server:
python -m http.server 3000
```
- Catálogo: `http://localhost:3000/catalogo.html`
- Carrinho: `http://localhost:3000/carrinho.html`
- Login Admin: `http://localhost:3000/admin-login.html`
- Painel Admin: `http://localhost:3000/admin.html`

### Credenciais Padrão (Seed):
- **Admin**: `admin@vevey.com.br` | Senha: `admin123`
