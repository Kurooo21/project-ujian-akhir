// ========================================================================
// Chi-Pok - Script Halaman Semua Menu (/menu)
// ========================================================================

const menuProducts = Array.isArray(typeof PRODUCTS_DATA !== 'undefined' ? PRODUCTS_DATA : [])
    ? PRODUCTS_DATA
    : [];

const currentUserData = typeof CURRENT_USER_DATA !== 'undefined' ? CURRENT_USER_DATA : null;
const menuCartStorageKey = 'chipok_cart';

let currentCategory = 'semua';

function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number || 0);
}

function resolveAppUrl(path) {
    if (!path) return path;
    if (/^https?:\/\//i.test(path)) return path;

    const base = (typeof APP_BASE_URL !== 'undefined' && APP_BASE_URL)
        ? String(APP_BASE_URL).replace(/\/+$/, '')
        : '';

    if (!base) return path;

    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
    return base + normalizedPath;
}

function resolveAssetUrl(path) {
    if (!path) return resolveAppUrl('/asset/logo merah.png');
    if (/^https?:\/\//i.test(path)) return path;
    const normalized = String(path).startsWith('/') ? String(path) : `/${path}`;
    return resolveAppUrl(normalized);
}

function getCart() {
    try {
        return JSON.parse(localStorage.getItem(menuCartStorageKey) || '[]');
    } catch (error) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(menuCartStorageKey, JSON.stringify(cart));
}

function getCartCount() {
    return getCart().reduce((sum, item) => sum + Number(item.qty || 0), 0);
}

function updateCartCount() {
    const cartCount = document.getElementById('menu-cart-count');
    if (!cartCount) return;

    if (!currentUserData) {
        cartCount.textContent = '0';
        cartCount.classList.add('hidden');
        return;
    }

    const totalItems = getCartCount();
    if (totalItems > 0) {
        cartCount.textContent = totalItems;
        cartCount.classList.remove('hidden');
    } else {
        cartCount.textContent = '0';
        cartCount.classList.add('hidden');
    }
}

function promptLoginForOrder() {
    if (typeof showLoginPopup === 'function') {
        showLoginPopup();
        return;
    }

    Swal.fire({
        icon: 'info',
        title: 'Login Dulu untuk Pesan',
        text: 'Masuk ke akunmu dulu ya supaya menu yang kamu pilih bisa masuk ke keranjang.',
        showCancelButton: true,
        confirmButtonText: 'Login Sekarang',
        cancelButtonText: 'Nanti Dulu',
        confirmButtonColor: '#D20000',
        cancelButtonColor: '#64748b'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = typeof LOGIN_URL !== 'undefined' ? LOGIN_URL : resolveAppUrl('/login');
        }
    });
}

function showCartToast(productName) {
    Swal.fire({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 1800,
        timerProgressBar: true,
        icon: 'success',
        title: `${productName} berhasil masuk ke keranjang`
    });
}

function addToCart(productId) {
    if (!currentUserData) {
        promptLoginForOrder();
        return;
    }

    const product = menuProducts.find((item) => item.id === productId);
    if (!product) return;

    const cart = getCart();
    const existingItem = cart.find((item) => item.id === productId);

    if (existingItem) {
        existingItem.qty += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            qty: 1
        });
    }

    saveCart(cart);
    updateCartCount();
    showCartToast(product.name);
}

function getFilteredProducts() {
    if (currentCategory === 'semua') return menuProducts;
    return menuProducts.filter((product) => (product.category || 'makanan') === currentCategory);
}

function buildRatingMarkup(product) {
    if (product.reviews && product.reviews.length > 0) {
        const totalReviews = product.reviews.length;
        const sum = product.reviews.reduce((acc, curr) => acc + parseInt(curr.rating, 10), 0);
        const avgRating = (sum / totalReviews).toFixed(1);
        const fullStars = Math.floor(avgRating);
        const halfStar = avgRating % 1 >= 0.5 ? 1 : 0;
        let stars = '';

        for (let i = 0; i < fullStars; i += 1) {
            stars += '<i class="fas fa-star text-yellow-400"></i>';
        }

        if (halfStar) {
            stars += '<i class="fas fa-star-half-alt text-yellow-400"></i>';
        }

        return `
            <div class="rating flex items-center justify-center gap-1 text-sm text-text-dark">
                <span class="flex text-yellow-500">${stars || '<i class="fas fa-star text-yellow-500"></i>'}</span>
                <span class="ml-1 font-bold text-text-dark">${avgRating}</span>
                <span class="text-xs text-gray-400">(${totalReviews})</span>
            </div>
        `;
    }

    return `
        <div class="rating flex items-center justify-center gap-1 text-sm text-gray-400">
            <i class="far fa-star"></i>
            <span class="text-xs">Belum ada ulasan</span>
        </div>
    `;
}

