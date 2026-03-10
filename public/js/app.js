// ========================================================================
// Chi-Pok App - Laravel Version
// ========================================================================
// Data produk di-pass dari server via Blade (@json)
// Auth & CRUD menggunakan AJAX fetch ke Laravel routes

let products = PRODUCTS_DATA;
let currentUser = null;
let currentCategory = 'semua';
let carouselPage = 0;
const ITEMS_PER_PAGE = 4;

// ========================================================================
// CART SYSTEM
// ========================================================================
let cart = JSON.parse(localStorage.getItem('chipok_cart') || '[]');

function saveCart() {
    localStorage.setItem('chipok_cart', JSON.stringify(cart));
}

function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existing = cart.find(item => item.id === productId);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            qty: 1
        });
    }
    saveCart();
    updateCartBadge();
    showCartNotification(product.name);
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    updateCartBadge();
    renderCartModal();
}

function updateCartQty(productId, newQty) {
    const item = cart.find(i => i.id === productId);
    if (!item) return;
    if (newQty <= 0) {
        removeFromCart(productId);
        return;
    }
    item.qty = newQty;
    saveCart();
    renderCartModal();
}

function getCartTotal() {
    return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
}

function getCartCount() {
    return cart.reduce((sum, item) => sum + item.qty, 0);
}

function clearCart() {
    cart = [];
    saveCart();
    updateCartBadge();
    renderCartModal();
}

function showCartNotification(itemName) {
    // Buat notifikasi kecil di pojok
    const notif = document.createElement('div');
    notif.className = 'fixed bottom-24 right-8 bg-green-600 text-white px-5 py-3 rounded-xl shadow-2xl z-[9999] flex items-center gap-3 animate-bounce';
    notif.innerHTML = `<i class="fas fa-check-circle"></i> <span class="font-bold text-sm">${itemName}</span> ditambahkan ke keranjang!`;
    document.body.appendChild(notif);
    setTimeout(() => {
        notif.style.transition = 'all 0.5s ease';
        notif.style.opacity = '0';
        notif.style.transform = 'translateX(100px)';
        setTimeout(() => notif.remove(), 500);
    }, 2000);
}

// Helper: Format Rupiah
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
}

// Helper: AJAX request
async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    };
    if (data) options.body = JSON.stringify(data);
    const response = await fetch(url, options);
    return response.json();
}

