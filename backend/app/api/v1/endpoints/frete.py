from typing import List, Optional
from decimal import Decimal
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

router = APIRouter()


class CalculoFreteRequest(BaseModel):
    cep_destino: str = Field(..., min_length=8, max_length=9, description="CEP de entrega (8 dígitos)")
    subtotal: Decimal = Field(..., ge=0, description="Valor dos produtos para análise de frete grátis")


class OpcaoFrete(BaseModel):
    modalidade: str
    nome: str
    prazo_dias: str
    valor: Decimal
    gratis: bool = False


class CalculoFreteResponse(BaseModel):
    cep_destino: str
    opcoes: List[OpcaoFrete]


@router.post("/calcular", response_model=CalculoFreteResponse)
def calcular_frete(dados: CalculoFreteRequest):
    """RF005 / UC02: Cálculo de frete e prazos por CEP com suporte a PAC, Sedex e Retirada na Loja"""
    cep_limpo = "".join(filter(str.isdigit, dados.cep_destino))
    if len(cep_limpo) != 8:
        raise HTTPException(status_code=400, detail="CEP inválido. Deve conter 8 dígitos numéricos.")

    # Regra de negócio: Frete grátis acima de R$ 199,00
    tem_frete_gratis = dados.subtotal >= Decimal("199.00")

    # Simulação calibrada para SP / Brasil
    valor_pac = Decimal("0.00") if tem_frete_gratis else Decimal("11.50")
    valor_sedex = Decimal("18.90")

    opcoes = [
        OpcaoFrete(
            modalidade="SEDEX",
            nome="Sedex Expresso",
            prazo_dias="1 a 2 dias úteis",
            valor=valor_sedex,
            gratis=False,
        ),
        OpcaoFrete(
            modalidade="PAC",
            nome="PAC Econômico",
            prazo_dias="4 a 6 dias úteis",
            valor=valor_pac,
            gratis=tem_frete_gratis,
        ),
        OpcaoFrete(
            modalidade="RETIRADA",
            nome="Retirada Grátis na Loja Física (Vevey Co.)",
            prazo_dias="Disponível em até 2 horas",
            valor=Decimal("0.00"),
            gratis=True,
        ),
    ]

    return CalculoFreteResponse(
        cep_destino=cep_limpo,
        opcoes=opcoes,
    )
