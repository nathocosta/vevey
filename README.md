# Vevey Doces — E-Commerce & Varejo de Festas (TCC)

Projeto de E-commerce e Gestão de Varejo/Distribuidora de Confeitaria e Festas (**Vevey Co.**), desenvolvido no âmbito do curso de Engenharia de Software da FATEC.

A solução adota uma **Arquitetura Desacoplada em Três Camadas (3-Tier Decoupled Architecture)**, permitindo que cada serviço seja hospedado, escalado e mantido de forma independente.

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
│   ├── css/                      # Estilos e tokens visuais
│   ├── js/                       # Lógica de interface e chamadas de API
│   ├── index.html                # Tela 1: Home e Destaques
│   ├── catalogo.html             # Tela 2: Listagem e Filtros
│   ├── carrinho.html             # Tela 4: Carrinho de Compras
│   ├── checkout.html             # Tela 5: Checkout Seguro
│   └── confirmacao.html          # Tela 6: Confirmação do Pedido
├── backend/                      # Camada de Lógica de Negócio (API RESTful)
│   ├── app/
│   │   ├── core/                 # Configurações, variáveis de ambiente e sessão do banco
│   │   │   ├── config.py
│   │   │   └── database.py
│   │   ├── models.py             # Mapeamento Objeto-Relacional (SQLAlchemy 2.0)
│   │   ├── schemas.py            # Validação e serialização de dados (Pydantic v2)
│   │   └── main.py               # Ponto de entrada FastAPI com CORS e Swagger Docs
│   ├── .env.example              # Modelo de variáveis de ambiente
│   └── requirements.txt          # Dependências Python do backend
├── database/                     # Camada de Persistência (PostgreSQL / Supabase)
│   ├── schema.sql                # DDL físico completo (Tabelas, índices, triggers)
│   ├── seed.sql                  # Carga inicial de dados para testes
│   ├── alteracoes_schema.sql     # Ajustes de migração e controle RBAC de funcionários
│   └── documentacao_modelagem.md # Especificação técnica do banco e DER (Normas ABNT)
├── docs/                         # Documentações e Guias de Engenharia
│   └── design_system.md          # Tokens de design, paleta cromática e componentes
├── .gitignore                    # Regras de exclusão do repositório Git
└── README.md                     # Documentação geral do projeto
```

---

## 🛠️ Tecnologias e Serviços

* **Apresentação (Frontend):** HTML5 Semântico, CSS3 (Design Tokens, Grid, Flexbox), Vanilla JavaScript (preparado para deploy estático na Vercel/Netlify).
* **Lógica de Negócios (Backend):** Python 3.10+, FastAPI, SQLAlchemy 2.0, Pydantic v2, Uvicorn, PyJWT.
* **Persistência (Database):** PostgreSQL gerenciado na nuvem via **Supabase** (com pooling de conexões PgBouncer/Supavisor).

---

## 🚀 Como Executar Localmente

### 1. Banco de Dados (Supabase)
1. Crie um projeto no [Supabase](https://supabase.com).
2. Acesse o **SQL Editor** e execute na sequência os arquivos:
   - `database/schema.sql`
   - `database/alteracoes_schema.sql`
   - `database/seed.sql`

### 2. Backend (FastAPI)
1. Acesse o diretório `backend`:
   ```bash
   cd backend
   ```
2. Crie e ative um ambiente virtual:
   ```bash
   python -m venv venv
   # No Windows (PowerShell):
   .\venv\Scripts\Activate.ps1
   ```
3. Instale as dependências:
   ```bash
   pip install -r requirements.txt
   ```
4. Crie o arquivo `.env` a partir do `.env.example` e preencha sua `DATABASE_URL` do Supabase:
   ```bash
   cp .env.example .env
   ```
5. Inicie o servidor da API:
   ```bash
   uvicorn app.main:app --reload --port 8000
   ```
6. Acesse a documentação interativa Swagger em: [http://localhost:8000/api/v1/docs](http://localhost:8000/api/v1/docs).

### 3. Frontend
Abra o diretório `frontend` e execute com qualquer servidor local (como Live Server do VS Code ou `npx serve frontend`).