function renderMenuProducts() {
    const menuGrid = document.getElementById('full-menu-grid');
    const productCount = document.getElementById('product-count');
    if (!menuGrid) return;

    const filteredProducts = getFilteredProducts();
    menuGrid.innerHTML = '';

    if (productCount) {
        productCount.innerHTML = `
            <i class="fas fa-bowl-food text-red-500"></i>
            Menampilkan ${filteredProducts.length} menu
        `;
    }

    if (filteredProducts.length === 0) {
        menuGrid.innerHTML = `
            <div class="col-span-full rounded-[32px] border border-dashed border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-red-50 text-red-500">
                    <i class="fas fa-utensils text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Menu belum tersedia</h3>
                <p class="mt-2 text-sm text-slate-500">Kategori ini belum punya pilihan. Coba pindah ke kategori lain ya.</p>
            </div>
        `;
        return;
    }

    filteredProducts.forEach((product, index) => {
        const card = document.createElement('article');
        const category = product.category || 'makanan';
        const isDrink = category === 'minuman';
        const categoryIcon = isDrink ? 'fa-glass-water' : 'fa-drumstick-bite';
        const categoryLabel = isDrink ? 'Minuman' : 'Makanan';
        const badge = product.badge
            ? `<div class="badge absolute top-2 left-2 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10 tracking-wide shadow-sm">${product.badge}</div>`
            : '';

        card.dataset.id = product.id;
        card.className = 'product-card bg-white rounded-2xl p-4 text-center shadow-lg transition-all duration-300 mt-0 hover:-translate-y-2 hover:shadow-xl relative group flex flex-col justify-between overflow-hidden border border-gray-100';
        card.style.opacity = '0';
        card.style.transform = 'translateY(18px)';

        card.innerHTML = `
            <div class="w-full aspect-square bg-gray-50 rounded-xl mb-4 flex items-center justify-center p-4 relative overflow-hidden">
                ${badge}
                <img src="${resolveAssetUrl(product.image)}" alt="${product.name}" class="w-full h-full object-contain drop-shadow transition-transform duration-500 group-hover:scale-110">
            </div>

            <div class="product-info flex flex-col flex-grow">
                <div class="mb-2 flex justify-center">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${isDrink ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600'}">
                        <i class="fas ${categoryIcon} text-[8px]"></i> ${categoryLabel}
                    </span>
                </div>
                <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-2 leading-tight">${product.name}</h3>
                <p class="text-gray-500 text-xs mb-3 line-clamp-2 h-8">${product.desc || 'Menu favorit Chi-Pok siap menemani waktu makanmu.'}</p>
                ${buildRatingMarkup(product)}

                <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-50">
                    <span class="font-extrabold text-lg text-red-600">${formatRupiah(product.price)}</span>
                    <button type="button" data-id="${product.id}"
                        class="menu-cart-button btn-cart w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors shadow-sm"
                        title="${currentUserData ? 'Tambah ke Keranjang' : 'Login untuk pesan'}">
                        <i class="fas fa-cart-plus text-sm"></i>
                    </button>
                </div>
            </div>
        `;

        menuGrid.appendChild(card);

        window.setTimeout(() => {
            card.style.transition = 'all 0.45s cubic-bezier(0.2, 0.8, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 55);
    });

    document.querySelectorAll('.menu-cart-button').forEach((button) => {
        button.addEventListener('click', () => {
            addToCart(Number(button.dataset.id));
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    renderMenuProducts();

    const tabs = document.querySelectorAll('.menu-category-tab');
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            currentCategory = tab.dataset.category || 'semua';

            tabs.forEach((button) => {
                button.classList.remove('bg-primary-red', 'text-white', 'shadow-md', 'shadow-red-200', 'active-tab');
                button.classList.add('border', 'border-slate-200', 'bg-white', 'text-slate-700', 'shadow-sm');
            });

            tab.classList.remove('border', 'border-slate-200', 'bg-white', 'text-slate-700', 'shadow-sm');
            tab.classList.add('bg-primary-red', 'text-white', 'shadow-md', 'shadow-red-200', 'active-tab');

            renderMenuProducts();
        });
    });
});
