/**
 * VEVEY DOCES — SCRIPT PRINCIPAL DOS PROTÓTIPOS
 * Gerenciamento centralizado de Carrinho, Filtros, Abas e Interatividade
 */

document.addEventListener('DOMContentLoaded', () => {
  initCart();
  initGlobalSearch();
  initCatalogPage();
  initProductPage();
  initCartPage();
  initCheckoutPage();
  initConfirmationPage();
  initAccountPage();
});

/* ==========================================================================
   1. GERENCIAMENTO DE ESTADO DO CARRINHO (LocalStorage)
   ========================================================================== */

const DEFAULT_CART_ITEMS = [
  {
    id: 'BL-9921',
    name: 'Kit Balões Metalizados Rose Gold & Cereja (50 unid.)',
    price: 64.90,
    quantity: 1,
    category: 'baloes'
  },
  {
    id: 'CF-4402',
    name: 'Jogo de Bicos de Confeitar em Inox (12 Peças + Adaptador)',
    price: 79.90,
    quantity: 1,
    category: 'confeitaria'
  },
  {
    id: 'PA-1029',
    name: 'Pasta Americana Tradicional Vermelho Rubi 800g',
    price: 39.90,
    quantity: 1,
    category: 'confeitaria'
  }
];

function getCart() {
  const saved = localStorage.getItem('vevey_cart');
  if (!saved) {
    localStorage.setItem('vevey_cart', JSON.stringify(DEFAULT_CART_ITEMS));
    return DEFAULT_CART_ITEMS;
  }
  try {
    return JSON.parse(saved);
  } catch (e) {
    return DEFAULT_CART_ITEMS;
  }
}

function saveCart(cart) {
  localStorage.setItem('vevey_cart', JSON.stringify(cart));
  updateCartBadge();
}

function addToCart(product, qty = 1) {
  const cart = getCart();
  const existing = cart.find(item => item.id === product.id);
  if (existing) {
    existing.quantity += qty;
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: qty,
      category: product.category || 'geral'
    });
  }
  saveCart(cart);
  showToast(`"${product.name}" adicionado ao carrinho!`, 'success');
}

function updateCartBadge() {
  const cart = getCart();
  const totalCount = cart.reduce((acc, item) => acc + item.quantity, 0);
  const totalValue = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);

  document.querySelectorAll('.cart-count, #cart-badge').forEach(el => {
    el.textContent = totalCount;
  });

  document.querySelectorAll('.cart-total-badge').forEach(el => {
    el.textContent = `R$ ${totalValue.toFixed(2).replace('.', ',')}`;
  });
}

function initCart() {
  updateCartBadge();
}

/* ==========================================================================
   2. SISTEMA DE NOTIFICAÇÕES (Toast)
   ========================================================================== */

function showToast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type === 'success' ? 'toast-success' : type === 'error' ? 'toast-error' : ''}`;
  
  const iconName = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';
  toast.innerHTML = `
    <span class="material-symbols-outlined text-xl text-${type === 'success' ? 'emerald' : type === 'error' ? 'red' : 'brand'}-600">
      ${iconName}
    </span>
    <span class="font-medium">${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'fadeOut 0.3s ease-in forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/* ==========================================================================
   3. BUSCA GLOBAL NO CABEÇALHO
   ========================================================================== */

function initGlobalSearch() {
  const searchInputs = document.querySelectorAll('header input[type="text"]');
  searchInputs.forEach(input => {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const query = input.value.trim();
        if (query) {
          window.location.href = `catalogo.html?q=${encodeURIComponent(query)}`;
        }
      }
    });
  });
}

/* ==========================================================================
   4. TELA DE CATÁLOGO (Filtros e Busca em Tempo Real)
   ========================================================================== */

