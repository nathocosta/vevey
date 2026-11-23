const CART_KEY = 'vevey_cart_v1';
const FAV_KEY = 'vevey_favs_v1';
const FREE_SHIPPING_CENTS = 15000;

function formatBrl(cents) {
    return 'R$ ' + (cents / 100).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function readStorage(key, fallback) {
    try {
        const raw = localStorage.getItem(key);
        return raw ? JSON.parse(raw) : fallback;
    } catch {
        return fallback;
    }
}

function writeStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch {
        // Ignore storage quota errors
    }
}

let toastTimer = null;
function showToast(message) {
    const toast = document.getElementById('store-toast');
    if (!toast) {
        return;
    }

    toast.textContent = message;
    toast.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
    }, 2400);
}

function getCart() {
    return readStorage(CART_KEY, []);
}

function saveCart(items) {
    writeStorage(CART_KEY, items);
    renderCart();
}

function addToCart(item, qty = 1) {
    const cart = getCart();
    const existing = cart.find((entry) => entry.id === item.id);

    if (existing) {
        existing.qty += qty;
    } else {
        cart.push({ ...item, qty });
    }

    saveCart(cart);
    showToast(`${item.name} adicionado ao carrinho`);
}

function updateCartQty(id, delta) {
    const cart = getCart()
        .map((item) => (item.id === id ? { ...item, qty: item.qty + delta } : item))
        .filter((item) => item.qty > 0);

    saveCart(cart);
}

function removeFromCart(id) {
    saveCart(getCart().filter((item) => item.id !== id));
}

function openCartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    const backdrop = document.getElementById('cart-backdrop');
    const panel = document.getElementById('cart-panel');

    if (!drawer || !backdrop || !panel) {
        return;
    }

    drawer.classList.remove('hidden');
    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('translate-x-full');
    });
    document.body.style.overflow = 'hidden';
}

function closeCartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    const backdrop = document.getElementById('cart-backdrop');
    const panel = document.getElementById('cart-panel');

    if (!drawer || !backdrop || !panel) {
        return;
    }

    backdrop.classList.add('opacity-0');
    panel.classList.add('translate-x-full');
    document.body.style.overflow = '';

    setTimeout(() => {
        drawer.classList.add('hidden');
    }, 200);
}

