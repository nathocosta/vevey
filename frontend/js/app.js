/**
 * VEVEY DOCES — SCRIPT PRINCIPAL DOS PROTÓTIPOS
 * Gerenciamento centralizado de Carrinho, Filtros, Abas e Interatividade
 */

window.VEVEY_CONFIG = {
  API_URL: (!window.location.hostname || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.protocol === 'file:')
    ? 'http://localhost:8000/api/v1'
    : 'https://vevey-api.onrender.com/api/v1'
};

function getApiUrl() {
  return window.VEVEY_CONFIG.API_URL;
}

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

function getVeveyProductImage(id, category = '') {
  const normId = String(id || '').toLowerCase();
  if (typeof VEVEY_PRODUCT_IMAGES !== 'undefined' && VEVEY_PRODUCT_IMAGES[normId]) {
    return VEVEY_PRODUCT_IMAGES[normId];
  }
  const cat = String(category || '').toLowerCase();
  if (cat.includes('bal')) return 'assets/images/prod-baloes.webp';
  if (cat.includes('confeit') || cat.includes('doce')) return 'assets/images/cat-confeitaria.webp';
  if (cat.includes('choc')) return 'assets/images/prod-chocolate.webp';
  if (cat.includes('decor')) return 'assets/images/prod-bubble.webp';
  return 'assets/images/prod-baloes.webp';
}

function addToCart(product, qty = 1) {
  const cart = getCart();
  const existing = cart.find(item => item.id === product.id);
  const img = product.image || getVeveyProductImage(product.id, product.category);
  if (existing) {
    existing.quantity += qty;
    if (!existing.image) existing.image = img;
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      price: Number(product.price) || 0,
      quantity: qty,
      category: product.category || 'Confeitaria & Decoração',
      image: img
    });
  }
  saveCart(cart);
  showToast(`"${product.name}" adicionado à sacola!`, 'success');
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
  'bl-9921': 'assets/images/prod-baloes.webp',
  'bb-1024': 'assets/images/prod-bubble.webp',
  'cf-4402': 'assets/images/prod-bicos.webp',
  'pa-1029': 'assets/images/cat-confeitaria.webp',
  'fd-5510': 'assets/images/cat-doces.webp',
  'to-3310': 'assets/images/hero-vevey.webp',
  'in-8801': 'assets/images/cat-baloes.webp',
  'ch-7720': 'assets/images/prod-chocolate.webp'
};

async function initHomePage() {
  const homeGrid = document.getElementById('home-featured-products');
  if (!homeGrid) return;

  const apiUrl = getApiUrl();

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
        const imgUrl = p.imagem_url || VEVEY_PRODUCT_IMAGES[slugKey] || 'assets/images/cat-baloes.webp';
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

  const apiUrl = getApiUrl();

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
          const imgUrl = p.imagem_url || VEVEY_PRODUCT_IMAGES[slugKey] || 'assets/images/cat-baloes.webp';
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
        const apiUrl = getApiUrl();
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
          const descEl = document.getElementById('product-detail-description') || document.querySelector('.product-description');
          const stockEl = document.getElementById('product-detail-stock');
          const slugKey = (matched.slug || '').toLowerCase();
          const imgUrl = matched.imagem_url || VEVEY_PRODUCT_IMAGES[slugKey] || 'assets/images/cat-baloes.webp';
          const preco = Number(matched.preco_promocional || matched.preco);

          if (titleEl) titleEl.textContent = matched.nome;
          document.title = `${matched.nome} — Vevey Doces`;
          if (skuEl) skuEl.textContent = `Cód: ${(matched.slug || matched.id).toUpperCase()}`;
          if (priceEl) priceEl.textContent = `R$ ${preco.toFixed(2).replace('.', ',')}`;
          if (descEl && matched.descricao) descEl.textContent = matched.descricao;
          if (stockEl) stockEl.textContent = `Estoque disponível: ${matched.estoque} unidades`;

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
        p.classList.remove('border-[#800F2F]', 'bg-[#800F2F]/10', 'text-[#800F2F]', 'ring-1', 'ring-[#800F2F]');
        p.classList.add('border-[#EAE3D9]', 'text-[#665A5B]');
      });
      pill.classList.remove('border-[#EAE3D9]', 'text-[#665A5B]');
      pill.classList.add('border-[#800F2F]', 'bg-[#800F2F]/10', 'text-[#800F2F]', 'ring-1', 'ring-[#800F2F]');
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
        b.classList.remove('border-[#800F2F]', 'text-[#800F2F]', 'font-bold');
        b.classList.add('border-transparent', 'text-[#665A5B]');
      });
      btn.classList.add('border-[#800F2F]', 'text-[#800F2F]', 'font-bold');
      btn.classList.remove('border-transparent', 'text-[#665A5B]');

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
let appliedShippingMethod = 'calc'; // 'retirada', 'sedex', 'calc'
let appliedShippingCost = 0;

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
        localStorage.setItem('vevey_discount_coupon', 'VEVEY10');
        showToast('Cupom VEVEY10 aplicado: 10% de desconto!', 'success');
        updateCartSummary();
      } else if (code === '') {
        showToast('Digite o código do cupom.', 'error');
      } else {
        showToast('Cupom inválido ou expirado.', 'error');
      }
    });
  }

  // Se já havia cupom salvo
  if (localStorage.getItem('vevey_discount_coupon') === 'VEVEY10') {
    appliedDiscountPercent = 0.10;
    if (inputCoupon) inputCoupon.value = 'VEVEY10';
    updateCartSummary();
  }
}