function initCatalogPage() {
  const catalogGrid = document.getElementById('catalog-products');
  const searchInput = document.getElementById('catalog-search');
  const categoryFilters = document.querySelectorAll('input[name="category-filter"]');
  
  if (!catalogGrid) return;

  // Adicionar ao carrinho nos botões dos cards
  document.querySelectorAll('[data-action="add-to-cart"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const card = btn.closest('.product-card') || btn.closest('article');
      const id = btn.getAttribute('data-id') || 'PRD-' + Math.floor(Math.random() * 9000 + 1000);
      const name = card ? card.querySelector('h2, h3, .product-title')?.textContent?.trim() : 'Produto Selecionado';
      const priceText = card ? card.querySelector('.product-price')?.textContent?.replace('R$', '').replace('.', '').replace(',', '.').trim() : '49.90';
      const price = parseFloat(priceText) || 49.90;

      addToCart({ id, name, price });
    });
  });

  // Busca em tempo real
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const term = searchInput.value.toLowerCase();
      const cards = catalogGrid.querySelectorAll('.product-card');
      cards.forEach(card => {
        const title = card.querySelector('.product-title, h3, h2')?.textContent?.toLowerCase() || '';
        const match = title.includes(term);
        card.style.display = match ? '' : 'none';
      });
    });
  }

  // Filtros por Categoria
  categoryFilters.forEach(chk => {
    chk.addEventListener('change', () => {
      const activeCategories = Array.from(categoryFilters)
        .filter(c => c.checked)
        .map(c => c.value);

      const cards = catalogGrid.querySelectorAll('.product-card');
      cards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        if (activeCategories.length === 0 || activeCategories.includes(cat)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
}

/* ==========================================================================
   5. TELA DE DETALHES DO PRODUTO (produto.html)
   ========================================================================== */

function initProductPage() {
  const qtyInput = document.getElementById('product-qty');
  const btnMinus = document.getElementById('btn-qty-minus');
  const btnPlus = document.getElementById('btn-qty-plus');
  const btnAddToCart = document.getElementById('btn-add-product');
  const variationPills = document.querySelectorAll('.variation-pill');
  const tabButtons = document.querySelectorAll('[data-tab-target]');
  const cepInput = document.getElementById('cep-input');
  const btnCalcFrete = document.getElementById('btn-calc-frete');
  const freteResult = document.getElementById('frete-result');

  // Controle de Quantidade (+ / -)
  if (btnMinus && qtyInput) {
    btnMinus.addEventListener('click', () => {
      let val = parseInt(qtyInput.value) || 1;
      if (val > 1) qtyInput.value = val - 1;
    });
  }
  if (btnPlus && qtyInput) {
    btnPlus.addEventListener('click', () => {
      let val = parseInt(qtyInput.value) || 1;
      qtyInput.value = val + 1;
    });
  }

  // Variações do Produto (Cores / Tamanhos)
  variationPills.forEach(pill => {
    pill.addEventListener('click', () => {
      const group = pill.getAttribute('data-group') || 'default';
      document.querySelectorAll(`.variation-pill[data-group="${group}"]`).forEach(p => {
        p.classList.remove('border-brand-600', 'bg-brand-50', 'text-brand-600', 'ring-2', 'ring-brand-500');
        p.classList.add('border-gray-200');
      });
      pill.classList.remove('border-gray-200');
      pill.classList.add('border-brand-600', 'bg-brand-50', 'text-brand-600', 'ring-2', 'ring-brand-500');
    });
  });

  // Botão Adicionar ao Carrinho
  if (btnAddToCart) {
    btnAddToCart.addEventListener('click', () => {
      const qty = parseInt(qtyInput?.value) || 1;
      const title = document.getElementById('product-detail-title')?.textContent?.trim() || 'Kit Balões Metalizados Rose Gold & Cereja (50 unid.)';
      const priceText = document.getElementById('product-detail-price')?.textContent?.replace('R$', '').replace('.', '').replace(',', '.').trim() || '64.90';
      const price = parseFloat(priceText) || 64.90;
      const sku = document.getElementById('product-detail-sku')?.textContent?.replace('Cód:', '').trim() || 'BL-9921';

      addToCart({ id: sku, name: title, price }, qty);
    });
  }

  // Alternância de Abas (Tabs)
  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-tab-target');
      tabButtons.forEach(b => {
        b.classList.remove('border-brand-600', 'text-brand-600', 'font-bold');
        b.classList.add('border-transparent', 'text-gray-500');
      });
      btn.classList.add('border-brand-600', 'text-brand-600', 'font-bold');
      btn.classList.remove('border-transparent', 'text-gray-500');

      document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.add('hidden');
      });
      const activePane = document.getElementById(targetId);
      if (activePane) activePane.classList.remove('hidden');
    });
  });

  // Cálculo de Frete Simulado
  if (btnCalcFrete && cepInput && freteResult) {
    btnCalcFrete.addEventListener('click', () => {
      const cep = cepInput.value.replace(/\D/g, '');
      if (cep.length < 8) {
        showToast('Digite um CEP válido com 8 dígitos.', 'error');
        return;
      }
      freteResult.classList.remove('hidden');
      freteResult.innerHTML = `
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs space-y-1 text-emerald-900">
          <div class="flex justify-between font-bold">
            <span>Sedex Expresso:</span>
            <span>R$ 18,90 (2 a 3 dias úteis)</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Retirada no Centro de Distribuição:</span>
            <span class="text-emerald-700 font-bold">Grátis (1 dia útil)</span>
          </div>
        </div>
      `;
    });
  }
}

