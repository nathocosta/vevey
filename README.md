# Vevey Doces — E-Commerce (TCC)

Projeto de E-commerce para confeitaria festiva e doces finos (**Vevey Doces**), contemplando protótipos de alta fidelidade e especificação de backend com modelagem de banco de dados relacional.

---

## 🎂 Sobre o Projeto

O **Vevey Doces** é uma solução completa de comércio eletrônico desenvolvida como Trabalho de Conclusão de Curso (TCC). O projeto alia uma experiência visual atraente e moderna com uma arquitetura técnica robusta:

- **Protótipos de Interface:** Páginas de vitrine, catálogo com filtros avançados, carrinho dinâmico, checkout com múltiplas etapas e tela de confirmação de pedido.
- **Design System:** Guia completo de identidade visual, paleta cromática festiva (carmim, âmbar e violeta), tipografia e biblioteca de componentes reutilizáveis.
- **Modelagem de Dados e Backend:** Especificação física para PostgreSQL, modelos SQLAlchemy, schemas Pydantic e scripts DDL/Seed para FastAPI.

---

## 📁 Estrutura de Diretórios

```text
├── banco de dados/
│   ├── alteracoes_schema.sql         # Scripts de migração e ajustes de schema
│   ├── documentacao_modelagem.md     # Especificação técnica do banco e DER
│   ├── models.py                     # Modelos ORM SQLAlchemy 2.0
│   ├── schema.sql                    # DDL completo do banco relacional
│   ├── schemas.py                    # Schemas Pydantic v2 para validação e serialização
│   └── seed.sql                      # Carga inicial de dados para testes
├── prototipos/
│   ├── css/                          # Folhas de estilo modularizadas
│   ├── js/                           # Lógica frontend dos protótipos
│   ├── index.html                    # Página inicial (Home)
│   ├── catalogo.html                 # Catálogo e busca de produtos
│   ├── carrinho.html                 # Carrinho de compras
│   ├── checkout.html                 # Fluxo de finalização de compra
│   └── confirmacao.html              # Confirmação e resumo do pedido
├── design_system.md                  # Tokens de design e guia de estilo
└── .gitignore                        # Regras de exclusão do Git
```

---

## 🛠️ Tecnologias Utilizadas

- **Frontend:** HTML5 semântico, CSS3 (Design Tokens, Flexbox, CSS Grid), JavaScript vanilla.
- **Backend & Modelagem:** Python 3.10+, FastAPI, SQLAlchemy 2.0, Pydantic v2, PostgreSQL.
- **Identidade Visual:** Design System proprietário com paleta festiva e tipografia moderna.

---

## 🚀 Como Executar Localmente

### Visualizar os Protótipos
1. Navegue até o diretório `prototipos/`.
2. Abra qualquer um dos arquivos `.html` diretamente no navegador (ou utilize a extensão **Live Server** do VS Code).

### Banco de Dados e Backend
1. Consulte a documentação em [`banco de dados/documentacao_modelagem.md`](banco%20de%20dados/documentacao_modelagem.md).
2. Execute os scripts `schema.sql` e `seed.sql` em um banco PostgreSQL para criar a estrutura e carregar os dados de exemplo.