function renderCartItems() {
  const cartContainer = document.getElementById('cart-items-container');
  if (!cartContainer) return;

  const cart = getCart();

  if (cart.length === 0) {
    cartContainer.innerHTML = `
      <div class="text-center py-14 px-6 bg-white rounded-3xl border border-[#EAE3D9] shadow-sm space-y-4">
        <div class="w-20 h-20 mx-auto rounded-full bg-[#FAF7F2] border border-[#EAE3D9] flex items-center justify-center text-[#800F2F]">
          <span class="material-symbols-outlined text-4xl">shopping_bag</span>
        </div>
        <div class="space-y-1">
          <h3 class="font-serif text-2xl font-bold text-[#201516]">Sua sacola está vazia</h3>
          <p class="text-xs text-[#665A5B] max-w-sm mx-auto">Explore nossas criações de confeitaria artística, balões metalizados e decorações para celebrar com elegância.</p>
        </div>
        <div class="pt-2">
          <a href="catalogo.html" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#800F2F] hover:bg-[#5E0921] text-white font-sans font-bold text-xs shadow-md hover:shadow-lg transition-all">
            <span class="material-symbols-outlined text-[16px]">storefront</span>
            <span>Explorar Catálogo Completo</span>
          </a>
        </div>
      </div>
    `;
    const btnProceed = document.getElementById('btn-proceed-checkout');
    if (btnProceed) {
      btnProceed.classList.add('opacity-50', 'pointer-events-none');
    }
    updateCartSummary();
    return;
  }

  const btnProceed = document.getElementById('btn-proceed-checkout');
  if (btnProceed) {
    btnProceed.classList.remove('opacity-50', 'pointer-events-none');
  }

  cartContainer.innerHTML = cart.map(item => {
    const imgSrc = item.image || getVeveyProductImage(item.id, item.category);
    const itemTotal = (item.price * item.quantity).toFixed(2).replace('.', ',');
    const unitPrice = item.price.toFixed(2).replace('.', ',');

    return `
      <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-5 bg-white border border-[#EAE3D9] rounded-2xl gap-4 hover:border-[#800F2F]/30 transition-all shadow-sm" data-item-id="${item.id}">
        <div class="flex items-center gap-4 min-w-0">
          <img src="${imgSrc}" alt="${item.name}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover border border-[#EAE3D9] shrink-0 bg-[#FAF7F2]" onerror="this.src='assets/images/cat-confeitaria.webp'">
          <div class="min-w-0">
            <span class="text-[10px] font-sans font-bold uppercase tracking-wider text-[#B88636] block">${item.category || 'Confeitaria & Decoração'}</span>
            <h4 class="font-serif font-bold text-sm sm:text-base text-[#201516] truncate">${item.name}</h4>
            <p class="text-xs font-mono font-semibold text-[#800F2F] mt-1">R$ ${unitPrice} <span class="text-[11px] text-[#665A5B] font-sans font-normal">/ unid.</span></p>
          </div>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-6 pt-3 sm:pt-0 border-t sm:border-t-0 border-[#F4EFEB]">
          <!-- Quantidade -->
          <div class="flex items-center border border-[#EAE3D9] rounded-xl bg-[#FAF7F2] overflow-hidden">
            <button class="w-8 h-8 flex items-center justify-center text-[#201516] hover:bg-[#EAE3D9] hover:text-[#800F2F] transition-colors font-bold text-sm" onclick="changeItemQty('${item.id}', -1)" aria-label="Diminuir quantidade">−</button>
            <span class="w-8 text-center text-xs font-bold text-[#201516] font-mono">${item.quantity}</span>
            <button class="w-8 h-8 flex items-center justify-center text-[#201516] hover:bg-[#EAE3D9] hover:text-[#800F2F] transition-colors font-bold text-sm" onclick="changeItemQty('${item.id}', 1)" aria-label="Aumentar quantidade">+</button>
          </div>

          <!-- Subtotal -->
          <div class="text-right min-w-[90px]">
            <span class="text-[10px] uppercase font-sans font-semibold text-[#665A5B] block">Subtotal</span>
            <span class="text-sm sm:text-base font-serif font-bold text-[#800F2F]">R$ ${itemTotal}</span>
          </div>

          <!-- Botão Remover -->
          <button class="w-8 h-8 rounded-full hover:bg-rose-50 text-[#665A5B] hover:text-rose-700 flex items-center justify-center transition-colors" onclick="removeCartItem('${item.id}')" title="Remover item da sacola">
            <span class="material-symbols-outlined text-[18px]">delete_outline</span>
          </button>
        </div>
      </div>
    `;
  }).join('');

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
  showToast('Item removido da sacola.', 'info');
}

