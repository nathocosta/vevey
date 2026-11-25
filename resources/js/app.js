const CART_KEY = 'vevey_cart_v1';
const FAV_KEY = 'vevey_favs_v1';
const FREE_SHIPPING_CENTS = 15000;

function formatBrl(cents) {
    return 'R$ ' + (cents / 100).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function safeUrl(value) {
    const raw = String(value ?? '').trim();
    if (!raw) {
        return '#';
    }
    if (raw.startsWith('/') || raw.startsWith('http://') || raw.startsWith('https://')) {
        return escapeHtml(raw);
    }
    return '#';
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
                <a href="${safeUrl(item.url)}" class="w-16 h-16 rounded-lg bg-linen border hairline overflow-hidden shrink-0 flex items-center justify-center">
                    ${
                        item.image
                            ? `<img src="${safeUrl(item.image)}" alt="${escapeHtml(item.name)}" loading="lazy" decoding="async" class="w-full h-full object-cover" />`
                            : `<span class="material-symbols-outlined text-espresso-muted text-[18px]">inventory_2</span>`
                    }
                </a>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <a href="${safeUrl(item.url)}" class="font-medium text-sm text-espresso hover:text-brand-700 line-clamp-2 leading-snug transition-colors duration-150">${escapeHtml(item.name)}</a>
                        <button type="button" data-cart-remove="${escapeHtml(item.id)}" aria-label="Remover item" class="text-espresso-muted hover:text-negative p-1 -mr-1 transition-colors duration-150 cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">delete</span>
                        </button>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <div class="inline-flex items-center border hairline rounded-lg bg-paper">
                            <button type="button" data-cart-dec="${escapeHtml(item.id)}" aria-label="Diminuir quantidade" class="w-8 h-8 flex items-center justify-center text-espresso-soft hover:text-espresso transition-colors duration-150 cursor-pointer">−</button>
                            <span class="tnum text-xs font-medium text-espresso px-2">${Number(item.qty) || 1}</span>
                            <button type="button" data-cart-inc="${escapeHtml(item.id)}" aria-label="Aumentar quantidade" class="w-8 h-8 flex items-center justify-center text-espresso-soft hover:text-espresso transition-colors duration-150 cursor-pointer">+</button>
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
        document.querySelectorAll('[data-fav-count]').forEach((el) => {
            el.textContent = String(favs.size);
            el.classList.toggle('hidden', favs.size === 0);
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
        document.dispatchEvent(new CustomEvent('vevey:favs-changed', { detail: Array.from(favs) }));
    });
}

function initFavoritesPage() {
    const page = document.getElementById('favorites-page');
    if (!page) {
        return;
    }

    const endpoint = page.getAttribute('data-endpoint') || '/favoritos/itens';
    const grid = document.getElementById('favorites-grid');
    const emptyEl = document.getElementById('favorites-empty');
    const subtitleEl = document.getElementById('favorites-subtitle');
    const clearBtn = document.getElementById('favorites-clear-btn');

    const loadFavorites = async () => {
        const ids = readStorage(FAV_KEY, []);
        if (!Array.isArray(ids) || ids.length === 0) {
            if (grid) grid.innerHTML = '';
            emptyEl?.classList.remove('hidden');
            clearBtn?.classList.add('hidden');
            if (subtitleEl) subtitleEl.textContent = 'Nenhum produto salvo no momento';
            return;
        }

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ ids: ids.slice(0, 50) }),
            });

            if (!res.ok) {
                throw new Error('Falha ao carregar favoritos');
            }

            const data = await res.json();
            const items = Array.isArray(data.items) ? data.items : [];

            if (items.length === 0) {
                if (grid) grid.innerHTML = '';
                emptyEl?.classList.remove('hidden');
                clearBtn?.classList.add('hidden');
                if (subtitleEl) subtitleEl.textContent = 'Nenhum produto ativo encontrado nos favoritos';
                return;
            }

            emptyEl?.classList.add('hidden');
            clearBtn?.classList.remove('hidden');
            if (subtitleEl) {
                subtitleEl.textContent = `${items.length} ${items.length === 1 ? 'produto salvo' : 'produtos salvos'}`;
            }

            if (grid) {
                grid.innerHTML = items
                    .map((item) => {
                        const outOfStock = Number(item.available_stock) <= 0;
                        return `
                        <article class="group bg-porcelain rounded-xl border hairline hover:border-espresso/20 hover:shadow-[var(--shadow-float)] overflow-hidden flex flex-col transition-[border-color,box-shadow] duration-200" data-product-card>
                            <div class="relative aspect-square overflow-hidden bg-linen border-b hairline">
                                <a href="${safeUrl(item.url)}" class="block w-full h-full">
                                    ${
                                        item.image
                                            ? `<img src="${safeUrl(item.image)}" alt="${escapeHtml(item.name)}" width="400" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03] ${outOfStock ? 'opacity-60 grayscale' : ''}" />`
                                            : `<div class="w-full h-full flex items-center justify-center"><span class="material-symbols-outlined text-[28px] text-espresso-muted">inventory_2</span></div>`
                                    }
                                </a>
                                <button type="button" data-fav-toggle="${escapeHtml(item.id)}" aria-label="Remover dos favoritos" class="fav-active absolute top-3 right-3 w-8 h-8 rounded-lg bg-porcelain/95 border hairline flex items-center justify-center text-brand-600 cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">favorite</span>
                                </button>
                            </div>
                            <div class="p-4 flex flex-col flex-1">
                                ${item.category ? `<span class="text-xs text-espresso-muted">${escapeHtml(item.category)}</span>` : ''}
                                <h3 class="font-display font-medium text-sm sm:text-base text-espresso leading-snug mt-1 line-clamp-2">
                                    <a href="${safeUrl(item.url)}" class="hover:text-brand-700 transition-colors">${escapeHtml(item.name)}</a>
                                </h3>
                                <div class="mt-auto pt-3 flex items-baseline gap-2">
                                    <span class="tnum font-display font-semibold text-base text-espresso">${escapeHtml(item.price)}</span>
                                    ${item.regular_price ? `<span class="tnum text-xs text-espresso-muted line-through">${escapeHtml(item.regular_price)}</span>` : ''}
                                </div>
                                <div class="mt-4">
                                    ${
                                        outOfStock
                                            ? `<button type="button" disabled class="w-full inline-flex items-center justify-center bg-linen text-espresso-muted font-medium text-sm rounded-lg h-10 cursor-not-allowed">Indisponível</button>`
                                            : `<button type="button" data-add-cart data-product-id="${escapeHtml(item.id)}" data-product-name="${escapeHtml(item.name)}" data-product-price-cents="${Number(item.price_cents) || 0}" data-product-image="${safeUrl(item.image)}" data-product-url="${safeUrl(item.url)}" class="w-full inline-flex items-center justify-center gap-1.5 bg-espresso hover:bg-[#332D25] text-porcelain font-medium text-sm rounded-lg h-10 transition-colors cursor-pointer"><span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>Adicionar</button>`
                                    }
                                </div>
                            </div>
                        </article>`;
                    })
                    .join('');
            }
        } catch {
            if (subtitleEl) subtitleEl.textContent = 'Não foi possível carregar seus favoritos.';
        }
    };

    clearBtn?.addEventListener('click', () => {
        writeStorage(FAV_KEY, []);
        document.querySelectorAll('[data-fav-count]').forEach((el) => {
            el.textContent = '0';
            el.classList.add('hidden');
        });
        loadFavorites();
        showToast('Lista de favoritos limpa');
    });

    document.addEventListener('vevey:favs-changed', loadFavorites);
    loadFavorites();
}

function initInputMasks() {
    const formatCpf = (v) => {
        const d = v.replace(/\D/g, '').slice(0, 11);
        return d
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    };

    const formatPhone = (v) => {
        const d = v.replace(/\D/g, '').slice(0, 11);
        if (d.length <= 10) {
            return d.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2');
        }
        return d.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
    };

    const formatCep = (v) => {
        const d = v.replace(/\D/g, '').slice(0, 8);
        return d.replace(/(\d{5})(\d)/, '$1-$2');
    };

    const bind = (selector, formatter) => {
        document.querySelectorAll(selector).forEach((el) => {
            el.addEventListener('input', () => {
                el.value = formatter(el.value);
            });
        });
    };

    bind('[data-mask="cpf"]', formatCpf);
    bind('[data-mask="phone"], #customer_phone', formatPhone);
    bind('[data-mask="cep"], #checkout-cep, #cep-input', formatCep);
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function lookupAddressClient(digits) {
    try {
        const res = await fetch(`/cep/${encodeURIComponent(digits)}`, {
            headers: { Accept: 'application/json' },
        });
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
        // Ignore network errors
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
                    ? `<div class="text-xs text-espresso-muted pb-2 mb-2 border-b hairline">${data.address.street ? escapeHtml(data.address.street) + ' · ' : ''}${escapeHtml(data.address.city)}/${escapeHtml(data.address.state)}</div>`
                    : '';

                cepResult.innerHTML =
                    cityLine +
                    `<div class="space-y-2 text-xs text-espresso-soft">` +
                    data.options
                        .map(
                            (opt) => `
                            <div class="flex justify-between gap-2">
                                <span>${escapeHtml(opt.name)} <span class="text-espresso-muted">(${escapeHtml(opt.delivery_label)})</span></span>
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

    const couponInput = document.getElementById('checkout-coupon');
    const couponBtn = document.getElementById('checkout-apply-coupon');
    const couponStatus = document.getElementById('checkout-coupon-status');
    const couponRowEl = document.getElementById('summary-coupon-row');
    const couponLabelEl = document.getElementById('summary-coupon-label');
    const couponDiscountEl = document.getElementById('summary-coupon-discount');

    let appliedCoupon = null;

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
        const couponDiscountCents = appliedCoupon ? Math.min(subtotalCents, appliedCoupon.discountCents) : 0;
        const afterCouponCents = Math.max(0, subtotalCents - couponDiscountCents);
        const isPix = currentPaymentMethod() === 'pix';
        const pixDiscountCents = isPix ? Math.round(afterCouponCents * 0.05) : 0;
        const shippingCents = currentDeliveryType() === 'pickup' ? 0 : selectedShipping.priceCents;
        const totalCents = Math.max(0, subtotalCents - couponDiscountCents - pixDiscountCents + shippingCents);

        if (itemsEl) {
            itemsEl.innerHTML = cart
                .map(
                    (item) => `
                    <li class="py-3 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-linen border hairline overflow-hidden shrink-0 flex items-center justify-center">
                            ${item.image ? `<img src="${safeUrl(item.image)}" alt="${escapeHtml(item.name)}" loading="lazy" decoding="async" class="w-full h-full object-cover" />` : `<span class="material-symbols-outlined text-[16px] text-espresso-muted">inventory_2</span>`}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-espresso line-clamp-1">${escapeHtml(item.name)}</p>
                            <p class="tnum text-xs text-espresso-muted">${Number(item.qty) || 1} × ${formatBrl(item.priceCents)}</p>
                        </div>
                        <span class="tnum text-xs font-medium text-espresso">${formatBrl(item.priceCents * item.qty)}</span>
                    </li>`
                )
                .join('');
        }

        if (subtotalEl) subtotalEl.textContent = formatBrl(subtotalCents);
        if (couponRowEl) couponRowEl.classList.toggle('hidden', couponDiscountCents <= 0);
        if (couponLabelEl && appliedCoupon) couponLabelEl.textContent = appliedCoupon.label;
        if (couponDiscountEl) couponDiscountEl.textContent = `- ${formatBrl(couponDiscountCents)}`;
        if (discountRowEl) discountRowEl.classList.toggle('hidden', !isPix || pixDiscountCents <= 0);
        if (discountEl) discountEl.textContent = `- ${formatBrl(pixDiscountCents)}`;
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
                                <input type="radio" name="shipping_option" value="${escapeHtml(opt.code)}" data-price-cents="${Number(opt.price_cents) || 0}" ${index === 0 ? 'checked' : ''} class="accent-brand-600" />
                                <span>
                                    <span class="block text-sm font-medium text-espresso">${escapeHtml(opt.name)}</span>
                                    <span class="block text-xs text-espresso-muted">${escapeHtml(opt.delivery_label)}</span>
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
                const sourceLabel = data.source === 'melhor_envio' ? 'Cotação oficial Correios' : 'Estimativa Correios (SEDEX / PAC)';
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

    page.querySelectorAll('[data-saved-address]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const setVal = (id, attr) => {
                const el = document.getElementById(id);
                if (el) el.value = btn.getAttribute(attr) || '';
            };
            setVal('checkout-cep', 'data-cep');
            setVal('street', 'data-street');
            setVal('number', 'data-number');
            setVal('neighborhood', 'data-neighborhood');
            setVal('city', 'data-city');
            setVal('state', 'data-state');
            quoteCep();
        });
    });

    couponBtn?.addEventListener('click', async () => {
        const code = (couponInput?.value || '').trim().toUpperCase();
        if (!code) {
            appliedCoupon = null;
            couponStatus?.classList.add('hidden');
            renderSummary();
            return;
        }

        const cart = getCart();
        const subtotalCents = cart.reduce((sum, item) => sum + item.priceCents * item.qty, 0);

        try {
            const res = await fetch(page.getAttribute('data-coupon-url') || '/checkout/cupom/validar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ code, subtotal_cents: subtotalCents }),
            });
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Cupom inválido.');
            }
            appliedCoupon = { code: data.code, label: data.label, discountCents: data.discount_cents };
            if (couponStatus) {
                couponStatus.textContent = `${data.label} aplicado (- ${formatBrl(data.discount_cents)})`;
                couponStatus.className = 'text-xs text-positive font-medium';
            }
        } catch (err) {
            appliedCoupon = null;
            if (couponStatus) {
                couponStatus.textContent = err.message;
                couponStatus.className = 'text-xs text-negative font-medium';
            }
        }
        renderSummary();
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
            coupon_code: appliedCoupon ? appliedCoupon.code : null,
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
            const pixPanel = document.getElementById('sandbox-pix-panel');
            const cardPanel = document.getElementById('sandbox-card-panel');
            const pixCodeInput = document.getElementById('sandbox-pix-code');
            const qrWrap = document.getElementById('sandbox-qr-wrap');
            const copyPixBtn = document.getElementById('sandbox-copy-pix');
            const simulateBtn = document.getElementById('sandbox-simulate-pay-btn');
            const payFeedback = document.getElementById('sandbox-pay-feedback');
            const statusIcon = document.getElementById('success-status-icon');
            const headingEl = document.getElementById('success-heading');

            if (codeEl) codeEl.textContent = data.order_code;
            if (summaryTextEl) {
                summaryTextEl.textContent = `${data.customer_name} · ${data.shipping_label}`;
            }

            if (data.payment_method === 'pix' && data.pix_code) {
                pixPanel?.classList.remove('hidden');
                cardPanel?.classList.add('hidden');
                if (pixCodeInput) pixCodeInput.value = data.pix_code;
                if (qrWrap) {
                    // Deterministic visual QR SVG derived from order code for sandbox demo
                    const seed = data.order_code || 'VEVEY';
                    let rects = '';
                    const n = 21;
                    for (let r = 0; r < n; r++) {
                        for (let c = 0; c < n; c++) {
                            const inFinder = (r < 7 && c < 7) || (r < 7 && c >= n - 7) || (r >= n - 7 && c < 7);
                            let on = false;
                            if (inFinder) {
                                const lr = r < 7 ? r : r - (n - 7);
                                const lc = c < 7 ? c : c - (n - 7);
                                on = lr === 0 || lr === 6 || lc === 0 || lc === 6 || (lr >= 2 && lr <= 4 && lc >= 2 && lc <= 4);
                            } else {
                                const h = (seed.charCodeAt((r * n + c) % seed.length) * 31 + r * 17 + c * 13) % 7;
                                on = h < 3;
                            }
                            if (on) rects += `<rect x="${c}" y="${r}" width="1" height="1" fill="#1F1B16"/>`;
                        }
                    }
                    qrWrap.innerHTML = `<svg viewBox="0 0 ${n} ${n}" class="w-full h-full" shape-rendering="crispEdges">${rects}</svg>`;
                }
                copyPixBtn?.addEventListener('click', () => {
                    navigator.clipboard?.writeText(data.pix_code);
                    if (copyPixBtn) copyPixBtn.textContent = 'Copiado!';
                    setTimeout(() => { if (copyPixBtn) copyPixBtn.textContent = 'Copiar'; }, 2000);
                });
            } else {
                pixPanel?.classList.add('hidden');
                cardPanel?.classList.remove('hidden');
            }

            if (simulateBtn && data.simulate_pay_url) {
                simulateBtn.onclick = async () => {
                    simulateBtn.disabled = true;
                    simulateBtn.textContent = 'Aprovando...';
                    try {
                        const payRes = await fetch(data.simulate_pay_url, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                            },
                        });
                        const payData = await payRes.json();
                        if (!payRes.ok) throw new Error(payData.message || 'Erro ao aprovar pagamento');

                        if (headingEl) headingEl.textContent = 'Pagamento aprovado e pedido em separação!';
                        if (statusIcon) {
                            statusIcon.className = 'w-10 h-10 rounded-lg bg-positive-soft text-positive flex items-center justify-center shrink-0';
                            statusIcon.innerHTML = '<span class="material-symbols-outlined text-[20px]">check_circle</span>';
                        }
                        if (payFeedback) {
                            payFeedback.textContent = payData.message;
                            payFeedback.classList.remove('hidden');
                        }
                        simulateBtn.classList.add('hidden');
                    } catch (e) {
                        simulateBtn.disabled = false;
                        simulateBtn.textContent = 'Simular pagamento aprovado';
                    }
                };
            }

            if (breakdownEl) {
                breakdownEl.innerHTML =
                    data.items
                        .map(
                            (it) => `<div class="flex justify-between"><span>${Number(it.qty) || 1}× ${escapeHtml(it.name)}</span><span class="tnum font-medium text-espresso">${formatBrl(it.line_cents)}</span></div>`
                        )
                        .join('') +
                    `<div class="pt-3 mt-2 border-t hairline space-y-1.5">` +
                    `<div class="flex justify-between text-espresso-soft"><span>Subtotal</span><span class="tnum">${formatBrl(data.subtotal_cents)}</span></div>` +
                    (data.discount_cents > 0
                        ? `<div class="flex justify-between text-positive"><span>Descontos</span><span class="tnum">- ${formatBrl(data.discount_cents)}</span></div>`
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

    async function syncCartWithServer() {
        const cart = getCart();
        if (cart.length === 0) {
            return;
        }

        try {
            const res = await fetch('/carrinho/validar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    items: cart.map((item) => ({ id: item.id, qty: Math.max(1, Math.min(99, Number(item.qty) || 1)) })),
                }),
            });

            if (!res.ok) {
                return;
            }

            const data = await res.json();
            if (Array.isArray(data.items)) {
                saveCart(data.items);
                renderSummary();
            }
            if (Array.isArray(data.warnings) && data.warnings.length > 0 && errorEl) {
                errorEl.textContent = data.warnings.join(' ');
                errorEl.classList.remove('hidden');
            }
        } catch {
            // Keep local cart if offline
        }
    }

    renderSummary();
    syncCartWithServer();
}

