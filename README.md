# Vevey Doces — E-Commerce & Varejo de Festas (TCC)

Projeto de E-commerce e Gestão de Varejo/Distribuidora de Confeitaria e Festas (**Vevey Co.**), desenvolvido no âmbito do curso de Engenharia de Software da FATEC.

A solução adota uma **Arquitetura Desacoplada em Três Camadas (3-Tier Decoupled Architecture)**, permitindo que cada serviço seja hospedado, escalado e mantido de forma independente.

> 📖 **Guia Completo da Arquitetura & Continuidade:** Veja os detalhes e o checklist de conexão no arquivo [ARQUITETURA_3_CAMADAS.md](./ARQUITETURA_3_CAMADAS.md).
> 📋 **Roadmap & Backlog da Equipe:** Acompanhe as tarefas pendentes em [ROADMAP_PROXIMA_SESSAO.md](./ROADMAP_PROXIMA_SESSAO.md).

---

## 🏗️ Arquitetura do Sistema

```text
┌─────────────────────────┐       HTTPS / JSON       ┌──────────────────────────┐       SSL / Pooler       ┌────────────────────────┐
│        FRONTEND         │ ───────────────────────> │         BACKEND          │ ───────────────────────> │    BANCO DE DADOS      │
│     (Vercel / Web)      │ <─────────────────────── │     (Render / Cloud)     │ <─────────────────────── │       (Supabase)       │
│   HTML5 / CSS3 / JS     │           CORS           │     FastAPI / Python     │        SQLAlchemy        │    PostgreSQL Nativo   │
└─────────────────────────┘                          └──────────────────────────┘                          └────────────────────────┘
```

---

## 📁 Estrutura de Diretórios

```text
vevey/
├── frontend/                     # Camada de Apresentação (Interface Web do Usuário)
│   ├── css/                      # Estilos e tokens visuais (Design System Vevey)
│   ├── js/                       # Lógica de interface, fallbacks e chamadas de API
│   ├── index.html                # Tela 1: Home e Destaques
│   ├── catalogo.html             # Tela 2: Listagem, Busca e Filtros
│   ├── produto.html              # Tela 3: Detalhes do Produto e Cálculo de Frete
│   ├── carrinho.html             # Tela 4: Carrinho de Compras
│   ├── checkout.html             # Tela 5: Checkout Seguro e Simulação de Pagamento
│   ├── confirmacao.html          # Tela 6: Confirmação do Pedido e Pix Copia-e-Cola
│   ├── admin-login.html          # Tela 7: Login Restrito de Funcionários
│   ├── admin.html                # Tela 8: Backoffice Unificado e Gestão Operacional
│   ├── minha-conta.html          # Tela 9: Portal do Cliente
│   └── acesso-negado.html        # Tela de Bloqueio RBAC (HTTP 403)
├── backend/                      # Camada de Lógica de Negócio (API RESTful)
│   ├── app/
│   │   ├── api/v1/endpoints/     # Rotas (auth, produtos, pedidos, funcionários, frete)
│   │   ├── core/                 # Configurações, segurança JWT, rate limiter e banco
│   │   ├── models.py             # Mapeamento Objeto-Relacional (SQLAlchemy 2.0)
│   │   ├── schemas.py            # Validação e serialização de dados (Pydantic v2)
│   │   └── main.py               # Ponto de entrada FastAPI com CORS e Swagger Docs
│   ├── .env                      # Variáveis de ambiente ativas (Repositório Privado)
│   ├── .env.example              # Modelo documentado de variáveis
│   ├── requirements.txt          # Dependências Python do backend
│   ├── seed.py                   # Script de carga inicial automatizado via SQLAlchemy
│   └── seed.sql                  # Script SQL com dados de catálogo de Santo André
├── database/                     # Camada de Persistência (PostgreSQL / Supabase)
│   ├── schema.sql                # DDL físico completo (Tabelas, índices, triggers)
│   ├── seed.sql                  # Carga inicial do banco de dados
│   ├── alteracoes_schema.sql     # Ajustes de migração e controle RBAC de funcionários
│   └── documentacao_modelagem.md # Especificação técnica do banco e DER (Normas ABNT)
├── docs/                         # Documentações e Guias de Engenharia
│   └── design_system.md          # Tokens de design, paleta cromática e componentes
├── iniciar_backend.bat           # Atalho Windows para iniciar a API em 1 clique
├── iniciar_frontend.bat          # Atalho Windows para iniciar o frontend em 1 clique
├── .gitignore                    # Regras de exclusão do repositório Git
├── ARQUITETURA_3_CAMADAS.md      # Especificação arquitetural profunda
├── ROADMAP_PROXIMA_SESSAO.md     # Backlog de tarefas e status atual
└── README.md                     # Documentação geral do projeto
```

