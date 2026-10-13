from typing import List, Optional, Any
from decimal import Decimal
import uuid
from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel, Field
from sqlalchemy.orm import Session
from app.core.database import get_db
from app.models import (
    Pedido,
    ItemPedido,
    Pagamento,
    Produto,
    Cliente,
    Endereco,
    HistoricoStatusPedido,
    StatusPedido,
    StatusPagamento,
    MetodoPagamento,
    TipoEntrega,
)
from app.api.deps import get_current_client, get_current_admin

router = APIRouter()


# Schemas para Criação de Pedido Online
class ItemPedidoInput(BaseModel):
    produto_id: int
    quantidade: int = Field(..., gt=0)


class PedidoOnlineCreate(BaseModel):
    endereco_entrega_id: int
    metodo_pagamento: MetodoPagamento
    tipo_entrega: TipoEntrega = TipoEntrega.ENVIO_TRANSPORTADORA
    valor_frete: Decimal = Field(default=Decimal("0.00"), ge=0)
    itens: List[ItemPedidoInput]


# Schemas para Pedido Manual (WhatsApp - Juliana / UC03)
class PedidoManualCreate(BaseModel):
    cliente_nome: str
    cliente_whatsapp: str
    cliente_cpf: str
    endereco_entrega: str
    valor_frete: Decimal = Field(default=Decimal("0.00"), ge=0)
    itens: List[ItemPedidoInput]


class StatusUpdateInput(BaseModel):
    novo_status: StatusPedido
    codigo_rastreio: Optional[str] = None
    observacao: Optional[str] = None


@router.post("/", status_code=status.HTTP_201_CREATED)
def criar_pedido_online(
    dados: PedidoOnlineCreate,
    db: Session = Depends(get_db),
    cliente: Cliente = Depends(get_current_client),
) -> Any:
    """UC02 / RF004: Realizar checkout online, validação de estoque e registro de pedido"""
    if not dados.itens:
        raise HTTPException(status_code=400, detail="O pedido deve conter pelo menos um item.")

    # 1. Verifica endereço de entrega
    endereco = db.query(Endereco).filter(Endereco.id == dados.endereco_entrega_id, Endereco.cliente_id == cliente.id).first()
    if not endereco:
        raise HTTPException(status_code=404, detail="Endereço de entrega não encontrado.")

    # 2. Valida estoque de cada produto (RNF005) e calcula subtotal
    subtotal = Decimal("0.00")
    itens_para_criar = []

    for item in dados.itens:
        produto = db.query(Produto).filter(Produto.id == item.produto_id).first()
        if not produto or not produto.ativo:
            raise HTTPException(status_code=400, detail=f"Produto ID {item.produto_id} indisponível.")
        
        if produto.estoque < item.quantidade:
            raise HTTPException(
                status_code=400,
                detail=f"Estoque insuficiente para o produto '{produto.nome}'. Disponível: {produto.estoque}.",
            )

        preco_aplicado = produto.preco_promocional if produto.preco_promocional else produto.preco
        subtotal += preco_aplicado * item.quantidade

        # Decrementa o estoque
        produto.estoque -= item.quantidade

        itens_para_criar.append({
            "produto_id": produto.id,
            "quantidade": item.quantidade,
            "preco_unitario": preco_aplicado,
        })

    valor_total = subtotal + dados.valor_frete

    # 3. Cria registro do pedido
    novo_pedido = Pedido(
        cliente_id=cliente.id,
        endereco_entrega_id=endereco.id,
        valor_produtos=subtotal,
        valor_frete=dados.valor_frete,
        valor_total=valor_total,
        status=StatusPedido.AGUARDANDO_PAGAMENTO,
        tipo_entrega=dados.tipo_entrega,
    )
    db.add(novo_pedido)
    db.flush()

    # 4. Registra os itens
    for item_data in itens_para_criar:
        item_db = ItemPedido(
            pedido_id=novo_pedido.id,
            produto_id=item_data["produto_id"],
            quantidade=item_data["quantidade"],
            preco_unitario=item_data["preco_unitario"],
        )
        db.add(item_db)

    # 5. Registra o pagamento do gateway
    transacao_id = f"PAY-{uuid.uuid4().hex[:12].upper()}"
    pagamento_db = Pagamento(
        pedido_id=novo_pedido.id,
        transacao_gateway_id=transacao_id,
        metodo=dados.metodo_pagamento,
        status=StatusPagamento.PENDENTE,
        detalhes_gateway={
            "pix_qrcode": "00020126580014br.gov.bcb.pix...",
            "pix_copia_cola": "00020126580014br.gov.bcb.pix0136vevey-pix-chave-chave",
            "gateway": "MercadoPago",
        },
    )
    db.add(pagamento_db)

    # 6. Grava histórico inicial (RNF011)
    historico = HistoricoStatusPedido(
        pedido_id=novo_pedido.id,
        status="Aguardando Pagamento",
        observacao="Pedido criado via checkout online.",
    )
    db.add(historico)

    db.commit()
    db.refresh(novo_pedido)

    return {
        "pedido_id": novo_pedido.id,
        "numero_pedido": f"#VV-2026-{novo_pedido.id:04d}",
        "valor_total": float(novo_pedido.valor_total),
        "status": novo_pedido.status.value,
        "transacao_id": transacao_id,
        "pix_copia_cola": "00020126580014br.gov.bcb.pix0136vevey-chave-aleatoria-simulada",
    }


