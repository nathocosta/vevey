<x-store.layout
    :title="($activeCategory ? $activeCategory->name . ' — ' : '') . 'Catálogo — Vevey Doces'"
    :nav-categories="$navCategories"
    :search="$search"
    :active-category-slug="$activeCategory?->slug"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <!-- Breadcrumb -->
        <nav aria-label="Navegação estrutural" class="text-xs text-espresso-muted flex items-center gap-2 mb-6 flex-wrap">
            <a href="{{ route('store.home') }}" class="hover:text-espresso transition-colors duration-150">Início</a>
            <span>/</span>
            <a href="{{ route('store.catalog') }}" class="hover:text-espresso transition-colors duration-150">Catálogo</a>
            @if ($activeCategory?->parent)
                <span>/</span>
                <a href="{{ route('store.catalog', ['categoria' => $activeCategory->parent->slug]) }}" class="hover:text-espresso transition-colors duration-150">
                    {{ $activeCategory->parent->name }}
                </a>
            @endif
            @if ($activeCategory)
                <span>/</span>
                <span class="text-espresso font-medium">{{ $activeCategory->name }}</span>
            @endif
        </nav>

        <!-- Cabeçalho do Catálogo -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b hairline">
            <div>
                <h1 class="font-display font-semibold text-2xl sm:text-3xl text-espresso">
                    @if ($search !== '')
                        Resultados para "{{ $search }}"
                    @elseif ($activeCategory)
                        {{ $activeCategory->name }}
                    @elseif ($onlyPromo)
                        Ofertas da semana
                    @else
                        Todos os produtos
                    @endif
                </h1>
                <p class="text-sm text-espresso-muted mt-1 tnum">
                    {{ $products->total() }} {{ $products->total() === 1 ? 'produto encontrado' : 'produtos encontrados' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    id="mobile-filter-toggle"
                    class="lg:hidden inline-flex items-center gap-2 bg-porcelain border hairline-strong rounded-lg px-4 h-10 text-sm font-medium text-espresso cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px] text-espresso-muted">tune</span>
                    Filtros
                </button>

                <form action="{{ route('store.catalog') }}" method="GET" class="flex items-center gap-2">
                    @if ($search !== '')
                        <input type="hidden" name="q" value="{{ $search }}" />
                    @endif
                    @if ($activeCategory)
                        <input type="hidden" name="categoria" value="{{ $activeCategory->slug }}" />
                    @endif
                    @if ($onlyPromo)
                        <input type="hidden" name="ofertas" value="1" />
                    @endif
                    @foreach ($selectedAttributes as $attrSlug)
                        <input type="hidden" name="atributos[]" value="{{ $attrSlug }}" />
                    @endforeach

                    <label for="sort-select" class="text-xs font-medium text-espresso-muted hidden sm:inline">Ordenar</label>
                    <select
                        id="sort-select"
                        name="ordenar"
                        onchange="this.form.submit()"
                        class="bg-porcelain border hairline-strong focus:border-espresso/50 rounded-lg px-3 h-10 text-sm text-espresso outline-none"
                    >
                        <option value="relevancia" @selected($sort === 'relevancia')>Mais relevantes</option>
                        <option value="menor_preco" @selected($sort === 'menor_preco')>Menor preço</option>
                        <option value="maior_preco" @selected($sort === 'maior_preco')>Maior preço</option>
                        <option value="novidades" @selected($sort === 'novidades')>Novidades</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="grid lg:grid-cols-12 gap-8 pt-8">
            <!-- Sidebar de Filtros -->
            <aside id="catalog-filters" class="hidden lg:block lg:col-span-3 space-y-6">
                <form action="{{ route('store.catalog') }}" method="GET" class="bg-porcelain border hairline rounded-xl p-5 space-y-6">
                    @if ($search !== '')
                        <input type="hidden" name="q" value="{{ $search }}" />
                    @endif
                    @if ($activeCategory)
                        <input type="hidden" name="categoria" value="{{ $activeCategory->slug }}" />
                    @endif
                    <input type="hidden" name="ordenar" value="{{ $sort }}" />

                    <!-- Categorias -->
                    <div>
                        <h2 class="font-display font-medium text-sm text-espresso mb-3">Departamentos</h2>
                        <ul class="space-y-1 text-sm">
                            <li>
                                <a
                                    href="{{ route('store.catalog', array_filter(['q' => $search, 'ofertas' => $onlyPromo ? 1 : null])) }}"
                                    class="block py-1 transition-colors duration-150 {{ $activeCategory === null ? 'font-medium text-brand-700' : 'text-espresso-soft hover:text-espresso' }}"
                                >
                                    Todos os departamentos
                                </a>
                            </li>
                            @foreach ($navCategories as $category)
                                <li>
                                    <a
                                        href="{{ route('store.catalog', array_filter(['categoria' => $category->slug, 'q' => $search, 'ofertas' => $onlyPromo ? 1 : null])) }}"
                                        class="block py-1 transition-colors duration-150 {{ $activeCategory?->id === $category->id ? 'font-medium text-brand-700' : 'text-espresso hover:text-brand-700' }}"
                                    >
                                        {{ $category->name }}
                                    </a>
                                    @if ($category->children->isNotEmpty())
                                        <ul class="pl-3 mt-1 space-y-1 border-l hairline">
                                            @foreach ($category->children as $child)
                                                <li>
                                                    <a
                                                        href="{{ route('store.catalog', array_filter(['categoria' => $child->slug, 'q' => $search, 'ofertas' => $onlyPromo ? 1 : null])) }}"
                                                        class="block py-0.5 text-xs transition-colors duration-150 {{ $activeCategory?->id === $child->id ? 'font-medium text-brand-700' : 'text-espresso-muted hover:text-espresso' }}"
                                                    >
                                                        {{ $child->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Ofertas -->
                    <div class="pt-5 border-t hairline">
                        <label class="flex items-center gap-2.5 cursor-pointer text-sm font-medium text-espresso">
                            <input
                                type="checkbox"
                                name="ofertas"
                                value="1"
                                @checked($onlyPromo)
                                class="w-4 h-4 rounded-xs border hairline-strong accent-brand-600"
                            />
                            Somente ofertas
                        </label>
                    </div>

                    <!-- Atributos do catálogo (Cor, Tipo, Tamanho) -->
                    @foreach ($attributes as $attribute)
                        <div class="pt-5 border-t hairline">
                            <h3 class="font-display font-medium text-sm text-espresso mb-3">{{ $attribute->name }}</h3>
                            <div class="space-y-2">
                                @foreach ($attribute->values as $value)
                                    <label class="flex items-center gap-2.5 cursor-pointer text-sm text-espresso-soft hover:text-espresso">
                                        <input
                                            type="checkbox"
                                            name="atributos[]"
                                            value="{{ $value->slug }}"
                                            @checked(in_array($value->slug, $selectedAttributes, true))
                                            class="w-4 h-4 rounded-xs border hairline-strong accent-brand-600"
                                        />
                                        <span>{{ $value->value }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-4 border-t hairline flex flex-col gap-2">
                        <button type="submit" class="w-full bg-espresso hover:bg-[#332D25] active:scale-[0.98] text-porcelain font-medium text-sm rounded-lg h-10 transition-[background-color,transform] duration-150 cursor-pointer">
                            Aplicar filtros
                        </button>
                        @if ($search !== '' || $activeCategory !== null || $onlyPromo || $selectedAttributes !== [])
                            <a href="{{ route('store.catalog') }}" class="w-full inline-flex items-center justify-center text-xs font-medium text-espresso-muted hover:text-espresso py-1.5 transition-colors duration-150">
                                Limpar filtros
                            </a>
                        @endif
                    </div>
                </form>
            </aside>

            <!-- Grade de Produtos -->
            <div class="lg:col-span-9 space-y-8">
                @if ($products->isNotEmpty())
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                        @foreach ($products as $product)
                            <x-store.product-card :product="$product" />
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="pt-4">
                            {{ $products->links() }}
                        </div>
                    @endif
                @else
                    <div class="bg-porcelain border hairline rounded-2xl p-12 text-center space-y-4">
                        <span class="material-symbols-outlined text-[24px] text-espresso-muted">search_off</span>
                        <h2 class="font-display font-medium text-lg text-espresso">Nenhum produto combina com esses filtros</h2>
                        <p class="text-sm text-espresso-soft max-w-md mx-auto">
                            Busque por outro termo, remova um atributo selecionado ou explore todos os departamentos.
                        </p>
                        <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center bg-espresso hover:bg-[#332D25] text-porcelain font-medium text-sm rounded-lg px-6 h-10 transition-colors duration-150">
                            Ver todos os produtos
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-store.layout>
