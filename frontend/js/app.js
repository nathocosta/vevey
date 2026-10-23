/**
 * VEVEY DOCES — SCRIPT PRINCIPAL DOS PROTÓTIPOS
 * Gerenciamento centralizado de Carrinho, Filtros, Abas e Interatividade
 */

window.VEVEY_CONFIG = {
  API_URL: (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')
    ? 'http://localhost:8000/api/v1'
    : 'https://vevey-api.onrender.com/api/v1'
};

document.addEventListener('DOMContentLoaded', () => {
  initUserHeader();
  initCart();
  initGlobalSearch();
  initHomePage();
  initCatalogPage();
  initProductPage();
  initCartPage();
  initCheckoutPage();
  initConfirmationPage();
  initAccountPage();
  initCookieConsent();
});

/* ==========================================================================
   0.1 AVISO DE COOKIES & LGPD
   ========================================================================== */

function initCookieConsent() {
  if (localStorage.getItem('vevey_cookie_consent')) return;

  const banner = document.createElement('div');
  banner.id = 'vevey-cookie-banner';
  banner.className = 'fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:max-w-md bg-white/95 backdrop-blur-md border border-[#EAE3D9] p-5 rounded-2xl shadow-2xl z-50 transition-all duration-300 font-sans text-xs text-[#201516] flex flex-col gap-3.5';
  banner.innerHTML = `
    <div class="flex items-start gap-3">
      <div class="w-8 h-8 rounded-xl bg-[#FAF7F2] border border-[#EAE3D9] text-[#800F2F] flex items-center justify-center flex-shrink-0">
        <span class="material-symbols-outlined text-[20px]">cookie</span>
      </div>
      <div>
        <p class="font-bold text-sm text-[#800F2F] leading-tight">Privacidade &amp; Cookies (LGPD)</p>
        <p class="text-[#665A5B] mt-1 leading-relaxed">
          Utilizamos cookies essenciais para garantir navegação segura, integridade do carrinho e respeito à LGPD.
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2 pt-1 border-t border-[#F4EFEB]">
      <button id="btn-accept-cookies" class="flex-1 py-2 px-4 rounded-full bg-[#800F2F] hover:bg-[#5E0921] text-white font-bold text-xs transition-colors shadow-sm active:scale-95 text-center">
        Aceitar Todos
      </button>
      <button id="btn-decline-cookies" class="py-2 px-3 rounded-full bg-[#FAF7F2] hover:bg-[#EAE3D9] text-[#665A5B] font-semibold text-xs transition-colors text-center">
        Apenas Essenciais
      </button>
    </div>
  `;

  document.body.appendChild(banner);

  const closeBanner = () => {
    banner.style.opacity = '0';
    banner.style.transform = 'translateY(15px)';
    setTimeout(() => banner.remove(), 300);
  };

  document.getElementById('btn-accept-cookies')?.addEventListener('click', () => {
    localStorage.setItem('vevey_cookie_consent', 'all');
    closeBanner();
  });

  document.getElementById('btn-decline-cookies')?.addEventListener('click', () => {
    localStorage.setItem('vevey_cookie_consent', 'essential');
    closeBanner();
  });
}


/* ==========================================================================
   0. GESTÃO DE USUÁRIO & CABEÇALHO DINÂMICO
   ========================================================================== */

function initUserHeader() {
  const userGreeting = document.getElementById('header-user-greeting');
  const userAction = document.getElementById('header-user-action');
  const userAvatar = document.getElementById('header-user-avatar');
  const userLink = document.getElementById('header-user-btn');

  // Limpa sessão mockada se existir resquício de teste
  const savedUser = localStorage.getItem('vevey_user');
  if (savedUser) {
    try {
      const user = JSON.parse(savedUser);
      const firstName = user.nome ? user.nome.split(' ')[0] : 'Cliente';
      if (userGreeting) userGreeting.textContent = `Olá, ${firstName}`;
      if (userAction) userAction.textContent = 'Meus Pedidos';
      if (userAvatar) {
        userAvatar.innerHTML = `<span class="font-bold text-primary">${firstName.charAt(0).toUpperCase()}</span>`;
      }
      if (userLink) userLink.href = 'minha-conta.html';
      return;
    } catch (e) {}
  }

  // Visitante padrão (Não logado)
  if (userGreeting) userGreeting.textContent = 'Entre ou Cadastre-se';
  if (userAction) userAction.textContent = 'Minha Conta';
  if (userAvatar) {
    userAvatar.innerHTML = '<span class="material-symbols-outlined text-[18px]">person</span>';
  }
  if (userLink) userLink.href = 'minha-conta.html';
}

/* ==========================================================================
   1. GERENCIAMENTO DE ESTADO DO CARRINHO (LocalStorage)
   ========================================================================== */

// Limpeza automática de dados mockados antigos de sessões anteriores
(function cleanupOldMocks() {
  const versionKey = 'vevey_clean_v2';
  if (!localStorage.getItem(versionKey)) {
    const existing = localStorage.getItem('vevey_cart');
    if (existing) {
      try {
        const parsed = JSON.parse(existing);
        if (parsed.some(item => item.id === 'BL-9921' && parsed.length <= 3)) {
          localStorage.removeItem('vevey_cart');
        }
      } catch (e) {}
    }
    localStorage.setItem(versionKey, 'true');
  }
})();

function getCart() {
  const saved = localStorage.getItem('vevey_cart');
  if (!saved) {
    return [];
  }
  try {
    return JSON.parse(saved);
  } catch (e) {
    return [];
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
    if (totalCount === 0) {
      el.classList.add('opacity-0');
    } else {
      el.classList.remove('opacity-0');
    }
  });

  document.querySelectorAll('.cart-total-badge').forEach(el => {
    el.textContent = totalValue > 0 ? `R$ ${totalValue.toFixed(2).replace('.', ',')}` : 'R$ 0,00';
  });
}

