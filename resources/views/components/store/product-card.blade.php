@props(['product'])

@php
    use App\Http\Controllers\StorefrontController;

    $imageUrl = StorefrontController::productImageUrl($product);
    $discount = StorefrontController::discountPercent($product);
    $salePriceCents = $product->sale_price->getMinorAmount()->toInt();
    $primaryCategory = $product->categories->first();
    $productUrl = route('store.product', ['slug' => $product->slug]);

    $placeholderIcon = match (mb_strtolower($primaryCategory?->slug ?? '')) {
        'caixas-de-doces', 'doces', 'balas', 'chocolates' => 'shopping_bag',
        'baloes', 'festa' => 'celebration',
        'descartaveis', 'papeloes', 'embalagens' => 'inventory_2',
        default => 'inventory_2',
    };
@endphp

<article class="bg-porcelain rounded-xl border hairline overflow-hidden flex flex-col">
    <div class="relative aspect-square overflow-hidden bg-linen border-b hairline">
        <a href="{{ $productUrl }}" class="block w-full h-full">
            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="w-full h-full object-cover"
                />
            @else
                <div class="w-full h-full flex flex-col items-center justify-center p-4">
                    <span class="material-symbols-outlined text-[28px] text-espresso-muted">{{ $placeholderIcon }}</span>
                    <span class="mt-2 font-mono text-xs text-espresso-muted">{{ $product->sku }}</span>
                </div>
            @endif
        </a>

        @if ($discount !== null)
            <span class="absolute top-3 left-3 px-2 py-1 rounded-md bg-brand-600 text-white text-xs font-medium tnum">
                -{{ $discount }}%
            </span>
        @elseif ($primaryCategory)
            <span class="absolute top-3 left-3 px-2 py-1 rounded-md bg-porcelain/95 border hairline text-espresso text-xs font-medium">
                {{ $primaryCategory->name }}
            </span>
        @endif

        <button
            type="button"
            data-fav-toggle="{{ $product->id }}"
            aria-label="Salvar {{ $product->name }} nos favoritos"
            class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-porcelain/95 border hairline flex items-center justify-center text-espresso-muted hover:text-espresso transition-colors duration-150 cursor-pointer"
        >
            <span class="material-symbols-outlined text-[18px]">favorite</span>
        </button>
    </div>

    <div class="p-4 flex flex-col flex-1">
        @if ($primaryCategory)
            <span class="text-xs text-espresso-muted">{{ $primaryCategory->name }}</span>
        @endif

        <h3 class="font-display font-medium text-sm sm:text-base text-espresso leading-snug mt-1 line-clamp-2">
            <a href="{{ $productUrl }}" class="hover:text-brand-700 transition-colors duration-150">
                {{ $product->name }}
            </a>
        </h3>

        <div class="mt-auto pt-3 space-y-0.5">
            <div class="flex items-baseline gap-2 flex-wrap">
                <span class="tnum font-display font-semibold text-base text-espresso">
                    {{ StorefrontController::formatBrl($product->sale_price) }}
                </span>
                @if ($product->promo_price_cents !== null)
                    <span class="tnum text-xs text-espresso-muted line-through">
                        {{ StorefrontController::formatBrl($product->price_cents) }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-espresso-muted">
                <span class="tnum text-positive font-medium">{{ 'R$ ' . number_format(($salePriceCents * 0.95) / 100, 2, ',', '.') }}</span> no Pix
            </p>
        </div>

        <button
            type="button"
            data-add-cart
            data-product-id="{{ $product->id }}"
            data-product-name="{{ $product->name }}"
            data-product-price-cents="{{ $salePriceCents }}"
            data-product-image="{{ $imageUrl ?? '' }}"
            data-product-url="{{ $productUrl }}"
            class="mt-4 w-full inline-flex items-center justify-center gap-2 bg-espresso hover:bg-[#332D25] active:scale-[0.98] text-porcelain font-medium text-sm rounded-lg h-10 transition-[background-color,transform] duration-150 cursor-pointer"
        >
            <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
            Adicionar
        </button>
    </div>
</article>
