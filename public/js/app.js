// ========================================================================
// Chi-Pok App - Laravel Version
// ========================================================================
// Data produk di-pass dari server via Blade (@json)
// Auth & CRUD menggunakan AJAX fetch ke Laravel routes

let products = PRODUCTS_DATA;
let currentUser = null;

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
    const orderModal = document.getElementById('orderModal');
    const adminModal = document.getElementById('adminModal');

    const btnLoginHeader = document.getElementById('btn-login');
    const btnAdminPanel = document.getElementById('btn-admin-panel');
    const navAdmin = document.getElementById('nav-admin');

    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const orderForm = document.getElementById('orderForm');
    const addMenuForm = document.getElementById('addMenuForm');

    // ====================================================================
    // INIT - Check Auth Status (now INSIDE DOMContentLoaded)
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
    // PRODUCT RENDERING
    // ====================================================================
    function renderProducts() {
        menuGrid.innerHTML = '';
        const isAdmin = currentUser && currentUser.role === 'admin';
        const layoutMode = localStorage.getItem('menuLayout') || 'grid';

        if (layoutMode === 'grid') {
            menuGrid.className = "menu-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8";
        } else {
            menuGrid.className = "menu-grid flex flex-col gap-6 max-w-4xl mx-auto";
        }

        products.forEach((product, index) => {
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
                        <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-2 leading-tight">${product.name}</h3>
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2 h-8">${product.desc}</p>
                        ${ratingHtml}
                        <div class="flex justify-between items-center mt-auto pt-4 border-t border-gray-50">
                            <span class="font-extrabold text-lg text-red-600">${formatRupiah(product.price)}</span>
                            <button class="btn-cart w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors shadow-sm">
                                <i class="fas fa-shopping-cart text-sm"></i>
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
                                    <h3 class="font-bold text-xl text-gray-800 mb-2">${product.name}</h3>
                                    <p class="text-gray-500 text-sm mb-3 max-w-lg">${product.desc}</p>
                                </div>
                                <div class="flex justify-center md:justify-start">${ratingHtml}</div>
                            </div>
                            <div class="price-action flex flex-row md:flex-col items-center justify-between md:justify-center md:items-end gap-3 mt-4 md:mt-0 w-full md:w-auto">
                                <span class="font-extrabold text-2xl text-red-600">${formatRupiah(product.price)}</span>
                                <button class="btn-cart bg-red-600 text-white w-full md:w-auto px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-red-700 transition shadow-lg shadow-red-200 flex items-center justify-center gap-2">
                                    <i class="fas fa-plus"></i> Keranjang
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
            }, index * 50);

            menuGrid.appendChild(card);
        });

        // Re-attach cart listeners
        document.querySelectorAll('.btn-cart').forEach(btn => {
            btn.addEventListener('click', handleCartClick);
        });

        // Sync Radio Buttons
        const radios = document.querySelectorAll('input[name="layout_mode"]');
        const currentLayoutMode = localStorage.getItem('menuLayout') || 'grid';
        radios.forEach(r => { if (r.value === currentLayoutMode) r.checked = true; });
    }

    // ====================================================================
    // AUTH UI
    // ====================================================================
    function updateLoginUI() {
        const loginIcon = btnLoginHeader.querySelector('i');
        const fab = document.getElementById('btn-add-menu-fab');

        if (currentUser) {
            loginIcon.classList.remove('fa-sign-in-alt');
            loginIcon.classList.add('fa-sign-out-alt');
            btnLoginHeader.title = `Logout (${currentUser.name})`;

            if (currentUser.role === 'admin') {
                navAdmin.classList.remove('hidden');
                if (fab) fab.classList.remove('hidden');
            } else {
                navAdmin.classList.add('hidden');
                if (fab) fab.classList.add('hidden');
            }
        } else {
            loginIcon.classList.remove('fa-sign-out-alt');
            loginIcon.classList.add('fa-sign-in-alt');
            btnLoginHeader.title = "Login";
            navAdmin.classList.add('hidden');
            if (fab) fab.classList.add('hidden');
        }
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
    // REGISTER
    // ====================================================================
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('signup_name').value;
        const username = document.getElementById('signup_username').value;
        const password = document.getElementById('signup_password').value;

        try {
            const result = await apiRequest('/register', 'POST', { name, username, password });
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

        try {
            const result = await apiRequest('/products', 'POST', { name, price, description, image });
            if (result.success) {
                products.push({
                    id: result.product.id,
                    name: result.product.name,
                    price: result.product.price,
                    desc: result.product.description,
                    image: result.product.image,
                    badge: result.product.badge,
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
    // ORDER
    // ====================================================================
    let currentItem = null;

    function handleCartClick(e) {
        e.preventDefault();
        const btn = e.target.closest('.btn-cart');
        const card = btn.closest('.product-card');
        const id = parseInt(card.dataset.id);
        const product = products.find(p => p.id === id);

        if (!currentUser) {
            alert('Silakan login untuk memesan.');
            loginModal.classList.remove('hidden');
            return;
        }

        currentItem = product;
        showOrderModal();
    }

    function showOrderModal() {
        orderModal.classList.remove('hidden');
        document.getElementById('pesanan_item').value = currentItem.name;
        document.getElementById('display_harga_satuan').innerText = formatRupiah(currentItem.price);
        document.getElementById('harga_satuan').value = currentItem.price;
        document.getElementById('jumlah').value = 1;
        updateTotal();

        if (currentUser) {
            document.getElementById('nama').value = currentUser.name;
        }
    }

    orderForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const data = {
            nama: document.getElementById('nama').value,
            no_hp: document.getElementById('no_hp').value,
            alamat: document.getElementById('alamat').value,
            pesanan_item: document.getElementById('pesanan_item').value,
            jumlah: parseInt(document.getElementById('jumlah').value),
            harga_satuan: parseFloat(document.getElementById('harga_satuan').value),
            jenis_belanja: document.getElementById('jenis_belanja').value,
        };

        try {
            const result = await apiRequest('/pesanan', 'POST', data);
            if (result.success) {
                alert(result.message);
                orderModal.classList.add('hidden');
                orderForm.reset();
            } else {
                alert(result.message || 'Gagal membuat pesanan.');
            }
        } catch (err) {
            alert('Gagal membuat pesanan. Pastikan semua data terisi.');
        }
    });

    document.getElementById('jumlah').addEventListener('input', updateTotal);

    function updateTotal() {
        if (!currentItem) return;
        const qty = parseInt(document.getElementById('jumlah').value) || 1;
        const total = qty * currentItem.price;
        document.getElementById('display_total_harga').innerText = formatRupiah(total);
        document.getElementById('total_harga').value = total;
    }

    // ====================================================================
    // MODAL CLOSING
    // ====================================================================
    document.getElementById('closeLoginModal').addEventListener('click', () => loginModal.classList.add('hidden'));
    document.getElementById('closeSignupModal').addEventListener('click', () => signupModal.classList.add('hidden'));
    document.getElementById('closeModal').addEventListener('click', () => orderModal.classList.add('hidden'));
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

    // Mobile Menu
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('toggle');
            navLinks.classList.toggle('!flex');
            navLinks.classList.toggle('flex-col');
            navLinks.classList.toggle('bg-white');
            navLinks.classList.toggle('p-4');
            navLinks.classList.toggle('absolute');
            navLinks.classList.toggle('top-full');
            navLinks.classList.toggle('right-0');
            navLinks.classList.toggle('w-full');
        });
    }

    // ====================================================================
    // INIT - Render products immediately, then check auth in background
    // ====================================================================
    renderProducts();  // Tampilkan menu langsung tanpa tunggu auth
    checkAuth();       // Cek login di background (akan re-render jika ada session)
});
