<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Brick\Money\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;

final class StorefrontController extends Controller
{
    public function home(): View
    {
        $categories = $this->navigationCategories();

        $promoProducts = Product::query()
            ->active()
            ->whereNotNull('promo_price_cents')
            ->with(['categories', 'attributeValues.attribute', 'media'])
            ->latest()
            ->limit(4)
            ->get();

        $featuredProducts = Product::query()
            ->active()
            ->with(['categories', 'attributeValues.attribute', 'media'])
            ->latest()
            ->limit(8)
            ->get();

        $heroProducts = $promoProducts->isNotEmpty()
            ? $promoProducts->take(3)
            : $featuredProducts->take(3);

        $colorValues = AttributeValue::query()
            ->whereHas('attribute', static fn (Builder $query): Builder => $query->where('slug', 'cor'))
            ->whereHas('products', static fn (Builder $query): Builder => $query->active())
            ->withCount(['products' => static fn (Builder $query): Builder => $query->active()])
            ->orderBy('value')
            ->get();

        return view('store.home', [
            'navCategories' => $categories,
            'heroProducts' => $heroProducts,
            'promoProducts' => $promoProducts,
            'featuredProducts' => $featuredProducts,
            'colorValues' => $colorValues,
        ]);
    }

    public function catalog(Request $request): View
    {
        $navCategories = $this->navigationCategories();

        $search = mb_trim((string) $request->string('q'));
        $categorySlug = mb_trim((string) $request->string('categoria'));
        $onlyPromo = $request->boolean('ofertas');
        $sort = (string) $request->string('ordenar', 'relevancia');

        /** @var list<string> $selectedAttributes */
        $selectedAttributes = array_values(array_filter(
            array_map(strval(...), (array) $request->input('atributos', [])),
            static fn (string $value): bool => $value !== '',
        ));

        $activeCategory = $categorySlug !== ''
            ? Category::query()
                ->where('is_active', true)
                ->where('slug', $categorySlug)
                ->with(['parent', 'children' => fn ($query) => $query->where('is_active', true)->orderBy('position')])
                ->first()
            : null;

        $query = Product::query()
            ->active()
            ->with(['categories', 'attributeValues.attribute', 'media']);

        if ($search !== '') {
            $query->search($search);
        }

        if ($activeCategory instanceof Category) {
            $categoryIds = collect([$activeCategory->id])
                ->merge($activeCategory->children->pluck('id'))
                ->all();

            $query->whereHas('categories', static function (Builder $builder) use ($categoryIds): void {
                $builder->whereIn('categories.id', $categoryIds);
            });
        }

        if ($onlyPromo) {
            $query->whereNotNull('promo_price_cents');
        }

        // ADR 6: valores do mesmo atributo são OU; atributos diferentes são E.
        if ($selectedAttributes !== []) {
            $groupedByAttribute = AttributeValue::query()
                ->whereIn('slug', $selectedAttributes)
                ->get(['id', 'attribute_id'])
                ->groupBy('attribute_id');

            foreach ($groupedByAttribute as $attributeValues) {
                $valueIds = $attributeValues->pluck('id')->all();

                $query->whereHas('attributeValues', static function (Builder $builder) use ($valueIds): void {
                    $builder->whereIn('attribute_values.id', $valueIds);
                });
            }
        }

        match ($sort) {
            'menor_preco' => $query->orderByRaw('COALESCE(promo_price_cents, price_cents) ASC'),
            'maior_preco' => $query->orderByRaw('COALESCE(promo_price_cents, price_cents) DESC'),
            'novidades' => $query->latest(),
            default => $query->orderByRaw('CASE WHEN promo_price_cents IS NOT NULL THEN 0 ELSE 1 END')->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $attributes = Attribute::query()
            ->with([
                'values' => fn ($q) => $q
                    ->whereHas('products', fn ($p) => $p->active())
                    ->withCount(['products' => fn ($p) => $p->active()])
                    ->orderBy('value'),
            ])
            ->whereHas('values.products', fn ($p) => $p->active())
            ->orderBy('name')
            ->get();

        $activeAttributeModels = $selectedAttributes !== []
            ? AttributeValue::query()
                ->whereIn('slug', $selectedAttributes)
                ->with('attribute')
                ->get()
            : collect();

        return view('store.catalog', [
            'navCategories' => $navCategories,
            'products' => $products,
            'attributes' => $attributes,
            'activeCategory' => $activeCategory,
            'search' => $search,
            'onlyPromo' => $onlyPromo,
            'sort' => $sort,
            'selectedAttributes' => $selectedAttributes,
            'activeAttributeModels' => $activeAttributeModels,
        ]);
    }

    public function product(string $slug): View
    {
        $product = Product::query()
            ->active()
            ->where('slug', $slug)
            ->with(['categories.parent', 'attributeValues.attribute', 'media'])
            ->firstOrFail();

        $categoryIds = $product->categories->pluck('id')->all();

        $relatedProducts = Product::query()
            ->active()
            ->whereKeyNot($product->id)
            ->when($categoryIds !== [], static function (Builder $builder) use ($categoryIds): void {
                $builder->whereHas('categories', static function (Builder $catQuery) use ($categoryIds): void {
                    $catQuery->whereIn('categories.id', $categoryIds);
                });
            })
            ->with(['categories', 'attributeValues.attribute', 'media'])
            ->limit(4)
            ->get();

        // Produtos irmãos por cor (ADR 6: catálogo plano, cada cor é um produto)
        $hasColor = $product->attributeValues->contains(
            static fn (AttributeValue $val): bool => $val->attribute->slug === 'cor'
        );

        $colorSiblings = collect();
        if ($hasColor && $categoryIds !== []) {
            $nonColorIds = $product->attributeValues
                ->filter(static fn (AttributeValue $val): bool => $val->attribute->slug !== 'cor')
                ->pluck('id')
                ->all();

            $siblingQuery = Product::query()
                ->active()
                ->whereHas('categories', static function (Builder $builder) use ($categoryIds): void {
                    $builder->whereIn('categories.id', $categoryIds);
                })
                ->whereHas('attributeValues.attribute', static function (Builder $builder): void {
                    $builder->where('slug', 'cor');
                })
                ->with(['attributeValues.attribute']);

            foreach ($nonColorIds as $nonColorId) {
                $siblingQuery->whereHas('attributeValues', static function (Builder $builder) use ($nonColorId): void {
                    $builder->where('attribute_values.id', $nonColorId);
                });
            }

            $colorSiblings = $siblingQuery->limit(8)->get();
        }

        return view('store.product', [
            'navCategories' => $this->navigationCategories(),
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'colorSiblings' => $colorSiblings,
        ]);
    }

    public function searchSuggestions(Request $request): JsonResponse
    {
        $term = mb_trim((string) $request->string('q'));

        if (mb_strlen($term) < 2) {
            return response()->json(['items' => []]);
        }

        $products = Product::query()
            ->active()
            ->search($term)
            ->with(['categories', 'media'])
            ->limit(5)
            ->get();

        $items = $products->map(static fn (Product $product): array => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->categories->first()?->name,
            'price' => self::formatBrl($product->sale_price),
            'image' => self::productImageUrl($product),
            'url' => route('store.product', ['slug' => $product->slug]),
        ])->values()->all();

        return response()->json(['items' => $items]);
    }