function initCart() {
  updateCartBadge();
}

/* ==========================================================================
   1.1 VITRINE DINÂMICA DA HOME (index.html)
   ========================================================================== */

const VEVEY_PRODUCT_IMAGES = {
  'bl-9921': 'assets/images/prod-baloes.jpg',
  'bb-1024': 'assets/images/prod-bubble.jpg',
  'cf-4402': 'assets/images/prod-bicos.jpg',
  'pa-1029': 'assets/images/cat-confeitaria.jpg',
  'fd-5510': 'assets/images/cat-doces.jpg',
  'to-3310': 'assets/images/hero-vevey.jpg',
  'in-8801': 'assets/images/cat-baloes.jpg',
  'ch-7720': 'assets/images/prod-chocolate.jpg'
};

async function initHomePage() {
  const homeGrid = document.getElementById('home-featured-products');
  if (!homeGrid) return;

  const apiUrl = window.VEVEY_CONFIG ? window.VEVEY_CONFIG.API_URL : 'https://vevey-api.onrender.com/api/v1';

  try {
    const res = await fetch(`${apiUrl}/produtos?ativo=true&limite=8`);
    if (!res.ok) throw new Error('Falha ao carregar produtos');
    const produtos = await res.json();

    if (produtos && produtos.length > 0) {
      homeGrid.innerHTML = produtos.map(p => {
        const precoAtual = Number(p.preco_promocional || p.preco);
        const precoOriginal = Number(p.preco);
        const temPromo = p.preco_promocional && p.preco_promocional < p.preco;
        const slugKey = (p.slug || '').toLowerCase();
        const imgUrl = VEVEY_PRODUCT_IMAGES[slugKey] || 'assets/images/hero-vevey.jpg';
        const categoriaNome = p.categorias && p.categorias.length > 0 ? p.categorias[0].nome : 'Confeitaria & Decoração';

        return `
          <div class="product-card group flex flex-col p-4 bg-white rounded-2xl border border-[#EAE3D9] hover:border-[#800F2F]/40 shadow-sm hover:shadow-xl transition-all duration-300">
            <div class="product-image-wrap relative w-full aspect-square rounded-xl overflow-hidden bg-[#FAF7F2] mb-3">
              <img 
                src="${imgUrl}" 
                alt="${p.nome}" 
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
              >
              ${temPromo ? `
                <span class="absolute top-2.5 left-2.5 bg-[#800F2F] text-white text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow-sm">
                  Oferta
                </span>
              ` : ''}
              <button 
                type="button"
                aria-label="Favoritar"
                class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-white/90 backdrop-blur text-[#665A5B] hover:text-[#800F2F] flex items-center justify-center shadow-sm transition-colors"
              >
                <span class="material-symbols-outlined text-[18px]">favorite</span>
              </button>
            </div>

            <span class="text-[11px] font-semibold text-[#B88636] uppercase tracking-wider">${categoriaNome}</span>
            <a href="produto.html?id=${p.id}" class="block mt-1">
              <h3 class="font-bold text-sm text-[#201516] line-clamp-2 min-h-[40px] group-hover:text-[#800F2F] transition-colors leading-snug">
                ${p.nome}
              </h3>
            </a>

            <div class="mt-auto pt-3 border-t border-[#F4EFEB]">
              <div class="flex items-baseline gap-2">
                <span class="text-lg font-extrabold text-[#800F2F]">
                  R$ ${precoAtual.toFixed(2).replace('.', ',')}
                </span>
                ${temPromo ? `
                  <span class="text-xs text-[#665A5B] line-through">
                    R$ ${precoOriginal.toFixed(2).replace('.', ',')}
                  </span>
                ` : ''}
              </div>
              <p class="text-[11px] text-[#665A5B] mt-0.5">Em até 3x sem juros</p>
            </div>

            <button 
              type="button"
              data-action="add-to-cart" 
              data-id="${p.slug || p.id}"
              data-name="${p.nome.replace(/"/g, '&quot;')}"
              data-price="${precoAtual}"
              class="btn-add-cart-dynamic mt-3 w-full py-2.5 px-4 rounded-full bg-[#800F2F] hover:bg-[#5E0921] text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-all active:scale-95 shadow-sm"
            >
              <span class="material-symbols-outlined text-[16px]">shopping_bag</span>
              <span>Adicionar à Sacola</span>
            </button>
          </div>
        `;
      }).join('');

      // Liga os botões de adicionar ao carrinho dinâmicos
      homeGrid.querySelectorAll('.btn-add-cart-dynamic').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const id = btn.getAttribute('data-id');
          const name = btn.getAttribute('data-name');
          const price = parseFloat(btn.getAttribute('data-price')) || 0;
          addToCart({ id, name, price }, 1);
        });
      });
    }
  } catch (err) {
    console.warn('API em modo fallback para a Home:', err);
  }
}


