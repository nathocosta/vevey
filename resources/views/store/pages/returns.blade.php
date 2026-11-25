<x-store.layout
    title="Trocas e Devoluções — Vevey Doces"
    description="Condições para troca, cancelamento e devolução de pedidos na Vevey Doces conforme o Código de Defesa do Consumidor."
    :nav-categories="$navCategories"
>
    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-8">
        <header class="space-y-2 border-b hairline pb-6">
            <p class="text-xs font-medium uppercase tracking-widest text-brand-700">Atendimento ao Cliente</p>
            <h1 class="font-display font-semibold text-3xl text-espresso">Trocas, Cancelamentos e Devoluções</h1>
            <p class="text-xs text-espresso-muted">Transparência garantida pelo Código de Defesa do Consumidor (CDC)</p>
        </header>

        <div class="bg-porcelain border hairline rounded-2xl p-6 sm:p-8 space-y-6 text-sm text-espresso-soft leading-relaxed">
            <section class="space-y-2">
                <h2 class="font-display font-semibold text-base text-espresso">1. Cancelamento imediato</h2>
                <p>
                    Pedidos com pagamento pendente podem ser cancelados diretamente na <a href="{{ route('store.account') }}" class="text-brand-700 font-medium hover:underline">Área do Cliente</a> com apenas um clique, liberando imediatamente os itens reservados.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="font-display font-semibold text-base text-espresso">2. Direito de arrependimento (7 dias)</h2>
                <p>
                    Para compras online, você tem até <strong>7 dias corridos</strong> após o recebimento para solicitar a devolução por arrependimento, desde que os produtos (embalagens, balões, descartáveis e caixas de doces) estejam lacrados, sem sinais de violação e em sua embalagem original.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="font-display font-semibold text-base text-espresso">3. Produtos avariados ou em desacordo</h2>
                <p>
                    Caso receba algum item danificado no transporte ou diferente do pedido, entre em contato em até 48 horas informando o número do pedido para efetuarmos a reposição sem custo adicional ou estorno integral.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="font-display font-semibold text-base text-espresso">4. Prazos de estorno</h2>
                <p>
                    Pagamentos via Pix são estornados em até 2 dias úteis após a conferência da devolução. Compras no cartão de crédito seguem o prazo da operadora (1 a 2 faturas subsequentes).
                </p>
            </section>
        </div>
    </article>
</x-store.layout>
