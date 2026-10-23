"""
Script Python de Seed do Banco de Dados — Vevey Doces (TCC)
Popula categorias, produtos, administradores e clientes de demonstração.

Uso:
    cd backend
    python seed.py
"""

from decimal import Decimal
import sys
import os

# Adiciona o diretório atual ao sys.path para importação correta
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app.core.database import SessionLocal, engine
from app.core.security import get_password_hash
from app.models import (
    Base,
    Categoria,
    Produto,
    UsuarioAdmin,
    CargoAdmin,
    Cliente,
    Endereco
)

def run_seed():
    print("Criando tabelas no banco de dados se não existirem...")
    Base.metadata.create_all(bind=engine)

    db = SessionLocal()
    try:
        # 1. Categorias
        print("Inserindo categorias...")
        categorias_data = [
            {"id": 1, "nome": "Balões & Festas", "slug": "baloes", "descricao": "Balões metalizados, látex biodegradável, arcos e acessórios para festas"},
            {"id": 2, "nome": "Confeitaria Artística", "slug": "confeitaria", "descricao": "Bicos de confeitar em inox, pasta americana, corantes e chocolates"},
            {"id": 3, "nome": "Descartáveis & Embalagens", "slug": "descartaveis", "descricao": "Forminhas 4 pétalas, embalagens individuais e pratos descartáveis"},
            {"id": 4, "nome": "Decoração de Mesa", "slug": "decoracao", "descricao": "Topos de bolo em acrílico, suportes, bandejas e peças temáticas"},
            {"id": 5, "nome": "Acessórios & Infladores", "slug": "acessorios", "descricao": "Infladores elétricos, bombinhas manuais, fitas e suportes"}
        ]
        
        cat_map = {}
        for c in categorias_data:
            existente = db.query(Categoria).filter(Categoria.slug == c["slug"]).first()
            if not existente:
                novo = Categoria(id=c["id"], nome=c["nome"], slug=c["slug"], descricao=c["descricao"])
                db.add(novo)
                db.flush()
                cat_map[c["slug"]] = novo
            else:
                cat_map[c["slug"]] = existente

        # 2. Produtos
        print("Inserindo produtos...")
        produtos_data = [
            {
                "id": 1,
                "nome": "Kit Balões Metalizados Rose Gold & Cereja (50 unid.)",
                "slug": "bl-9921",
                "descricao": "Kit com 50 balões metalizados e perolados de 12 polegadas nas cores Rose Gold e Cereja. Acompanha fita para arco desconstruído de 5 metros.",
                "preco": Decimal("64.90"),
                "preco_promocional": Decimal("49.90"),
                "imagem_url": "celebration",
                "estoque": 15,
                "categoria_slug": "baloes"
            },
            {
                "id": 2,
                "nome": "Balão Bubble Cristal Transparente com Confetes (24 pol.)",
                "slug": "bb-1024",
                "descricao": "Balão Bubble inflável em silicone cristalino de 24 polegadas com confetes metálicos dourados. Alta durabilidade e brilho intenso.",
                "preco": Decimal("29.90"),
                "preco_promocional": None,
                "imagem_url": "bubble_chart",
                "estoque": 30,
                "categoria_slug": "baloes"
            },
            {
                "id": 3,
                "nome": "Jogo de Bicos de Confeitar em Inox (12 Peças + Adaptador)",
                "slug": "cf-4402",
                "descricao": "Kit profissional com 12 bicos de confeitar sem emenda em aço inoxidável 304 (Pitanga, Folha, Babado e Flor), inclui 1 adaptador de engate rápido.",
                "preco": Decimal("79.90"),
                "preco_promocional": Decimal("69.90"),
                "imagem_url": "cake",
                "estoque": 8,
                "categoria_slug": "confeitaria"
            },
            {
                "id": 4,
                "nome": "Pasta Americana Tradicional Vermelho Rubi 800g",
                "slug": "pa-1029",
                "descricao": "Pasta de açúcar elástica pronta para cobrir bolos e modelar doces finos. Sabor suave de baunilha e coloração uniforme Vermelho Rubi.",
                "preco": Decimal("39.90"),
                "preco_promocional": None,
                "imagem_url": "cookie",
                "estoque": 3,
                "categoria_slug": "confeitaria"
            },
            {
                "id": 5,
                "nome": "Forminhas 4 Pétalas Rosa Bebê (100 unid.)",
                "slug": "fd-5510",
                "descricao": "Caixa com 100 forminhas de papel offset 180g com acabamento fosco e corte a laser perfeito para doces finos e brigadeiros gourmet.",
                "preco": Decimal("18.90"),
                "preco_promocional": None,
                "imagem_url": "celebration",
                "estoque": 45,
                "categoria_slug": "descartaveis"
            },
            {
                "id": 6,
                "nome": "Topo de Bolo Dourado 'Parabéns' em Acrílico Espelhado",
                "slug": "to-3310",
                "descricao": "Topper elegante cortado a laser em acrílico espelhado dourado de 2mm com película protetora. Altura de 15cm com haste resistente.",
                "preco": Decimal("24.90"),
                "preco_promocional": None,
                "imagem_url": "cake",
                "estoque": 12,
                "categoria_slug": "decoracao"
            },
            {
                "id": 7,
                "nome": "Inflador de Balões Elétrico Profissional 600W (Bivolt)",
                "slug": "in-8801",
                "descricao": "Compressor elétrico bivolt de alta potência com 2 bicos para inflar centenas de balões em minutos. Acionamento contínuo ou por toque.",
                "preco": Decimal("189.90"),
                "preco_promocional": Decimal("169.90"),
                "imagem_url": "local_shipping",
                "estoque": 4,
                "categoria_slug": "acessorios"
            },
            {
                "id": 8,
                "nome": "Chocolate Nobre Gotas Meio Amargo 1,01kg",
                "slug": "ch-7720",
                "descricao": "Gotas de chocolate nobre com 54% de cacau para derretimento rápido, têmpera perfeita e brilho excepcional em trufas e bombons.",
                "preco": Decimal("54.90"),
                "preco_promocional": None,
                "imagem_url": "cookie",
                "estoque": 19,
                "categoria_slug": "confeitaria"
            }
        ]

        for p in produtos_data:
            existente = db.query(Produto).filter(Produto.slug == p["slug"]).first()
            if not existente:
                novo_prod = Produto(
                    id=p["id"],
                    nome=p["nome"],
                    slug=p["slug"],
                    descricao=p["descricao"],
                    preco=p["preco"],
                    preco_promocional=p["preco_promocional"],
                    imagem_url=p["imagem_url"],
                    estoque=p["estoque"],
                    ativo=True
                )
                cat = cat_map.get(p["categoria_slug"])
                if cat:
                    novo_prod.categorias.append(cat)
                db.add(novo_prod)

        # 3. Administradores (Senha inicial: admin123)
        print("Inserindo usuários administradores...")
        admin_pass = get_password_hash("admin123")
        admins = [
            {"id": 1, "nome": "Miriam Sócia", "email": "miriam@veveydoces.com.br", "cargo": CargoAdmin.ADMIN},
            {"id": 2, "nome": "Juliana Atendente", "email": "juliana@veveydoces.com.br", "cargo": CargoAdmin.OPERADOR}
        ]
        for a in admins:
            if not db.query(UsuarioAdmin).filter(UsuarioAdmin.email == a["email"]).first():
                db.add(UsuarioAdmin(
                    id=a["id"],
                    nome=a["nome"],
                    email=a["email"],
                    senha_hash=admin_pass,
                    cargo=a["cargo"]
                ))

        # 4. Cliente de Demonstração (Senha inicial: cliente123)
        print("Inserindo cliente padrão...")
        client_email = "nathan@exemplo.com.br"
        if not db.query(Cliente).filter(Cliente.email == client_email).first():
            cliente = Cliente(
                id=1,
                nome="Nathan Costa Santos",
                cpf="12345678909",
                email=client_email,
                telefone="11987654321",
                senha_hash=get_password_hash("cliente123"),
                ativo=True
            )
            db.add(cliente)
            db.flush()

            db.add(Endereco(
                id=1,
                cliente_id=cliente.id,
                identificador="Casa",
                cep="01310100",
                logradouro="Avenida Paulista",
                numero="1000",
                complemento="Apto 42",
                bairro="Bela Vista",
                cidade="São Paulo",
                estado="SP",
                principal=True
            ))

        db.commit()
        print("\nSucesso! Banco de dados populado com categorias, produtos, administradores e cliente de teste.")
        print("Credenciais de acesso criadas:")
        print("  Admin (Miriam):  miriam@veveydoces.com.br  / admin123")
        print("  Atendente (Juliana): juliana@veveydoces.com.br / admin123")
        print("  Cliente (Nathan):   nathan@exemplo.com.br   / cliente123")

    except Exception as e:
        db.rollback()
        print(f"Erro durante o seed: {e}")
        raise
    finally:
        db.close()

if __name__ == "__main__":
    run_seed()