window.clearEntireCart = function() {
  if (confirm('Tem certeza que deseja esvaziar sua sacola de compras?')) {
    saveCart([]);
    renderCartItems();
    showToast('Sacola esvaziada com sucesso.', 'info');
  }
};

window.calculateCartShipping = function() {
  const cepInput = document.getElementById('cart-cep-input');
  const resultDiv = document.getElementById('cart-frete-result');
  if (!cepInput || !resultDiv) return;

  const cep = cepInput.value.replace(/\D/g, '');
  if (cep.length !== 8) {
    showToast('Digite um CEP válido com 8 dígitos.', 'error');
    return;
  }

  const cart = getCart();
  const subtotal = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
  const sedexPrice = subtotal >= 199 ? 0 : 15.00;

  resultDiv.classList.remove('hidden');
  resultDiv.innerHTML = `
    <div class="space-y-2 pt-2">
      <label class="flex items-center justify-between p-3 rounded-xl bg-[#FAF7F2] border border-[#B88636]/40 cursor-pointer hover:bg-white transition-colors">
        <div class="flex items-center gap-2.5">
          <input type="radio" name="cart_shipping_option" value="retirada" onchange="selectCartShipping('retirada', 0)" class="accent-[#800F2F]">
          <div>
            <span class="font-sans font-bold text-xs text-[#201516] block">Retirada na Loja Física (Santo André - SP)</span>
            <span class="text-[11px] text-[#665A5B]">Pronto em 2h úteis • Seg a Sex 08h-18h | Sáb 08h-16h</span>
          </div>
        </div>
        <span class="font-sans font-bold text-xs text-emerald-700 uppercase">Grátis</span>
      </label>

      <label class="flex items-center justify-between p-3 rounded-xl bg-white border border-[#EAE3D9] cursor-pointer hover:bg-[#FAF7F2] transition-colors">
        <div class="flex items-center gap-2.5">
          <input type="radio" name="cart_shipping_option" value="sedex" onchange="selectCartShipping('sedex', ${sedexPrice})" class="accent-[#800F2F]">
          <div>
            <span class="font-sans font-bold text-xs text-[#201516] block">Sedex Express (Correios)</span>
            <span class="text-[11px] text-[#665A5B]">Entrega em 2 a 4 dias úteis no seu endereço</span>
          </div>
        </div>
        <span class="font-sans font-bold text-xs text-[#201516]">${sedexPrice === 0 ? '<span class="text-emerald-700 uppercase">Grátis</span>' : `R$ ${sedexPrice.toFixed(2).replace('.', ',')}`}</span>
      </label>
    </div>
  `;
  showToast('Opções de frete e prazos calculadas!', 'success');
};

window.selectCartShipping = function(method, cost) {
  appliedShippingMethod = method;
  appliedShippingCost = cost;
  localStorage.setItem('vevey_selected_shipping', JSON.stringify({ method, cost }));
  updateCartSummary();
};

