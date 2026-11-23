@php
    use App\Http\Controllers\StorefrontController;
@endphp

<x-store.layout
    title="Vevey Doces — Doces, Embalagens e Artigos para Festa"
    :nav-categories="$navCategories"
>
    <!-- Hero Editorial Scene (foto em largura total com área limpa à esquerda para tipografia) -->
    <section class="relative overflow-hidden border-b hairline bg-[#EFE7DA]">
        <img
            src="{{ asset('images/hero-vevey-scene.jpg') }}"
            alt="Caixas de chocolates, balas, embalagens de confeitaria e balões metalizados da Vevey Doces"
            class="absolute inset-0 w-full h-full object-cover object-right lg:object-center select-none pointer-events-none"
        />
        <!-- Scrim suave apenas no mobile/tablet para garantir contraste 100% -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#F5EFE4]/95 via-[#F5EFE4]/80 to-transparent lg:from-[#F5EFE4]/65 lg:via-transparent lg:to-transparent pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-[460px] lg:min-h-[540px] py-14 lg:py-20 flex items-center">
            <div class="max-w-xl space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-porcelain/90 border hairline text-xs font-medium text-espresso">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                    <span>Cupom <strong class="font-mono text-brand-700">VEVEY10</strong> · 10% OFF acima de R$ 30</span>
                </div>

                <h1 class="font-display font-semibold text-4xl sm:text-5xl lg:text-[52px] leading-[1.06] tracking-[-0.03em] text-espresso">
                    Doces em caixa, embalagens e festa no mesmo pedido
                </h1>

                <p class="text-base sm:text-lg text-espresso-soft leading-relaxed max-w-md">
                    Chocolates, balas, balões metalizados e papelões no varejo ou em caixas fechadas, com retirada no mesmo dia ou envio pelos Correios.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-medium text-sm rounded-full px-7 h-12 shadow-[var(--shadow-subtle)] transition-[background-color,transform] duration-150">
                        <span>Explorar catálogo</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>

                    <a href="{{ route('store.catalog', ['ofertas' => 1]) }}" class="inline-flex items-center justify-center bg-porcelain/95 border hairline-strong hover:bg-porcelain text-espresso font-medium text-sm rounded-full px-6 h-12 transition-colors duration-150">
                        Ofertas da semana
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Faixa de Condições Comerciais -->
    <section class="bg-porcelain border-b hairline">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-paper border hairline flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px] text-espresso-soft">local_shipping</span>
                </span>
                <div>
                    <h2 class="font-display font-medium text-sm text-espresso">Frete grátis acima de R$ 150</h2>
                    <p class="text-xs text-espresso-muted">Ou retire na loja sem custo</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-paper border hairline flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px] text-espresso-soft">qr_code_2</span>
                </span>
                <div>
                    <h2 class="font-display font-medium text-sm text-espresso">5% OFF à vista no Pix</h2>
                    <p class="text-xs text-espresso-muted">Aprovação e separação imediatas</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-paper border hairline flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px] text-espresso-soft">inventory_2</span>
                </span>
                <div>
                    <h2 class="font-display font-medium text-sm text-espresso">Varejo e caixas fechadas</h2>
                    <p class="text-xs text-espresso-muted">Para sua festa ou ponto de revenda</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-paper border hairline flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px] text-espresso-soft">storefront</span>
                </span>
                <div>
                    <h2 class="font-display font-medium text-sm text-espresso">Retirada no mesmo dia</h2>
                    <p class="text-xs text-espresso-muted">Segunda a sábado, 08h às 19h</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Departamentos Visuais (cards fotográficos editoriais) -->
    @if ($navCategories->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14">
            <div class="flex items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display font-semibold text-2xl sm:text-[26px] tracking-[-0.02em] text-espresso">Compre por departamento</h2>
                    <p class="text-sm text-espresso-soft mt-1">Tudo o que você precisa para montar a festa ou abastecer o estoque</p>
                </div>
                <a href="{{ route('store.catalog') }}" class="text-sm font-medium text-brand-700 hover:text-brand-800 shrink-0 transition-colors duration-150">
                    Ver todos
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($navCategories as $category)
                    @php
                        $deptImage = match ($category->slug) {
                            'festa' => asset('images/produtos/bal-met-9-az.jpg'),
                            'doces' => asset('images/produtos/doc-kitkat-cx24.jpg'),
                            'embalagens-e-papeloes' => asset('images/produtos/emb-pap-doce-25.jpg'),
                            default => asset('images/produtos/prt-desc-az.jpg'),
                        };
                    @endphp
                    <div class="group relative rounded-2xl overflow-hidden border hairline bg-espresso-dark aspect-[16/10] flex flex-col justify-end p-5 shadow-[var(--shadow-subtle)] hover:shadow-[var(--shadow-float)] transition-shadow duration-200">
                        <img
                            src="{{ $deptImage }}"
                            alt="{{ $category->name }}"
                            loading="lazy"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.05]"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-espresso-dark/90 via-espresso-dark/35 to-transparent"></div>

                        <div class="relative z-10 space-y-2.5">
                            <a href="{{ route('store.catalog', ['categoria' => $category->slug]) }}" class="flex items-center justify-between gap-2 text-porcelain">
                                <h3 class="font-display font-semibold text-xl tracking-[-0.015em]">{{ $category->name }}</h3>
                                <span class="w-8 h-8 rounded-full bg-porcelain/15 backdrop-blur-xs flex items-center justify-center group-hover:bg-brand-600 transition-colors duration-200">
                                    <span class="material-symbols-outlined text-[18px] text-porcelain">arrow_forward</span>
                                </span>
                            </a>

                            @if ($category->children->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($category->children as $child)
                                        <a
                                            href="{{ route('store.catalog', ['categoria' => $child->slug]) }}"
                                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-porcelain/90 hover:bg-porcelain text-espresso transition-colors duration-150"
                                        >
                                            {{ $child->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Monte pela Paleta de Cores da Festa -->
    @if ($colorValues->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
            <div class="bg-porcelain border hairline rounded-2xl p-6 sm:p-7 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="font-display font-semibold text-lg text-espresso">Monte pela paleta de cores da festa</h2>
                    <p class="text-sm text-espresso-soft mt-0.5">Combine balões metalizados e descartáveis no mesmo tom</p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    @foreach ($colorValues as $color)
                        <a
                            href="{{ route('store.catalog', ['atributos' => [$color->slug]]) }}"
                            class="group inline-flex items-center gap-2.5 px-4 h-10 rounded-full bg-paper border hairline-strong hover:border-espresso/40 hover:bg-linen text-sm font-medium text-espresso transition-colors duration-150"
                        >
                            <span
                                class="w-4 h-4 rounded-full border hairline-strong shrink-0 transition-transform duration-150 group-hover:scale-110"
                                style="background-color: {{ StorefrontController::colorSwatchHex($color->slug) }}"
                            ></span>
                            <span>{{ $color->value }}</span>
                            <span class="tnum text-xs text-espresso-muted">{{ $color->products_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Ofertas da Semana -->
    @if ($promoProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16">
            <div class="flex items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display font-semibold text-2xl sm:text-[26px] tracking-[-0.02em] text-espresso">Ofertas da semana</h2>
                    <p class="text-sm text-espresso-soft mt-1">Preços promocionais selecionados por tempo limitado</p>
                </div>
                <a href="{{ route('store.catalog', ['ofertas' => 1]) }}" class="text-sm font-medium text-brand-700 hover:text-brand-800 shrink-0 transition-colors duration-150">
                    Ver todas as ofertas
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach ($promoProducts as $product)
                    <x-store.product-card :product="$product" :available="$stockMap[$product->id] ?? null" />
                @endforeach
            </div>
        </section>
    @endif

    <!-- Vitrine Principal -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <h2 class="font-display font-semibold text-2xl sm:text-[26px] tracking-[-0.02em] text-espresso">Destaques da Vevey</h2>
                <p class="text-sm text-espresso-soft mt-1">Os itens mais procurados em doces, embalagens e festas</p>
            </div>
            <a href="{{ route('store.catalog') }}" class="text-sm font-medium text-brand-700 hover:text-brand-800 shrink-0 transition-colors duration-150">
                Catálogo completo
            </a>
        </div>

        @if ($featuredProducts->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach ($featuredProducts as $product)
                    <x-store.product-card :product="$product" :available="$stockMap[$product->id] ?? null" />
                @endforeach
            </div>
        @else
            <div class="bg-porcelain border hairline rounded-xl p-10 text-center space-y-2">
                <p class="font-display font-medium text-base text-espresso">Nenhum produto ativo encontrado</p>
                <p class="text-sm text-espresso-soft">Execute as migrations e o seed do catálogo para popular a vitrine.</p>
            </div>
        @endif
    </section>

    <!-- Faixa Editorial Escura -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16">
        <div class="rounded-2xl bg-espresso-dark text-[#EFE9DF] p-8 sm:p-12 grid lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-8 space-y-3">
                <h2 class="font-display font-semibold text-2xl sm:text-3xl leading-tight text-porcelain">
                    Abastecendo sua festa, evento ou ponto de revenda
                </h2>
                <p class="text-sm sm:text-base text-[#C8C0B4] leading-relaxed max-w-2xl">
                    Monte seu pedido online com caixas fechadas de chocolates, balas, descartáveis e papelões. Retire no balcão no mesmo dia ou receba via SEDEX e PAC.
                </p>
            </div>
            <div class="lg:col-span-4 flex lg:justify-end">
                <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center gap-2 bg-porcelain hover:bg-linen text-espresso font-medium text-sm rounded-full px-7 h-12 transition-colors duration-150">
                    <span>Montar meu pedido</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>
</x-store.layout>
