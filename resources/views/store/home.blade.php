@php
    use App\Http\Controllers\StorefrontController;
@endphp

<x-store.layout
    title="Vevey Doces — Doces, Embalagens e Artigos para Festa"
    :nav-categories="$navCategories"
>
    <!-- Hero Comercial -->
    <section class="bg-paper border-b hairline">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 grid lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            <div class="lg:col-span-6 space-y-6">
                <h1 class="font-display font-semibold text-4xl sm:text-5xl leading-[1.08] tracking-[-0.025em] text-espresso">
                    Caixas de doces, embalagens e artigos de festa no mesmo pedido
                </h1>

                <p class="text-base sm:text-lg text-espresso-soft leading-relaxed max-w-lg">
                    Chocolates em caixa, balas, descartáveis, papelões e balões para sua festa ou revenda. Compre no varejo ou em pacotes fechados com retirada na loja no mesmo dia.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-medium text-sm rounded-lg px-6 h-12 transition-[background-color,transform] duration-150">
                        Explorar catálogo
                    </a>

                    <a href="{{ route('store.catalog', ['ofertas' => 1]) }}" class="inline-flex items-center justify-center bg-porcelain border hairline-strong hover:bg-linen text-espresso font-medium text-sm rounded-lg px-6 h-12 transition-colors duration-150">
                        Ver ofertas da semana
                    </a>
                </div>
            </div>

            <!-- Vitrine editorial de destaque no Hero -->
            <div class="lg:col-span-6">
                @if ($heroProducts->isNotEmpty())
                    <div class="bg-porcelain rounded-2xl border hairline p-4 shadow-[var(--shadow-float)]">
                        <div class="flex items-center justify-between px-1 pb-3 border-b hairline mb-4">
                            <span class="text-xs font-medium text-espresso-soft">Seleção da vitrine</span>
                            <a href="{{ route('store.catalog', ['ofertas' => 1]) }}" class="text-xs font-medium text-brand-700 hover:text-brand-800 transition-colors duration-150">
                                Ver todas as ofertas
                            </a>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            @foreach ($heroProducts as $index => $heroProduct)
                                @php
                                    $heroImg = StorefrontController::productImageUrl($heroProduct);
                                    $heroDiscount = StorefrontController::discountPercent($heroProduct);
                                    $isLead = $index === 0;
                                @endphp
                                <a
                                    href="{{ route('store.product', ['slug' => $heroProduct->slug]) }}"
                                    class="{{ $isLead ? 'col-span-2 sm:col-span-1 sm:row-span-2' : '' }} group bg-paper rounded-xl border hairline p-3 flex flex-col justify-between hover:border-espresso/25 transition-colors duration-150"
                                >
                                    <div class="{{ $isLead ? 'aspect-square' : 'aspect-[4/3]' }} rounded-lg bg-linen border hairline overflow-hidden flex flex-col items-center justify-center mb-3">
                                        @if ($heroImg)
                                            <img src="{{ $heroImg }}" alt="{{ $heroProduct->name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.04]" />
                                        @else
                                            <span class="material-symbols-outlined text-[24px] text-espresso-muted">inventory_2</span>
                                            <span class="mt-1.5 font-mono text-xs text-espresso-muted">{{ $heroProduct->sku }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        @if ($heroDiscount !== null)
                                            <span class="inline-block mb-1.5 px-2 py-0.5 rounded-md bg-brand-600 text-white text-xs font-medium tnum">-{{ $heroDiscount }}%</span>
                                        @endif
                                        <p class="font-display font-medium text-sm text-espresso group-hover:text-brand-700 line-clamp-2 transition-colors duration-150">{{ $heroProduct->name }}</p>
                                        <p class="tnum font-display font-semibold text-base text-espresso mt-1">
                                            {{ StorefrontController::formatBrl($heroProduct->sale_price) }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl bg-porcelain border hairline p-10 text-center space-y-2">
                        <p class="font-display font-medium text-base text-espresso">Catálogo pronto para receber produtos</p>
                        <p class="text-sm text-espresso-soft">Cadastre itens no painel administrativo ou rode o CatalogSeeder.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Faixa de Condições Comerciais (quieta, sem caixas coloridas) -->
    <section class="bg-porcelain border-b hairline">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-[18px] text-espresso-muted mt-0.5 shrink-0">local_shipping</span>
                <div>
                    <h2 class="font-display font-medium text-sm text-espresso">Frete grátis acima de R$ 150</h2>
                    <p class="text-xs text-espresso-muted mt-0.5">Ou retire na loja sem custo adicional</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-[18px] text-espresso-muted mt-0.5 shrink-0">qr_code_2</span>
                <div>
                    <h2 class="font-display font-medium text-sm text-espresso">5% de desconto no Pix</h2>
                    <p class="text-xs text-espresso-muted mt-0.5">Aprovação imediata para separar o pedido</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-[18px] text-espresso-muted mt-0.5 shrink-0">inventory_2</span>
                <div>
                    <h2 class="font-display font-medium text-sm text-espresso">Caixas fechadas e varejo</h2>
                    <p class="text-xs text-espresso-muted mt-0.5">Doces, balões, papelões e descartáveis</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-[18px] text-espresso-muted mt-0.5 shrink-0">payments</span>
                <div>
                    <h2 class="font-display font-medium text-sm text-espresso">Preços exatos em reais</h2>
                    <p class="text-xs text-espresso-muted mt-0.5">Pagamento no Pix, crédito ou débito</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Departamentos Visuais (com fotos reais do catálogo) -->
    @if ($navCategories->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16">
            <div class="flex items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display font-semibold text-2xl text-espresso">Compre por departamento</h2>
                    <p class="text-sm text-espresso-soft mt-1">Encontre rápido o que falta para sua festa ou estoque</p>
                </div>
                <a href="{{ route('store.catalog') }}" class="text-sm font-medium text-brand-700 hover:text-brand-800 shrink-0 transition-colors duration-150">
                    Ver todos
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                @foreach ($navCategories as $category)
                    @php
                        $deptImage = match ($category->slug) {
                            'festa' => asset('images/produtos/bal-met-9-az.jpg'),
                            'doces' => asset('images/produtos/doc-kitkat-cx24.jpg'),
                            'embalagens-e-papeloes' => asset('images/produtos/emb-pap-doce-25.jpg'),
                            default => asset('images/produtos/prt-desc-az.jpg'),
                        };
                    @endphp
                    <div class="group bg-porcelain rounded-xl border hairline hover:border-espresso/20 hover:shadow-[var(--shadow-float)] overflow-hidden flex items-stretch transition-[border-color,box-shadow] duration-200">
                        <a href="{{ route('store.catalog', ['categoria' => $category->slug]) }}" class="w-28 sm:w-32 shrink-0 bg-linen border-r hairline overflow-hidden">
                            <img src="{{ $deptImage }}" alt="{{ $category->name }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.04]" />
                        </a>
                        <div class="p-4 flex-1 flex flex-col justify-between min-w-0">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <a href="{{ route('store.catalog', ['categoria' => $category->slug]) }}" class="font-display font-semibold text-base text-espresso hover:text-brand-700 transition-colors duration-150 truncate">
                                        {{ $category->name }}
                                    </a>
                                    <span class="material-symbols-outlined text-[18px] text-espresso-muted shrink-0">chevron_right</span>
                                </div>

                                @if ($category->children->isNotEmpty())
                                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                                        @foreach ($category->children as $child)
                                            <a href="{{ route('store.catalog', ['categoria' => $child->slug]) }}" class="px-2 py-0.5 rounded-md text-xs font-medium bg-paper border hairline text-espresso-soft hover:text-espresso hover:bg-linen transition-colors duration-150">
                                                {{ $child->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('store.catalog', ['categoria' => $category->slug]) }}" class="mt-4 text-xs font-medium text-brand-700 hover:text-brand-800 transition-colors duration-150">
                                Explorar {{ mb_strtolower($category->name) }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Compre por Cor da Festa (inspirado em Festabox / Rizzo) -->
    @if ($colorValues->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14">
            <div class="bg-porcelain border hairline rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="font-display font-semibold text-lg text-espresso">Monte pela paleta de cores da festa</h2>
                    <p class="text-sm text-espresso-soft mt-0.5">Filtre balões e descartáveis combinando na mesma cor</p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    @foreach ($colorValues as $color)
                        <a
                            href="{{ route('store.catalog', ['atributos' => [$color->slug]]) }}"
                            class="inline-flex items-center gap-2.5 px-3.5 h-10 rounded-lg bg-paper border hairline-strong hover:border-espresso/40 text-sm font-medium text-espresso transition-colors duration-150"
                        >
                            <span
                                class="w-4 h-4 rounded-full border hairline-strong shrink-0"
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
                    <h2 class="font-display font-semibold text-2xl text-espresso">Ofertas da semana</h2>
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
                <h2 class="font-display font-semibold text-2xl text-espresso">Destaques da Vevey</h2>
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
                <p class="text-sm sm:text-base text-[#AAA398] max-w-2xl leading-relaxed">
                    Filtre balões e descartáveis por cor e tamanho e combine com caixas de chocolates, balas e embalagens de papelão no mesmo carrinho.
                </p>
            </div>
            <div class="lg:col-span-4 flex lg:justify-end">
                <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center bg-porcelain hover:bg-linen text-espresso font-medium text-sm rounded-lg px-6 h-12 transition-colors duration-150">
                    Montar meu pedido
                </a>
            </div>
        </div>
    </section>
</x-store.layout>
