<x-filament-panels::page>
    @if ($lastOrderNumber)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/40 p-4 flex items-center justify-between gap-4">
            <div class="text-sm text-emerald-900 dark:text-emerald-200">
                Última venda concluída: <strong class="font-mono">{{ $lastOrderNumber }}</strong>
            </div>
            @if ($lastOrderUrl)
                <a href="{{ $lastOrderUrl }}" class="text-sm font-semibold text-emerald-700 dark:text-emerald-300 underline">
                    Ver pedido
                </a>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Catálogo rápido -->
        <div class="lg:col-span-7 space-y-4">
            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4 space-y-4">
                <div>
                    <label for="pos-search" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Buscar por nome, SKU ou código de barras
                    </label>
                    <input
                        id="pos-search"
                        type="search"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Ex.: KitKat, BAL-MET-9-AZ..."
                        class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-800 text-sm"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[540px] overflow-y-auto pr-1">
                    @forelse ($this->products as $product)
                        <button
                            type="button"
                            wire:click="addToCart('{{ $product['id'] }}')"
                            class="text-left rounded-lg border border-gray-200 dark:border-white/10 hover:border-rose-500 p-3 transition flex flex-col justify-between gap-2 bg-gray-50/50 dark:bg-gray-800/50"
                        >
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-mono text-gray-500">{{ $product['sku'] }}</span>
                                    <span class="text-[11px] font-medium px-1.5 py-0.5 rounded {{ $product['on_hand'] <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                        Est.: {{ $product['on_hand'] }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1 line-clamp-2">{{ $product['name'] }}</p>
                            </div>
                            <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                                R$ {{ number_format($product['price_cents'] / 100, 2, ',', '.') }}
                            </p>
                        </button>
                    @empty
                        <p class="col-span-2 text-sm text-gray-500 py-8 text-center">Nenhum produto encontrado.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Carrinho do Balcão -->
        <div class="lg:col-span-5">
            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-5 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Itens da venda</h3>
                    @if ($cart !== [])
                        <button type="button" wire:click="clearCart" class="text-xs text-gray-500 hover:text-red-600">
                            Limpar
                        </button>
                    @endif
                </div>

                @if ($cart === [])
                    <p class="text-sm text-gray-500 py-6 text-center border border-dashed border-gray-200 dark:border-white/10 rounded-lg">
                        Clique nos produtos ao lado para adicionar ao balcão.
                    </p>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-white/10 max-h-64 overflow-y-auto">
                        @foreach ($cart as $item)
                            <div class="py-2.5 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['name'] }}</p>
                                    <p class="text-xs text-gray-500 font-mono">
                                        {{ $item['sku'] }} · R$ {{ number_format($item['unit_cents'] / 100, 2, ',', '.') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" wire:click="updateQty('{{ $item['id'] }}', -1)" class="w-7 h-7 rounded border border-gray-300 dark:border-white/15 text-sm font-semibold">−</button>
                                    <span class="w-6 text-center text-sm font-medium">{{ $item['qty'] }}</span>
                                    <button type="button" wire:click="updateQty('{{ $item['id'] }}', 1)" class="w-7 h-7 rounded border border-gray-300 dark:border-white/15 text-sm font-semibold">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-3 pt-3 border-t border-gray-200 dark:border-white/10">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cliente</label>
                        <input type="text" wire:model="customerName" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-800 text-sm" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Telefone (opcional)</label>
                            <input type="text" wire:model="customerPhone" placeholder="(11) 99999-9999" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-800 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Pagamento</label>
                            <select wire:model="paymentMethod" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-800 text-sm">
                                <option value="cash">Dinheiro</option>
                                <option value="pix">Pix</option>
                                <option value="debit_card">Cartão de débito</option>
                                <option value="credit_card">Cartão de crédito</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Observação (opcional)</label>
                        <input type="text" wire:model="notes" placeholder="Ex.: Embalagem para presente" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-800 text-sm" />
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-200 dark:border-white/10 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total da venda</span>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">
                        R$ {{ number_format($this->subtotalCents / 100, 2, ',', '.') }}
                    </span>
                </div>

                <x-filament::button
                    wire:click="submitSale"
                    color="primary"
                    size="lg"
                    class="w-full"
                    :disabled="$cart === []"
                >
                    Concluir venda no balcão
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