---

## 🛠️ Tecnologias e Serviços

* **Apresentação (Frontend):** HTML5 Semântico, CSS3 (Design Tokens, Grid, Flexbox), Vanilla JavaScript (preparado para deploy estático na Vercel/Netlify).
* **Lógica de Negócios (Backend):** Python 3.10+, FastAPI, SQLAlchemy 2.0, Pydantic v2, Uvicorn, PyJWT.
* **Persistência (Database):** PostgreSQL gerenciado na nuvem via **Supabase** (com pooling de conexões PgBouncer/Supavisor).

---

## 👥 Guia de Início Rápido para Novos Colaboradores

Se você acabou de ser adicionado ao projeto, siga este passo a passo para rodar tudo na sua máquina em menos de 5 minutos:

### 1. Clonar o Repositório
```bash
git clone https://github.com/nathocosta/vevey.git
cd vevey
```

### 2. Configuração do Backend (FastAPI)
O repositório já inclui o arquivo `backend/.env` configurado com a instância ativa do **Supabase** na nuvem (AWS São Paulo `sa-east-1`), portanto **não é necessário criar um banco de dados local**:

1. Acesse o diretório do backend:
   ```bash
   cd backend
   ```
2. Crie e ative um ambiente virtual Python (recomendado Python 3.10 ou superior):
   ```bash
   python -m venv venv
   # No Windows (PowerShell):
   .\venv\Scripts\Activate.ps1
   # No Linux/Mac:
   source venv/bin/activate
   ```
3. Instale as dependências:
   ```bash
   pip install -r requirements.txt
   ```
4. Inicie o servidor da API:
   ```bash
   uvicorn app.main:app --reload --port 8000
   ```
   *Ou use o atalho na raiz:* basta dar duplo clique em `iniciar_backend.bat`.
5. Acesse a documentação interativa Swagger em: [http://localhost:8000/api/v1/docs](http://localhost:8000/api/v1/docs).

### 3. Configuração do Frontend
O frontend é 100% estático (Vanilla JS / Tailwind CDN):
1. Inicie um servidor web local na porta 3000 (para manter alinhamento com as permissões de CORS):
   - **Opção A:** Execute `iniciar_frontend.bat` na raiz.
   - **Opção B:** Use o **Live Server** do VS Code abrindo `frontend/index.html`.
   - **Opção C:** Via terminal:
     ```bash
     cd frontend
     python -m http.server 3000
     ```
2. Acesse a loja no navegador: [http://localhost:3000/catalogo.html](http://localhost:3000/catalogo.html).

---

## 🔑 Credenciais Padrão de Teste (Seed no Banco)

O banco de dados na nuvem já possui os seguintes usuários pré-cadastrados para teste:

| Tipo | Nome | E-mail | Senha | Nível de Acesso |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | Miriam Sócia | `miriam@veveydoces.com.br` | `admin123` | Total (Visão Financeira, Gestão de Usuários e Catálogo) |
| **Operador** | Juliana Atendente | `juliana@veveydoces.com.br` | `admin123` | Operacional (Pedidos WhatsApp, Vendas e Estoque Balcão) |
| **Logística** | Carlos Logística | `carlos@veveydoces.com.br` | `admin123` | Logística e Despacho de Pedidos |
| **Cliente** | Nathan Costa Santos | `nathan@exemplo.com.br` | `cliente123` | Compras e Acompanhamento de Pedidos |

* Para testar o painel administrativo: acesse [http://localhost:3000/admin-login.html](http://localhost:3000/admin-login.html).