function renderCart() {
    const cart = getCart();
    const totalCount = cart.reduce((sum, item) => sum + item.qty, 0);
    const subtotalCents = cart.reduce((sum, item) => sum + item.priceCents * item.qty, 0);
    const pixCents = Math.round(subtotalCents * 0.95);

    document.querySelectorAll('[data-cart-count]').forEach((el) => {
        el.textContent = String(totalCount);
        el.classList.toggle('hidden', totalCount === 0);
    });

    const listEl = document.getElementById('cart-items');
    const emptyEl = document.getElementById('cart-empty');
    const footerEl = document.getElementById('cart-footer');
    const subtotalEl = document.getElementById('cart-subtotal');
    const pixEl = document.getElementById('cart-pix-total');
    const shippingTextEl = document.getElementById('cart-shipping-text');
    const shippingBarEl = document.getElementById('cart-shipping-bar');

    if (!listEl || !emptyEl || !footerEl) {
        return;
    }

    const progress = Math.min(100, Math.round((subtotalCents / FREE_SHIPPING_CENTS) * 100));
    if (shippingBarEl) {
        shippingBarEl.style.transform = `scaleX(${progress / 100})`;
        shippingBarEl.classList.toggle('bg-positive', subtotalCents >= FREE_SHIPPING_CENTS);
        shippingBarEl.classList.toggle('bg-brand-600', subtotalCents < FREE_SHIPPING_CENTS);
    }

    if (shippingTextEl) {
        if (subtotalCents === 0) {
            shippingTextEl.innerHTML = 'Frete grátis a partir de <span class="tnum font-medium text-espresso">R$ 150,00</span>';
        } else if (subtotalCents >= FREE_SHIPPING_CENTS) {
            shippingTextEl.innerHTML = '<span class="text-positive font-medium">Frete grátis liberado neste pedido</span>';
        } else {
            const remaining = FREE_SHIPPING_CENTS - subtotalCents;
            shippingTextEl.innerHTML = `Faltam <span class="tnum font-medium text-espresso">${formatBrl(remaining)}</span> para frete grátis`;
        }
    }

    if (cart.length === 0) {
        listEl.innerHTML = '';
        emptyEl.classList.remove('hidden');
        footerEl.classList.add('hidden');
        return;
    }

    emptyEl.classList.add('hidden');
    footerEl.classList.remove('hidden');

    if (subtotalEl) {
        subtotalEl.textContent = formatBrl(subtotalCents);
    }
    if (pixEl) {
        pixEl.textContent = formatBrl(pixCents);
    }

    listEl.innerHTML = cart
        .map(
            (item) => `
            <li class="flex gap-4 py-4 border-b hairline">
                <a href="${item.url}" class="w-16 h-16 rounded-lg bg-linen border hairline overflow-hidden shrink-0 flex items-center justify-center">
                    ${
                        item.image
                            ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover" />`
                            : `<span class="material-symbols-outlined text-espresso-muted text-[18px]">inventory_2</span>`
                    }
                </a>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <a href="${item.url}" class="font-medium text-sm text-espresso hover:text-brand-700 line-clamp-2 leading-snug transition-colors duration-150">${item.name}</a>
                        <button type="button" data-cart-remove="${item.id}" aria-label="Remover item" class="text-espresso-muted hover:text-negative p-1 -mr-1 transition-colors duration-150 cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">delete</span>
                        </button>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <div class="inline-flex items-center border hairline rounded-lg bg-paper">
                            <button type="button" data-cart-dec="${item.id}" aria-label="Diminuir quantidade" class="w-8 h-8 flex items-center justify-center text-espresso-soft hover:text-espresso transition-colors duration-150 cursor-pointer">−</button>
                            <span class="tnum text-xs font-medium text-espresso px-2">${item.qty}</span>
                            <button type="button" data-cart-inc="${item.id}" aria-label="Aumentar quantidade" class="w-8 h-8 flex items-center justify-center text-espresso-soft hover:text-espresso transition-colors duration-150 cursor-pointer">+</button>
                        </div>
                        <span class="tnum font-medium text-sm text-espresso">${formatBrl(item.priceCents * item.qty)}</span>
                    </div>
                </div>
            </li>
        `
        )
        .join('');
}

function initFavorites() {
    const favs = new Set(readStorage(FAV_KEY, []));

    const syncButtons = () => {
        document.querySelectorAll('[data-fav-toggle]').forEach((btn) => {
            const id = btn.getAttribute('data-fav-toggle');
            btn.classList.toggle('fav-active', favs.has(id));
        });
    };

    syncButtons();

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-fav-toggle]');
        if (!btn) {
            return;
        }

        event.preventDefault();
        const id = btn.getAttribute('data-fav-toggle');
        if (favs.has(id)) {
            favs.delete(id);
            showToast('Removido dos favoritos');
        } else {
            favs.add(id);
            showToast('Salvo nos favoritos');
        }

        writeStorage(FAV_KEY, Array.from(favs));
        syncButtons();
    });
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function lookupAddressClient(digits) {
    try {
        const res = await fetch(`https://viacep.com.br/ws/${digits}/json/`);
        if (res.ok) {
            const data = await res.json();
            if (!data.erro) {
                return {
                    street: data.logradouro || '',
                    neighborhood: data.bairro || '',
                    city: data.localidade || '',
                    state: data.uf || '',
                };
            }
        }
    } catch {
        // Fallback to BrasilAPI
    }

    try {
        const res = await fetch(`https://brasilapi.com.br/api/cep/v2/${digits}`);
        if (res.ok) {
            const data = await res.json();
            return {
                street: data.street || '',
                neighborhood: data.neighborhood || '',
                city: data.city || '',
                state: data.state || '',
            };
        }
    } catch {
        // Ignore
    }

    return null;
}

async function requestShippingQuote(cep, subtotalCents = 0) {
    const digits = cep.replace(/\D/g, '');
    const response = await fetch('/frete/calcular', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ cep: digits, subtotal_cents: subtotalCents }),
    });

    if (!response.ok) {
        throw new Error('CEP inválido');
    }

    const data = await response.json();
    if (!data.address && digits.length === 8) {
        data.address = await lookupAddressClient(digits);
    }

    return data;
}

