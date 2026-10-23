# Arquitetura em 3 Camadas — Vevey Doces (TCC)

> **Documento de Referência Arquitetural e de Continuidade de Sessão**  
> Projeto: **Vevey Doces & Festas** (FATEC — Engenharia de Software)  
> Padrão: **3-Tier Decoupled Architecture (Apresentação, Negócio e Dados)**

---

## 1. Visão Geral da Arquitetura

O sistema da **Vevey Doces** adota uma arquitetura em **três camadas desacopladas** *(Three-Tier Architecture)*. Cada camada opera em um ambiente independente, comunicando-se por meio de protocolos padronizados de mercado (HTTPS/REST e TCP/PostgreSQL SSL).

```mermaid
flowchart TD
    subgraph Camada1 ["1. Camada de Apresentação (Frontend)"]
        UI["SPA / Multi-Page Vanilla Web<br/>HTML5 Semântico + TailwindCSS + JS ES6+"]
        Deploy1["Hospedagem: Vercel / Netlify / Localhost"]
        State["LocalStorage (Fallback Offline & Carrinho)"]
    end

    subgraph Camada2 ["2. Camada de Lógica de Negócios (Backend API)"]
        API["FastAPI (Python 3.10+)"]
        Auth["Segurança & JWT (OAuth2 + BCrypt)"]
        Validation["Validação & Schemas (Pydantic v2)"]
        ORM["Mapeamento Objeto-Relacional (SQLAlchemy 2.0)"]
        Deploy2["Hospedagem: Render / Railway / Uvicorn"]
    end

    subgraph Camada3 ["3. Camada de Persistência (Banco de Dados)"]
        DB["PostgreSQL Nativo (Cloud)"]
        Pooler["Connection Pooler (PgBouncer / Supavisor)"]
        Tables["Tabelas Relacionais ACID<br/>(Produtos, Pedidos, Clientes, RBAC)"]
        Deploy3["Hospedagem: Supabase"]
    end

    UI -->|1. Requisições REST HTTPS / JSON| API
    API -->|2. Resposta JSON com CORS Permitido| UI
    ORM -->|3. Consultas SQL / Transações ACID| Pooler
    Pooler --> DB
    DB -->|4. Conjunto de Resultados Tuplas| ORM
```

---

## 2. Detalhamento de Cada Camada

### 🟡 Camada 1: Apresentação (Frontend)
* **Localização no repositório**: diretório [`frontend/`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend)
* **Hospedagem em Produção**: **Vercel** ou **Netlify** (CDN Global distribuído)
* **Tecnologias**: HTML5, Vanilla JavaScript (ES6+), Tailwind CSS via CDN, Google Fonts (*Plus Jakarta Sans* e *Inter*), Material Symbols.
* **Telas do Sistema**:
  - [`index.html`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/index.html): Home institucional, banners e categorias.
  - [`catalogo.html`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/catalogo.html): Catálogo geral com busca em tempo real, filtros de categorias, ordenação de preços e favoritos.
  - [`produto.html`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/produto.html): Detalhes do produto dinâmico via `?id=SKU` com cálculo de frete via API.
  - [`carrinho.html`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/carrinho.html): Gestão de quantidades, cálculo de subtotal e cupom de desconto.
  - [`checkout.html`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/checkout.html): Formulário de entrega e seleção de pagamento (Pix, Cartão, Boleto).
  - [`confirmacao.html`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/confirmacao.html): Tela de sucesso com código Pix Copia-e-Cola e número do pedido.
  - [`minha-conta.html`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/minha-conta.html): Histórico de compras do cliente e rastreamento.
  - [`admin.html`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/admin.html): Backoffice unificado (Dashboard, Pedido Manual WhatsApp Juliana, Ajuste de Estoque Balcão e Cadastro de Produtos).
* **Comunicação com a API**:
  - Centralizada em [`frontend/js/app.js`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/frontend/js/app.js) através do objeto global `window.VEVEY_CONFIG`:
    ```javascript
    window.VEVEY_CONFIG = {
      API_URL: (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')
        ? 'http://localhost:8000/api/v1'
        : 'https://vevey-api.onrender.com/api/v1'
    };
    ```
  - **Tolerância a Falhas (Fallback Offline)**: Se o backend estiver temporariamente desligado, o frontend utiliza os dados cacheados no `localStorage` sem travar a interface do usuário.