@router.post("/manual", status_code=status.HTTP_201_CREATED)
def criar_pedido_manual_whatsapp(
    dados: PedidoManualCreate,
    db: Session = Depends(get_db),
    admin: Any = Depends(get_current_admin),
) -> Any:
    """UC03 / RF006: Atendente Juliana cria pedido manual para cliente atendido pelo WhatsApp"""
    # 1. Localiza ou cria cliente simplificado (RF013)
    cpf_limpo = "".join(filter(str.isdigit, dados.cliente_cpf)) or "00000000000"
    cliente = db.query(Cliente).filter(Cliente.telefone == dados.cliente_whatsapp).first()

    if not cliente:
        cliente = Cliente(
            nome=dados.cliente_nome,
            cpf=cpf_limpo[:11].zfill(11),
            email=f"whatsapp_{dados.cliente_whatsapp.replace(' ', '')}@veveydoces.com.br",
            telefone=dados.cliente_whatsapp,
            senha_hash="hash_temporario_whatsapp",
            ativo=True,
        )
        db.add(cliente)
        db.flush()

    # Cria endereço provisório
    endereco = Endereco(
        cliente_id=cliente.id,
        identificador="WhatsApp Entrega",
        cep="01001000",
        logradouro=dados.endereco_entrega,
        numero="S/N",
        bairro="Centro",
        cidade="São Paulo",
        estado="SP",
    )
    db.add(endereco)
    db.flush()

    # 2. Calcula subtotal e decrementa estoque
    subtotal = Decimal("0.00")
    itens_para_criar = []

    for item in dados.itens:
        produto = db.query(Produto).filter(Produto.id == item.produto_id).first()
        if not produto or produto.estoque < item.quantidade:
            raise HTTPException(status_code=400, detail=f"Estoque insuficiente para o produto ID {item.produto_id}.")
        
        produto.estoque -= item.quantidade
        subtotal += produto.preco * item.quantidade
        itens_para_criar.append((produto.id, item.quantidade, produto.preco))

    total = subtotal + dados.valor_frete

    # 3. Registra Pedido
    pedido = Pedido(
        cliente_id=cliente.id,
        endereco_entrega_id=endereco.id,
        valor_produtos=subtotal,
        valor_frete=dados.valor_frete,
        valor_total=total,
        status=StatusPedido.AGUARDANDO_PAGAMENTO,
        tipo_entrega=TipoEntrega.ENVIO_TRANSPORTADORA,
    )
    db.add(pedido)
    db.flush()

    for p_id, qtd, preco in itens_para_criar:
        db.add(ItemPedido(pedido_id=pedido.id, produto_id=p_id, quantidade=qtd, preco_unitario=preco))

    # Link de Pagamento (RF007)
    link_token = uuid.uuid4().hex[:10]
    link_pagamento = f"https://pagamento.veveydoces.com.br/pay/{link_token}"

    db.add(Pagamento(
        pedido_id=pedido.id,
        transacao_gateway_id=f"LINK-{link_token.upper()}",
        metodo=MetodoPagamento.PIX,
        status=StatusPagamento.PENDENTE,
        detalhes_gateway={"link_pagamento": link_pagamento},
    ))

    db.add(HistoricoStatusPedido(
        pedido_id=pedido.id,
        status="Aguardando Pagamento",
        observacao=f"Pedido criado manualmente por {getattr(admin, 'nome', 'Atendente WhatsApp')}.",
    ))

    db.commit()
    db.refresh(pedido)

    return {
        "pedido_id": pedido.id,
        "numero_pedido": f"#VV-MANUAL-{pedido.id:04d}",
        "valor_total": float(total),
        "link_pagamento": link_pagamento,
        "mensagem_whatsapp": f"Olá {dados.cliente_nome}! Seu pedido na Vevey Doces ficou no total de R$ {total:.2f}. Acesse o link para realizar o pagamento com segurança: {link_pagamento}",
    }


