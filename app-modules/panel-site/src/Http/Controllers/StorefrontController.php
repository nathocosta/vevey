<?php

declare(strict_types=1);

namespace Vevey\PanelSite\Http\Controllers;

use App\Http\Controllers\Controller;

use Brick\Money\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;
use Vevey\Inventory\Models\StockLevel;

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

        $stockMap = $this->stockMap(
            $promoProducts->merge($featuredProducts)->merge($heroProducts)
        );

        return view('store.home', [
            'navCategories' => $categories,
            'heroProducts' => $heroProducts,
            'promoProducts' => $promoProducts,
            'featuredProducts' => $featuredProducts,
            'colorValues' => $colorValues,
            'stockMap' => $stockMap,
        ]);
    }

    public function catalog(Request $request): View
    {
        $navCategories = $this->navigationCategories();

        $search = mb_substr(mb_trim((string) $request->string('q')), 0, 100);
        $categorySlug = mb_substr(mb_trim((string) $request->string('categoria')), 0, 100);
        $onlyPromo = $request->boolean('ofertas');
        $sort = (string) $request->string('ordenar', 'relevancia');

        /** @var list<string> $selectedAttributes */
        $selectedAttributes = array_slice(
            array_values(array_filter(
                array_map(strval(...), (array) $request->input('atributos', [])),
                static fn (string $value): bool => $value !== '' && mb_strlen($value) <= 60,
            )),
            0,
            20,
        );

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

        $stockMap = $this->stockMap($products->getCollection());

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
            'stockMap' => $stockMap,
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

        $stockMap = $this->stockMap($relatedProducts->concat([$product]));

        return view('store.product', [
            'navCategories' => $this->navigationCategories(),
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'colorSiblings' => $colorSiblings,
            'stockMap' => $stockMap,
            'availableStock' => $stockMap[$product->id] ?? 0,
        ]);
    }

    public function searchSuggestions(Request $request): JsonResponse
    {
        $term = mb_substr(mb_trim((string) $request->string('q')), 0, 80);

        if (mb_strlen($term) < 2) {
            return response()->json(['items' => []]);
        }

        $products = Product::query()
            ->active()
            ->search($term)
            ->with(['categories', 'media'])
            ->limit(5)
            ->get();

        $stockMap = $this->stockMap($products);

        $items = $products->map(static fn (Product $product): array => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->categories->first()?->name,
            'price' => self::formatBrl($product->sale_price),
            'available_stock' => $stockMap[$product->id] ?? 0,
            'image' => self::productImageUrl($product, thumb: true),
            'url' => route('store.product', ['slug' => $product->slug]),
        ])->values()->all();

        return response()
            ->json(['items' => $items])
            ->header('Cache-Control', 'public, max-age=30');
    }

    public function favorites(): View
    {
        return view('store.favorites', [
            'navCategories' => $this->navigationCategories(),
        ]);
    }

    public function favoriteItems(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'max:50'],
            'ids.*' => ['required', 'uuid'],
        ]);

        /** @var list<string> $ids */
        $ids = array_values(array_unique($validated['ids']));

        $products = Product::query()
            ->active()
            ->whereKey($ids)
            ->with(['categories', 'media'])
            ->get();

        $stockMap = $this->stockMap($products);

        $items = $products->map(static fn (Product $product): array => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->categories->first()?->name,
            'price' => self::formatBrl($product->sale_price),
            'price_cents' => $product->sale_price->getMinorAmount()->toInt(),
            'regular_price' => $product->promo_price_cents !== null ? self::formatBrl($product->price_cents) : null,
            'discount' => self::discountPercent($product),
            'available_stock' => $stockMap[$product->id] ?? 0,
            'image' => self::productImageUrl($product, thumb: true),
            'url' => route('store.product', ['slug' => $product->slug]),
        ])->values()->all();

        return response()->json(['items' => $items]);
    }

    public function about(): View
    {
        return view('store.pages.about', [
            'navCategories' => $this->navigationCategories(),
        ]);
    }

    public function privacy(): View
    {
        return view('store.pages.privacy', [
            'navCategories' => $this->navigationCategories(),
        ]);
    }

    public function returns(): View
    {
        return view('store.pages.returns', [
            'navCategories' => $this->navigationCategories(),
        ]);
    }

    public function sitemap(): Response
    {
        $xml = Cache::remember('vevey:sitemap:xml', now()->addHour(), static function (): string {
            $urls = [
                ['loc' => route('store.home'), 'priority' => '1.0', 'changefreq' => 'daily'],
                ['loc' => route('store.catalog'), 'priority' => '0.9', 'changefreq' => 'daily'],
                ['loc' => route('store.track'), 'priority' => '0.5', 'changefreq' => 'monthly'],
                ['loc' => route('store.about'), 'priority' => '0.5', 'changefreq' => 'monthly'],
                ['loc' => route('store.privacy'), 'priority' => '0.4', 'changefreq' => 'monthly'],
                ['loc' => route('store.returns'), 'priority' => '0.4', 'changefreq' => 'monthly'],
            ];

            $categories = Category::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->get(['slug', 'updated_at']);

            foreach ($categories as $category) {
                $urls[] = [
                    'loc' => route('store.catalog', ['categoria' => $category->slug]),
                    'lastmod' => $category->updated_at?->toAtomString(),
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                ];
            }

            $products = Product::query()
                ->active()
                ->orderByDesc('updated_at')
                ->get(['slug', 'updated_at']);

            foreach ($products as $product) {
                $urls[] = [
                    'loc' => route('store.product', ['slug' => $product->slug]),
                    'lastmod' => $product->updated_at?->toAtomString(),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                ];
            }

            $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
            foreach ($urls as $entry) {
                $lines[] = '  <url>';
                $lines[] = '    <loc>' . e($entry['loc']) . '</loc>';
                if (! empty($entry['lastmod'])) {
                    $lines[] = '    <lastmod>' . e($entry['lastmod']) . '</lastmod>';
                }
                $lines[] = '    <changefreq>' . $entry['changefreq'] . '</changefreq>';
                $lines[] = '    <priority>' . $entry['priority'] . '</priority>';
                $lines[] = '  </url>';
            }
            $lines[] = '</urlset>';

            return implode("\n", $lines);
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
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

            if (! $thumb && $media->hasGeneratedConversion(Product::LARGE_CONVERSION)) {
                return $media->getUrl(Product::LARGE_CONVERSION);
            }

            return $media->getUrl();
        }

        $skuFile = mb_strtolower($product->sku);
        $candidates = $thumb
            ? ["images/produtos/{$skuFile}-thumb.webp", "images/produtos/{$skuFile}.webp", "images/produtos/{$skuFile}.png", "images/produtos/{$skuFile}.jpg"]
            : ["images/produtos/{$skuFile}.webp", "images/produtos/{$skuFile}.png", "images/produtos/{$skuFile}.jpg"];

        foreach ($candidates as $relative) {
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        $categoryBase = match ($product->categories->first()?->slug) {
            'baloes' => 'images/produtos/bal-met-9-az',
            'descartaveis' => 'images/produtos/prt-desc-az',
            'chocolates' => 'images/produtos/doc-kitkat-cx24',
            'balas-e-guloseimas' => 'images/produtos/doc-bala-fini-500',
            'embalagens-e-papeloes' => 'images/produtos/emb-pap-doce-25',
            default => null,
        };

        if ($categoryBase !== null) {
            $fallbackCandidates = $thumb
                ? ["{$categoryBase}-thumb.webp", "{$categoryBase}.webp", "{$categoryBase}.jpg"]
                : ["{$categoryBase}.webp", "{$categoryBase}.jpg"];

            foreach ($fallbackCandidates as $fallback) {
                if (is_file(public_path($fallback))) {
                    return asset($fallback);
                }
            }
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
     * @param  iterable<int, Product>  $products
     * @return array<string, int>
     */
    private function stockMap(iterable $products): array
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[$product->id] = $product->id;
        }

        if ($ids === []) {
            return [];
        }

        return StockLevel::query()
            ->whereIn('product_id', array_values($ids))
            ->get(['product_id', 'on_hand', 'reserved'])
            ->mapWithKeys(static fn (StockLevel $level): array => [
                $level->product_id => max(0, $level->on_hand - $level->reserved),
            ])
            ->all();
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