function initAccountAddressCep() {
    const form = document.getElementById('account-address-form');
    if (!form) {
        return;
    }

    const cepInput = form.querySelector('input[name="cep"]');
    if (!cepInput) {
        return;
    }

    cepInput.addEventListener('input', async () => {
        const digits = cepInput.value.replace(/\D/g, '');
        if (digits.length !== 8) {
            return;
        }

        const addr = await lookupAddressClient(digits);
        if (!addr) {
            return;
        }

        const setField = (name, value) => {
            const el = form.querySelector(`input[name="${name}"]`);
            if (el && value) {
                el.value = value;
            }
        };

        setField('street', addr.street);
        setField('neighborhood', addr.neighborhood);
        setField('city', addr.city);
        setField('state', addr.state);
    });
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
                    results.innerHTML = `<div class="px-4 py-3 text-xs text-espresso-muted">Nenhum produto encontrado para "${escapeHtml(term)}"</div>`;
                    results.classList.remove('hidden');
                    return;
                }

                results.innerHTML =
                    items
                        .map(
                            (item) => `
                            <a href="${safeUrl(item.url)}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-paper border-b hairline last:border-b-0 transition-colors duration-150">
                                <div class="w-10 h-10 rounded-md bg-linen border hairline overflow-hidden shrink-0 flex items-center justify-center">
                                    ${item.image ? `<img src="${safeUrl(item.image)}" alt="${escapeHtml(item.name)}" loading="lazy" decoding="async" class="w-full h-full object-cover" />` : `<span class="material-symbols-outlined text-[16px] text-espresso-muted">inventory_2</span>`}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-espresso truncate">${escapeHtml(item.name)}</p>
                                    <p class="text-xs text-espresso-muted">${escapeHtml(item.category || item.sku)}</p>
                                </div>
                                <span class="tnum text-sm font-medium text-espresso shrink-0">${escapeHtml(item.price)}</span>
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

function initLgpdBanner() {
    const banner = document.getElementById('lgpd-banner');
    const acceptBtn = document.getElementById('lgpd-accept-btn');
    if (!banner || !acceptBtn) {
        return;
    }

    const accepted = readStorage('vevey_lgpd_ok_v1', false);
    if (!accepted) {
        banner.classList.remove('hidden');
    }

    acceptBtn.addEventListener('click', () => {
        writeStorage('vevey_lgpd_ok_v1', true);
        banner.classList.add('hidden');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    initFavorites();
    initFavoritesPage();
    initInputMasks();
    initLgpdBanner();
    initPdpControls();
    initCheckout();
    initAccountAddressCep();
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