---

### 🔵 Camada 2: Lógica de Negócios (Backend API)
* **Localização no repositório**: diretório [`backend/`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend)
* **Hospedagem em Produção**: **Render** (Web Service rodando `uvicorn app.main:app --host 0.0.0.0 --port $PORT`)
* **Tecnologias**: Python 3.10+, **FastAPI**, **SQLAlchemy 2.0**, **Pydantic v2**, **PyJWT**, **Passlib (BCrypt)**.
* **Módulos Principais**:
  - [`backend/app/main.py`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/app/main.py): Ponto de entrada, configuração de middlewares CORS, rotas `/api/v1` e documentação interativa Swagger UI em `/docs`.
  - [`backend/app/core/database.py`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/app/core/database.py): Criação do `engine` SQLAlchemy com pool de conexões e injeção de dependência `get_db()`.
  - [`backend/app/core/security.py`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/app/core/security.py): Criptografia de senhas (BCrypt) e emissão de tokens JWT com expiração.
  - [`backend/app/models.py`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/app/models.py): Mapeamento ORM relacional do banco.
  - [`backend/app/schemas.py`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/app/schemas.py): Validação estrita de entradas e saídas (validação de CPF, CEP, senhas, etc.).
  - [`backend/app/api/v1/endpoints/`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/app/api/v1/endpoints/):
    - `produtos.py`: Listagem, busca, filtro por categoria, cadastro, atualização de estoque e status.
    - `pedidos.py`: Checkout online, criação de pedido manual WhatsApp (Juliana) e atualização de status/rastreio.
    - `auth.py`: Login com geração de Bearer Token JWT e registro de clientes.
    - `frete.py`: Cálculo simulado e integração de fretes (Sedex, PAC e Retirada).

---

### 🟢 Camada 3: Persistência de Dados (Database)
* **Localização no repositório**: diretório [`database/`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/database) e scripts auxiliares em `backend/`
* **Hospedagem em Produção**: **Supabase** (PostgreSQL 15+ em nuvem AWS com pooling Supavisor/PgBouncer)
* **Estrutura Relacional (10 Tabelas Principais)**:
  1. `categorias`: Categorias de produtos (`baloes`, `confeitaria`, `descartaveis`, etc.).
  2. `produtos`: Dados do item, SKU, preço, preço promocional, estoque e status ativo.
  3. `produtos_categorias`: Tabela associativa N:N entre produtos e categorias.
  4. `usuarios_admin`: Funcionários com papéis RBAC (`ADMIN` Miriam, `OPERADOR` Juliana).
  5. `clientes`: Clientes cadastrados com CPF único, e-mail e hash de senha.
  6. `enderecos`: Endereços de entrega associados a clientes.
  7. `pedidos`: Cabeçalho da compra (valor produtos, valor frete, valor total, status e rastreio).
  8. `itens_pedidos`: Itens vinculados a cada pedido com snapshot de preço unitário no momento da venda.
  9. `pagamentos`: Transações registradas (Pix, Cartão, Gateway simulado).
  10. `historico_status_pedidos`: Log de auditoria (RNF011) de todas as mudanças de status do pedido.
* **Scripts de Inicialização e Carga**:
  - [`backend/seed.sql`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/seed.sql): Script SQL para colar no **Supabase SQL Editor** e criar dados em 1 clique.
  - [`backend/seed.py`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/seed.py): Script Python automatizado (`python seed.py`) para popular via SQLAlchemy.
  - [`database/schema.sql`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/database/schema.sql): DDL físico completo com constraints e triggers.

---

## 3. Fluxo de Vida de uma Compra (End-to-End)

```mermaid
sequenceDiagram
    autonumber
    actor Cliente as Cliente (Navegador)
    participant Front as Frontend (Vercel)
    participant Back as Backend FastAPI (Render)
    participant DB as PostgreSQL (Supabase)

    Cliente->>Front: 1. Acessa catálogo e pesquisa "Balões"
    Front->>Back: 2. GET /api/v1/produtos?busca=Balões
    Back->>DB: 3. SELECT * FROM produtos WHERE nome ILIKE '%Balões%' AND ativo = true
    DB-->>Back: 4. Retorna registros de produtos
    Back-->>Front: 5. Retorna JSON com lista de produtos
    Front-->>Cliente: 6. Renderiza os cards na tela

    Cliente->>Front: 7. Adiciona ao carrinho e finaliza checkout
    Front->>Back: 8. POST /api/v1/pedidos (itens, frete, pagamento)
    Note over Back: Valida regras de negócio<br/>(estoque disponível, valor total)
    Back->>DB: 9. INSERT INTO pedidos & itens_pedidos<br/>UPDATE produtos SET estoque = estoque - qtd
    DB-->>Back: 10. Confirmação de commit transacional
    Back-->>Front: 11. Retorna pedido criado (#VV-2026-XXXX) + Pix Copia-Cola
    Front-->>Cliente: 12. Redireciona para confirmacao.html
```