/* ==========================================================================
   2. SISTEMA DE NOTIFICAÇÕES (Toast)
   ========================================================================== */

function escapeHtml(text) {
  if (typeof text !== 'string') return text;
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

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
    <span class="font-medium">${escapeHtml(message)}</span>
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

async function initCatalogPage() {
  const catalogGrid = document.getElementById('catalog-products');
  const searchInputs = document.querySelectorAll('#catalog-search, #catalog-search-mobile');
  const categoryFilters = document.querySelectorAll('input[name="category-filter"]');
  
  if (!catalogGrid) return;

  const apiUrl = window.VEVEY_CONFIG ? window.VEVEY_CONFIG.API_URL : 'https://vevey-api.onrender.com/api/v1';

  // 1. Busca produtos reais da API
  try {
    const res = await fetch(`${apiUrl}/produtos?ativo=true`);
    if (res.ok) {
      const produtos = await res.json();
      if (produtos && produtos.length > 0) {
        catalogGrid.innerHTML = produtos.map(p => {
          const precoAtual = Number(p.preco_promocional || p.preco);
          const precoOriginal = Number(p.preco);
          const temPromo = p.preco_promocional && p.preco_promocional < p.preco;
          const slugKey = (p.slug || '').toLowerCase();
          const imgUrl = VEVEY_PRODUCT_IMAGES[slugKey] || 'assets/images/hero-vevey.jpg';
          const categoriaObj = p.categorias && p.categorias.length > 0 ? p.categorias[0] : null;
          const categoriaNome = categoriaObj ? categoriaObj.nome : 'Confeitaria & Decoração';
          const categoriaSlug = categoriaObj ? categoriaObj.slug : 'geral';

          return `
            <div class="product-card group flex flex-col p-4 bg-white rounded-2xl border border-[#EAE3D9] hover:border-[#800F2F]/40 shadow-sm hover:shadow-xl transition-all duration-300" data-category="${categoriaSlug}" data-sku="${p.slug || p.id}">
              <div class="product-image-wrap relative w-full aspect-square rounded-xl overflow-hidden bg-[#FAF7F2] mb-3">
                <img 
                  src="${imgUrl}" 
                  alt="${p.nome}" 
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                  loading="lazy"
                >
                ${temPromo ? `
                  <span class="absolute top-2.5 left-2.5 bg-[#800F2F] text-white text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow-sm">
                    Oferta
                  </span>
                ` : ''}
                <button 
                  type="button"
                  aria-label="Favoritar"
                  class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-white/90 backdrop-blur text-[#665A5B] hover:text-[#800F2F] flex items-center justify-center shadow-sm transition-colors"
                >
                  <span class="material-symbols-outlined text-[18px]">favorite</span>
                </button>
              </div>

              <span class="text-[11px] font-semibold text-[#B88636] uppercase tracking-wider">${categoriaNome}</span>
              <a href="produto.html?id=${p.id}" class="block mt-1">
                <h3 class="product-title font-bold text-sm text-[#201516] line-clamp-2 min-h-[40px] group-hover:text-[#800F2F] transition-colors leading-snug">
                  ${p.nome}
                </h3>
              </a>

              <div class="mt-auto pt-3 border-t border-[#F4EFEB]">
                <div class="flex items-baseline gap-2">
                  <span class="product-price text-lg font-extrabold text-[#800F2F]">
                    R$ ${precoAtual.toFixed(2).replace('.', ',')}
                  </span>
                  ${temPromo ? `
                    <span class="text-xs text-[#665A5B] line-through">
                      R$ ${precoOriginal.toFixed(2).replace('.', ',')}
                    </span>
                  ` : ''}
                </div>
                <p class="text-[11px] text-[#665A5B] mt-0.5">Em até 3x sem juros</p>
              </div>

              <button 
                type="button"
                data-action="add-to-cart" 
                data-id="${p.slug || p.id}"
                data-name="${p.nome.replace(/"/g, '&quot;')}"
                data-price="${precoAtual}"
                class="btn-add-cart-dynamic mt-3 w-full py-2.5 px-4 rounded-full bg-[#800F2F] hover:bg-[#5E0921] text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-all active:scale-95 shadow-sm"
              >
                <span class="material-symbols-outlined text-[16px]">shopping_bag</span>
                <span>Adicionar à Sacola</span>
              </button>
            </div>
          `;
        }).join('');

        // Liga os botões de adicionar ao carrinho dinâmicos
        catalogGrid.querySelectorAll('.btn-add-cart-dynamic').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const price = parseFloat(btn.getAttribute('data-price')) || 0;
            addToCart({ id, name, price }, 1);
          });
        });
      }
    }
  } catch (err) {
    console.warn('API catálogo offline, mantendo fallback local:', err);
  }

  // Função central de filtragem
  function applyFilters() {
    const term = (searchInputs[0]?.value || searchInputs[1]?.value || '').toLowerCase().trim();
    const activeCategories = Array.from(categoryFilters)
      .filter(c => c.checked)
      .map(c => c.value.toLowerCase());

    const cards = catalogGrid.querySelectorAll('.product-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const title = card.querySelector('.product-title, h3, h2')?.textContent?.toLowerCase() || '';
      const cat = (card.getAttribute('data-category') || '').toLowerCase();
      
      const matchesSearch = !term || title.includes(term);
      const matchesCategory = activeCategories.length === 0 || activeCategories.includes(cat);

      if (matchesSearch && matchesCategory) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    // Mensagem de nenhum resultado encontrado
    let noResultsEl = document.getElementById('catalog-no-results');
    if (visibleCount === 0) {
      if (!noResultsEl) {
        noResultsEl = document.createElement('div');
        noResultsEl.id = 'catalog-no-results';
        noResultsEl.className = 'col-span-full py-16 text-center text-[#665A5B]';
        noResultsEl.innerHTML = `
          <span class="material-symbols-outlined text-5xl text-[#800F2F]/40 mb-2">search_off</span>
          <h3 class="font-serif text-xl font-bold text-[#201516]">Nenhum produto encontrado</h3>
          <p class="text-sm mt-1">Tente buscar por outro termo ou desmarcar os filtros selecionados.</p>
          <button id="btn-reset-filters" class="mt-4 px-5 py-2.5 bg-[#800F2F] text-white font-bold text-xs rounded-full hover:bg-[#5E0921] transition-colors">
            Limpar Filtros
          </button>
        `;
        catalogGrid.appendChild(noResultsEl);
        document.getElementById('btn-reset-filters')?.addEventListener('click', () => {
          searchInputs.forEach(i => i.value = '');
          categoryFilters.forEach(c => c.checked = false);
          applyFilters();
        });
      } else {
        noResultsEl.style.display = '';
      }
    } else if (noResultsEl) {
      noResultsEl.style.display = 'none';
    }
  }

  // Leitura de parâmetros de URL (?categoria=... ou ?q=...)
  const urlParams = new URLSearchParams(window.location.search);
  const paramCategory = urlParams.get('categoria');
  const paramQuery = urlParams.get('q');

  if (paramQuery) {
    searchInputs.forEach(input => input.value = paramQuery);
  }

  if (paramCategory) {
    const matchingCheck = Array.from(categoryFilters).find(c => c.value.toLowerCase() === paramCategory.toLowerCase());
    if (matchingCheck) {
      matchingCheck.checked = true;
    }
  }

  // Aciona filtro inicial se houver parâmetros
  if (paramCategory || paramQuery) {
    applyFilters();
  }

  // Configura links de detalhe do produto
  document.querySelectorAll('.product-card').forEach(card => {
    const sku = card.getAttribute('data-sku') || card.querySelector('[data-id]')?.getAttribute('data-id');
    const link = card.querySelector('a[href*="produto.html"]');
    if (sku && link && !link.getAttribute('href').includes('?id=')) {
      link.setAttribute('href', `produto.html?id=${sku}`);
    }
  });

  // Ordenação de Produtos
  const sortSelect = document.getElementById('sort-select');
  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      const mode = sortSelect.value;
      const cards = Array.from(catalogGrid.querySelectorAll('.product-card'));
      cards.sort((a, b) => {
        const priceA = parseFloat(a.querySelector('.product-price')?.textContent?.replace('R$', '').replace('.', '').replace(',', '.').trim() || '0');
        const priceB = parseFloat(b.querySelector('.product-price')?.textContent?.replace('R$', '').replace('.', '').replace(',', '.').trim() || '0');
        if (mode === 'price-asc') return priceA - priceB;
        if (mode === 'price-desc') return priceB - priceA;
        return 0;
      });
      cards.forEach(card => catalogGrid.appendChild(card));
      applyFilters();
    });
  }

  // Sistema de Favoritos (Wishlist)
  document.querySelectorAll('button[aria-label*="Favoritar"], .btn-wishlist').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const icon = btn.querySelector('.material-symbols-outlined');
      if (icon) {
        if (icon.textContent === 'favorite') {
          icon.textContent = 'favorite_border';
          showToast('Produto removido dos favoritos.', 'info');
        } else {
          icon.textContent = 'favorite';
          showToast('Produto adicionado aos seus favoritos!', 'success');
        }
      }
    });
  });

  // Aplica filtros logo no carregamento
  applyFilters();

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
  searchInputs.forEach(input => {
    input.addEventListener('input', applyFilters);
  });

  // Filtros por Categoria
  categoryFilters.forEach(chk => {
    chk.addEventListener('change', applyFilters);
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

  // Carrega dados dinâmicos do produto se houver ?id= ou ?sku= na URL
  const urlParams = new URLSearchParams(window.location.search);
  const targetId = urlParams.get('id') || urlParams.get('sku');

  if (targetId) {
    (async () => {
      try {
        const apiUrl = window.VEVEY_CONFIG ? window.VEVEY_CONFIG.API_URL : 'https://vevey-api.onrender.com/api/v1';
        let matched = null;

        // Tenta buscar diretamente por ID numérico
        if (!isNaN(targetId)) {
          const res = await fetch(`${apiUrl}/produtos/${targetId}`);
          if (res.ok) matched = await res.json();
        }

        // Se não achou por ID numérico, busca na listagem por slug
        if (!matched) {
          const listRes = await fetch(`${apiUrl}/produtos?ativo=true`);
          if (listRes.ok) {
            const list = await listRes.json();
            matched = list.find(p => String(p.id) === targetId || (p.slug && p.slug.toLowerCase() === targetId.toLowerCase()));
          }
        }

        if (matched) {
          const titleEl = document.getElementById('product-detail-title');
          const skuEl = document.getElementById('product-detail-sku');
          const priceEl = document.getElementById('product-detail-price');
          const heroImg = document.getElementById('main-product-image');
          const slugKey = (matched.slug || '').toLowerCase();
          const imgUrl = VEVEY_PRODUCT_IMAGES[slugKey] || 'assets/images/hero-vevey.jpg';
          const preco = Number(matched.preco_promocional || matched.preco);

          if (titleEl) titleEl.textContent = matched.nome;
          if (skuEl) skuEl.textContent = `Cód: ${(matched.slug || matched.id).toUpperCase()}`;
          if (priceEl) priceEl.textContent = `R$ ${preco.toFixed(2).replace('.', ',')}`;

          if (heroImg) {
            heroImg.innerHTML = `
              <img src="${imgUrl}" alt="${matched.nome}" class="w-full h-full object-cover rounded-2xl shadow-sm">
            `;
          }
        }
      } catch (e) {
        console.warn('Erro ao carregar detalhes do produto:', e);
      }
    })();
  }

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

  // Cálculo de Frete Conectado à API (com fallback elegante)
  if (btnCalcFrete && cepInput && freteResult) {
    btnCalcFrete.addEventListener('click', async () => {
      const cep = cepInput.value.replace(/\D/g, '');
      if (cep.length !== 8) {
        showToast('Digite um CEP válido com 8 dígitos.', 'error');
        return;
      }

      const priceText = document.getElementById('product-detail-price')?.textContent?.replace('R$', '').replace('.', '').replace(',', '.').trim() || '64.90';
      const subtotal = parseFloat(priceText) || 64.90;

      const originalBtnText = btnCalcFrete.textContent;
      btnCalcFrete.disabled = true;
      btnCalcFrete.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Calculando...';

      try {
        const apiUrl = window.VEVEY_CONFIG ? window.VEVEY_CONFIG.API_URL : 'http://localhost:8000/api/v1';
        const response = await fetch(`${apiUrl}/frete/calcular`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ cep_destino: cep, subtotal: subtotal })
        });

        if (!response.ok) throw new Error('Falha no cálculo pela API');
        const data = await response.json();

        freteResult.classList.remove('hidden');
        freteResult.innerHTML = `
          <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-lg text-xs space-y-2 text-emerald-950">
            <p class="font-bold text-emerald-800 text-[11px] uppercase tracking-wide">Opções para o CEP ${cep.replace(/(\d{5})(\d{3})/, '$1-$2')}:</p>
            ${data.opcoes.map(op => `
              <div class="flex justify-between items-center py-1.5 border-b border-emerald-100/80 last:border-none">
                <div>
                  <span class="font-bold text-gray-800">${op.nome}</span>
                  <span class="text-gray-500 block text-[11px]">${op.prazo_dias}</span>
                </div>
                <span class="font-bold ${op.gratis ? 'text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full' : 'text-gray-900'}">
                  ${op.gratis ? 'Grátis' : 'R$ ' + Number(op.valor).toFixed(2).replace('.', ',')}
                </span>
              </div>
            `).join('')}
          </div>
        `;
        showToast('Frete calculado com sucesso!', 'success');
      } catch (err) {
        // Fallback offline / sem backend rodando no momento
        freteResult.classList.remove('hidden');
        freteResult.innerHTML = `
          <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-lg text-xs space-y-2 text-emerald-950">
            <p class="font-bold text-emerald-800 text-[11px] uppercase tracking-wide">Opções para o CEP ${cep.replace(/(\d{5})(\d{3})/, '$1-$2')}:</p>
            <div class="flex justify-between items-center py-1.5 border-b border-emerald-100/80">
              <div>
                <span class="font-bold text-gray-800">Sedex Expresso</span>
                <span class="text-gray-500 block text-[11px]">1 a 2 dias úteis</span>
              </div>
              <span class="font-bold text-gray-900">R$ 18,90</span>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-emerald-100/80">
              <div>
                <span class="font-bold text-gray-800">PAC Econômico</span>
                <span class="text-gray-500 block text-[11px]">4 a 6 dias úteis</span>
              </div>
              <span class="font-bold ${subtotal >= 199 ? 'text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full' : 'text-gray-900'}">
                ${subtotal >= 199 ? 'Grátis' : 'R$ 11,50'}
              </span>
            </div>
            <div class="flex justify-between items-center py-1.5">
              <div>
                <span class="font-bold text-gray-800">Retirada na Loja (Vevey Co.)</span>
                <span class="text-gray-500 block text-[11px]">Em até 2 horas</span>
              </div>
              <span class="font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Grátis</span>
            </div>
          </div>
        `;
      } finally {
        btnCalcFrete.disabled = false;
        btnCalcFrete.textContent = originalBtnText;
      }
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
      const nomeInput = document.getElementById('checkout-nome')?.value?.trim();
      const emailInput = document.getElementById('checkout-email')?.value?.trim();
      const telInput = document.getElementById('checkout-telefone')?.value?.trim();
      const selectedShipping = document.querySelector('input[name="shipping_method"]:checked')?.value;
      const isRetirada = selectedShipping === 'retirada';

      const orderId = '#VV-' + Math.floor(10000 + Math.random() * 90000);
      const orderData = {
        orderId,
        date: new Date().toLocaleDateString('pt-BR'),
        cliente: nomeInput || 'Cliente Online',
        email: emailInput || 'cliente@exemplo.com.br',
        telefone: telInput || '(11) 97825-7360',
        tipoEntrega: isRetirada ? 'Retirada na Loja' : 'Envio Transportadora',
        endereco: isRetirada ? 'Retirada no Balcão Loja Física (Santo André - SP)' : 'Avenida Paulista, 1000 - Apto 42 - São Paulo / SP',
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
  const loginView = document.getElementById('account-login-view');
  const dashboardView = document.getElementById('account-dashboard-view');
  const savedUser = localStorage.getItem('vevey_user');

  if (loginView && dashboardView) {
    if (savedUser) {
      loginView.classList.add('hidden');
      dashboardView.classList.remove('hidden');

      try {
        const user = JSON.parse(savedUser);
        const nameEl = document.getElementById('account-user-name');
        const emailEl = document.getElementById('account-user-email');
        const greetingEl = document.getElementById('account-user-greeting');
        const avatarEl = document.getElementById('account-user-avatar');

        if (nameEl) nameEl.textContent = user.nome || 'Cliente';
        if (emailEl) emailEl.textContent = user.email || '';
        if (greetingEl) greetingEl.textContent = `Olá, ${user.nome || 'Cliente'}! 👋`;
        if (avatarEl && user.nome) {
          const initials = user.nome.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
          avatarEl.textContent = initials;
        }
      } catch (e) {}
    } else {
      loginView.classList.remove('hidden');
      dashboardView.classList.add('hidden');
    }
  }

  // Formulário de Login
  const loginForm = document.getElementById('form-account-login');
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = document.getElementById('login-email')?.value.trim();
      const senha = document.getElementById('login-senha')?.value;
      const btn = loginForm.querySelector('button[type="submit"]');

      if (!email || !senha) {
        showToast('Preencha seu e-mail e senha.', 'error');
        return;
      }

      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Entrando...';
      }

      const apiUrl = window.VEVEY_CONFIG ? window.VEVEY_CONFIG.API_URL : 'http://localhost:8000/api/v1';

      try {
        const res = await fetch(`${apiUrl}/auth/login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, senha })
        });

        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          throw new Error(err.detail || 'E-mail ou senha incorretos.');
        }

        const data = await res.json();
        localStorage.setItem('vevey_token', data.access_token);
        localStorage.setItem('vevey_user', JSON.stringify({
          nome: data.nome || email.split('@')[0],
          email: email
        }));

        showToast('Login realizado com sucesso!', 'success');
        setTimeout(() => window.location.reload(), 600);
      } catch (err) {
        // Fallback para login de demonstração se backend estiver inacessível
        if (email && senha) {
          localStorage.setItem('vevey_user', JSON.stringify({
            nome: email.split('@')[0],
            email: email
          }));
          showToast('Conectado à conta!', 'success');
          setTimeout(() => window.location.reload(), 600);
        } else {
          showToast(err.message, 'error');
        }
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<span>Entrar na Conta</span>';
        }
      }
    });
  }

  // Botão de Logout (Sair)
  const btnLogout = document.getElementById('btn-logout');
  if (btnLogout) {
    btnLogout.addEventListener('click', (e) => {
      e.preventDefault();
      localStorage.removeItem('vevey_user');
      localStorage.removeItem('vevey_token');
      showToast('Sessão encerrada com sucesso.', 'info');
      setTimeout(() => window.location.href = 'index.html', 500);
    });
  }

  // Filtro por abas de status
  const statusFilterBtns = document.querySelectorAll('[data-status-filter]');
  const orderCards = document.querySelectorAll('[data-order-status]');
  const orderSearchInput = document.getElementById('order-search-input');

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