@router.get("/meus")
def listar_meus_pedidos(
    db: Session = Depends(get_db),
    cliente: Cliente = Depends(get_current_client),
) -> Any:
    """RF011 / Tela 7: Histórico de pedidos do cliente autenticado"""
    pedidos = db.query(Pedido).filter(Pedido.cliente_id == cliente.id).order_by(Pedido.id.desc()).all()
    resultado = []
    for p in pedidos:
        resultado.append({
            "id": p.id,
            "numero": f"#VV-2026-{p.id:04d}",
            "data": p.created_at.strftime("%d/%m/%Y às %H:%M") if p.created_at else "",
            "valor_total": float(p.valor_total),
            "status": p.status.value,
            "codigo_rastreio": p.codigo_rastreio,
            "itens": [
                {
                    "produto_nome": item.produto.nome if item.produto else "Item",
                    "quantidade": item.quantidade,
                    "preco_unitario": float(item.preco_unitario),
                }
                for item in p.itens
            ],
        })
    return resultado


@router.get("/")
def listar_todos_pedidos(
    db: Session = Depends(get_db),
    admin: Any = Depends(get_current_admin),
    status_filtro: Optional[str] = None,
) -> Any:
    """Feature 6 / Painel Admin: Listagem geral de pedidos para separação e despacho"""
    query = db.query(Pedido).order_by(Pedido.id.desc())
    if status_filtro:
        query = query.filter(Pedido.status == status_filtro)
    pedidos = query.all()
    
    return [
        {
            "id": p.id,
            "numero": f"#VV-2026-{p.id:04d}",
            "cliente_nome": p.cliente.nome if p.cliente else "Cliente",
            "valor_total": float(p.valor_total),
            "status": p.status.value,
            "codigo_rastreio": p.codigo_rastreio,
            "created_at": p.created_at.strftime("%d/%m/%Y %H:%M") if p.created_at else "",
        }
        for p in pedidos
    ]


@router.patch("/{id}/status")
def atualizar_status_pedido(
    id: int,
    dados: StatusUpdateInput,
    db: Session = Depends(get_db),
    admin: Any = Depends(get_current_admin),
) -> Any:
    """RF015 / RNF011: Atualiza status do pedido com registro de log/histórico e rastreio"""
    pedido = db.query(Pedido).filter(Pedido.id == id).first()
    if not pedido:
        raise HTTPException(status_code=404, detail="Pedido não encontrado.")

    status_antigo = pedido.status.value
    pedido.status = dados.novo_status

    if dados.codigo_rastreio:
        pedido.codigo_rastreio = dados.codigo_rastreio

    # Log de auditoria (RNF011)
    obs = dados.observacao or f"Status alterado de '{status_antigo}' para '{dados.novo_status.value}' por {getattr(admin, 'nome', 'Admin')}."
    db.add(HistoricoStatusPedido(
        pedido_id=pedido.id,
        status=dados.novo_status.value,
        observacao=obs,
    ))

    db.commit()
    db.refresh(pedido)

    return {
        "pedido_id": pedido.id,
        "novo_status": pedido.status.value,
        "codigo_rastreio": pedido.codigo_rastreio,
        "mensagem": "Status atualizado e registrado no histórico com sucesso.",
    }