function updateCartSummary() {
  const cart = getCart();
  const subtotal = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
  const discount = subtotal * appliedDiscountPercent;

  let shipping = 0;
  if (cart.length > 0) {
    if (appliedShippingMethod === 'retirada') {
      shipping = 0;
    } else if (appliedShippingMethod === 'sedex') {
      shipping = appliedShippingCost;
    } else {
      shipping = subtotal >= 199 ? 0 : (subtotal > 0 ? 15.00 : 0);
    }
  }

  const total = Math.max(0, subtotal - discount + shipping);

  const subtotalEl = document.getElementById('summary-subtotal');
  const discountRow = document.getElementById('summary-discount-row');
  const discountEl = document.getElementById('summary-discount');
  const shippingEl = document.getElementById('summary-shipping');
  const totalEl = document.getElementById('summary-total');

  if (subtotalEl) subtotalEl.textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;

  if (discountRow && discountEl) {
    if (discount > 0) {
      discountRow.classList.remove('hidden');
      discountEl.textContent = `- R$ ${discount.toFixed(2).replace('.', ',')}`;
    } else {
      discountRow.classList.add('hidden');
    }
  }

  if (shippingEl) {
    if (cart.length === 0) {
      shippingEl.textContent = 'R$ 0,00';
    } else if (appliedShippingMethod === 'retirada') {
      shippingEl.textContent = 'Retirada Grátis';
    } else if (shipping === 0) {
      shippingEl.textContent = 'Grátis';
    } else {
      shippingEl.textContent = `R$ ${shipping.toFixed(2).replace('.', ',')}`;
    }
  }

  if (totalEl) totalEl.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

/* ==========================================================================
   7. TELA DE CHECKOUT (checkout.html)
   ========================================================================== */

function initCheckoutPage() {
  const checkoutSummaryContainer = document.getElementById('checkout-items-summary');
  const checkoutForm = document.getElementById('checkout-form');
  if (!checkoutSummaryContainer && !checkoutForm) return;

  const cart = getCart();

  // Se a sacola estiver vazia, avisa e desativa
  if (cart.length === 0 && checkoutSummaryContainer) {
    checkoutSummaryContainer.innerHTML = `
      <div class="text-center py-8 text-[#665A5B]">
        <p class="font-sans text-sm mb-2">Sua sacola está vazia.</p>
        <a href="catalogo.html" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#800F2F] text-white text-xs font-bold hover:bg-[#5E0921] transition-colors">
          <span class="material-symbols-outlined text-[16px]">storefront</span>
          <span>Ir às Compras</span>
        </a>
      </div>
    `;
  } else if (checkoutSummaryContainer) {
    // Renderiza itens reais da sacola no resumo lateral
    checkoutSummaryContainer.innerHTML = cart.map(item => {
      const imgSrc = item.image || getVeveyProductImage(item.id, item.category);
      const totalItem = (item.price * item.quantity).toFixed(2).replace('.', ',');
      return `
        <div class="flex items-center gap-3 py-3 border-b border-[#F4EFEB] last:border-0">
          <div class="relative w-14 h-14 rounded-xl overflow-hidden bg-[#FAF7F2] border border-[#EAE3D9] shrink-0">
            <img src="${imgSrc}" alt="${item.name}" class="w-full h-full object-cover" onerror="this.src='assets/images/cat-confeitaria.webp'">
            <span class="absolute top-0 right-0 bg-[#800F2F] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-bl-lg font-mono">${item.quantity}x</span>
          </div>
          <div class="flex-1 min-w-0">
            <h4 class="font-serif font-bold text-xs text-[#201516] truncate">${item.name}</h4>
            <span class="text-[11px] text-[#665A5B]">R$ ${item.price.toFixed(2).replace('.', ',')} cada</span>
          </div>
          <span class="font-serif font-bold text-xs text-[#800F2F] shrink-0">R$ ${totalItem}</span>
        </div>
      `;
    }).join('');
  }

  // Abas de Pagamento
  let selectedPaymentMethod = 'pix';
  const paymentTabs = document.querySelectorAll('[data-payment-tab]');
  paymentTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const method = tab.getAttribute('data-payment-tab');
      selectedPaymentMethod = method;

      paymentTabs.forEach(t => {
        t.classList.remove('border-[#800F2F]', 'bg-[#800F2F]/10', 'text-[#800F2F]', 'font-bold');
        t.classList.add('border-[#EAE3D9]', 'text-[#665A5B]', 'bg-white');
      });
      tab.classList.add('border-[#800F2F]', 'bg-[#800F2F]/10', 'text-[#800F2F]', 'font-bold');
      tab.classList.remove('border-[#EAE3D9]', 'text-[#665A5B]', 'bg-white');

      document.querySelectorAll('.payment-pane').forEach(p => p.classList.add('hidden'));
      const activePane = document.getElementById(`pane-${method}`);
      if (activePane) activePane.classList.remove('hidden');

      recalcCheckoutTotals();
    });
  });

  // Modalidade de Frete
  const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
  const addressFieldsContainer = document.getElementById('checkout-address-section');
  const pickupNotice = document.getElementById('checkout-pickup-notice');

  function updateShippingUI() {
    const selected = document.querySelector('input[name="shipping_method"]:checked')?.value || 'retirada';
    if (selected === 'retirada') {
      if (addressFieldsContainer) addressFieldsContainer.classList.add('opacity-40', 'pointer-events-none');
      if (pickupNotice) pickupNotice.classList.remove('hidden');
    } else {
      if (addressFieldsContainer) addressFieldsContainer.classList.remove('opacity-40', 'pointer-events-none');
      if (pickupNotice) pickupNotice.classList.add('hidden');
    }
    recalcCheckoutTotals();
  }

  shippingRadios.forEach(r => r.addEventListener('change', updateShippingUI));
  updateShippingUI();

  function recalcCheckoutTotals() {
    const subtotal = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
    const shippingMethod = document.querySelector('input[name="shipping_method"]:checked')?.value || 'retirada';
    
    // Frete
    let shipping = 0;
    if (shippingMethod === 'sedex') {
      shipping = subtotal >= 199 ? 0 : 15.00;
    }

    // Desconto de cupom se ativo
    const couponSaved = localStorage.getItem('vevey_discount_coupon') === 'VEVEY10';
    const couponDiscount = couponSaved ? subtotal * 0.10 : 0;

    // Desconto Pix (5% extra à vista)
    const pixDiscount = (selectedPaymentMethod === 'pix' && subtotal > 0) ? (subtotal - couponDiscount) * 0.05 : 0;

    const total = Math.max(0, subtotal - couponDiscount - pixDiscount + shipping);

    const subtotalEl = document.getElementById('checkout-subtotal');
    const shippingEl = document.getElementById('checkout-shipping');
    const pixDiscountRow = document.getElementById('checkout-pix-discount-row');
    const pixDiscountEl = document.getElementById('checkout-pix-discount');
    const couponDiscountRow = document.getElementById('checkout-coupon-discount-row');
    const couponDiscountEl = document.getElementById('checkout-coupon-discount');
    const totalEl = document.getElementById('checkout-total');
    const submitBtnTotal = document.getElementById('checkout-submit-btn-total');

    if (subtotalEl) subtotalEl.textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;

    if (shippingEl) {
      if (shippingMethod === 'retirada') {
        shippingEl.textContent = 'Retirada Grátis';
      } else if (shipping === 0) {
        shippingEl.textContent = 'Grátis (Acima de R$ 199)';
      } else {
        shippingEl.textContent = `R$ ${shipping.toFixed(2).replace('.', ',')}`;
      }
    }

    if (couponDiscountRow && couponDiscountEl) {
      if (couponDiscount > 0) {
        couponDiscountRow.classList.remove('hidden');
        couponDiscountEl.textContent = `- R$ ${couponDiscount.toFixed(2).replace('.', ',')}`;
      } else {
        couponDiscountRow.classList.add('hidden');
      }
    }

    if (pixDiscountRow && pixDiscountEl) {
      if (pixDiscount > 0) {
        pixDiscountRow.classList.remove('hidden');
        pixDiscountEl.textContent = `- R$ ${pixDiscount.toFixed(2).replace('.', ',')}`;
      } else {
        pixDiscountRow.classList.add('hidden');
      }
    }

    const formattedTotal = `R$ ${total.toFixed(2).replace('.', ',')}`;
    if (totalEl) totalEl.textContent = formattedTotal;
    if (submitBtnTotal) submitBtnTotal.textContent = formattedTotal;

    return { subtotal, shipping, couponDiscount, pixDiscount, total };
  }

  // Submissão do Pedido
  if (checkoutForm) {
    checkoutForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (cart.length === 0) {
        showToast('Sua sacola está vazia. Adicione itens antes de finalizar.', 'error');
        return;
      }

      const submitBtn = document.getElementById('btn-submit-order');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-wait');
      }

      const nomeInput = document.getElementById('checkout-nome')?.value?.trim();
      const emailInput = document.getElementById('checkout-email')?.value?.trim();
      const telInput = document.getElementById('checkout-telefone')?.value?.trim();
      const cpfInput = document.getElementById('checkout-cpf')?.value?.trim();
      const selectedShipping = document.querySelector('input[name="shipping_method"]:checked')?.value || 'retirada';
      const isRetirada = selectedShipping === 'retirada';

      const enderecoRua = document.getElementById('checkout-rua')?.value?.trim() || 'Avenida Principal';
      const enderecoNum = document.getElementById('checkout-num')?.value?.trim() || 'S/N';
      const enderecoBairro = document.getElementById('checkout-bairro')?.value?.trim() || 'Centro';
      const enderecoCidade = document.getElementById('checkout-cidade')?.value?.trim() || 'Santo André';
      const enderecoUF = document.getElementById('checkout-uf')?.value?.trim() || 'SP';
      const enderecoCEP = document.getElementById('checkout-cep')?.value?.trim() || '09010160';

      const enderecoFinal = isRetirada
        ? 'Retirada no Balcão Loja Física (Santo André - SP) — R. Sen. Flaquer, 691, Centro, CEP 09010-160'
        : `${enderecoRua}, ${enderecoNum} - ${enderecoBairro}, ${enderecoCidade} - ${enderecoUF}, CEP ${enderecoCEP}`;

      const { total, shipping } = recalcCheckoutTotals();
      let orderId = '#VV-2026-' + Math.floor(1000 + Math.random() * 9000);
      let pixCopiaCola = '00020126580014br.gov.bcb.pix0136vevey-pix-santoandre-oficial520400005303986540' + total.toFixed(2).replace('.', '') + '5802BR5911Vevey Doces6011Santo Andre62070503***6304';
      let backendSuccess = false;

      // Monta payload oficial para a API do backend
      const payloadApi = {
        cliente_nome: nomeInput || 'Cliente Vevey',
        cliente_email: emailInput || 'cliente@veveydoces.com.br',
        cliente_telefone: telInput || '(11) 97825-7360',
        cliente_cpf: (cpfInput || '00000000000').replace(/\D/g, '') || '00000000000',
        endereco_rua: enderecoRua,
        endereco_numero: enderecoNum,
        endereco_bairro: enderecoBairro,
        endereco_cidade: enderecoCidade,
        endereco_uf: enderecoUF,
        endereco_cep: enderecoCEP.replace(/\D/g, '') || '09010160',
        tipo_entrega: isRetirada ? 'RETIRADA_LOJA' : 'ENVIO_TRANSPORTADORA',
        metodo_pagamento: selectedPaymentMethod === 'pix' ? 'PIX' : (selectedPaymentMethod === 'card' ? 'CARTAO_CREDITO' : 'BOLETO'),
        valor_frete: shipping,
        itens: cart.map(i => ({
          produto_id: Number(i.id) || 1,
          quantidade: Number(i.quantity) || 1
        }))
      };

      try {
        const resp = await fetch(`${getApiUrl()}/pedidos/checkout`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payloadApi)
        });

        if (resp.ok) {
          const dadosBackend = await resp.json();
          orderId = dadosBackend.numero_pedido || orderId;
          if (dadosBackend.pix_copia_cola) {
            pixCopiaCola = dadosBackend.pix_copia_cola;
          }
          backendSuccess = true;
        }
      } catch (err) {
        console.warn('Backend offline ou indisponível. Utilizando fallback local:', err);
      }

      const orderData = {
        orderId,
        date: new Date().toLocaleDateString('pt-BR'),
        cliente: nomeInput || 'Cliente Vevey',
        email: emailInput || 'cliente@veveydoces.com.br',
        telefone: telInput || '(11) 97825-7360',
        cpf: cpfInput || '000.000.000-00',
        formaPagamento: selectedPaymentMethod === 'pix' ? 'Pix à Vista (5% OFF)' : (selectedPaymentMethod === 'card' ? 'Cartão de Crédito' : 'Boleto Bancário'),
        tipoEntrega: isRetirada ? 'Retirada na Loja' : 'Envio Transportadora',
        endereco: enderecoFinal,
        items: cart,
        total: total,
        pixCopiaCola: pixCopiaCola,
        backendPersisted: backendSuccess
      };

      // Salva último pedido para confirmação e sincronização com o Admin
      localStorage.setItem('vevey_last_order', JSON.stringify(orderData));
      
      // Limpa carrinho após fechar pedido
      localStorage.setItem('vevey_cart', JSON.stringify([]));
      updateCartBadge();

      showToast(backendSuccess ? 'Pedido registrado com sucesso no banco de dados!' : 'Pedido gerado com sucesso!', 'success');
      setTimeout(() => {
        window.location.href = 'confirmacao.html';
      }, 350);
    });
  }
}

