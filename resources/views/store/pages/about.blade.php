<x-store.layout
    title="Sobre a Vevey Doces — Varejo, Atacado e Festas"
    description="Conheça a Vevey Doces: chocolates em caixa fechada, balas, balões metalizados, descartáveis e embalagens de papelão com retirada no balcão ou entrega pelos Correios."
    :nav-categories="$navCategories"
>
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-10">
        <header class="space-y-3 border-b hairline pb-8">
            <p class="text-xs font-medium uppercase tracking-widest text-brand-700">Institucional</p>
            <h1 class="font-display font-semibold text-3xl sm:text-4xl tracking-[-0.02em] text-espresso">
                Doces, embalagens e festa em um só lugar
            </h1>
            <p class="text-base text-espresso-soft leading-relaxed max-w-2xl">
                A Vevey Doces conecta loja física e e-commerce com estoque integrado em tempo real — atendendo tanto quem está montando uma festa em casa quanto confeiteiros, organizadores de eventos e revendedores.
            </p>
        </header>

        <section class="grid sm:grid-cols-3 gap-5">
            <div class="bg-porcelain border hairline rounded-2xl p-6 space-y-2">
                <span class="material-symbols-outlined text-[22px] text-brand-600">inventory_2</span>
                <h2 class="font-display font-semibold text-base text-espresso">Varejo e caixas fechadas</h2>
                <p class="text-sm text-espresso-soft leading-relaxed">
                    Displays fechados de chocolates, pacotes de balas, descartáveis e papelões com preço competitivo.
                </p>
            </div>
            <div class="bg-porcelain border hairline rounded-2xl p-6 space-y-2">
                <span class="material-symbols-outlined text-[22px] text-brand-600">storefront</span>
                <h2 class="font-display font-semibold text-base text-espresso">Retirada no mesmo dia</h2>
                <p class="text-sm text-espresso-soft leading-relaxed">
                    Compre online, reserve seus itens no estoque na hora e retire no balcão sem filas.
                </p>
            </div>
            <div class="bg-porcelain border hairline rounded-2xl p-6 space-y-2">
                <span class="material-symbols-outlined text-[22px] text-brand-600">local_shipping</span>
                <h2 class="font-display font-semibold text-base text-espresso">Envio para todo o Brasil</h2>
                <p class="text-sm text-espresso-soft leading-relaxed">
                    Entregas via Correios (SEDEX e PAC) com frete grátis no PAC em compras acima de R$ 150,00.
                </p>
            </div>
        </section>

        <section class="bg-porcelain border hairline rounded-2xl p-6 sm:p-8 space-y-4">
            <h2 class="font-display font-semibold text-xl text-espresso">Horário e canais de atendimento</h2>
            <dl class="divide-y divide-[rgba(31,27,22,0.08)] text-sm">
                <div class="py-3 flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-espresso-muted">Loja física e retirada</dt>
                    <dd class="font-medium text-espresso">Segunda a sábado, das 08h às 19h</dd>
                </div>
                <div class="py-3 flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-espresso-muted">E-mail de atendimento</dt>
                    <dd class="font-medium text-espresso">contato@veveydoces.com.br</dd>
                </div>
                <div class="py-3 flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-espresso-muted">Acompanhamento de pedidos</dt>
                    <dd>
                        <a href="{{ route('store.track') }}" class="font-medium text-brand-700 hover:underline">Rastrear pedido online</a>
                    </dd>
                </div>
            </dl>
        </section>
    </article>
</x-store.layout>
