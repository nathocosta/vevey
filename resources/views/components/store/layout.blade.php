@props([
    'title' => 'Vevey Doces — Doces, Embalagens e Artigos para Festa',
    'navCategories' => collect(),
    'search' => '',
    'activeCategorySlug' => null,
])

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />

    @fonts
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,350,0,0" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-paper text-espresso antialiased selection:bg-brand-100 selection:text-brand-900">

    <!-- Faixa de condições comerciais -->
    <div class="bg-espresso-dark text-[#EFE9DF] text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-center sm:justify-between gap-6">
            <span>Frete grátis acima de <span class="tnum font-medium text-porcelain">R$ 150,00</span></span>
            <div class="hidden sm:flex items-center gap-6 text-[#AAA398]">
                <span>5% de desconto no Pix</span>
                <span>Retirada na loja no mesmo dia</span>
            </div>
        </div>
    </div>

    <!-- Header principal -->
    <header class="sticky top-0 z-30 bg-porcelain/95 backdrop-blur-sm border-b hairline">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('store.home') }}" class="inline-flex items-center shrink-0 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600 rounded-md">
                <x-brand.logo />
            </a>

            <!-- Busca Preditiva (Desktop) -->
            <div class="hidden md:block relative flex-1 max-w-md" id="live-search-wrapper" data-suggestions-url="{{ route('store.search.suggestions') }}">
                <form action="{{ route('store.catalog') }}" method="GET" class="flex items-center bg-paper border hairline focus-within:border-espresso/40 focus-within:bg-porcelain rounded-lg h-10 pl-3 pr-1 transition-colors duration-150">
                    <span class="material-symbols-outlined text-[18px] text-espresso-muted mr-2 shrink-0">search</span>
                    <input
                        id="live-search-input"
                        type="search"
                        name="q"
                        autocomplete="off"
                        value="{{ $search }}"
                        placeholder="Buscar doces, balões, descartáveis ou SKU"
                        class="w-full bg-transparent text-sm text-espresso placeholder:text-espresso-muted outline-none"
                    />
                    <button type="submit" class="px-3 h-8 rounded-md bg-porcelain border hairline hover:bg-linen text-xs font-medium text-espresso transition-colors duration-150 shrink-0 cursor-pointer">
                        Buscar
                    </button>
                </form>
                <div id="live-search-results" class="hidden absolute top-12 inset-x-0 z-40 bg-porcelain border hairline-strong rounded-xl shadow-[var(--shadow-float)] overflow-hidden"></div>
            </div>

            <!-- Navegação direita -->
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <a href="{{ route('store.catalog', ['ofertas' => 1]) }}" class="hidden lg:inline-block text-sm font-medium text-espresso-soft hover:text-espresso transition-colors duration-150">
                    Ofertas
                </a>

                <a href="{{ url('/admin') }}" class="hidden sm:inline-block text-sm font-medium text-espresso-muted hover:text-espresso transition-colors duration-150">
                    Painel
                </a>

                <a
                    href="{{ auth('customers')->check() ? route('store.account') : route('store.login') }}"
                    class="inline-flex items-center gap-1.5 h-10 px-3 rounded-lg border hairline bg-paper hover:bg-linen text-xs font-medium text-espresso transition-colors duration-150"
                >
                    <span class="material-symbols-outlined text-[18px]">person</span>
                    <span class="hidden sm:inline">{{ auth('customers')->check() ? \Illuminate\Support\Str::words(auth('customers')->user()->name, 1, '') : 'Minha conta' }}</span>
                </a>

                <button
                    type="button"
                    data-open-cart
                    class="inline-flex items-center gap-2 bg-espresso hover:bg-[#332D25] active:scale-[0.98] text-porcelain font-medium text-sm rounded-lg px-4 h-10 transition-[background-color,transform] duration-150 cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                    <span class="hidden sm:inline">Carrinho</span>
                    <span data-cart-count class="hidden tnum inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-md bg-brand-600 text-white text-xs font-medium">0</span>
                </button>
            </div>
        </div>

        <!-- Busca Mobile -->
        <div class="md:hidden px-4 pb-3">
            <form action="{{ route('store.catalog') }}" method="GET" class="flex items-center bg-paper border hairline focus-within:border-espresso/40 focus-within:bg-porcelain rounded-lg h-10 pl-3 pr-2 transition-colors duration-150">
                <span class="material-symbols-outlined text-[18px] text-espresso-muted mr-2 shrink-0">search</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Buscar doces, balões, descartáveis ou SKU"
                    class="w-full bg-transparent text-sm text-espresso placeholder:text-espresso-muted outline-none"
                />
            </form>
        </div>

        <!-- Barra de Categorias -->
        <nav aria-label="Departamentos" class="border-t hairline bg-porcelain">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-11 flex items-center gap-1 overflow-x-auto">
                <a
                    href="{{ route('store.catalog') }}"
                    class="shrink-0 px-3 h-8 inline-flex items-center rounded-md text-xs font-medium transition-colors duration-150 {{ $activeCategorySlug === null && request()->routeIs('store.catalog') && ! request()->boolean('ofertas') ? 'bg-linen text-espresso' : 'text-espresso-soft hover:text-espresso hover:bg-paper' }}"
                >
                    Todo o catálogo
                </a>

                @foreach ($navCategories as $category)
                    <a
                        href="{{ route('store.catalog', ['categoria' => $category->slug]) }}"
                        class="shrink-0 px-3 h-8 inline-flex items-center rounded-md text-xs font-medium transition-colors duration-150 {{ $activeCategorySlug === $category->slug ? 'bg-linen text-espresso' : 'text-espresso-soft hover:text-espresso hover:bg-paper' }}"
                    >
                        {{ $category->name }}
                    </a>
                    @foreach ($category->children as $child)
                        <a
                            href="{{ route('store.catalog', ['categoria' => $child->slug]) }}"
                            class="shrink-0 px-3 h-8 inline-flex items-center rounded-md text-xs font-normal transition-colors duration-150 {{ $activeCategorySlug === $child->slug ? 'bg-linen text-espresso font-medium' : 'text-espresso-muted hover:text-espresso hover:bg-paper' }}"
                        >
                            {{ $child->name }}
                        </a>
                    @endforeach
                @endforeach

                <a
                    href="{{ route('store.catalog', ['ofertas' => 1]) }}"
                    class="shrink-0 px-3 h-8 inline-flex items-center rounded-md text-xs font-medium transition-colors duration-150 {{ request()->boolean('ofertas') ? 'bg-brand-50 text-brand-700' : 'text-brand-700 hover:bg-brand-50/70' }}"
                >
                    Ofertas da semana
                </a>
            </div>
        </nav>
    </header>

    <!-- Conteúdo -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Rodapé -->
    <footer class="bg-espresso-dark text-[#EFE9DF] mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div class="space-y-3">
                <a href="{{ route('store.home') }}" class="inline-flex items-center">
                    <x-brand.logo theme="dark" size="sm" />
                </a>
                <p class="text-sm text-[#AAA398] leading-relaxed">
                    Caixas de doces, balas, balões, descartáveis e embalagens de papelão no varejo ou em pacotes fechados.
                </p>
            </div>

            <div>
                <h3 class="font-display font-medium text-sm text-porcelain mb-4">Departamentos</h3>
                <ul class="space-y-2.5 text-sm text-[#AAA398]">
                    <li><a href="{{ route('store.catalog') }}" class="hover:text-porcelain transition-colors duration-150">Catálogo completo</a></li>
                    @foreach ($navCategories as $category)
                        <li><a href="{{ route('store.catalog', ['categoria' => $category->slug]) }}" class="hover:text-porcelain transition-colors duration-150">{{ $category->name }}</a></li>
                    @endforeach
                    <li><a href="{{ route('store.catalog', ['ofertas' => 1]) }}" class="hover:text-porcelain transition-colors duration-150">Ofertas da semana</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-display font-medium text-sm text-porcelain mb-4">Atendimento</h3>
                <ul class="space-y-2.5 text-sm text-[#AAA398]">
                    <li>Segunda a sábado, 08h às 19h</li>
                    <li>Retirada na loja no mesmo dia</li>
                    <li>Varejo e caixas fechadas para festas</li>
                </ul>
            </div>

            <div>
                <h3 class="font-display font-medium text-sm text-porcelain mb-4">Pagamento</h3>
                <div class="flex flex-wrap gap-2 text-xs text-[#EFE9DF]">
                    <span class="px-2.5 py-1 rounded-md bg-white/8 border border-white/10">Pix (5% OFF)</span>
                    <span class="px-2.5 py-1 rounded-md bg-white/8 border border-white/10">Crédito</span>
                    <span class="px-2.5 py-1 rounded-md bg-white/8 border border-white/10">Débito</span>
                </div>
                <p class="text-xs text-[#8A847A] mt-4">Valores em Reais (BRL) calculados em centavos no servidor</p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[#8A847A]">
                <span>© {{ date('Y') }} Vevey Doces</span>
                <a href="{{ url('/admin') }}" class="hover:text-porcelain transition-colors duration-150">Acesso administrativo</a>
            </div>
        </div>
    </footer>

    <!-- Drawer de Carrinho -->
    <div id="cart-drawer" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Carrinho de compras">
        <div id="cart-backdrop" data-close-cart class="fixed inset-0 bg-espresso/45 opacity-0 transition-opacity duration-200"></div>

        <aside id="cart-panel" class="fixed top-0 right-0 bottom-0 w-full max-w-[420px] bg-porcelain shadow-[var(--shadow-overlay)] translate-x-full transition-transform duration-200 ease-[var(--ease-ui)] flex flex-col">
            <div class="px-6 h-16 border-b hairline flex items-center justify-between">
                <h2 class="font-display font-semibold text-base text-espresso">Carrinho</h2>
                <button type="button" data-close-cart aria-label="Fechar carrinho" class="w-9 h-9 rounded-lg hover:bg-linen flex items-center justify-center text-espresso-muted hover:text-espresso transition-colors duration-150 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <!-- Progresso Frete Grátis -->
            <div class="px-6 py-4 bg-paper border-b hairline space-y-2">
                <p id="cart-shipping-text" class="text-xs text-espresso-soft">Frete grátis a partir de <span class="tnum font-medium text-espresso">R$ 150,00</span></p>
                <div class="w-full h-1.5 rounded-full bg-linen overflow-hidden">
                    <div id="cart-shipping-bar" class="h-full w-full origin-left rounded-full bg-brand-600 transition-transform duration-300 ease-[var(--ease-ui)]" style="transform: scaleX(0)"></div>
                </div>
            </div>

            <!-- Lista de itens -->
            <div class="flex-1 overflow-y-auto px-6">
                <div id="cart-empty" class="py-16 text-center space-y-3">
                    <span class="material-symbols-outlined text-[24px] text-espresso-muted">shopping_bag</span>
                    <p class="font-display font-medium text-base text-espresso">Seu carrinho está vazio</p>
                    <p class="text-sm text-espresso-soft max-w-xs mx-auto">Adicione caixas de doces, embalagens ou artigos de festa para montar o pedido.</p>
                    <button type="button" data-close-cart class="mt-2 inline-flex items-center justify-center bg-espresso hover:bg-[#332D25] text-porcelain font-medium text-sm rounded-lg px-5 h-10 transition-colors duration-150 cursor-pointer">
                        Explorar catálogo
                    </button>
                </div>

                <ul id="cart-items"></ul>
            </div>

            <!-- Resumo do Carrinho -->
            <div id="cart-footer" class="hidden px-6 py-5 border-t hairline bg-paper space-y-4">
                <div class="space-y-1.5 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-espresso-soft">Subtotal</span>
                        <span id="cart-subtotal" class="tnum font-display font-semibold text-base text-espresso">R$ 0,00</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-positive font-medium">No Pix (5% OFF)</span>
                        <span id="cart-pix-total" class="tnum font-medium text-positive">R$ 0,00</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <a href="{{ route('store.checkout') }}" class="w-full inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-medium text-sm rounded-lg h-11 transition-[background-color,transform] duration-150">
                        Finalizar pedido
                    </a>
                    <button type="button" data-close-cart class="w-full text-xs font-medium text-espresso-muted hover:text-espresso py-1.5 transition-colors duration-150 cursor-pointer">
                        Continuar comprando
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <!-- Toast -->
    <div id="store-toast" class="fixed bottom-5 right-5 z-50 bg-espresso text-porcelain text-xs font-medium px-4 py-3 rounded-lg shadow-[var(--shadow-overlay)] opacity-0 translate-y-2 pointer-events-none transition-[transform,opacity] duration-200 ease-[var(--ease-ui)]"></div>
</body>
</html>
