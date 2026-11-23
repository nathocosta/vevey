<x-filament-panels::page>
    <style>
        .vevey-pos-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        @media (min-width: 1024px) {
            .vevey-pos-grid {
                grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
            }
        }
        .vevey-pos-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 0.75rem;
            max-height: 540px;
            overflow-y: auto;
            padding-right: 0.25rem;
        }
        .vevey-pos-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.5rem;
            text-align: left;
            padding: 0.875rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: rgba(15, 23, 42, 0.03);
            transition: border-color 0.15s ease, background-color 0.15s ease, transform 0.12s ease;
            cursor: pointer;
        }
        .dark .vevey-pos-card {
            border-color: rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.03);
        }
        .vevey-pos-card:hover {
            border-color: #e11d48;
            background: rgba(225, 29, 72, 0.06);
        }
        .vevey-pos-card:active {
            transform: scale(0.99);
        }
        .vevey-pos-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            font-size: 0.7rem;
        }
        .vevey-pos-sku {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            opacity: 0.65;
        }
        .vevey-pos-stock {
            font-weight: 600;
            padding: 0.125rem 0.45rem;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.14);
            color: #10b981;
        }
        .vevey-pos-stock.is-low {
            background: rgba(239, 68, 68, 0.16);
            color: #f87171;
        }
        .vevey-pos-name {
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.3;
        }
        .vevey-pos-price {
            font-size: 0.95rem;
            font-weight: 700;
            color: #f43f5e;
            font-variant-numeric: tabular-nums;
        }
        .vevey-pos-cart-list {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            max-height: 260px;
            overflow-y: auto;
        }
        .vevey-pos-cart-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding-bottom: 0.625rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        }
        .vevey-pos-qty-btn {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(148, 163, 184, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            cursor: pointer;
        }
        .vevey-pos-qty-btn:hover {
            border-color: #e11d48;
            color: #f43f5e;
        }
        .vevey-pos-field-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .vevey-pos-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
            opacity: 0.85;
        }
        .vevey-pos-total-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            padding-top: 0.875rem;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }
        .vevey-pos-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(16, 185, 129, 0.35);
            background: rgba(16, 185, 129, 0.1);
            font-size: 0.875rem;
        }
    </style>

    @if ($lastOrderNumber)
        <div class="vevey-pos-banner">
            <span>
                Última venda concluída: <strong style="font-family: ui-monospace, monospace;">{{ $lastOrderNumber }}</strong>
            </span>
            @if ($lastOrderUrl)
                <x-filament::link :href="$lastOrderUrl" weight="semibold">
                    Ver pedido
                </x-filament::link>
            @endif
        </div>
    @endif

    <div class="vevey-pos-grid">
        <!-- Catálogo rápido -->
        <x-filament::section heading="Catálogo rápido" description="Clique nos produtos para adicionar à venda de balcão">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label for="pos-search" class="vevey-pos-label">Buscar por nome, SKU ou código de barras</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            id="pos-search"
                            type="search"
                            wire:model.live.debounce.250ms="search"
                            placeholder="Ex.: KitKat, BAL-MET-9-AZ..."
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="vevey-pos-products">
                    @forelse ($this->products as $product)
                        <button
                            type="button"
                            wire:click="addToCart('{{ $product['id'] }}')"
                            class="vevey-pos-card"
                        >
                            <div style="display: flex; flex-direction: column; gap: 0.35rem; width: 100%;">
                                <div class="vevey-pos-meta">
                                    <span class="vevey-pos-sku">{{ $product['sku'] }}</span>
                                    <span class="vevey-pos-stock {{ $product['on_hand'] <= 0 ? 'is-low' : '' }}">
                                        Est.: {{ $product['on_hand'] }}
                                    </span>
                                </div>
                                <div class="vevey-pos-name">{{ $product['name'] }}</div>
                            </div>
                            <div class="vevey-pos-price">
                                R$ {{ number_format($product['price_cents'] / 100, 2, ',', '.') }}
                            </div>
                        </button>
                    @empty
                        <p style="grid-column: 1 / -1; text-align: center; padding: 2rem 0; opacity: 0.65; font-size: 0.875rem;">
                            Nenhum produto encontrado.
                        </p>
                    @endforelse
                </div>
            </div>
        </x-filament::section>

        <!-- Carrinho do Balcão -->
        <x-filament::section heading="Itens da venda">
            <x-slot name="headerEnd">
                @if ($cart !== [])
                    <x-filament::link tag="button" wire:click="clearCart" color="danger" size="sm">
                        Limpar
                    </x-filament::link>
                @endif
            </x-slot>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @if ($cart === [])
                    <div style="padding: 1.5rem; text-align: center; border-radius: 0.75rem; border: 1px dashed rgba(148, 163, 184, 0.35); opacity: 0.7; font-size: 0.875rem;">
                        Clique nos produtos ao lado para adicionar ao balcão.
                    </div>
                @else
                    <div class="vevey-pos-cart-list">
                        @foreach ($cart as $item)
                            <div class="vevey-pos-cart-row">
                                <div style="min-width: 0; flex: 1;">
                                    <div style="font-size: 0.875rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $item['name'] }}
                                    </div>
                                    <div style="font-size: 0.75rem; opacity: 0.65; font-family: ui-monospace, monospace;">
                                        {{ $item['sku'] }} · R$ {{ number_format($item['unit_cents'] / 100, 2, ',', '.') }}
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                                    <button type="button" wire:click="updateQty('{{ $item['id'] }}', -1)" class="vevey-pos-qty-btn">−</button>
                                    <span style="min-width: 1.5rem; text-align: center; font-weight: 600; font-size: 0.875rem;">{{ $item['qty'] }}</span>
                                    <button type="button" wire:click="updateQty('{{ $item['id'] }}', 1)" class="vevey-pos-qty-btn">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div style="display: flex; flex-direction: column; gap: 0.75rem; padding-top: 0.5rem; border-top: 1px solid rgba(148, 163, 184, 0.2);">
                    <div>
                        <label class="vevey-pos-label">Cliente</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model="customerName" />
                        </x-filament::input.wrapper>
                    </div>

                    <div class="vevey-pos-field-grid">
                        <div>
                            <label class="vevey-pos-label">Telefone (opcional)</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" wire:model="customerPhone" placeholder="(11) 99999-9999" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="vevey-pos-label">Pagamento</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model="paymentMethod">
                                    <option value="cash">Dinheiro</option>
                                    <option value="pix">Pix</option>
                                    <option value="debit_card">Cartão de débito</option>
                                    <option value="credit_card">Cartão de crédito</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>

                    <div>
                        <label class="vevey-pos-label">Observação (opcional)</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model="notes" placeholder="Ex.: Embalagem para presente" />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <div class="vevey-pos-total-row">
                    <span style="font-size: 0.875rem; font-weight: 500; opacity: 0.75;">Total da venda</span>
                    <span style="font-size: 1.4rem; font-weight: 800; font-variant-numeric: tabular-nums;">
                        R$ {{ number_format($this->subtotalCents / 100, 2, ',', '.') }}
                    </span>
                </div>

                <x-filament::button
                    wire:click="submitSale"
                    color="primary"
                    size="lg"
                    style="width: 100%;"
                    :disabled="$cart === []"
                >
                    Concluir venda no balcão
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