/* ==========================================================================
   8. TELA DE CONFIRMAÇÃO (confirmacao.html)
   ========================================================================== */

function initConfirmationPage() {
  const lastOrder = JSON.parse(localStorage.getItem('vevey_last_order') || 'null');

  // Elementos da tela
  const orderIdEl = document.getElementById('orderNumber');
  const orderDateEl = document.getElementById('orderDate');
  const orderClientEl = document.getElementById('orderCustomer');
  const deliveryTypeEl = document.getElementById('orderDeliveryType');
  const deliveryAddressEl = document.getElementById('orderDeliveryAddress');
  const deliveryHoursEl = document.getElementById('orderDeliveryHours');
  const totalPixEl = document.getElementById('orderTotalPix');
  const summaryTotalEl = document.getElementById('orderSummaryTotal');
  const itemsContainer = document.getElementById('confirmation-items-list');
  const pixKeyInput = document.getElementById('pixCodeInput');
  const copyPixBtn = document.getElementById('copyPixBtn');
  const btnWhatsapp = document.getElementById('btn-order-whatsapp');

  if (lastOrder) {
    if (orderIdEl) orderIdEl.textContent = lastOrder.orderId;
    if (orderDateEl) orderDateEl.textContent = lastOrder.date || new Date().toLocaleDateString('pt-BR');
    if (orderClientEl) orderClientEl.textContent = lastOrder.cliente;

    const formattedTotal = `R$ ${Number(lastOrder.total || 0).toFixed(2).replace('.', ',')}`;
    if (totalPixEl) totalPixEl.textContent = formattedTotal;
    if (summaryTotalEl) summaryTotalEl.textContent = formattedTotal;

    const isRetirada = lastOrder.tipoEntrega && lastOrder.tipoEntrega.includes('Retirada');

    if (deliveryTypeEl) {
      deliveryTypeEl.textContent = isRetirada ? 'Retirada na Loja Física (Santo André - SP)' : 'Envio via Transportadora';
    }

    if (deliveryAddressEl) {
      deliveryAddressEl.textContent = isRetirada
        ? 'R. Sen. Flaquer, 691 - Centro, Santo André - SP, 09010-160'
        : (lastOrder.endereco || 'Endereço cadastrado na finalização');
    }

    if (deliveryHoursEl) {
      if (isRetirada) {
        deliveryHoursEl.innerHTML = '<span class="text-emerald-700 font-bold">Horário de Retirada:</span> Seg a Sex das 08h às 18h | Sáb das 08h às 16h (Apresente documento no balcão)';
        deliveryHoursEl.classList.remove('hidden');
      } else {
        deliveryHoursEl.innerHTML = 'Previsão de Entrega: 2 a 4 dias úteis no seu endereço';
      }
    }

    // Código Pix dinâmico com o valor exato
    if (pixKeyInput) {
      pixKeyInput.value = `00020126580014br.gov.bcb.pix0136veveydoces-pagamentos@banco.com.br5204000053039865406${(lastOrder.total || 0).toFixed(2)}5802BR5915VEVEY DOCES LTDA6009SANTO ANDRE62070503${lastOrder.orderId.replace('#', '')}6304E8A1`;
    }

    // WhatsApp botão de suporte com texto pronto
    if (btnWhatsapp) {
      const msg = encodeURIComponent(`Olá! Acabei de realizar o pedido ${lastOrder.orderId} na Vevey Doces (${isRetirada ? 'Retirada na Loja' : 'Envio'}) no valor de ${formattedTotal} e gostaria de acompanhar.`);
      btnWhatsapp.href = `https://wa.me/5511978257360?text=${msg}`;
    }

    // Renderiza itens dinâmicos do pedido
    if (itemsContainer && Array.isArray(lastOrder.items) && lastOrder.items.length > 0) {
      itemsContainer.innerHTML = lastOrder.items.map(item => {
        const imgSrc = item.image || getVeveyProductImage(item.id, item.category);
        const subtotal = (item.price * item.quantity).toFixed(2).replace('.', ',');
        return `
          <div class="py-3 flex items-center justify-between gap-3 border-b border-[#F4EFEB] last:border-0">
            <div class="flex items-center gap-3 min-w-0">
              <img src="${imgSrc}" alt="${item.name}" class="w-12 h-12 rounded-xl object-cover border border-[#EAE3D9] shrink-0 bg-[#FAF7F2]" onerror="this.src='assets/images/cat-confeitaria.webp'">
              <div class="min-w-0">
                <h4 class="font-serif font-bold text-xs text-[#201516] truncate">${item.name}</h4>
                <span class="text-[11px] text-[#665A5B]">${item.quantity}x de R$ ${item.price.toFixed(2).replace('.', ',')}</span>
              </div>
            </div>
            <span class="font-serif font-bold text-xs text-[#800F2F] shrink-0">R$ ${subtotal}</span>
          </div>
        `;
      }).join('');
    }
  }

  // Copiar código Pix
  if (copyPixBtn && pixKeyInput) {
    copyPixBtn.addEventListener('click', () => {
      pixKeyInput.select();
      navigator.clipboard.writeText(pixKeyInput.value).then(() => {
        showToast('Código Pix copiado para a área de transferência!', 'success');
        copyPixBtn.innerText = 'Copiado!';
        copyPixBtn.classList.add('bg-emerald-700', 'text-white');
        setTimeout(() => {
          copyPixBtn.innerText = 'Copiar';
          copyPixBtn.classList.remove('bg-emerald-700', 'text-white');
        }, 2000);
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

  // Renderização Dinâmica dos Pedidos Reais do Cliente
  async function loadCustomerOrders() {
    const container = document.getElementById('account-orders-container');
    if (!container) return;

    let orders = [];

    // 1. Tenta carregar pedidos do backend via API
    try {
      const token = localStorage.getItem('vevey_token') || localStorage.getItem('vevey_admin_token');
      if (token) {
        const res = await fetch(`${getApiUrl()}/pedidos/`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (res.ok) {
          const apiOrders = await res.json();
          if (Array.isArray(apiOrders)) {
            orders = apiOrders.map(o => ({
              orderId: `#VV-${o.id}`,
              date: new Date(o.created_at || Date.now()).toLocaleDateString('pt-BR'),
              total: Number(o.valor_total || 0),
              status: o.status,
              formaPagamento: o.forma_pagamento || 'Pix à Vista',
              tipoEntrega: (o.status && o.status.includes('Retirada')) ? 'Retirada na Loja' : 'Envio Transportadora',
              endereco: 'R. Sen. Flaquer, 691 - Centro, Santo André - SP',
              items: o.itens || []
            }));
          }
        }
      }
    } catch (e) {
      console.warn('Backend pedidos indisponível, usando armazenamento local:', e);
    }

    // 2. Mescla com pedidos do localStorage (checkout recente)
    const localLast = JSON.parse(localStorage.getItem('vevey_last_order') || 'null');
    const localHistory = JSON.parse(localStorage.getItem('vevey_orders_history') || '[]');

    if (localLast && !orders.some(o => o.orderId === localLast.orderId)) {
      orders.unshift(localLast);
    }

    localHistory.forEach(lh => {
      if (!orders.some(o => o.orderId === lh.orderId)) {
        orders.push(lh);
      }
    });

    // Se não houver nenhum pedido
    if (orders.length === 0) {
      container.innerHTML = `
        <div class="py-16 text-center text-on-surface-variant flex flex-col items-center justify-center gap-3 bg-surface-container-lowest rounded-2xl border border-surface-container">
          <span class="material-symbols-outlined text-5xl text-primary/40">shopping_bag</span>
          <h4 class="font-bold text-base text-on-surface">Você ainda não realizou nenhum pedido</h4>
          <p class="text-xs text-on-surface-variant max-w-sm">Adicione artigos de confeitaria e balões ao seu carrinho e finalize seu primeiro pedido.</p>
          <a href="catalogo.html" class="mt-2 px-6 py-2.5 rounded-full bg-primary hover:bg-primary-container text-white font-bold text-xs shadow-sm transition-all">
            Explorar Catálogo da Loja
          </a>
        </div>
      `;
      return;
    }

    // Renderiza cada pedido real
    container.innerHTML = orders.map((o, idx) => {
      const isRetirada = o.tipoEntrega && o.tipoEntrega.includes('Retirada');
      const statusRaw = (o.status || 'Em Separação no Estoque');
      const isPronto = statusRaw.includes('Pronto') || statusRaw.includes('Enviado');
      const isEntregue = statusRaw.includes('Entregue');

      const statusBadge = isEntregue
        ? `<span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200"><span class="material-symbols-outlined text-[14px]">done_all</span> Entregue</span>`
        : isPronto
        ? `<span class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200"><span class="material-symbols-outlined text-[14px]">local_shipping</span> ${statusRaw}</span>`
        : `<span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200"><span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> ${statusRaw}</span>`;

      return `
        <article data-order-status="${isEntregue ? 'entregue' : 'andamento'}" class="bg-surface-container-lowest rounded-2xl border ${idx === 0 ? 'border-2 border-primary/40 shadow-md' : 'border-surface-container shadow-sm'} p-space-md md:p-space-lg space-y-space-md relative overflow-hidden">
          ${idx === 0 ? `<div class="absolute top-0 right-0 bg-primary text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl uppercase tracking-wider">Último Pedido</div>` : ''}

          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-surface-container pb-space-sm pt-1">
            <div>
              <span class="text-xs text-on-surface-variant font-mono">Pedido:</span>
              <h3 class="order-id font-title-md text-base font-extrabold text-primary">${o.orderId}</h3>
              <p class="text-xs text-on-surface-variant">Realizado em ${o.date} • Pagamento via <strong>${o.formaPagamento || 'Pix'}</strong></p>
            </div>
            <div class="text-right">
              <span class="text-xs text-on-surface-variant">Valor Total:</span>
              <p class="font-headline-sm text-lg font-black text-on-surface">R$ ${Number(o.total || 0).toFixed(2).replace('.', ',')}</p>
              ${statusBadge}
            </div>
          </div>

          <div class="bg-surface-container-low p-3 rounded-xl flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">${isRetirada ? 'storefront' : 'pin_drop'}</span>
              <div>
                <span class="font-bold text-on-surface">${isRetirada ? 'Retirada na Loja Física (Santo André - SP):' : 'Endereço de Entrega:'}</span>
                <p class="text-on-surface-variant">${o.endereco || 'R. Sen. Flaquer, 691 - Centro, Santo André - SP, 09010-160'}</p>
              </div>
            </div>
            <a href="https://wa.me/5511978257360?text=${encodeURIComponent('Olá! Gostaria de consultar o status do meu pedido ' + o.orderId)}" target="_blank" rel="noopener noreferrer" class="bg-[#25D366] hover:bg-[#1EBE5D] text-white px-3 py-1.5 rounded-full font-bold text-[11px] shadow-sm flex items-center gap-1 transition-colors">
              <span class="material-symbols-outlined text-[14px]">chat</span>
              <span>Acompanhar no WhatsApp</span>
            </a>
          </div>
        </article>
      `;
    }).join('');

    setupOrderFilterEvents();
  }

  function setupOrderFilterEvents() {
    const statusFilterBtns = document.querySelectorAll('[data-status-filter]');
    const orderCards = document.querySelectorAll('#account-orders-container [data-order-status]');
    const orderSearchInput = document.getElementById('order-search-input');

    statusFilterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const status = btn.getAttribute('data-status-filter');
        statusFilterBtns.forEach(b => {
          b.classList.remove('bg-primary', 'text-white', 'font-bold');
          b.classList.add('border', 'border-surface-container', 'text-on-surface-variant', 'bg-white');
        });
        btn.classList.add('bg-primary', 'text-white', 'font-bold');
        btn.classList.remove('border-surface-container', 'text-on-surface-variant', 'bg-white');

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

    if (orderSearchInput) {
      orderSearchInput.addEventListener('input', () => {
        const term = orderSearchInput.value.toLowerCase().trim();
        orderCards.forEach(card => {
          const idText = card.querySelector('.order-id, h3, p')?.textContent?.toLowerCase() || '';
          if (!term || idText.includes(term)) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    }
  }

  loadCustomerOrders();
}