/* ==========================================================================
   6. TELA DO CARRINHO (carrinho.html)
   ========================================================================== */

let appliedDiscountPercent = 0;

function initCartPage() {
  const cartContainer = document.getElementById('cart-items-container');
  if (!cartContainer) return;

  renderCartItems();

  // Cupom de Desconto
  const btnCoupon = document.getElementById('btn-apply-coupon');
  const inputCoupon = document.getElementById('coupon-input');
  if (btnCoupon && inputCoupon) {
    btnCoupon.addEventListener('click', () => {
      const code = inputCoupon.value.trim().toUpperCase();
      if (code === 'VEVEY10') {
        appliedDiscountPercent = 0.10;
        showToast('Cupom VEVEY10 aplicado: 10% de desconto!', 'success');
        updateCartSummary();
      } else if (code === '') {
        showToast('Digite o código do cupom.', 'error');
      } else {
        showToast('Cupom inválido ou expirado.', 'error');
      }
    });
  }
}

function renderCartItems() {
  const cartContainer = document.getElementById('cart-items-container');
  if (!cartContainer) return;

  const cart = getCart();

  if (cart.length === 0) {
    cartContainer.innerHTML = `
      <div class="text-center py-12 bg-white border border-gray-200 rounded-xl p-8 space-y-4">
        <span class="material-symbols-outlined text-5xl text-gray-300">shopping_cart</span>
        <h3 class="font-bold text-gray-700 text-lg">Seu carrinho está vazio</h3>
        <p class="text-xs text-gray-500">Explore nosso catálogo e encontre tudo para sua festa e confeitaria!</p>
        <a href="catalogo.html" class="btn-primary inline-flex">Ver Produtos</a>
      </div>
    `;
    updateCartSummary();
    return;
  }

  cartContainer.innerHTML = cart.map(item => `
    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border border-gray-200 rounded-xl gap-4" data-item-id="${item.id}">
      <div class="flex items-center gap-4 min-w-0">
        <!-- Placeholder Neutro de Imagem -->
        <div class="w-16 h-16 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shrink-0">
          <span class="material-symbols-outlined text-2xl">image</span>
        </div>
        <div class="min-w-0">
          <h4 class="font-bold text-sm text-gray-900 truncate">${item.name}</h4>
          <span class="text-xs text-gray-500">Cód: ${item.id}</span>
          <p class="text-xs text-brand-600 font-bold mt-1">R$ ${item.price.toFixed(2).replace('.', ',')}</p>
        </div>
      </div>

      <div class="flex items-center justify-between sm:justify-end gap-6">
        <!-- Quantidade Selector -->
        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
          <button class="px-2.5 py-1 text-gray-600 hover:bg-gray-200 transition-colors" onclick="changeItemQty('${item.id}', -1)">-</button>
          <span class="px-3 py-1 text-xs font-bold text-gray-800">${item.quantity}</span>
          <button class="px-2.5 py-1 text-gray-600 hover:bg-gray-200 transition-colors" onclick="changeItemQty('${item.id}', 1)">+</button>
        </div>

        <!-- Subtotal -->
        <div class="text-right min-w-[80px]">
          <span class="text-xs text-gray-400 block">Subtotal</span>
          <span class="text-sm font-bold text-gray-900">R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
        </div>

        <!-- Botão Remover -->
        <button class="text-gray-400 hover:text-red-600 p-1.5 transition-colors" onclick="removeCartItem('${item.id}')" title="Remover item">
          <span class="material-symbols-outlined text-lg">delete</span>
        </button>
      </div>
    </div>
  `).join('');

  updateCartSummary();
}

function changeItemQty(id, delta) {
  const cart = getCart();
  const item = cart.find(i => i.id === id);
  if (!item) return;

  item.quantity += delta;
  if (item.quantity <= 0) {
    removeCartItem(id);
    return;
  }
  saveCart(cart);
  renderCartItems();
}

function removeCartItem(id) {
  let cart = getCart();
  cart = cart.filter(i => i.id !== id);
  saveCart(cart);
  renderCartItems();
  showToast('Item removido do carrinho.', 'info');
}