// ========================================================================
// DOM READY
// ========================================================================
document.addEventListener("DOMContentLoaded", () => {
    // --- Elements ---
    const menuGrid = document.getElementById('menu-grid');
    const loginModal = document.getElementById('loginModal');
    const signupModal = document.getElementById('signupModal');
    const cartModal = document.getElementById('cartModal');
    const adminModal = document.getElementById('adminModal');

    const btnLoginHeader = document.getElementById('btn-login');
    const btnCartHeader = document.getElementById('btn-cart-header');
    const btnAdminPanel = document.getElementById('btn-admin-panel');
    const navAdmin = document.getElementById('nav-admin');

    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const checkoutForm = document.getElementById('checkoutForm');
    const addMenuForm = document.getElementById('addMenuForm');

    // Cart elements
    const cartItemsContainer = document.getElementById('cart-items-container');
    const cartTotalDisplay = document.getElementById('cart-total-display');
    const cartBadge = document.getElementById('cart-badge');
    const btnCheckout = document.getElementById('btn-checkout');
    const btnClearCart = document.getElementById('btn-clear-cart');
    const cartCheckoutSection = document.getElementById('cart-checkout-section');
    const cartActionButtons = document.getElementById('cart-action-buttons');
    const btnBackToCart = document.getElementById('btn-back-to-cart');

    // Carousel elements
    const carouselPrev = document.getElementById('carousel-prev');
    const carouselNext = document.getElementById('carousel-next');
    const carouselDots = document.getElementById('carousel-dots');

    // ====================================================================
    // CART BADGE UPDATE
    // ====================================================================
    function updateCartBadge() {
        const count = getCartCount();
        if (count > 0) {
            cartBadge.textContent = count;
            cartBadge.classList.remove('hidden');
            cartBadge.classList.add('flex');
        } else {
            cartBadge.classList.add('hidden');
            cartBadge.classList.remove('flex');
        }
    }
    // Expose globally
    window.updateCartBadge = updateCartBadge;

    // Init badge
    updateCartBadge();

    // ====================================================================
    // CART MODAL RENDERING
    // ====================================================================
    function renderCartModal() {
        if (!cartItemsContainer) return;

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-shopping-basket text-4xl text-gray-300 mb-3 block"></i>
                    <p class="text-gray-400 text-sm">Keranjang belanja kosong</p>
                    <p class="text-gray-300 text-xs mt-1">Yuk tambahkan menu favoritmu!</p>
                </div>`;
            btnCheckout.disabled = true;
            cartTotalDisplay.textContent = formatRupiah(0);
            return;
        }

        btnCheckout.disabled = false;
        cartItemsContainer.innerHTML = '';

        cart.forEach(item => {
            const imgSrc = item.image.startsWith('http') ? item.image : '/' + item.image;
            const subtotal = item.price * item.qty;

            const el = document.createElement('div');
            el.className = 'flex items-center gap-3 bg-gray-50 rounded-xl p-3 border border-gray-100 transition hover:shadow-sm';
            el.innerHTML = `
                <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center p-1 shrink-0 border border-gray-100">
                    <img src="${imgSrc}" alt="${item.name}" class="w-full h-full object-contain">
                </div>
                <div class="flex-grow min-w-0">
                    <h4 class="font-bold text-sm text-gray-800 truncate">${item.name}</h4>
                    <p class="text-xs text-gray-500">${formatRupiah(item.price)}</p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button class="cart-qty-btn w-7 h-7 rounded-full bg-gray-200 hover:bg-red-100 text-gray-600 hover:text-red-600 flex items-center justify-center text-xs font-bold transition" data-id="${item.id}" data-action="minus">
                        <i class="fas fa-minus text-[10px]"></i>
                    </button>
                    <span class="w-8 text-center font-bold text-sm">${item.qty}</span>
                    <button class="cart-qty-btn w-7 h-7 rounded-full bg-gray-200 hover:bg-green-100 text-gray-600 hover:text-green-600 flex items-center justify-center text-xs font-bold transition" data-id="${item.id}" data-action="plus">
                        <i class="fas fa-plus text-[10px]"></i>
                    </button>
                </div>
                <div class="text-right shrink-0 w-24">
                    <p class="font-extrabold text-sm text-red-600">${formatRupiah(subtotal)}</p>
                </div>
                <button class="cart-remove-btn text-gray-300 hover:text-red-500 transition shrink-0" data-id="${item.id}" title="Hapus">
                    <i class="fas fa-times"></i>
                </button>`;
            cartItemsContainer.appendChild(el);
        });

        // Total
        cartTotalDisplay.textContent = formatRupiah(getCartTotal());

        // Attach listeners
        document.querySelectorAll('.cart-qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.dataset.id);
                const action = btn.dataset.action;
                const item = cart.find(i => i.id === id);
                if (!item) return;
                updateCartQty(id, action === 'plus' ? item.qty + 1 : item.qty - 1);
                updateCartBadge();
            });
        });

        document.querySelectorAll('.cart-remove-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                removeFromCart(parseInt(btn.dataset.id));
            });
        });
    }
    // Expose globally
    window.renderCartModal = renderCartModal;

    // ====================================================================
    // CART MODAL OPEN/CLOSE
    // ====================================================================
    btnCartHeader.addEventListener('click', () => {
        // Reset ke tampilan cart (bukan checkout)
        cartCheckoutSection.classList.add('hidden');
        cartActionButtons.classList.remove('hidden');
        renderCartModal();
        cartModal.classList.remove('hidden');
    });

    document.getElementById('closeCartModal').addEventListener('click', () => {
        cartModal.classList.add('hidden');
    });

    btnClearCart.addEventListener('click', () => {
        if (cart.length === 0) return;
        if (confirm('Kosongkan keranjang belanja?')) {
            clearCart();
        }
    });

    // ====================================================================
    // CHECKOUT FLOW
    // ====================================================================
    btnCheckout.addEventListener('click', () => {
        if (!currentUser) {
            alert('Silakan login terlebih dahulu untuk checkout.');
            cartModal.classList.add('hidden');
            loginModal.classList.remove('hidden');
            return;
        }

        // Switch ke checkout view
        cartActionButtons.classList.add('hidden');
        cartCheckoutSection.classList.remove('hidden');

        // Pre-fill data
        document.getElementById('checkout_nama').value = currentUser.name || '';
        document.getElementById('checkout_no_hp').value = currentUser.no_hp || '';

        // Alamat options
        const addressOptions = document.getElementById('address-options');
        const savedAddressPreview = document.getElementById('saved-address-preview');
        const useAddressCheckbox = document.getElementById('use_saved_address');
        const alamatField = document.getElementById('checkout_alamat');

        if (currentUser.alamat) {
            addressOptions.classList.remove('hidden');
            savedAddressPreview.textContent = currentUser.alamat.length > 50
                ? currentUser.alamat.substring(0, 50) + '...'
                : currentUser.alamat;

            // Auto-check and fill
            useAddressCheckbox.checked = true;
            alamatField.value = currentUser.alamat;

            useAddressCheckbox.onchange = () => {
                if (useAddressCheckbox.checked) {
                    alamatField.value = currentUser.alamat;
                } else {
                    alamatField.value = '';
                }
            };
        } else {
            addressOptions.classList.add('hidden');
            alamatField.value = '';
        }
    });

    btnBackToCart.addEventListener('click', () => {
        cartCheckoutSection.classList.add('hidden');
        cartActionButtons.classList.remove('hidden');
    });

    // Submit Checkout
    checkoutForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const data = {
            nama: document.getElementById('checkout_nama').value,
            no_hp: document.getElementById('checkout_no_hp').value,
            alamat: document.getElementById('checkout_alamat').value,
            jenis_belanja: document.getElementById('checkout_jenis').value,
            items: cart.map(item => ({
                pesanan_item: item.name,
                jumlah: item.qty,
                harga_satuan: item.price
            }))
        };

        try {
            const result = await apiRequest('/pesanan', 'POST', data);
            if (result.success) {
                alert(result.message);
                clearCart();
                cartModal.classList.add('hidden');
                checkoutForm.reset();
                cartCheckoutSection.classList.add('hidden');
                cartActionButtons.classList.remove('hidden');
            } else {
                alert(result.message || 'Gagal membuat pesanan.');
            }
        } catch (err) {
            alert('Gagal membuat pesanan. Pastikan semua data terisi.');
        }
    });

    // ====================================================================
    // INIT - Check Auth Status
    // ====================================================================
    async function checkAuth() {
        try {
            const result = await apiRequest('/api/user');
            if (result.logged_in) {
                currentUser = result.user;
            } else {
                currentUser = null;
            }
        } catch (e) {
            currentUser = null;
        }
        updateLoginUI();
    }

    // ====================================================================
    // CATEGORY FILTER & CAROUSEL
    // ====================================================================
    function getFilteredProducts() {
        if (currentCategory === 'semua') return products;
        return products.filter(p => (p.category || 'makanan') === currentCategory);
    }

    function getTotalPages(filtered) {
        return Math.ceil(filtered.length / ITEMS_PER_PAGE);
    }

    function updateCarouselControls(filtered) {
        const totalPages = getTotalPages(filtered);
        const needsCarousel = totalPages > 1;

        if (carouselPrev && carouselNext) {
            if (needsCarousel) {
                carouselPrev.classList.remove('hidden');
                carouselPrev.classList.add('flex');
                carouselNext.classList.remove('hidden');
                carouselNext.classList.add('flex');
                carouselPrev.disabled = carouselPage === 0;
                carouselNext.disabled = carouselPage >= totalPages - 1;
                carouselPrev.style.opacity = carouselPage === 0 ? '0.3' : '1';
                carouselNext.style.opacity = carouselPage >= totalPages - 1 ? '0.3' : '1';
            } else {
                carouselPrev.classList.add('hidden');
                carouselPrev.classList.remove('flex');
                carouselNext.classList.add('hidden');
                carouselNext.classList.remove('flex');
            }
        }

        // Dots
        if (carouselDots) {
            if (needsCarousel) {
                carouselDots.classList.remove('hidden');
                carouselDots.innerHTML = '';
                for (let i = 0; i < totalPages; i++) {
                    const dot = document.createElement('button');
                    dot.className = `w-3 h-3 rounded-full transition-all duration-300 ${i === carouselPage ? 'bg-primary-red scale-125 shadow-md' : 'bg-gray-300 hover:bg-gray-400'}`;
                    dot.addEventListener('click', () => {
                        carouselPage = i;
                        renderProducts();
                    });
                    carouselDots.appendChild(dot);
                }
            } else {
                carouselDots.classList.add('hidden');
            }
        }
    }

    // Category tab click handler
    const categoryTabs = document.querySelectorAll('.category-tab');
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            currentCategory = tab.dataset.category;
            carouselPage = 0;

            // Update tab styles
            categoryTabs.forEach(t => {
                t.classList.remove('bg-primary-red', 'text-white', 'active-tab');
                t.classList.add('bg-white', 'text-text-dark', 'border', 'border-gray-200');
            });
            tab.classList.add('bg-primary-red', 'text-white', 'active-tab');
            tab.classList.remove('bg-white', 'text-text-dark', 'border', 'border-gray-200');

            renderProducts();
        });
    });

    // Carousel navigation
    if (carouselPrev) {
        carouselPrev.addEventListener('click', () => {
            const filtered = getFilteredProducts();
            if (carouselPage > 0) {
                carouselPage--;
                renderProducts();
            }
        });
    }
    if (carouselNext) {
        carouselNext.addEventListener('click', () => {
            const filtered = getFilteredProducts();
            const totalPages = getTotalPages(filtered);
            if (carouselPage < totalPages - 1) {
                carouselPage++;
                renderProducts();
            }
        });
    }

    // ====================================================================
    // PRODUCT RENDERING
    // ====================================================================
    function renderProducts() {
        menuGrid.innerHTML = '';
        const isAdmin = currentUser && currentUser.role === 'admin';
        const layoutMode = localStorage.getItem('menuLayout') || 'grid';

        const filtered = getFilteredProducts();
        const totalPages = getTotalPages(filtered);

        // Clamp carousel page
        if (carouselPage >= totalPages) carouselPage = Math.max(0, totalPages - 1);

        // Get the page slice
        const start = carouselPage * ITEMS_PER_PAGE;
        const pageItems = filtered.slice(start, start + ITEMS_PER_PAGE);

        if (layoutMode === 'grid') {
            menuGrid.className = "menu-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 transition-all duration-500 ease-in-out";
        } else {
            menuGrid.className = "menu-grid flex flex-col gap-6 max-w-4xl mx-auto transition-all duration-500 ease-in-out";
        }

        if (pageItems.length === 0) {
            menuGrid.innerHTML = `
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-utensils text-5xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-400 text-lg">Belum ada menu di kategori ini.</p>
                </div>`;
            updateCarouselControls(filtered);
            return;
        }

        pageItems.forEach((product, index) => {
            const card = document.createElement('div');
            card.dataset.id = product.id;

            // Rating HTML
            let ratingHtml = '';
            let avgRating = 0;
            let totalReviews = 0;

            if (product.reviews && product.reviews.length > 0) {
                totalReviews = product.reviews.length;
                const sum = product.reviews.reduce((acc, curr) => acc + parseInt(curr.rating), 0);
                avgRating = (sum / totalReviews).toFixed(1);

                let stars = '';
                const fullStars = Math.floor(avgRating);
                const halfStar = avgRating % 1 >= 0.5 ? 1 : 0;
                for (let i = 0; i < fullStars; i++) stars += '<i class="fas fa-star text-yellow-500"></i>';
                if (halfStar) stars += '<i class="fas fa-star-half-alt text-yellow-500"></i>';

                ratingHtml = `
                    <div class="rating flex justify-center items-center gap-1 text-sm mt-2 cursor-pointer hover:bg-gray-50 rounded px-2 py-1 transition" onclick="openReviewModal(${product.id})">
                        <div class="flex text-yellow-500">${stars}</div>
                        <span class="font-bold text-text-dark ml-1">${avgRating}</span>
                        <span class="text-gray-400 text-xs">(${totalReviews})</span>
                    </div>`;
            } else {
                ratingHtml = `
                    <div class="rating flex justify-center items-center gap-1 text-sm mt-2 cursor-pointer text-gray-400 hover:text-yellow-600 transition" onclick="openReviewModal(${product.id})">
                        <i class="far fa-star"></i> <span class="text-xs">Beri Ulasan</span>
                    </div>`;
            }

            // Admin Controls
            let adminControls = '';
            if (isAdmin) {
                adminControls = `
                    <div class="absolute top-2 right-2 flex gap-2 z-10 transition-opacity opacity-0 group-hover:opacity-100">
                        <button class="bg-white/80 text-gray-700 p-2 rounded-full hover:bg-white hover:text-yellow-600 transition shadow-sm border border-gray-200" onclick="editProduct(${product.id})" title="Edit"><i class="fas fa-pencil-alt text-xs"></i></button>
                        <button class="bg-white/80 text-gray-700 p-2 rounded-full hover:bg-white hover:text-red-600 transition shadow-sm border border-gray-200" onclick="deleteProduct(${product.id})" title="Hapus"><i class="fas fa-trash text-xs"></i></button>
                    </div>`;
            }

            // Badge
            let badgeHtml = '';
            if (product.badge) {
                badgeHtml = `<div class="badge absolute top-2 left-2 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10 tracking-wide shadow-sm">${product.badge}</div>`;
            }

            // Category badge
            const categoryIcon = (product.category || 'makanan') === 'minuman' ? 'fa-glass-water' : 'fa-drumstick-bite';
            const categoryLabel = (product.category || 'makanan') === 'minuman' ? 'Minuman' : 'Makanan';

            // Image path - use asset path
            const imgSrc = product.image.startsWith('http') ? product.image : '/' + product.image;

            if (layoutMode === 'grid') {
                card.className = "product-card bg-white rounded-2xl p-4 text-center shadow-lg transition-all duration-300 mt-0 hover:-translate-y-2 hover:shadow-xl relative group flex flex-col justify-between overflow-hidden border border-gray-100";
                const imgContainer = `
                    <div class="w-full aspect-square bg-gray-50 rounded-xl mb-4 flex items-center justify-center p-4 relative overflow-hidden">
                        ${badgeHtml}
                        <img src="${imgSrc}" alt="${product.name}" class="w-full h-full object-contain drop-shadow transition-transform duration-500 group-hover:scale-110">
                    </div>`;

                card.innerHTML = `
                    ${adminControls}
                    ${imgContainer}
                    <div class="product-info flex flex-col flex-grow">
                        <div class="flex justify-center mb-2">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${(product.category || 'makanan') === 'minuman' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600'}">
                                <i class="fas ${categoryIcon} text-[8px]"></i> ${categoryLabel}
                            </span>
                        </div>
                        <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-2 leading-tight">${product.name}</h3>
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2 h-8">${product.desc}</p>
                        ${ratingHtml}
                        <div class="flex justify-between items-center mt-auto pt-4 border-t border-gray-50">
                            <span class="font-extrabold text-lg text-red-600">${formatRupiah(product.price)}</span>
                            <button class="btn-cart w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors shadow-sm" title="Tambah ke Keranjang">
                                <i class="fas fa-cart-plus text-sm"></i>
                            </button>
                        </div>
                    </div>`;
            } else {
                card.className = "product-card bg-white rounded-2xl p-4 shadow-md transition-all duration-300 relative group flex flex-col md:flex-row items-center gap-6 hover:shadow-lg border border-gray-100";
                if (product.badge) {
                    badgeHtml = `<div class="badge absolute top-3 left-3 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10">${product.badge}</div>`;
                }
                card.innerHTML = `
                    ${badgeHtml}
                    ${adminControls}
                    <div class="img-container shrink-0 w-full md:w-[160px] aspect-square bg-gray-50 rounded-xl flex items-center justify-center p-2">
                        <img src="${imgSrc}" alt="${product.name}" class="w-full h-full object-contain drop-shadow transition-transform duration-300 group-hover:scale-110">
                    </div>
                    <div class="product-info flex-grow text-center md:text-left w-full">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start h-full">
                            <div class="flex flex-col justify-between h-full">
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mb-2 ${(product.category || 'makanan') === 'minuman' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600'}">
                                        <i class="fas ${categoryIcon} text-[8px]"></i> ${categoryLabel}
                                    </span>
                                    <h3 class="font-bold text-xl text-gray-800 mb-2">${product.name}</h3>
                                    <p class="text-gray-500 text-sm mb-3 max-w-lg">${product.desc}</p>
                                </div>
                                <div class="flex justify-center md:justify-start">${ratingHtml}</div>
                            </div>
                            <div class="price-action flex flex-row md:flex-col items-center justify-between md:justify-center md:items-end gap-3 mt-4 md:mt-0 w-full md:w-auto">
                                <span class="font-extrabold text-2xl text-red-600">${formatRupiah(product.price)}</span>
                                <button class="btn-cart bg-red-600 text-white w-full md:w-auto px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-red-700 transition shadow-lg shadow-red-200 flex items-center justify-center gap-2" title="Tambah ke Keranjang">
                                    <i class="fas fa-cart-plus"></i> Keranjang
                                </button>
                            </div>
                        </div>
                    </div>`;
            }

            // Animation
            card.style.opacity = "0";
            card.style.transform = "translateY(20px)";
            setTimeout(() => {
                card.style.transition = "all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)";
                card.style.opacity = "1";
                card.style.transform = "translateY(0)";
            }, index * 80);

            menuGrid.appendChild(card);
        });

        // Re-attach cart listeners — NOW adds to cart instead of opening order modal
        document.querySelectorAll('.btn-cart').forEach(btn => {
            btn.addEventListener('click', handleCartClick);
        });

        // Sync Radio Buttons
        const radios = document.querySelectorAll('input[name="layout_mode"]');
        const currentLayoutMode = localStorage.getItem('menuLayout') || 'grid';
        radios.forEach(r => { if (r.value === currentLayoutMode) r.checked = true; });

        // Update carousel controls
        updateCarouselControls(filtered);
    }

    // ====================================================================
    // AUTH UI
    // ====================================================================
    function updateLoginUI() {
        const loginIcon = btnLoginHeader.querySelector('i');
        const fab = document.getElementById('btn-add-menu-fab');

        if (currentUser) {
            // Ubah icon menjadi fa-user (person icon) saat login
            loginIcon.classList.remove('fa-sign-in-alt', 'fa-sign-out-alt');
            loginIcon.classList.add('fa-user');
            btnLoginHeader.title = `${currentUser.name} (Logout)`;

            if (currentUser.role === 'admin') {
                navAdmin.classList.remove('hidden');
                if (fab) fab.classList.remove('hidden');
            } else {
                navAdmin.classList.add('hidden');
                if (fab) fab.classList.add('hidden');
            }
        } else {
            // Ubah icon kembali ke fa-sign-in-alt saat logout
            loginIcon.classList.remove('fa-user', 'fa-sign-out-alt');
            loginIcon.classList.add('fa-sign-in-alt');
            btnLoginHeader.title = "Login";
            navAdmin.classList.add('hidden');
            if (fab) fab.classList.add('hidden');
        }
        updateMobileDrawerUI();
        renderProducts();
    }

    // ====================================================================
    // LOGIN
    // ====================================================================
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        try {
            const result = await apiRequest('/login', 'POST', { username, password });
            if (result.success) {
                currentUser = result.user;
                alert(result.message);
                updateLoginUI();
                loginModal.classList.add('hidden');
                loginForm.reset();
            } else {
                alert(result.message || 'Login gagal!');
            }
        } catch (err) {
            alert('Username atau Password salah!');
        }
    });

    // ====================================================================
    // REGISTER — sekarang dengan alamat & no_hp
    // ====================================================================
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('signup_name').value;
        const username = document.getElementById('signup_username').value;
        const password = document.getElementById('signup_password').value;
        const alamat = document.getElementById('signup_alamat').value;
        const no_hp = document.getElementById('signup_no_hp').value;

        try {
            const result = await apiRequest('/register', 'POST', { name, username, password, alamat, no_hp });
            if (result.success) {
                alert(result.message);
                signupModal.classList.add('hidden');
                loginModal.classList.remove('hidden');
                signupForm.reset();
            } else {
                alert(result.message || 'Pendaftaran gagal!');
            }
        } catch (err) {
            alert('Terjadi kesalahan saat mendaftar.');
        }
    });

    // ====================================================================
    // LOGOUT
    // ====================================================================
    btnLoginHeader.addEventListener('click', async () => {
        if (currentUser) {
            if (confirm('Yakin ingin logout?')) {
                await apiRequest('/logout', 'POST');
                currentUser = null;
                updateLoginUI();
            }
        } else {
            loginModal.classList.remove('hidden');
        }
    });

    // ====================================================================
    // ADMIN PANEL
    // ====================================================================
    btnAdminPanel.addEventListener('click', (e) => {
        e.preventDefault();
        adminModal.classList.remove('hidden');
        renderOrdersTable();
    });

    async function renderOrdersTable() {
        const tbody = document.getElementById('orders-table-body');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Memuat...</td></tr>';

        try {
            const result = await apiRequest('/admin/pesanan');
            if (result.success && result.data.length > 0) {
                tbody.innerHTML = '';
                result.data.forEach(order => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.date}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${order.customerName}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.items}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">${formatRupiah(order.total)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Berhasil</span>
                        </td>`;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada pesanan masuk.</td></tr>';
            }
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-red-500">Gagal memuat data pesanan.</td></tr>';
        }
    }

    // Layout Settings
    document.querySelectorAll('input[name="layout_mode"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            localStorage.setItem('menuLayout', e.target.value);
            renderProducts();
        });
    });

    // ====================================================================
    // ADD MENU (Admin)
    // ====================================================================
    const fabAddMenu = document.getElementById('btn-add-menu-fab');
    const addMenuModal = document.getElementById('addMenuModal');
    const closeAddMenuBtn = document.getElementById('closeAddMenuModal');

    if (fabAddMenu) {
        fabAddMenu.addEventListener('click', () => addMenuModal.classList.remove('hidden'));
    }
    if (closeAddMenuBtn) {
        closeAddMenuBtn.addEventListener('click', () => addMenuModal.classList.add('hidden'));
    }

    addMenuForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('new_menu_name').value;
        const price = parseInt(document.getElementById('new_menu_price').value);
        const description = document.getElementById('new_menu_desc').value;
        const image = document.getElementById('new_menu_img').value || 'asset/logo merah.png';
        const category = document.getElementById('new_menu_category').value || 'makanan';

        try {
            const result = await apiRequest('/products', 'POST', { name, price, description, image, category });
            if (result.success) {
                products.push({
                    id: result.product.id,
                    name: result.product.name,
                    price: result.product.price,
                    desc: result.product.description,
                    image: result.product.image,
                    badge: result.product.badge,
                    category: result.product.category || 'makanan',
                    reviews: []
                });
                alert(result.message);
                addMenuModal.classList.add('hidden');
                addMenuForm.reset();
                renderProducts();
            }
        } catch (err) {
            alert('Gagal menambahkan menu.');
        }
    });

    // ====================================================================
    // DELETE & EDIT PRODUCT (Admin)
    // ====================================================================
    window.deleteProduct = async (id) => {
        if (confirm('Yakin ingin menghapus menu ini?')) {
            try {
                const result = await apiRequest(`/products/${id}`, 'DELETE');
                if (result.success) {
                    products = products.filter(p => p.id !== id);
                    renderProducts();
                }
            } catch (err) {
                alert('Gagal menghapus menu.');
            }
        }
    };

    window.editProduct = async (id) => {
        const product = products.find(p => p.id === id);
        if (!product) return;

        const newPrice = prompt(`Edit Harga untuk ${product.name}:`, product.price);
        if (newPrice !== null && !isNaN(newPrice)) {
            try {
                const result = await apiRequest(`/products/${id}`, 'PUT', { price: parseInt(newPrice) });
                if (result.success) {
                    product.price = parseInt(newPrice);
                    renderProducts();
                    alert('Harga berhasil diubah!');
                }
            } catch (err) {
                alert('Gagal mengubah harga.');
            }
        }
    };

    // ====================================================================
    // REVIEW
    // ====================================================================
    const reviewModal = document.getElementById('reviewModal');
    const reviewForm = document.getElementById('reviewForm');
    const closeReviewBtn = document.getElementById('closeReviewModal');
    let starRatingValue = 0;

    window.openReviewModal = (id) => {
        if (!currentUser) {
            alert('Silakan login untuk melihat atau memberikan ulasan.');
            loginModal.classList.remove('hidden');
            return;
        }

        const product = products.find(p => p.id === id);
        if (!product) return;

        document.getElementById('review_product_id').value = id;
        document.getElementById('review-product-name').innerText = product.name;

        // Render reviews
        const reviewsContainer = document.getElementById('existing-reviews');
        reviewsContainer.innerHTML = '';

        if (product.reviews && product.reviews.length > 0) {
            product.reviews.slice().reverse().forEach(r => {
                const stars = '<i class="fas fa-star text-yellow-500 text-xs"></i>'.repeat(r.rating);
                const reviewItem = document.createElement('div');
                reviewItem.className = "bg-gray-50 p-3 rounded-lg border border-gray-100";
                reviewItem.innerHTML = `
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-sm text-gray-900">${r.user}</span>
                        <span class="text-xs text-gray-400">${r.date}</span>
                    </div>
                    <div class="flex items-center mb-1">${stars}</div>
                    <p class="text-sm text-gray-600">${r.comment || ''}</p>`;
                reviewsContainer.appendChild(reviewItem);
            });
        } else {
            reviewsContainer.innerHTML = `<p class="text-gray-500 text-sm italic text-center py-4">Belum ada ulasan untuk menu ini.</p>`;
        }

        reviewModal.classList.remove('hidden');
        resetStarRating();
    };

    if (closeReviewBtn) {
        closeReviewBtn.addEventListener('click', () => reviewModal.classList.add('hidden'));
    }

    // Star Rating
    const starInputs = document.querySelectorAll('#star-rating-input i');
    starInputs.forEach(star => {
        star.addEventListener('click', () => {
            starRatingValue = parseInt(star.dataset.value);
            document.getElementById('review_rating').value = starRatingValue;
            updateStarVisuals(starRatingValue);
        });
    });

    function updateStarVisuals(value) {
        starInputs.forEach(s => {
            const v = parseInt(s.dataset.value);
            if (v <= value) { s.classList.remove('far'); s.classList.add('fas'); }
            else { s.classList.remove('fas'); s.classList.add('far'); }
        });
    }

    function resetStarRating() {
        starRatingValue = 0;
        document.getElementById('review_rating').value = '';
        document.getElementById('review_comment').value = '';
        updateStarVisuals(0);
    }

    reviewForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const product_id = parseInt(document.getElementById('review_product_id').value);
        const rating = parseInt(document.getElementById('review_rating').value);
        const comment = document.getElementById('review_comment').value;

        if (!rating) { alert('Mohon pilih bintang rating!'); return; }

        try {
            const result = await apiRequest('/reviews', 'POST', { product_id, rating, comment });
            if (result.success) {
                const product = products.find(p => p.id === product_id);
                if (product) {
                    if (!product.reviews) product.reviews = [];
                    product.reviews.push(result.review);
                }
                alert(result.message);
                reviewModal.classList.add('hidden');
                renderProducts();
            }
        } catch (err) {
            alert('Gagal mengirim ulasan.');
        }
    });

    // ====================================================================
    // CART CLICK HANDLER — Add to cart instead of order modal
    // ====================================================================
    function handleCartClick(e) {
        e.preventDefault();
        const btn = e.target.closest('.btn-cart');
        const card = btn.closest('.product-card');
        const id = parseInt(card.dataset.id);

        if (!currentUser) {
            alert('Silakan login untuk memesan.');
            loginModal.classList.remove('hidden');
            return;
        }

        addToCart(id);
    }

    // ====================================================================
    // MODAL CLOSING
    // ====================================================================
    document.getElementById('closeLoginModal').addEventListener('click', () => loginModal.classList.add('hidden'));
    document.getElementById('closeSignupModal').addEventListener('click', () => signupModal.classList.add('hidden'));
    document.getElementById('closeAdminModal').addEventListener('click', () => adminModal.classList.add('hidden'));

    // Toggle Sign Up
    const signUpLink = document.getElementById('link-signup');
    if (signUpLink) {
        signUpLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.classList.add('hidden');
            signupModal.classList.remove('hidden');
        });
    }

    document.getElementById('btn-settings').addEventListener('click', () => alert('Fitur pengaturan belum tersedia.'));

    const HEADER_OFFSET = 100; // tinggi header fixed

    // Mobile Drawer
    const hamburgerBtn = document.querySelector('.hamburger-btn');
    const mobileDrawer = document.querySelector('.mobile-drawer');
    const drawerOverlay = document.querySelector('.drawer-overlay');
    const drawerClose = document.querySelector('.drawer-close');

    function openDrawer() {
        mobileDrawer.classList.add('open');
        drawerOverlay.classList.add('open');
        hamburgerBtn.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        mobileDrawer.classList.remove('open');
        drawerOverlay.classList.remove('open');
        hamburgerBtn.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', () => {
        mobileDrawer.classList.contains('open') ? closeDrawer() : openDrawer();
    });
    if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);
    if (drawerClose) drawerClose.addEventListener('click', closeDrawer);

    // Mobile drawer button proxies
    const btnLoginMobile = document.getElementById('btn-login-mobile');
    const btnSettingsMobile = document.getElementById('btn-settings-mobile');
    const btnAdminPanelMobile = document.getElementById('btn-admin-panel-mobile');

    if (btnLoginMobile) btnLoginMobile.addEventListener('click', () => {
        closeDrawer();
        btnLoginHeader.click();
    });
    if (btnSettingsMobile) btnSettingsMobile.addEventListener('click', () => {
        closeDrawer();
        document.getElementById('btn-settings').click();
    });
    if (btnAdminPanelMobile) btnAdminPanelMobile.addEventListener('click', (e) => {
        e.preventDefault();
        closeDrawer();
        btnAdminPanel.click();
    });

    // Update mobile drawer admin visibility & login button text
    function updateMobileDrawerUI() {
        const navAdminMobile = document.getElementById('nav-admin-mobile');
        if (currentUser) {
            if (btnLoginMobile) {
                btnLoginMobile.querySelector('i').classList.remove('fa-sign-in-alt');
                btnLoginMobile.querySelector('i').classList.add('fa-user');
                btnLoginMobile.querySelector('span').textContent = currentUser.name;
            }
            if (currentUser.role === 'admin' && navAdminMobile) {
                navAdminMobile.classList.remove('hidden');
            }
        } else {
            if (btnLoginMobile) {
                btnLoginMobile.querySelector('i').classList.remove('fa-user');
                btnLoginMobile.querySelector('i').classList.add('fa-sign-in-alt');
                btnLoginMobile.querySelector('span').textContent = 'Login';
            }
            if (navAdminMobile) navAdminMobile.classList.add('hidden');
        }
    }

    // Smooth scroll for drawer links
    document.querySelectorAll('.nav-links-mobile a[href^="#"]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            closeDrawer();
            const targetId = link.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        });
    });

    // Header shrink on scroll
    const mainHeader = document.getElementById('main-header');
    window.addEventListener('scroll', () => {
        if (mainHeader) {
            if (window.scrollY > 50) {
                mainHeader.classList.add('scrolled');
            } else {
                mainHeader.classList.remove('scrolled');
            }
        }
    });

    // ====================================================================
    // SMOOTH SCROLL ANIMATION FOR NAVBAR
    // ====================================================================

    // Smooth scroll saat klik link navbar
    document.querySelectorAll('.nav-links a[href^="#"]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href');
            const targetSection = document.querySelector(targetId);

            if (targetSection) {
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Smooth scroll juga untuk tombol "PESAN SEKARANG" di hero
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        // Skip navbar links (sudah di-handle di atas) dan link yang bukan navigasi
        if (link.closest('.nav-links') || link.getAttribute('href') === '#') return;

        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href');
            const targetSection = document.querySelector(targetId);

            if (targetSection) {
                e.preventDefault();
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Active nav link highlighting saat scroll
    const sections = document.querySelectorAll('section[id]');
    const navLinksAll = document.querySelectorAll('.nav-links a[href^="#"]');

    function updateActiveNavLink() {
        const scrollPos = window.scrollY + HEADER_OFFSET + 50;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');

            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                navLinksAll.forEach(link => {
                    link.classList.remove('text-primary-red', 'font-extrabold');
                    link.style.transform = '';
                    link.style.textShadow = '';
                });

                const activeLink = document.querySelector(`.nav-links a[href="#${sectionId}"]`);
                if (activeLink) {
                    activeLink.classList.add('text-primary-red');
                    activeLink.style.textShadow = '0 0 8px rgba(210, 0, 0, 0.3)';
                }
            }
        });
    }

    // Throttle scroll event untuk performa
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (scrollTimeout) return;
        scrollTimeout = setTimeout(() => {
            updateActiveNavLink();
            scrollTimeout = null;
        }, 50);
    });

    // Set active link on load
    updateActiveNavLink();

    // ====================================================================
    // INIT - Render products immediately, then check auth in background
    // ====================================================================
    renderProducts();  // Tampilkan menu langsung tanpa tunggu auth
    checkAuth();       // Cek login di background (akan re-render jika ada session)
});
