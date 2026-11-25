<x-store.layout
    title="Meus favoritos — Vevey Doces"
    description="Produtos salvos na sua lista de favoritos da Vevey Doces."
    :nav-categories="$navCategories"
>
    <div
        id="favorites-page"
        data-endpoint="{{ route('store.favorites.items') }}"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8"
    >
        <nav aria-label="Navegação estrutural" class="text-xs text-espresso-muted flex items-center gap-2">
            <a href="{{ route('store.home') }}" class="hover:text-espresso transition-colors duration-150">Início</a>
            <span>/</span>
            <span class="text-espresso font-medium">Favoritos</span>
        </nav>

        <div class="flex flex-wrap items-end justify-between gap-4 pb-6 border-b hairline">
            <div>
                <h1 class="font-display font-semibold text-2xl sm:text-3xl text-espresso">Meus favoritos</h1>
                <p id="favorites-subtitle" class="text-sm text-espresso-soft mt-1">Carregando seus produtos salvos...</p>
            </div>
            <button
                type="button"
                id="favorites-clear-btn"
                class="hidden px-4 h-9 rounded-lg border hairline-strong bg-porcelain hover:bg-linen text-xs font-medium text-espresso cursor-pointer transition-colors"
            >
                Limpar favoritos
            </button>
        </div>

        <div id="favorites-empty" class="hidden bg-porcelain border hairline rounded-2xl p-12 text-center space-y-4">
            <span class="material-symbols-outlined text-[28px] text-espresso-muted">favorite</span>
            <h2 class="font-display font-medium text-lg text-espresso">Você ainda não salvou nenhum produto</h2>
            <p class="text-sm text-espresso-soft max-w-md mx-auto">
                Clique no ícone de coração nos produtos do catálogo para montar sua lista e comprar depois com facilidade.
            </p>
            <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg px-6 h-11 transition-colors">
                Explorar catálogo
            </a>
        </div>

        <div id="favorites-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6"></div>
    </div>
</x-store.layout>