---

## 4. Checklist Prático para a Próxima Sessão (Conexão com o Supabase)

Quando você for conectar o banco de dados na próxima sessão, siga este roteiro de 5 passos:

### Passo 1: Criar o Projeto no Supabase
1. Acesse [supabase.com](https://supabase.com) e crie uma conta gratuita.
2. Crie um novo projeto chamado `vevey-doces` (região recomendada: `sa-east-1` São Paulo).
3. Guarde a senha mestre que você definir para o banco.

### Passo 2: Obter a String de Conexão
1. No painel do Supabase, vá em **Project Settings** > **Database**.
2. Na seção **Connection string**, selecione a aba **URI** (modo *Session* porta `5432` ou modo *Transaction* porta `6543`).
3. O formato será:
   ```text
   postgresql://postgres.[PROJECT-REF]:[SUA-SENHA]@aws-0-sa-east-1.pooler.supabase.com:6543/postgres
   ```

### Passo 3: Configurar o Arquivo `.env` do Backend
1. Abra o arquivo `backend/.env` (ou crie a partir de `backend/.env.example`).
2. Cole sua string de conexão:
   ```env
   PROJECT_NAME="Vevey Doces - API"
   ENVIRONMENT="development"
   DATABASE_URL="postgresql://postgres.[PROJECT-REF]:[SUA-SENHA]@aws-0-sa-east-1.pooler.supabase.com:6543/postgres"
   SECRET_KEY="sua_chave_secreta_jwt_para_o_tcc"
   ACCESS_TOKEN_EXPIRE_MINUTES=480
   BACKEND_CORS_ORIGINS=["http://localhost:5500", "http://127.0.0.1:5500", "http://localhost:3000", "*"]
   ```

### Passo 4: Popular o Banco de Dados
Você pode escolher uma das duas opções:
* **Opção A (Mais rápida via navegador)**:  
  Abra o **SQL Editor** no painel do Supabase, copie o conteúdo de [`backend/seed.sql`](file:///c:/Users/Nathan/Documents/Vevey%20-%20tcc/backend/seed.sql), cole e clique em **Run**.
* **Opção B (Via Python no terminal)**:  
  ```bash
  cd backend
  python seed.py
  ```

### Passo 5: Inicializar o Servidor Local
```bash
cd backend
uvicorn app.main:app --reload --port 8000
```
* Acesse a documentação interativa em: `http://localhost:8000/docs`
* Abra `frontend/index.html` ou `frontend/catalogo.html` pelo Live Server: os dados do catálogo e os novos pedidos serão 100% integrados em tempo real com o seu Supabase!

---

## 5. Justificativa Arquitetural para a Banca do TCC

Se a banca examinadora da FATEC perguntar **"Por que adotar uma arquitetura em 3 camadas e não um monolito tradicional?"**, os argumentos formais são:

1. **Separação Estrita de Responsabilidades (SoC)**: A equipe de design/front pode evoluir telas e estilos sem risco de quebrar regras fiscais, cálculo de fretes ou triggers do banco.
2. **Escalabilidade Elástica Independente**: O catálogo frontend (arquivos estáticos) consome quase zero processamento e pode ser cacheado em CDN global (Vercel). A API (FastAPI) processa apenas chamadas REST necessárias.
3. **Segurança por Isolamento (Princípio do Menor Privilégio)**: O banco de dados nunca é exposto à internet pública sem credenciais. O frontend nunca armazena senhas mestras de banco; todas as transações passam pelo crivo de validação do FastAPI.
4. **Custo Operacional Zero / Acadêmico**: Todas as 3 camadas cabem com folga nos planos gratuitos das ferramentas selecionadas (Vercel + Render + Supabase).
