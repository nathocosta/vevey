@php
    use Vevey\Orders\Enums\FulfillmentStatus;
    use Vevey\Orders\Enums\PaymentStatus;
    use Vevey\PanelSite\Http\Controllers\StorefrontController;
@endphp

<x-store.layout
    title="Rastrear pedido — Vevey Doces"
    description="Consulte o andamento do seu pedido na Vevey Doces informando o número do pedido e seu e-mail."
    :nav-categories="$navCategories"
>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-8">
        <div class="space-y-2 text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-porcelain border hairline text-xs font-medium text-espresso-soft">
                <span class="material-symbols-outlined text-[15px] text-brand-600">local_shipping</span>
                Acompanhamento em tempo real
            </span>
            <h1 class="font-display font-semibold text-2xl sm:text-3xl text-espresso">Rastrear meu pedido</h1>
            <p class="text-sm text-espresso-soft max-w-md mx-auto">
                Informe o número do pedido (ex.: <span class="font-mono text-espresso">VV-2026-000001</span>) e o e-mail utilizado na compra.
            </p>
        </div>

        <form method="POST" action="{{ route('store.track.lookup') }}" class="bg-porcelain border hairline rounded-2xl p-6 shadow-[var(--shadow-subtle)] space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="order_number" class="block text-xs font-medium text-espresso mb-1.5">Número do pedido</label>
                    <input
                        id="order_number"
                        name="order_number"
                        type="text"
                        required
                        value="{{ old('order_number', $orderNumberInput ?? '') }}"
                        placeholder="VV-2026-000001"
                        class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm font-mono uppercase text-espresso outline-none"
                    />
                </div>
                <div>
                    <label for="email" class="block text-xs font-medium text-espresso mb-1.5">E-mail da compra</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        value="{{ old('email', $emailInput ?? '') }}"
                        placeholder="voce@email.com"
                        class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none"
                    />
                </div>
            </div>

            @if ($errors->any())
                <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg px-6 h-11 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">search</span>
                Consultar pedido
            </button>
        </form>

        @if ($searched && ! $order)
            <div class="bg-porcelain border hairline rounded-2xl p-8 text-center space-y-2">
                <span class="material-symbols-outlined text-[24px] text-espresso-muted">search_off</span>
                <h2 class="font-display font-medium text-base text-espresso">Pedido não encontrado</h2>
                <p class="text-sm text-espresso-soft">Verifique se o número do pedido e o e-mail informados estão corretos.</p>
            </div>
        @endif

        @if ($order)
            @php
                $isCancelled = $order->fulfillment_status === FulfillmentStatus::Cancelled;
                $step = match (true) {
                    $isCancelled => 0,
                    $order->fulfillment_status === FulfillmentStatus::Delivered => 4,
                    $order->fulfillment_status === FulfillmentStatus::Shipped => 3,
                    $order->fulfillment_status === FulfillmentStatus::Preparing || $order->payment_status === PaymentStatus::Paid => 2,
                    default => 1,
                };
                $steps = [
                    1 => 'Pedido recebido',
                    2 => 'Pago / Separação',
                    3 => 'Enviado',
                    4 => 'Entregue',
                ];
            @endphp

            <section class="bg-porcelain border hairline rounded-2xl p-6 space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b hairline">
                    <div>
                        <p class="font-mono text-base font-semibold text-espresso">{{ $order->number }}</p>
                        <p class="text-xs text-espresso-muted mt-0.5">Realizado em {{ $order->created_at?->format('d/m/Y \à\s H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-medium">
                        <span class="px-2.5 py-1 rounded-full bg-linen text-espresso">{{ $order->payment_status->getLabel() }}</span>
                        <span class="px-2.5 py-1 rounded-full {{ $isCancelled ? 'bg-rose-50 text-rose-700' : 'bg-positive-soft text-positive' }}">
                            {{ $order->fulfillment_status->getLabel() }}
                        </span>
                    </div>
                </div>

                @if (! $isCancelled)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ($steps as $idx => $label)
                            <div class="space-y-1.5">
                                <div class="h-1.5 rounded-full {{ $step >= $idx ? 'bg-brand-600' : 'bg-linen' }}"></div>
                                <p class="text-[11px] font-medium {{ $step >= $idx ? 'text-espresso' : 'text-espresso-muted' }}">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-2 text-sm">
                    <h3 class="font-display font-medium text-xs uppercase tracking-wider text-espresso-muted">Itens do pedido</h3>
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between gap-2 text-espresso-soft">
                            <span>{{ $item->quantity }}× {{ $item->name }}</span>
                            <span class="tnum font-medium text-espresso">{{ StorefrontController::formatBrl($item->total_cents) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t hairline space-y-1.5 text-sm">
                    <div class="flex justify-between text-xs text-espresso-muted">
                        <span>Entrega / Retirada</span>
                        <span>{{ $order->shipping_method ?? $order->delivery_method->getLabel() }}</span>
                    </div>
                    <div class="flex justify-between font-display font-semibold text-base text-espresso pt-1">
                        <span>Total</span>
                        <span class="tnum">{{ StorefrontController::formatBrl($order->total_cents) }}</span>
                    </div>
                </div>

                @if ($order->statusHistories->isNotEmpty())
                    <div class="pt-4 border-t hairline space-y-2">
                        <h3 class="font-display font-medium text-xs uppercase tracking-wider text-espresso-muted">Histórico de atualizações</h3>
                        <ul class="space-y-2 text-xs text-espresso-soft">
                            @foreach ($order->statusHistories->sortByDesc('created_at') as $history)
                                <li class="flex items-start justify-between gap-3">
                                    <span>{{ $history->note ?: ucfirst($history->to_status) }}</span>
                                    <span class="font-mono text-[11px] text-espresso-muted shrink-0">{{ $history->created_at?->format('d/m H:i') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>
        @endif
    </div>
</x-store.layout>