function initPdpControls() {
    const qtyInput = document.getElementById('pdp-qty');
    const decBtn = document.getElementById('pdp-qty-dec');
    const incBtn = document.getElementById('pdp-qty-inc');

    if (qtyInput && decBtn && incBtn) {
        decBtn.addEventListener('click', () => {
            const next = Math.max(1, Number(qtyInput.value || 1) - 1);
            qtyInput.value = String(next);
        });
        incBtn.addEventListener('click', () => {
            const next = Math.min(99, Number(qtyInput.value || 1) + 1);
            qtyInput.value = String(next);
        });
    }

    const mainImage = document.getElementById('pdp-main-image');
    document.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const src = thumb.getAttribute('data-gallery-thumb');
            if (mainImage && src) {
                mainImage.src = src;
            }
            document.querySelectorAll('[data-gallery-thumb]').forEach((el) => {
                el.classList.remove('border-brand-600');
                el.classList.add('hairline');
            });
            thumb.classList.remove('hairline');
            thumb.classList.add('border-brand-600');
        });
    });

    const cepForm = document.getElementById('cep-form');
    const cepResult = document.getElementById('cep-result');
    if (cepForm && cepResult) {
        cepForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = cepForm.querySelector('input');
            const digits = (input?.value || '').replace(/\D/g, '');
            if (digits.length !== 8) {
                cepResult.innerHTML = '<span class="text-xs text-negative">Informe um CEP válido com 8 dígitos</span>';
                cepResult.classList.remove('hidden');
                return;
            }

            cepResult.innerHTML = '<span class="text-xs text-espresso-muted">Consultando SEDEX e PAC...</span>';
            cepResult.classList.remove('hidden');

            try {
                const addBtn = document.querySelector('[data-add-cart][data-qty-input="pdp-qty"]');
                const unitCents = Number(addBtn?.getAttribute('data-product-price-cents') || 0);
                const qty = Math.max(1, Number(qtyInput?.value || 1));
                const data = await requestShippingQuote(digits, unitCents * qty);
                const cityLine = data.address?.city
                    ? `<div class="text-xs text-espresso-muted pb-2 mb-2 border-b hairline">${data.address.street ? data.address.street + ' · ' : ''}${data.address.city}/${data.address.state}</div>`
                    : '';

                cepResult.innerHTML =
                    cityLine +
                    `<div class="space-y-2 text-xs text-espresso-soft">` +
                    data.options
                        .map(
                            (opt) => `
                            <div class="flex justify-between gap-2">
                                <span>${opt.name} <span class="text-espresso-muted">(${opt.delivery_label})</span></span>
                                <span class="tnum font-medium ${opt.price_cents === 0 ? 'text-positive' : 'text-espresso'}">${opt.price_cents === 0 ? 'Grátis' : formatBrl(opt.price_cents)}</span>
                            </div>`
                        )
                        .join('') +
                    `</div>`;
            } catch {
                cepResult.innerHTML = '<span class="text-xs text-negative">Não foi possível consultar este CEP</span>';
            }
        });
    }
}

