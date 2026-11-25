@php
    use Vevey\Orders\Enums\FulfillmentStatus;
    use Vevey\Orders\Enums\PaymentStatus;
    use Vevey\PanelSite\Http\Controllers\StorefrontController;
@endphp

<x-store.layout title="Minha Conta — Vevey Doces" :nav-categories="$navCategories">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b hairline">
            <div>
                <p class="text-xs text-espresso-muted">Área do cliente</p>
                <h1 class="font-display font-semibold text-2xl sm:text-3xl text-espresso mt-0.5">Olá, {{ $customer->name }}</h1>
                <p class="text-sm text-espresso-soft mt-1">{{ $customer->email }} @if ($customer->primaryPhone()) · {{ $customer->primaryPhone() }} @endif @if ($customer->cpf) · CPF {{ $customer->cpf }} @endif</p>
            </div>
            <form method="POST" action="{{ route('store.logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg border hairline-strong bg-porcelain hover:bg-linen text-xs font-medium text-espresso cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    Sair da conta
                </button>
            </form>
        </div>

        @if (session('status'))
            <div class="p-4 rounded-xl bg-positive-soft text-positive text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('order'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium">
                {{ $errors->first('order') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-12 gap-8 items-start">
            <!-- Meus Pedidos -->
            <section class="lg:col-span-8 space-y-4">
                <h2 class="font-display font-semibold text-lg text-espresso">Meus pedidos ({{ $orders->count() }})</h2>

                @forelse ($orders as $order)
                    @php
                        $isCancelled = $order->fulfillment_status === FulfillmentStatus::Cancelled;
                        $canCancel = $order->payment_status === PaymentStatus::Pending && $order->fulfillment_status === FulfillmentStatus::Pending;
                        $step = match (true) {
                            $isCancelled => 0,
                            $order->fulfillment_status === FulfillmentStatus::Delivered => 4,
                            $order->fulfillment_status === FulfillmentStatus::Shipped => 3,
                            $order->fulfillment_status === FulfillmentStatus::Preparing || $order->payment_status === PaymentStatus::Paid => 2,
                            default => 1,
                        };
                    @endphp
                    <div class="bg-porcelain border hairline rounded-2xl p-5 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b hairline">
                            <div>
                                <span class="font-mono text-sm font-semibold text-espresso">{{ $order->number }}</span>
                                <span class="text-xs text-espresso-muted ml-2">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium">
                                <span class="px-2.5 py-1 rounded-full bg-linen text-espresso">{{ $order->payment_status->getLabel() }}</span>
                                <span class="px-2.5 py-1 rounded-full {{ $isCancelled ? 'bg-rose-50 text-rose-700' : 'bg-paper border hairline text-espresso-soft' }}">{{ $order->fulfillment_status->getLabel() }}</span>
                            </div>
                        </div>

                        @if (! $isCancelled)
                            <div class="grid grid-cols-4 gap-2">
                                @foreach ([1 => 'Recebido', 2 => 'Separação', 3 => 'Enviado', 4 => 'Entregue'] as $idx => $stepLabel)
                                    <div class="space-y-1">
                                        <div class="h-1.5 rounded-full {{ $step >= $idx ? 'bg-brand-600' : 'bg-linen' }}"></div>
                                        <p class="text-[11px] {{ $step >= $idx ? 'text-espresso font-medium' : 'text-espresso-muted' }}">{{ $stepLabel }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="space-y-1.5 text-sm">
                            @foreach ($order->items as $item)
                                <div class="flex items-center justify-between gap-2 text-espresso-soft">
                                    <span>{{ $item->quantity }}x {{ $item->name }}</span>
                                    <span class="font-mono text-xs text-espresso">
                                        {{ StorefrontController::formatBrl($item->total_cents) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 border-t hairline flex flex-wrap items-center justify-between gap-3 text-sm">
                            <span class="text-xs text-espresso-muted">{{ $order->shipping_method ?? $order->delivery_method->getLabel() }}</span>
                            <div class="flex items-center gap-3">
                                @if ($canCancel)
                                    <form method="POST" action="{{ route('store.account.orders.cancel', $order->id) }}" onsubmit="return confirm('Deseja realmente cancelar este pedido?');">
                                        @csrf
                                        <button type="submit" class="px-3 h-8 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-medium cursor-pointer transition-colors">
                                            Cancelar pedido
                                        </button>
                                    </form>
                                @endif
                                <span class="font-display font-semibold text-espresso">
                                    Total: {{ StorefrontController::formatBrl($order->total_cents) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-porcelain border hairline rounded-2xl p-10 text-center space-y-3">
                        <span class="material-symbols-outlined text-[24px] text-espresso-muted">receipt_long</span>
                        <p class="font-display font-medium text-espresso">Você ainda não fez nenhum pedido</p>
                        <p class="text-sm text-espresso-soft">Explore nosso catálogo de chocolates, balões e embalagens.</p>
                        <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg px-5 h-10">
                            Ver catálogo
                        </a>
                    </div>
                @endforelse
            </section>

            <!-- Endereços salvos e Perfil -->
            <aside class="lg:col-span-4 space-y-6">
                <div class="space-y-4">
                    <h2 class="font-display font-semibold text-lg text-espresso">Endereços salvos</h2>

                    @foreach ($customer->addresses as $address)
                        <div class="bg-porcelain border hairline rounded-xl p-4 flex items-start justify-between gap-3 text-xs">
                            <div class="space-y-1">
                                <p class="font-semibold text-espresso">{{ $address->label }}</p>
                                <p class="text-espresso-soft">{{ $address->street }}, {{ $address->number }} {{ $address->complement ? '· ' . $address->complement : '' }}</p>
                                <p class="text-espresso-muted">{{ $address->neighborhood }} · {{ $address->city }}/{{ $address->state }} · CEP {{ $address->cep }}</p>
                            </div>
                            <form method="POST" action="{{ route('store.account.addresses.destroy', $address->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-espresso-muted hover:text-rose-600 cursor-pointer" title="Remover">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </form>
                        </div>
                    @endforeach

                    <form id="account-address-form" method="POST" action="{{ route('store.account.addresses.store') }}" class="bg-porcelain border hairline rounded-2xl p-5 space-y-3">
                        @csrf
                        <h3 class="font-display font-medium text-sm text-espresso">Novo endereço</h3>
                        @if ($errors->any() && ! $errors->has('order'))
                            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-2.5">
                            <input name="label" placeholder="Ex.: Casa" required class="col-span-1 bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                            <input name="cep" data-mask="cep" placeholder="CEP 00000-000" maxlength="9" required class="col-span-1 bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                            <input name="street" placeholder="Rua / Avenida" required class="col-span-2 bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                            <input name="number" placeholder="Número" required class="col-span-1 bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                            <input name="complement" placeholder="Complemento" class="col-span-1 bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                            <input name="neighborhood" placeholder="Bairro" required class="col-span-2 bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                            <input name="city" placeholder="Cidade" required class="col-span-1 bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                            <input name="state" placeholder="UF" maxlength="2" required class="col-span-1 bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso uppercase" />
                        </div>
                        <button type="submit" class="w-full bg-espresso hover:bg-[#332D25] text-porcelain text-xs font-medium rounded-lg h-9 cursor-pointer">
                            Salvar endereço
                        </button>
                    </form>
                </div>

                <!-- Meus dados -->
                <form method="POST" action="{{ route('store.account.profile.update') }}" class="bg-porcelain border hairline rounded-2xl p-5 space-y-3">
                    @csrf
                    @method('PUT')
                    <h3 class="font-display font-medium text-sm text-espresso">Meus dados e senha</h3>
                    <div class="space-y-2.5">
                        <input name="name" value="{{ old('name', $customer->name) }}" placeholder="Nome completo" required class="w-full bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                        <input name="cpf" data-mask="cpf" maxlength="14" value="{{ old('cpf', $customer->cpf) }}" placeholder="CPF 000.000.000-00" class="w-full bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                        <input name="password" type="password" minlength="8" placeholder="Nova senha (opcional, mín. 8 caracteres)" class="w-full bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                        <input name="password_confirmation" type="password" minlength="8" placeholder="Confirmar nova senha" class="w-full bg-paper border hairline rounded-lg px-3 h-9 text-xs text-espresso" />
                    </div>
                    <button type="submit" class="w-full bg-linen hover:bg-[#E5DDD0] text-espresso text-xs font-medium rounded-lg h-9 cursor-pointer transition-colors">
                        Atualizar dados
                    </button>
                </form>
            </aside>
        </div>
    </div>
</x-store.layout>
