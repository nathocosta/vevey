<x-store.layout
    title="Finalizar pedido — Vevey Doces"
    :nav-categories="$navCategories"
>
    <div
        class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10"
        id="checkout-page"
        data-quote-url="{{ route('store.shipping.quote') }}"
        data-coupon-url="{{ route('store.checkout.coupon') }}"
        data-submit-url="{{ route('store.checkout.submit') }}"
    >
        <!-- Breadcrumb -->
        <nav aria-label="Navegação estrutural" class="text-xs text-espresso-muted flex items-center gap-2 mb-6">
            <a href="{{ route('store.home') }}" class="hover:text-espresso transition-colors duration-150">Início</a>
            <span>/</span>
            <a href="{{ route('store.catalog') }}" class="hover:text-espresso transition-colors duration-150">Catálogo</a>
            <span>/</span>
            <span class="text-espresso font-medium">Finalizar pedido</span>
        </nav>

        <div class="pb-6 border-b hairline mb-8">
            <h1 class="font-display font-semibold text-2xl sm:text-3xl text-espresso">Finalizar pedido</h1>
            <p class="text-sm text-espresso-soft mt-1">Escolha retirada na loja ou entrega pelos Correios (SEDEX / PAC)</p>
        </div>

        <!-- Estado vazio -->
        <div id="checkout-empty" class="hidden bg-porcelain border hairline rounded-2xl p-12 text-center space-y-4">
            <span class="material-symbols-outlined text-[24px] text-espresso-muted">shopping_bag</span>
            <h2 class="font-display font-medium text-lg text-espresso">Seu carrinho está vazio</h2>
            <p class="text-sm text-espresso-soft max-w-md mx-auto">Adicione caixas de doces, balões ou embalagens antes de finalizar o pedido.</p>
            <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg px-6 h-11 transition-colors duration-150">
                Explorar catálogo
            </a>
        </div>

        <!-- Confirmação de pedido -->
        <div id="checkout-success" class="hidden bg-porcelain border hairline rounded-2xl p-8 sm:p-10 space-y-6 max-w-2xl mx-auto">
            <div class="flex items-start gap-4">
                <span class="w-10 h-10 rounded-lg bg-positive-soft text-positive flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </span>
                <div>
                    <p class="text-xs font-mono text-espresso-muted" id="success-order-code"></p>
                    <h2 class="font-display font-semibold text-2xl text-espresso mt-0.5">Pedido confirmado</h2>
                    <p class="text-sm text-espresso-soft mt-1" id="success-summary-text"></p>
                </div>
            </div>

            <div class="p-5 rounded-xl bg-paper border hairline space-y-3 text-sm" id="success-breakdown"></div>

            <div class="flex flex-wrap gap-3 pt-2">
                <a href="{{ auth('customers')->check() ? route('store.account') : route('store.login') }}" class="inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg px-6 h-11 transition-colors duration-150">
                    Acompanhar em Minha Conta
                </a>
                <a href="{{ route('store.catalog') }}" class="inline-flex items-center justify-center bg-espresso hover:bg-[#332D25] text-porcelain font-medium text-sm rounded-lg px-6 h-11 transition-colors duration-150">
                    Continuar comprando
                </a>
                <a href="{{ route('store.home') }}" class="inline-flex items-center justify-center bg-porcelain border hairline-strong hover:bg-linen text-espresso font-medium text-sm rounded-lg px-6 h-11 transition-colors duration-150">
                    Voltar ao início
                </a>
            </div>
        </div>

        <!-- Formulário de Checkout -->
        <form id="checkout-form" class="grid lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-7 space-y-6">
                <!-- 1. Dados do Cliente -->
                <section class="bg-porcelain border hairline rounded-2xl p-6 space-y-4">
                    <h2 class="font-display font-semibold text-base text-espresso">1. Seus dados</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="customer_name" class="block text-xs font-medium text-espresso mb-1.5">Nome completo</label>
                            <input id="customer_name" name="customer_name" type="text" value="{{ $customer?->name }}" required placeholder="Ex.: Maria Oliveira" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none transition-colors duration-150" />
                        </div>
                        <div>
                            <label for="customer_email" class="block text-xs font-medium text-espresso mb-1.5">E-mail</label>
                            <input id="customer_email" name="customer_email" type="email" value="{{ $customer?->email }}" required placeholder="voce@email.com" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none transition-colors duration-150" />
                        </div>
                        <div>
                            <label for="customer_phone" class="block text-xs font-medium text-espresso mb-1.5">WhatsApp / Telefone</label>
                            <input id="customer_phone" name="customer_phone" type="tel" value="{{ $customer?->primaryPhone() }}" required placeholder="(11) 99999-9999" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none transition-colors duration-150" />
                        </div>
                    </div>
                </section>

                <!-- 2. Entrega ou Retirada -->
                <section class="bg-porcelain border hairline rounded-2xl p-6 space-y-5">
                    <h2 class="font-display font-semibold text-base text-espresso">2. Entrega ou retirada</h2>

                    <div class="grid sm:grid-cols-2 gap-3">
                        <label class="relative flex items-start gap-3 p-4 rounded-xl border hairline-strong bg-paper cursor-pointer has-[:checked]:border-brand-600 has-[:checked]:bg-porcelain transition-colors duration-150">
                            <input type="radio" name="delivery_type" value="pickup" checked class="mt-0.5 accent-brand-600" />
                            <div>
                                <span class="block font-display font-medium text-sm text-espresso">Retirada na loja</span>
                                <span class="block text-xs text-positive font-medium mt-0.5">Grátis · Mesmo dia</span>
                            </div>
                        </label>

                        <label class="relative flex items-start gap-3 p-4 rounded-xl border hairline-strong bg-paper cursor-pointer has-[:checked]:border-brand-600 has-[:checked]:bg-porcelain transition-colors duration-150">
                            <input type="radio" name="delivery_type" value="delivery" class="mt-0.5 accent-brand-600" />
                            <div>
                                <span class="block font-display font-medium text-sm text-espresso">Entrega (Correios)</span>
                                <span class="block text-xs text-espresso-muted mt-0.5">SEDEX ou PAC pelo CEP</span>
                            </div>
                        </label>
                    </div>

                    <!-- Info de retirada -->
                    <div id="pickup-info" class="p-4 rounded-xl bg-paper border hairline text-xs text-espresso-soft space-y-1">
                        <p class="font-medium text-espresso">Retirada rápida no balcão da Vevey Doces</p>
                        <p>Segunda a sábado, das 08h às 19h. Avisamos assim que o pedido estiver separado.</p>
                    </div>

                    <!-- Campos de entrega + cotação SEDEX / PAC -->
                    <div id="delivery-fields" class="hidden space-y-4">
                        @if ($customer && $customer->addresses->isNotEmpty())
                            <div class="space-y-2">
                                <span class="block text-xs font-medium text-espresso-soft">Usar endereço salvo</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($customer->addresses as $addr)
                                        <button
                                            type="button"
                                            data-saved-address
                                            data-cep="{{ $addr->cep }}"
                                            data-street="{{ $addr->street }}"
                                            data-number="{{ $addr->number }}"
                                            data-neighborhood="{{ $addr->neighborhood }}"
                                            data-city="{{ $addr->city }}"
                                            data-state="{{ $addr->state }}"
                                            class="px-3 py-2 rounded-lg border hairline-strong bg-paper hover:bg-linen text-left text-xs transition-colors cursor-pointer"
                                        >
                                            <span class="font-semibold text-espresso block">{{ $addr->label }}</span>
                                            <span class="text-espresso-muted">{{ $addr->street }}, {{ $addr->number }} · {{ $addr->city }}/{{ $addr->state }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <label for="checkout-cep" class="block text-xs font-medium text-espresso mb-1.5">CEP de entrega</label>
                            <div class="flex gap-2">
                                <input id="checkout-cep" name="cep" type="text" maxlength="9" placeholder="00000-000" class="flex-1 bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none transition-colors duration-150" />
                                <button type="button" id="checkout-calc-cep" class="px-4 h-10 rounded-lg bg-linen hover:bg-[#E5DDD0] text-xs font-medium text-espresso transition-colors duration-150 cursor-pointer">
                                    Calcular frete
                                </button>
                            </div>
                            <p id="checkout-cep-status" class="text-xs text-espresso-muted mt-1.5"></p>
                        </div>

                        <!-- Opções de frete retornadas pela API -->
                        <div id="shipping-options-list" class="space-y-2"></div>

                        <div class="grid sm:grid-cols-6 gap-3 pt-2">
                            <div class="sm:col-span-4">
                                <label for="street" class="block text-xs font-medium text-espresso mb-1.5">Rua / Avenida</label>
                                <input id="street" name="street" type="text" placeholder="Preenchido pelo CEP" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                            </div>
                            <div class="sm:col-span-2">
                                <label for="number" class="block text-xs font-medium text-espresso mb-1.5">Número</label>
                                <input id="number" name="number" type="text" placeholder="123" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                            </div>
                            <div class="sm:col-span-2">
                                <label for="neighborhood" class="block text-xs font-medium text-espresso mb-1.5">Bairro</label>
                                <input id="neighborhood" name="neighborhood" type="text" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                            </div>
                            <div class="sm:col-span-3">
                                <label for="city" class="block text-xs font-medium text-espresso mb-1.5">Cidade</label>
                                <input id="city" name="city" type="text" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                            </div>
                            <div class="sm:col-span-1">
                                <label for="state" class="block text-xs font-medium text-espresso mb-1.5">UF</label>
                                <input id="state" name="state" type="text" maxlength="2" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3 h-10 text-sm text-espresso uppercase outline-none" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. Pagamento -->
                <section class="bg-porcelain border hairline rounded-2xl p-6 space-y-4">
                    <h2 class="font-display font-semibold text-base text-espresso">3. Forma de pagamento</h2>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 p-4 rounded-xl border hairline-strong bg-paper cursor-pointer has-[:checked]:border-brand-600 has-[:checked]:bg-porcelain transition-colors duration-150">
                            <input type="radio" name="payment_method" value="pix" checked class="mt-0.5 accent-brand-600" />
                            <div>
                                <span class="block font-display font-medium text-sm text-espresso">Pix</span>
                                <span class="block text-xs text-positive font-medium mt-0.5">5% de desconto na hora</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-xl border hairline-strong bg-paper cursor-pointer has-[:checked]:border-brand-600 has-[:checked]:bg-porcelain transition-colors duration-150">
                            <input type="radio" name="payment_method" value="credit_card" class="mt-0.5 accent-brand-600" />
                            <div>
                                <span class="block font-display font-medium text-sm text-espresso">Cartão</span>
                                <span class="block text-xs text-espresso-muted mt-0.5">Crédito ou débito</span>
                            </div>
                        </label>
                    </div>
                </section>

                <p id="checkout-error" class="hidden p-4 rounded-xl bg-negative-soft border border-negative/20 text-xs text-negative font-medium"></p>
            </div>

            <!-- Coluna Direita: Resumo Sticky -->
            <aside class="lg:col-span-5 lg:sticky lg:top-32">
                <div class="bg-porcelain border hairline rounded-2xl p-6 shadow-[var(--shadow-subtle)] space-y-5">
                    <h2 class="font-display font-semibold text-base text-espresso">Resumo do pedido</h2>

                    <ul id="checkout-items" class="divide-y divide-[rgba(31,27,22,0.08)] max-h-72 overflow-y-auto"></ul>

                    <!-- Cupom de desconto (ADR 1) -->
                    <div class="pt-4 border-t hairline space-y-2">
                        <label for="checkout-coupon" class="block text-xs font-medium text-espresso">Cupom de desconto</label>
                        <div class="flex gap-2">
                            <input id="checkout-coupon" type="text" placeholder="Ex.: VEVEY10" class="flex-1 bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3 h-9 text-xs text-espresso uppercase outline-none" />
                            <button type="button" id="checkout-apply-coupon" class="px-3.5 h-9 rounded-lg bg-linen hover:bg-[#E5DDD0] text-xs font-medium text-espresso transition-colors cursor-pointer">
                                Aplicar
                            </button>
                        </div>
                        <p id="checkout-coupon-status" class="text-xs text-espresso-muted hidden"></p>
                    </div>

                    <div class="pt-4 border-t hairline space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-espresso-soft">Subtotal</span>
                            <span id="summary-subtotal" class="tnum font-medium text-espresso">R$ 0,00</span>
                        </div>
                        <div id="summary-coupon-row" class="hidden flex justify-between text-positive">
                            <span id="summary-coupon-label">Cupom</span>
                            <span id="summary-coupon-discount" class="tnum font-medium">- R$ 0,00</span>
                        </div>
                        <div id="summary-discount-row" class="flex justify-between text-positive">
                            <span>Desconto Pix (5%)</span>
                            <span id="summary-discount" class="tnum font-medium">- R$ 0,00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-espresso-soft">Entrega</span>
                            <span id="summary-shipping" class="tnum font-medium text-positive">Grátis</span>
                        </div>
                        <div class="pt-3 mt-2 border-t hairline flex justify-between items-baseline">
                            <span class="font-display font-semibold text-base text-espresso">Total</span>
                            <span id="summary-total" class="tnum font-display font-semibold text-2xl text-espresso">R$ 0,00</span>
                        </div>
                    </div>

                    <button type="submit" id="checkout-submit-btn" class="w-full inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-medium text-sm rounded-lg h-12 transition-[background-color,transform] duration-150 cursor-pointer">
                        Confirmar pedido
                    </button>

                    <p class="text-xs text-espresso-muted text-center">Valores finais recalculados em centavos no servidor</p>
                </div>
            </aside>
        </form>
    </div>
</x-store.layout>