function initCheckout() {
    const page = document.getElementById('checkout-page');
    const form = document.getElementById('checkout-form');
    if (!page || !form) {
        return;
    }

    const emptyEl = document.getElementById('checkout-empty');
    const successEl = document.getElementById('checkout-success');
    const itemsEl = document.getElementById('checkout-items');
    const subtotalEl = document.getElementById('summary-subtotal');
    const discountRowEl = document.getElementById('summary-discount-row');
    const discountEl = document.getElementById('summary-discount');
    const shippingEl = document.getElementById('summary-shipping');
    const totalEl = document.getElementById('summary-total');
    const pickupInfo = document.getElementById('pickup-info');
    const deliveryFields = document.getElementById('delivery-fields');
    const cepInput = document.getElementById('checkout-cep');
    const cepBtn = document.getElementById('checkout-calc-cep');
    const cepStatus = document.getElementById('checkout-cep-status');
    const optionsList = document.getElementById('shipping-options-list');
    const errorEl = document.getElementById('checkout-error');
    const submitBtn = document.getElementById('checkout-submit-btn');

    let selectedShipping = { code: 'pickup', priceCents: 0 };

    function currentDeliveryType() {
        return form.querySelector('input[name="delivery_type"]:checked')?.value || 'pickup';
    }

    function currentPaymentMethod() {
        return form.querySelector('input[name="payment_method"]:checked')?.value || 'pix';
    }

    function renderSummary() {
        const cart = getCart();
        if (cart.length === 0) {
            form.classList.add('hidden');
            emptyEl?.classList.remove('hidden');
            return;
        }

        emptyEl?.classList.add('hidden');
        form.classList.remove('hidden');

        const subtotalCents = cart.reduce((sum, item) => sum + item.priceCents * item.qty, 0);
        const isPix = currentPaymentMethod() === 'pix';
        const discountCents = isPix ? Math.round(subtotalCents * 0.05) : 0;
        const shippingCents = currentDeliveryType() === 'pickup' ? 0 : selectedShipping.priceCents;
        const totalCents = Math.max(0, subtotalCents - discountCents + shippingCents);

        if (itemsEl) {
            itemsEl.innerHTML = cart
                .map(
                    (item) => `
                    <li class="py-3 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-linen border hairline overflow-hidden shrink-0 flex items-center justify-center">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover" />` : `<span class="material-symbols-outlined text-[16px] text-espresso-muted">inventory_2</span>`}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-espresso line-clamp-1">${item.name}</p>
                            <p class="tnum text-xs text-espresso-muted">${item.qty} × ${formatBrl(item.priceCents)}</p>
                        </div>
                        <span class="tnum text-xs font-medium text-espresso">${formatBrl(item.priceCents * item.qty)}</span>
                    </li>`
                )
                .join('');
        }

        if (subtotalEl) subtotalEl.textContent = formatBrl(subtotalCents);
        if (discountRowEl) discountRowEl.classList.toggle('hidden', !isPix);
        if (discountEl) discountEl.textContent = `- ${formatBrl(discountCents)}`;
        if (shippingEl) {
            shippingEl.textContent = shippingCents === 0 ? 'Grátis' : formatBrl(shippingCents);
            shippingEl.classList.toggle('text-positive', shippingCents === 0);
            shippingEl.classList.toggle('text-espresso', shippingCents > 0);
        }
        if (totalEl) totalEl.textContent = formatBrl(totalCents);
    }

    async function quoteCep() {
        const digits = (cepInput?.value || '').replace(/\D/g, '');
        if (digits.length !== 8) {
            if (cepStatus) cepStatus.textContent = 'Digite um CEP válido com 8 dígitos';
            return;
        }

        if (cepStatus) cepStatus.textContent = 'Buscando endereço e cotando SEDEX / PAC...';

        try {
            const cart = getCart();
            const subtotalCents = cart.reduce((sum, item) => sum + item.priceCents * item.qty, 0);
            const data = await requestShippingQuote(digits, subtotalCents);

            if (data.address) {
                const setVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el && val) el.value = val;
                };
                setVal('street', data.address.street);
                setVal('neighborhood', data.address.neighborhood);
                setVal('city', data.address.city);
                setVal('state', data.address.state);
            }

            const deliveryOptions = data.options.filter((opt) => opt.type === 'delivery');
            if (optionsList) {
                optionsList.innerHTML = deliveryOptions
                    .map(
                        (opt, index) => `
                        <label class="flex items-center justify-between gap-3 p-3.5 rounded-xl border hairline-strong bg-paper cursor-pointer has-[:checked]:border-brand-600 has-[:checked]:bg-porcelain transition-colors duration-150">
                            <span class="flex items-center gap-3">
                                <input type="radio" name="shipping_option" value="${opt.code}" data-price-cents="${opt.price_cents}" ${index === 0 ? 'checked' : ''} class="accent-brand-600" />
                                <span>
                                    <span class="block text-sm font-medium text-espresso">${opt.name}</span>
                                    <span class="block text-xs text-espresso-muted">${opt.delivery_label}</span>
                                </span>
                            </span>
                            <span class="tnum text-sm font-medium ${opt.price_cents === 0 ? 'text-positive' : 'text-espresso'}">${opt.price_cents === 0 ? 'Grátis' : formatBrl(opt.price_cents)}</span>
                        </label>`
                    )
                    .join('');

                if (deliveryOptions.length > 0) {
                    selectedShipping = { code: deliveryOptions[0].code, priceCents: deliveryOptions[0].price_cents };
                }
            }

            if (cepStatus) {
                const sourceLabel = data.source === 'melhor_envio' ? 'Cotação via Melhor Envio' : 'Estimativa Correios (configure MELHOR_ENVIO_TOKEN no .env para cotação ao vivo)';
                cepStatus.textContent = data.address?.city ? `${data.address.city}/${data.address.state} · ${sourceLabel}` : sourceLabel;
            }
            renderSummary();
        } catch {
            if (cepStatus) cepStatus.textContent = 'Não foi possível consultar este CEP';
        }
    }

    form.querySelectorAll('input[name="delivery_type"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            const isDelivery = currentDeliveryType() === 'delivery';
            pickupInfo?.classList.toggle('hidden', isDelivery);
            deliveryFields?.classList.toggle('hidden', !isDelivery);
            if (!isDelivery) {
                selectedShipping = { code: 'pickup', priceCents: 0 };
            }
            renderSummary();
        });
    });

    form.querySelectorAll('input[name="payment_method"]').forEach((radio) => {
        radio.addEventListener('change', renderSummary);
    });

    optionsList?.addEventListener('change', (event) => {
        const input = event.target.closest('input[name="shipping_option"]');
        if (input) {
            selectedShipping = {
                code: input.value,
                priceCents: Number(input.getAttribute('data-price-cents') || 0),
            };
            renderSummary();
        }
    });

    cepBtn?.addEventListener('click', quoteCep);
    cepInput?.addEventListener('input', () => {
        const digits = cepInput.value.replace(/\D/g, '');
        if (digits.length === 8) {
            quoteCep();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorEl?.classList.add('hidden');

        const cart = getCart();
        if (cart.length === 0) {
            return;
        }

        const payload = {
            customer_name: document.getElementById('customer_name')?.value || '',
            customer_email: document.getElementById('customer_email')?.value || '',
            customer_phone: document.getElementById('customer_phone')?.value || '',
            delivery_type: currentDeliveryType(),
            shipping_code: currentDeliveryType() === 'pickup' ? 'pickup' : selectedShipping.code,
            payment_method: currentPaymentMethod(),
            cep: cepInput?.value || null,
            street: document.getElementById('street')?.value || null,
            number: document.getElementById('number')?.value || null,
            neighborhood: document.getElementById('neighborhood')?.value || null,
            city: document.getElementById('city')?.value || null,
            state: (document.getElementById('state')?.value || '').toUpperCase() || null,
            items: cart.map((item) => ({ id: item.id, qty: item.qty })),
        };

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Confirmando...';
        }

        try {
            const response = await fetch(page.getAttribute('data-submit-url') || '/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                const firstErr = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                throw new Error(firstErr || data.message || 'Revise os campos obrigatórios');
            }

            saveCart([]);
            form.classList.add('hidden');
            emptyEl?.classList.add('hidden');
            successEl?.classList.remove('hidden');

            const codeEl = document.getElementById('success-order-code');
            const summaryTextEl = document.getElementById('success-summary-text');
            const breakdownEl = document.getElementById('success-breakdown');

            if (codeEl) codeEl.textContent = data.order_code;
            if (summaryTextEl) {
                summaryTextEl.textContent = `${data.customer_name} · ${data.shipping_label}`;
            }
            if (breakdownEl) {
                breakdownEl.innerHTML =
                    data.items
                        .map(
                            (it) => `<div class="flex justify-between"><span>${it.qty}× ${it.name}</span><span class="tnum font-medium text-espresso">${formatBrl(it.line_cents)}</span></div>`
                        )
                        .join('') +
                    `<div class="pt-3 mt-2 border-t hairline space-y-1.5">` +
                    `<div class="flex justify-between text-espresso-soft"><span>Subtotal</span><span class="tnum">${formatBrl(data.subtotal_cents)}</span></div>` +
                    (data.discount_cents > 0
                        ? `<div class="flex justify-between text-positive"><span>Desconto Pix</span><span class="tnum">- ${formatBrl(data.discount_cents)}</span></div>`
                        : '') +
                    `<div class="flex justify-between text-espresso-soft"><span>Entrega</span><span class="tnum">${data.shipping_cents === 0 ? 'Grátis' : formatBrl(data.shipping_cents)}</span></div>` +
                    `<div class="flex justify-between font-display font-semibold text-base text-espresso pt-2 border-t hairline"><span>Total</span><span class="tnum">${formatBrl(data.total_cents)}</span></div>` +
                    `</div>`;
            }
        } catch (err) {
            if (errorEl) {
                errorEl.textContent = err.message;
                errorEl.classList.remove('hidden');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirmar pedido';
            }
        }
    });

    renderSummary();
}

function initLiveSearch() {
    const wrapper = document.getElementById('live-search-wrapper');
    const input = document.getElementById('live-search-input');
    const results = document.getElementById('live-search-results');

    if (!wrapper || !input || !results) {
        return;
    }

    const endpoint = wrapper.getAttribute('data-suggestions-url') || '/busca/sugestoes';
    let debounceTimer = null;

    const hideResults = () => {
        results.classList.add('hidden');
        results.innerHTML = '';
    };

    input.addEventListener('input', () => {
        const term = input.value.trim();
        clearTimeout(debounceTimer);

        if (term.length < 2) {
            hideResults();
            return;
        }

        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`${endpoint}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!res.ok) {
                    return;
                }

                const data = await res.json();
                const items = data.items || [];

                if (items.length === 0) {
                    results.innerHTML = `<div class="px-4 py-3 text-xs text-espresso-muted">Nenhum produto encontrado para "${term}"</div>`;
                    results.classList.remove('hidden');
                    return;
                }

                results.innerHTML =
                    items
                        .map(
                            (item) => `
                            <a href="${item.url}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-paper border-b hairline last:border-b-0 transition-colors duration-150">
                                <div class="w-10 h-10 rounded-md bg-linen border hairline overflow-hidden shrink-0 flex items-center justify-center">
                                    ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover" />` : `<span class="material-symbols-outlined text-[16px] text-espresso-muted">inventory_2</span>`}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-espresso truncate">${item.name}</p>
                                    <p class="text-xs text-espresso-muted">${item.category || item.sku}</p>
                                </div>
                                <span class="tnum text-sm font-medium text-espresso shrink-0">${item.price}</span>
                            </a>`
                        )
                        .join('') +
                    `<a href="/catalogo?q=${encodeURIComponent(term)}" class="block px-4 py-2.5 bg-paper text-xs font-medium text-brand-700 hover:bg-linen text-center transition-colors duration-150">Ver todos os resultados</a>`;

                results.classList.remove('hidden');
            } catch {
                hideResults();
            }
        }, 220);
    });

    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target)) {
            hideResults();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            hideResults();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    initFavorites();
    initPdpControls();
    initCheckout();
    initLiveSearch();

    document.querySelectorAll('[data-open-cart]').forEach((btn) => {
        btn.addEventListener('click', openCartDrawer);
    });

    document.querySelectorAll('[data-close-cart]').forEach((btn) => {
        btn.addEventListener('click', closeCartDrawer);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeCartDrawer();
        }
    });

    document.addEventListener('click', (event) => {
        const cardDec = event.target.closest('[data-card-qty-dec]');
        if (cardDec) {
            event.preventDefault();
            const qtyEl = cardDec.closest('[data-product-card]')?.querySelector('[data-card-qty]');
            if (qtyEl) {
                qtyEl.textContent = String(Math.max(1, Number(qtyEl.textContent || 1) - 1));
            }
            return;
        }

        const cardInc = event.target.closest('[data-card-qty-inc]');
        if (cardInc) {
            event.preventDefault();
            const qtyEl = cardInc.closest('[data-product-card]')?.querySelector('[data-card-qty]');
            if (qtyEl) {
                qtyEl.textContent = String(Math.min(99, Number(qtyEl.textContent || 1) + 1));
            }
            return;
        }

        const addBtn = event.target.closest('[data-add-cart]');
        if (addBtn) {
            event.preventDefault();
            const qtySource = addBtn.getAttribute('data-qty-input');
            const cardQtyEl = addBtn.closest('[data-product-card]')?.querySelector('[data-card-qty]');
            const qty = qtySource
                ? Math.max(1, Number(document.getElementById(qtySource)?.value || 1))
                : cardQtyEl
                  ? Math.max(1, Number(cardQtyEl.textContent || 1))
                  : 1;

            addToCart(
                {
                    id: addBtn.getAttribute('data-product-id'),
                    name: addBtn.getAttribute('data-product-name'),
                    priceCents: Number(addBtn.getAttribute('data-product-price-cents')),
                    image: addBtn.getAttribute('data-product-image') || '',
                    url: addBtn.getAttribute('data-product-url'),
                },
                qty
            );

            if (addBtn.hasAttribute('data-open-after-add')) {
                openCartDrawer();
            }
            return;
        }

        const incBtn = event.target.closest('[data-cart-inc]');
        if (incBtn) {
            updateCartQty(incBtn.getAttribute('data-cart-inc'), 1);
            return;
        }

        const decBtn = event.target.closest('[data-cart-dec]');
        if (decBtn) {
            updateCartQty(decBtn.getAttribute('data-cart-dec'), -1);
            return;
        }

        const remBtn = event.target.closest('[data-cart-remove]');
        if (remBtn) {
            removeFromCart(remBtn.getAttribute('data-cart-remove'));
        }
    });

    const filterToggle = document.getElementById('mobile-filter-toggle');
    const filterSidebar = document.getElementById('catalog-filters');
    if (filterToggle && filterSidebar) {
        filterToggle.addEventListener('click', () => {
            filterSidebar.classList.toggle('hidden');
        });
    }
});