function updateCartSummary() {
  const cart = getCart();
  const subtotal = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
  const discount = subtotal * appliedDiscountPercent;
  const shipping = subtotal >= 199 || subtotal === 0 ? 0 : 15.00;
  const total = subtotal - discount + shipping;

  const subtotalEl = document.getElementById('summary-subtotal');
  const discountEl = document.getElementById('summary-discount');
  const shippingEl = document.getElementById('summary-shipping');
  const totalEl = document.getElementById('summary-total');

  if (subtotalEl) subtotalEl.textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
  if (discountEl) discountEl.textContent = discount > 0 ? `- R$ ${discount.toFixed(2).replace('.', ',')}` : 'R$ 0,00';
  if (shippingEl) shippingEl.textContent = shipping === 0 ? 'Grátis' : `R$ ${shipping.toFixed(2).replace('.', ',')}`;
  if (totalEl) totalEl.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

/* ==========================================================================
   7. TELA DE CHECKOUT (checkout.html)
   ========================================================================== */

function initCheckoutPage() {
  const paymentTabs = document.querySelectorAll('[data-payment-tab]');
  const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
  const checkoutForm = document.getElementById('checkout-form');

  // Alternância das abas de pagamento
  paymentTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.getAttribute('data-payment-tab');
      paymentTabs.forEach(t => {
        t.classList.remove('border-brand-600', 'bg-brand-50', 'text-brand-600', 'font-bold');
        t.classList.add('border-gray-200', 'text-gray-600');
      });
      tab.classList.add('border-brand-600', 'bg-brand-50', 'text-brand-600', 'font-bold');
      tab.classList.remove('border-gray-200', 'text-gray-600');

      document.querySelectorAll('.payment-pane').forEach(p => p.classList.add('hidden'));
      const activePane = document.getElementById(`pane-${target}`);
      if (activePane) activePane.classList.remove('hidden');
    });
  });

  // Atualização do Resumo no Checkout
  const cart = getCart();
  const subtotal = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
  const checkoutSubtotal = document.getElementById('checkout-subtotal');
  const checkoutTotal = document.getElementById('checkout-total');
  if (checkoutSubtotal) checkoutSubtotal.textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
  if (checkoutTotal) checkoutTotal.textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;

  // Submissão do Pedido
  if (checkoutForm) {
    checkoutForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const orderId = '#VV-' + Math.floor(10000 + Math.random() * 90000);
      const orderData = {
        orderId,
        date: new Date().toLocaleDateString('pt-BR'),
        items: cart,
        total: subtotal
      };

      localStorage.setItem('vevey_last_order', JSON.stringify(orderData));
      
      // Limpa carrinho após fechar pedido
      localStorage.setItem('vevey_cart', JSON.stringify([]));
      updateCartBadge();

      window.location.href = 'confirmacao.html';
    });
  }
}

/* ==========================================================================
   8. TELA DE CONFIRMAÇÃO (confirmacao.html)
   ========================================================================== */

function initConfirmationPage() {
  const btnCopyPix = document.getElementById('btn-copy-pix');
  const pixKeyInput = document.getElementById('pix-key-input');
  const orderIdDisplay = document.getElementById('order-id-display');

  const lastOrder = JSON.parse(localStorage.getItem('vevey_last_order') || 'null');
  if (lastOrder && orderIdDisplay) {
    orderIdDisplay.textContent = lastOrder.orderId;
  }

  if (btnCopyPix && pixKeyInput) {
    btnCopyPix.addEventListener('click', () => {
      pixKeyInput.select();
      navigator.clipboard.writeText(pixKeyInput.value).then(() => {
        showToast('Código Pix copiado com sucesso!', 'success');
      }).catch(() => {
        showToast('Código Pix selecionado!', 'info');
      });
    });
  }
}

/* ==========================================================================
   9. TELA DE MINHA CONTA & HISTÓRICO (minha-conta.html)
   ========================================================================== */

function initAccountPage() {
  const statusFilterBtns = document.querySelectorAll('[data-status-filter]');
  const orderCards = document.querySelectorAll('[data-order-status]');
  const orderSearchInput = document.getElementById('order-search-input');

  // Filtro por abas de status
  statusFilterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const status = btn.getAttribute('data-status-filter');
      statusFilterBtns.forEach(b => {
        b.classList.remove('bg-brand-600', 'text-white', 'font-bold');
        b.classList.add('border', 'border-gray-200', 'text-gray-600', 'bg-white');
      });
      btn.classList.add('bg-brand-600', 'text-white', 'font-bold');
      btn.classList.remove('border-gray-200', 'text-gray-600', 'bg-white');

      orderCards.forEach(card => {
        const cardStatus = card.getAttribute('data-order-status');
        if (status === 'todos' || cardStatus === status) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // Busca por ID do pedido
  if (orderSearchInput) {
    orderSearchInput.addEventListener('input', () => {
      const term = orderSearchInput.value.toLowerCase().trim();
      orderCards.forEach(card => {
        const idText = card.querySelector('.order-id, h3, p')?.textContent?.toLowerCase() || '';
        card.style.display = idText.includes(term) ? '' : 'none';
      });
    });
  }
}
