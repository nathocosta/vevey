@php
    use App\Http\Controllers\StorefrontController;
    use Vevey\Catalog\Models\Product;

    $mediaItems = $product->getMedia(Product::IMAGES_COLLECTION);
    $primaryImage = StorefrontController::productImageUrl($product, thumb: false);
    $thumbImage = StorefrontController::productImageUrl($product, thumb: true);
    $discount = StorefrontController::discountPercent($product);
    $salePriceCents = $product->sale_price->getMinorAmount()->toInt();
    $pixFormatted = 'R$ ' . number_format(($salePriceCents * 0.95) / 100, 2, ',', '.');
    $primaryCategory = $product->categories->first();
@endphp

<x-store.layout
    :title="$product->name . ' — Vevey Doces'"
    :nav-categories="$navCategories"
    :active-category-slug="$primaryCategory?->slug"
>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 pb-24 lg:pb-12">
        <!-- Breadcrumb -->
        <nav aria-label="Navegação estrutural" class="text-xs text-espresso-muted flex items-center gap-2 mb-6 flex-wrap">
            <a href="{{ route('store.home') }}" class="hover:text-espresso transition-colors duration-150">Início</a>
            <span>/</span>
            <a href="{{ route('store.catalog') }}" class="hover:text-espresso transition-colors duration-150">Catálogo</a>
            @if ($primaryCategory?->parent)
                <span>/</span>
                <a href="{{ route('store.catalog', ['categoria' => $primaryCategory->parent->slug]) }}" class="hover:text-espresso transition-colors duration-150">
                    {{ $primaryCategory->parent->name }}
                </a>
            @endif
            @if ($primaryCategory)
                <span>/</span>
                <a href="{{ route('store.catalog', ['categoria' => $primaryCategory->slug]) }}" class="hover:text-espresso transition-colors duration-150">
                    {{ $primaryCategory->name }}
                </a>
            @endif
            <span>/</span>
            <span class="text-espresso font-medium">{{ $product->name }}</span>
        </nav>

        <!-- Grid Principal (Galeria + Painel de Compra Sticky) -->
        <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            <!-- Coluna Esquerda: Galeria + Detalhes -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-porcelain rounded-2xl border hairline overflow-hidden">
                    <div class="relative aspect-square bg-linen flex flex-col items-center justify-center">
                        @if ($primaryImage)
                            <img id="pdp-main-image" src="{{ $primaryImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                        @else
                            <span class="material-symbols-outlined text-[32px] text-espresso-muted">inventory_2</span>
                            <span class="mt-2 font-mono text-xs text-espresso-muted">{{ $product->sku }}</span>
                        @endif

                        @if ($discount !== null)
                            <span class="absolute top-4 left-4 px-2.5 py-1 rounded-md bg-brand-600 text-white text-xs font-medium tnum">
                                -{{ $discount }}%
                            </span>
                        @endif
                    </div>

                    @if ($mediaItems->count() > 1)
                        <div class="p-4 border-t hairline flex gap-3 overflow-x-auto">
                            @foreach ($mediaItems as $index => $media)
                                <button
                                    type="button"
                                    data-gallery-thumb="{{ $media->getUrl() }}"
                                    class="w-16 h-16 rounded-lg overflow-hidden border {{ $index === 0 ? 'border-brand-600' : 'hairline' }} shrink-0 cursor-pointer"
                                >
                                    <img
                                        src="{{ $media->hasGeneratedConversion(Product::THUMB_CONVERSION) ? $media->getUrl(Product::THUMB_CONVERSION) : $media->getUrl() }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover"
                                    />
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Descrição e Especificações -->
                <div class="bg-porcelain rounded-2xl border hairline p-6 space-y-6">
                    <div>
                        <h2 class="font-display font-semibold text-base text-espresso mb-2">Sobre o produto</h2>
                        <p class="text-sm sm:text-base text-espresso-soft leading-relaxed">
                            {{ $product->description ?: 'Produto selecionado pela Vevey Doces para festas, embalagens e varejo.' }}
                        </p>
                    </div>

                    <div class="pt-5 border-t hairline">
                        <h3 class="font-display font-medium text-sm text-espresso mb-3">Especificações</h3>
                        <dl class="divide-y divide-[rgba(31,27,22,0.08)] text-sm">
                            <div class="py-2.5 flex justify-between gap-4">
                                <dt class="text-espresso-muted">SKU</dt>
                                <dd class="font-mono text-xs text-espresso">{{ $product->sku }}</dd>
                            </div>
                            @if ($product->barcode)
                                <div class="py-2.5 flex justify-between gap-4">
                                    <dt class="text-espresso-muted">Código de barras</dt>
                                    <dd class="font-mono text-xs text-espresso">{{ $product->barcode }}</dd>
                                </div>
                            @endif
                            @foreach ($product->attributeValues as $attrValue)
                                <div class="py-2.5 flex justify-between gap-4">
                                    <dt class="text-espresso-muted">{{ $attrValue->attribute->name }}</dt>
                                    <dd class="font-medium text-espresso">{{ $attrValue->value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Painel de Compra Sticky -->
            <div class="lg:col-span-5 lg:sticky lg:top-32">
                <div class="bg-porcelain rounded-2xl border hairline p-6 shadow-[var(--shadow-subtle)] space-y-6">
                    <div class="flex items-center justify-between gap-2">
                        @if ($primaryCategory)
                            <a href="{{ route('store.catalog', ['categoria' => $primaryCategory->slug]) }}" class="px-2.5 py-1 rounded-md bg-linen text-espresso text-xs font-medium hover:text-brand-700 transition-colors duration-150">
                                {{ $primaryCategory->name }}
                            </a>
                        @endif
                        <span class="text-xs font-mono text-espresso-muted">{{ $product->sku }}</span>
                    </div>

                    <h1 class="font-display font-semibold text-2xl leading-snug text-espresso">
                        {{ $product->name }}
                    </h1>

                    <!-- Bloco de Preço -->
                    <div class="p-4 rounded-xl bg-paper border hairline space-y-1">
                        @if ($product->promo_price_cents !== null)
                            <div class="flex items-center gap-2">
                                <span class="tnum text-sm text-espresso-muted line-through">{{ StorefrontController::formatBrl($product->price_cents) }}</span>
                                @if ($discount !== null)
                                    <span class="px-2 py-0.5 rounded-md bg-brand-600 text-white text-xs font-medium tnum">-{{ $discount }}%</span>
                                @endif
                            </div>
                        @endif
                        <div class="tnum font-display font-semibold text-3xl text-espresso">
                            {{ StorefrontController::formatBrl($product->sale_price) }}
                        </div>
                        <p class="text-xs text-positive font-medium">
                            <span class="tnum">{{ $pixFormatted }}</span> à vista no Pix (5% OFF)
                        </p>
                    </div>

                    <!-- Outras cores disponíveis (produtos irmãos — ADR 6) -->
                    @if ($colorSiblings->count() > 1)
                        <div class="space-y-2">
                            <span class="text-xs font-medium text-espresso-soft block">Outras cores disponíveis</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($colorSiblings as $sibling)
                                    @php
                                        $siblingColor = $sibling->attributeValues->first(fn ($v) => $v->attribute->slug === 'cor');
                                        $isCurrent = $sibling->id === $product->id;
                                    @endphp
                                    @if ($siblingColor)
                                        <a
                                            href="{{ route('store.product', ['slug' => $sibling->slug]) }}"
                                            class="inline-flex items-center gap-2 px-3 h-9 rounded-lg border text-xs font-medium transition-colors duration-150 {{ $isCurrent ? 'border-brand-600 bg-brand-50/60 text-espresso' : 'hairline-strong bg-paper text-espresso-soft hover:text-espresso hover:bg-linen' }}"
                                        >
                                            <span
                                                class="w-3.5 h-3.5 rounded-full border hairline-strong shrink-0"
                                                style="background-color: {{ StorefrontController::colorSwatchHex($siblingColor->slug) }}"
                                            ></span>
                                            <span>{{ $siblingColor->value }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Disponibilidade em estoque (ADR 2) -->
                    <div class="flex items-center gap-2 text-xs font-medium">
                        @if ($availableStock > 10)
                            <span class="w-2 h-2 rounded-full bg-positive"></span>
                            <span class="text-positive">Em estoque · {{ $availableStock }} disponíveis para envio imediato</span>
                        @elseif ($availableStock > 0)
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-amber-700">Últimas {{ $availableStock }} unidades disponíveis</span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-espresso-muted"></span>
                            <span class="text-espresso-muted">Indisponível no momento</span>
                        @endif
                    </div>

                    <!-- Quantidade + Botão Comprar -->
                    <div class="space-y-3">
                        @if ($availableStock > 0)
                            <div class="flex items-center gap-3">
                                <div class="inline-flex items-center border hairline-strong rounded-lg bg-paper h-12">
                                    <button type="button" id="pdp-qty-dec" aria-label="Diminuir quantidade" class="w-10 h-full flex items-center justify-center text-base text-espresso-soft hover:text-espresso transition-colors duration-150 cursor-pointer">−</button>
                                    <input id="pdp-qty" type="number" min="1" max="{{ min(99, $availableStock) }}" value="1" class="tnum w-12 text-center bg-transparent font-medium text-sm text-espresso outline-none" />
                                    <button type="button" id="pdp-qty-inc" aria-label="Aumentar quantidade" class="w-10 h-full flex items-center justify-center text-base text-espresso-soft hover:text-espresso transition-colors duration-150 cursor-pointer">+</button>
                                </div>

                                <button
                                    type="button"
                                    data-add-cart
                                    data-open-after-add
                                    data-qty-input="pdp-qty"
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price-cents="{{ $salePriceCents }}"
                                    data-product-image="{{ $thumbImage ?? '' }}"
                                    data-product-url="{{ route('store.product', ['slug' => $product->slug]) }}"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-medium text-sm rounded-lg h-12 transition-[background-color,transform] duration-150 cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                                    Adicionar ao carrinho
                                </button>
                            </div>
                        @else
                            <button type="button" disabled class="w-full inline-flex items-center justify-center bg-linen text-espresso-muted font-medium text-sm rounded-lg h-12 cursor-not-allowed">
                                Produto esgotado
                            </button>
                        @endif

                        <button
                            type="button"
                            data-fav-toggle="{{ $product->id }}"
                            class="w-full inline-flex items-center justify-center gap-2 bg-paper border hairline-strong hover:bg-linen text-espresso font-medium text-sm rounded-lg h-10 transition-colors duration-150 cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                            Salvar nos favoritos
                        </button>
                    </div>

                    <!-- Simulador de Entrega / Retirada -->
                    <div class="pt-4 border-t hairline space-y-2.5">
                        <label for="cep-input" class="text-xs font-medium text-espresso block">Calcular entrega ou retirada</label>
                        <form id="cep-form" class="flex gap-2">
                            <input
                                id="cep-input"
                                type="text"
                                maxlength="9"
                                placeholder="00000-000"
                                class="flex-1 bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3 h-10 text-sm text-espresso outline-none transition-colors duration-150"
                            />
                            <button type="submit" class="px-4 h-10 rounded-lg bg-linen hover:bg-[#E5DDD0] text-xs font-medium text-espresso transition-colors duration-150 cursor-pointer">
                                Calcular
                            </button>
                        </form>
                        <div id="cep-result" class="hidden p-3 rounded-lg bg-paper border hairline"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produtos Relacionados -->
        @if ($relatedProducts->isNotEmpty())
            <section class="pt-16">
                <h2 class="font-display font-semibold text-2xl text-espresso mb-6">Combine com este item</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($relatedProducts as $related)
                        <x-store.product-card :product="$related" :available="$stockMap[$related->id] ?? null" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <!-- Barra Fixa Mobile -->
    <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-porcelain border-t hairline px-4 py-3 flex items-center justify-between gap-4">
        <div>
            <span class="text-xs text-espresso-muted block">Preço</span>
            <span class="tnum font-display font-semibold text-lg text-espresso">{{ StorefrontController::formatBrl($product->sale_price) }}</span>
        </div>
        <button
            type="button"
            data-add-cart
            data-open-after-add
            data-qty-input="pdp-qty"
            data-product-id="{{ $product->id }}"
            data-product-name="{{ $product->name }}"
            data-product-price-cents="{{ $salePriceCents }}"
            data-product-image="{{ $thumbImage ?? '' }}"
            data-product-url="{{ route('store.product', ['slug' => $product->slug]) }}"
            class="flex-1 max-w-[220px] inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg h-11"
        >
            Adicionar
        </button>
    </div>
</x-store.layout>