    public static function formatBrl(Money $money): string
    {
        $cents = $money->getMinorAmount()->toInt();

        return 'R$ ' . number_format($cents / 100, 2, ',', '.');
    }

    public static function discountPercent(Product $product): ?int
    {
        if (! $product->promo_price_cents instanceof Money) {
            return null;
        }

        $regular = $product->price_cents->getMinorAmount()->toInt();
        $promo = $product->promo_price_cents->getMinorAmount()->toInt();

        if ($regular <= 0 || $promo >= $regular) {
            return null;
        }

        return (int) round((($regular - $promo) / $regular) * 100);
    }

    public static function productImageUrl(Product $product, bool $thumb = true): ?string
    {
        $media = $product->getFirstMedia(Product::IMAGES_COLLECTION);

        if ($media !== null) {
            if ($thumb && $media->hasGeneratedConversion(Product::THUMB_CONVERSION)) {
                return $media->getUrl(Product::THUMB_CONVERSION);
            }

            return $media->getUrl();
        }

        $skuFile = mb_strtolower($product->sku);
        foreach (['png', 'webp', 'jpg'] as $ext) {
            $relative = "images/produtos/{$skuFile}.{$ext}";
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        $categoryFallback = match ($product->categories->first()?->slug) {
            'baloes' => 'images/produtos/bal-met-9-az.jpg',
            'descartaveis' => 'images/produtos/prt-desc-az.jpg',
            'chocolates' => 'images/produtos/doc-kitkat-cx24.jpg',
            'balas-e-guloseimas' => 'images/produtos/doc-bala-fini-500.jpg',
            'embalagens-e-papeloes' => 'images/produtos/emb-pap-doce-25.jpg',
            default => null,
        };

        if ($categoryFallback !== null && is_file(public_path($categoryFallback))) {
            return asset($categoryFallback);
        }

        return null;
    }

    public static function colorSwatchHex(string $colorSlug): string
    {
        return match (mb_strtolower($colorSlug)) {
            'azul' => '#2563EB',
            'rosa' => '#EC4899',
            'dourado' => '#D97706',
            'branco' => '#FDFBF7',
            'vermelho' => '#E11D48',
            'verde' => '#16A34A',
            'roxo' => '#7C3AED',
            'preto' => '#1F1B16',
            default => '#A8A29E',
        };
    }

    /**
     * @return Collection<int, Category>
     */
    private function navigationCategories(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with([
                'children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->withCount(['products' => fn ($p) => $p->active()])
                    ->orderBy('position'),
            ])
            ->withCount(['products' => fn ($p) => $p->active()])
            ->orderBy('position')
            ->get();
    }
}
