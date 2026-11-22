<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Brick\Money\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Vevey\Catalog\Models\Attribute;
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

        return view('store.home', [
            'navCategories' => $categories,
            'heroProducts' => $heroProducts,
            'promoProducts' => $promoProducts,
            'featuredProducts' => $featuredProducts,
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

        if ($selectedAttributes !== []) {
            $query->whereHas('attributeValues', static function (Builder $builder) use ($selectedAttributes): void {
                $builder->whereIn('attribute_values.slug', $selectedAttributes);
            });
        }

        match ($sort) {
            'menor_preco' => $query->orderByRaw('COALESCE(promo_price_cents, price_cents) ASC'),
            'maior_preco' => $query->orderByRaw('COALESCE(promo_price_cents, price_cents) DESC'),
            'novidades' => $query->latest(),
            default => $query->orderByRaw('CASE WHEN promo_price_cents IS NOT NULL THEN 0 ELSE 1 END')->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $attributes = Attribute::query()
            ->with(['values' => fn ($q) => $q->whereHas('products', fn ($p) => $p->active())->orderBy('value')])
            ->whereHas('values.products', fn ($p) => $p->active())
            ->orderBy('name')
            ->get();

        return view('store.catalog', [
            'navCategories' => $navCategories,
            'products' => $products,
            'attributes' => $attributes,
            'activeCategory' => $activeCategory,
            'search' => $search,
            'onlyPromo' => $onlyPromo,
            'sort' => $sort,
            'selectedAttributes' => $selectedAttributes,
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

        return view('store.product', [
            'navCategories' => $this->navigationCategories(),
            'product' => $product,
            'relatedProducts' => $relatedProducts,
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
