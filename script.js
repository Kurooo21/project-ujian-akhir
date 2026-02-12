// Initialize Data
const defaultProducts = [
    {
        id: 1,
        name: "CHI'POK LAVA 🔥",
        price: 27000,
        desc: "Pedas & melimpah, sensasi lava cabe!",
        image: "asset/rasa lava.png",
        badge: "BEST SELLER"
    },
    {
        id: 2,
        name: "CHI'POK ORI",
        price: 25000,
        desc: "Gurih & renyah klasic favorit!",
        image: "asset/original.png",
        icon: '<i class="fas fa-check-circle text-primary-red"></i>'
    },
    {
        id: 3,
        name: "CHI'POK KEJU",
        price: 26000,
        desc: "Kriuk, gurih keju melimpah!",
        image: "asset/rasa keju.png",
        rating: 4.5
    },
    {
        id: 4,
        name: "CHI'POK BUMBU",
        price: 24000,
        desc: "Rasa kaya rempah, unik!",
        image: "asset/rasa bumbu.png"
    }
];

const hardcodedUsers = [
    { username: 'admin', password: 'admin123', name: 'Admin Chi-Pok', role: 'admin' },
    { username: 'pelanggan', password: 'pelanggan123', name: 'Pelanggan Setia', role: 'pelanggan' }
];

// State Management
let products = JSON.parse(localStorage.getItem('products')) || defaultProducts;
let orders = JSON.parse(localStorage.getItem('orders')) || [];
// Sync default products if localstorage is empty to ensure they exist
if (!localStorage.getItem('products')) {
    localStorage.setItem('products', JSON.stringify(defaultProducts));
}

document.addEventListener("DOMContentLoaded", () => {
    // --- Elements ---
    const menuGrid = document.querySelector('.menu-grid');
    const loginModal = document.getElementById('loginModal');
    const signupModal = document.getElementById('signupModal');
    const orderModal = document.getElementById('orderModal');
    const adminModal = document.getElementById('adminModal');

    // Buttons
    const btnLoginHeader = document.getElementById('btn-login');
    const btnAdminPanel = document.getElementById('btn-admin-panel');
    const navAdmin = document.getElementById('nav-admin');

    // Forms
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const orderForm = document.getElementById('orderForm');
    const addMenuForm = document.getElementById('addMenuForm');

    // --- Product Rendering ---
    function renderProducts() {
        menuGrid.innerHTML = '';
        const user = JSON.parse(localStorage.getItem('user'));
        const isAdmin = user && user.role === 'admin';
        const layoutMode = localStorage.getItem('menuLayout') || 'grid';

        // Set Grid Container Class based on Layout
        if (layoutMode === 'grid') {
            menuGrid.className = "menu-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8";
        } else {
            // List View: 1 Column, stacked
            menuGrid.className = "menu-grid flex flex-col gap-6 max-w-4xl mx-auto";
        }

        products.forEach((product, index) => {
            const card = document.createElement('div');
            card.dataset.id = product.id;

            // Calculate Rating
            let ratingHtml = '';
            let avgRating = 0;
            let totalReviews = 0;

            if (product.reviews && product.reviews.length > 0) {
                totalReviews = product.reviews.length;
                const sum = product.reviews.reduce((acc, curr) => acc + parseInt(curr.rating), 0);
                avgRating = (sum / totalReviews).toFixed(1);

                // Generate Stars
                let stars = '';
                const fullStars = Math.floor(avgRating);
                const halfStar = avgRating % 1 >= 0.5 ? 1 : 0;

                for (let i = 0; i < fullStars; i++) stars += '<i class="fas fa-star text-yellow-500"></i>';
                if (halfStar) stars += '<i class="fas fa-star-half-alt text-yellow-500"></i>';
                // remaining empty? maybe not needed for cleaner look or use gray stars

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


            // Admin Controls (Simplified)
            let adminControls = '';
            if (isAdmin) {
                adminControls = `
                    <div class="absolute top-2 right-2 flex gap-2 z-10 transition-opacity opacity-0 group-hover:opacity-100">
                         <button class="bg-white/80 text-gray-700 p-2 rounded-full hover:bg-white hover:text-yellow-600 transition shadow-sm border border-gray-200" onclick="editProduct(${product.id})" title="Edit"><i class="fas fa-pencil-alt text-xs"></i></button>
                        <button class="bg-white/80 text-gray-700 p-2 rounded-full hover:bg-white hover:text-red-600 transition shadow-sm border border-gray-200" onclick="deleteProduct(${product.id})" title="Hapus"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                `;
            }

            // Badge
            let badgeHtml = '';
            if (product.badge) {
                badgeHtml = `<div class="badge absolute top-2 left-2 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10 tracking-wide shadow-sm">${product.badge}</div>`;
            }

            if (layoutMode === 'grid') {
                // --- GRID VIEW CARD (FIXED UI) ---
                card.className = "product-card bg-white rounded-2xl p-4 text-center shadow-lg transition-all duration-300 mt-0 hover:-translate-y-2 hover:shadow-xl relative group flex flex-col justify-between overflow-hidden border border-gray-100";

                // Image Container
                const imgContainer = `
                    <div class="w-full aspect-square bg-gray-50 rounded-xl mb-4 flex items-center justify-center p-4 relative overflow-hidden">
                         ${badgeHtml}
                         <img src="${product.image}" alt="${product.name}" class="w-full h-full object-contain drop-shadow transition-transform duration-500 group-hover:scale-110">
                    </div>
                `;

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
                    </div>
                `;
            } else {
                // --- LIST VIEW CARD ---
                card.className = "product-card bg-white rounded-2xl p-4 shadow-md transition-all duration-300 relative group flex flex-col md:flex-row items-center gap-6 hover:shadow-lg border border-gray-100";

                if (product.badge) {
                    badgeHtml = `<div class="badge absolute top-3 left-3 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10">${product.badge}</div>`;
                }

                card.innerHTML = `
                    ${badgeHtml}
                    ${adminControls}
                    <div class="img-container shrink-0 w-full md:w-[160px] aspect-square bg-gray-50 rounded-xl flex items-center justify-center p-2">
                        <img src="${product.image}" alt="${product.name}"
                        class="w-full h-full object-contain drop-shadow transition-transform duration-300 group-hover:scale-110">
                    </div>
                    <div class="product-info flex-grow text-center md:text-left w-full">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start h-full">
                            <div class="flex flex-col justify-between h-full">
                                <div>
                                    <h3 class="font-bold text-xl text-gray-800 mb-2">${product.name}</h3>
                                    <p class="text-gray-500 text-sm mb-3 max-w-lg">${product.desc}</p>
                                </div>
                                <div class="flex justify-center md:justify-start">
                                     ${ratingHtml}
                                </div>
                            </div>
                            <div class="price-action flex flex-row md:flex-col items-center justify-between md:justify-center md:items-end gap-3 mt-4 md:mt-0 w-full md:w-auto">
                                <span class="font-extrabold text-2xl text-red-600">${formatRupiah(product.price)}</span>
                                <button class="btn-cart bg-red-600 text-white w-full md:w-auto px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-red-700 transition shadow-lg shadow-red-200 flex items-center justify-center gap-2">
                                    <i class="fas fa-plus"></i> Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                `;
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

        // Re-attach listeners for new cart buttons
        document.querySelectorAll('.btn-cart').forEach(btn => {
            btn.addEventListener('click', handleCartClick);
        });

        // Sync Radio Buttons in Admin Panel
        const radios = document.querySelectorAll('input[name="layout_mode"]');
        radios.forEach(r => {
            if (r.value === layoutMode) r.checked = true;
        });
    }

    // --- Auth Logic ---
    function getAllUsers() {
        const storedUsers = JSON.parse(localStorage.getItem('users')) || [];
        return [...hardcodedUsers, ...storedUsers];
    }

    function updateLoginUI() {
        const user = JSON.parse(localStorage.getItem('user'));
        const loginIcon = btnLoginHeader.querySelector('i');
        const fab = document.getElementById('btn-add-menu-fab'); // NEW FAB

        if (user) {
            loginIcon.classList.remove('fa-sign-in-alt');
            loginIcon.classList.add('fa-sign-out-alt');
            btnLoginHeader.title = `Logout (${user.name})`;

            if (user.role === 'admin') {
                navAdmin.classList.remove('hidden');
                if (fab) fab.classList.remove('hidden'); // Show FAB
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
        renderProducts(); // Re-render to show/hide admin controls
    }

    // Login Form Submit
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const u = document.getElementById('username').value;
        const p = document.getElementById('password').value;

        const allUsers = getAllUsers();
        const foundUser = allUsers.find(user => user.username === u && user.password === p);

        if (foundUser) {
            localStorage.setItem('user', JSON.stringify(foundUser));
            alert(`Login Berhasil! Selamat datang, ${foundUser.name} (${foundUser.role}).`);
            updateLoginUI();
            loginModal.classList.add('hidden');
        } else {
            alert('Username atau Password salah!');
        }
    });

    // Sign Up Form Submit
    signupForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const name = document.getElementById('signup_name').value;
        const username = document.getElementById('signup_username').value;
        const password = document.getElementById('signup_password').value;

        const allUsers = getAllUsers();
        if (allUsers.find(u => u.username === username)) {
            alert('Username sudah terdaftar!');
            return;
        }

        const newUser = { username, password, name, role: 'pelanggan' };

        // Save to local storage users
        const storedUsers = JSON.parse(localStorage.getItem('users')) || [];
        storedUsers.push(newUser);
        localStorage.setItem('users', JSON.stringify(storedUsers));

        alert('Pendaftaran Berhasil! Silakan Login.');
        signupModal.classList.add('hidden');
        loginModal.classList.remove('hidden');
    });

    // --- Admin Panel Logic ---
    btnAdminPanel.addEventListener('click', (e) => {
        e.preventDefault();
        adminModal.classList.remove('hidden');
        renderOrdersTable();
    });

    // Tabs
    const tabOrders = document.getElementById('tab-orders');
    const tabSettings = document.getElementById('tab-settings'); // NEW
    const contentOrders = document.getElementById('content-orders');
    const contentSettings = document.getElementById('content-settings'); // NEW

    function switchTab(activeTab, activeContent) {
        // Reset all
        [tabOrders, tabSettings].forEach(t => {
            if (t) {
                t.classList.remove('border-red-500', 'text-red-600');
                t.classList.add('border-transparent', 'text-gray-500');
            }
        });
        [contentOrders, contentSettings].forEach(c => {
            if (c) c.classList.add('hidden');
        });

        // Set active
        activeTab.classList.add('border-red-500', 'text-red-600');
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeContent.classList.remove('hidden');
    }

    if (tabOrders) {
        tabOrders.addEventListener('click', () => {
            switchTab(tabOrders, contentOrders);
            renderOrdersTable();
        });
    }



    if (tabSettings) {
        tabSettings.addEventListener('click', () => {
            switchTab(tabSettings, contentSettings);
        });
    }

    // Layout Settings Logic
    document.querySelectorAll('input[name="layout_mode"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const mode = e.target.value;
            localStorage.setItem('menuLayout', mode);
            renderProducts(); // Immediate update
        });
    });

    function renderOrdersTable() {
        const tbody = document.getElementById('orders-table-body');
        if (!tbody) return;
        tbody.innerHTML = '';
        const currentOrders = JSON.parse(localStorage.getItem('orders')) || [];

        if (currentOrders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada pesanan masuk.</td></tr>';
            return;
        }

        currentOrders.slice().reverse().forEach((order) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.date}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${order.customerName}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.items}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">${formatRupiah(order.total)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Berhasil
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="#" class="text-red-600 hover:text-red-900">Detail</a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Add Menu Logic (FAB Triggered)
    const fabAddMenu = document.getElementById('btn-add-menu-fab');
    const addMenuModal = document.getElementById('addMenuModal');
    const closeAddMenuBtn = document.getElementById('closeAddMenuModal');

    if (fabAddMenu) {
        fabAddMenu.addEventListener('click', () => {
            addMenuModal.classList.remove('hidden');
        });
    }

    if (closeAddMenuBtn) {
        closeAddMenuBtn.addEventListener('click', () => {
            addMenuModal.classList.add('hidden');
        });
    }

    addMenuForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const name = document.getElementById('new_menu_name').value;
        const price = parseInt(document.getElementById('new_menu_price').value);
        const desc = document.getElementById('new_menu_desc').value;
        const img = document.getElementById('new_menu_img').value || 'asset/logo merah.png';

        const newProduct = {
            id: Date.now(),
            name,
            price,
            desc,
            image: img,
            reviews: [] // Init empty reviews
        };

        products.push(newProduct);
        localStorage.setItem('products', JSON.stringify(products));
        alert('Menu berhasil ditambahkan!');
        addMenuModal.classList.add('hidden'); // Close standalone modal
        addMenuForm.reset();
        renderProducts();
    });

    // Make functions global for inline button calls
    window.deleteProduct = (id) => {
        if (confirm('Yakin ingin menghapus menu ini?')) {
            products = products.filter(p => p.id !== id);
            localStorage.setItem('products', JSON.stringify(products));
            renderProducts();
        }
    };

    window.editProduct = (id) => {
        const product = products.find(p => p.id === id);
        if (!product) return;

        const newPrice = prompt(`Edit Harga untuk ${product.name}:`, product.price);
        if (newPrice !== null && !isNaN(newPrice)) {
            product.price = parseInt(newPrice);
            localStorage.setItem('products', JSON.stringify(products));
            renderProducts();
            alert('Harga berhasil diubah!');
        }
    };

    // --- Review Logic ---
    const reviewModal = document.getElementById('reviewModal');
    const reviewForm = document.getElementById('reviewForm');
    const closeReviewBtn = document.getElementById('closeReviewModal');
    let starRatingValue = 0;

    window.openReviewModal = (id) => {
        const user = JSON.parse(localStorage.getItem('user'));
        if (!user) {
            alert('Silakan login untuk melihat atau memberikan ulasan.');
            loginModal.classList.remove('hidden');
            return;
        }

        const product = products.find(p => p.id === id);
        if (!product) return;

        document.getElementById('review_product_id').value = id;
        document.getElementById('review-product-name').innerText = product.name;

        // Render Existing Reviews
        const reviewsContainer = document.getElementById('existing-reviews');
        reviewsContainer.innerHTML = ''; // Clear prev

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
                    <p class="text-sm text-gray-600">${r.comment}</p>
                `;
                reviewsContainer.appendChild(reviewItem);
            });
        } else {
            reviewsContainer.innerHTML = `<p class="text-gray-500 text-sm italic text-center py-4">Belum ada ulasan untuk menu ini.</p>`;
        }

        reviewModal.classList.remove('hidden');
        resetStarRating();
    };

    if (closeReviewBtn) {
        closeReviewBtn.addEventListener('click', () => {
            reviewModal.classList.add('hidden');
        });
    }

    // Star Rating Interaction
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
            if (v <= value) {
                s.classList.remove('far');
                s.classList.add('fas');
            } else {
                s.classList.remove('fas');
                s.classList.add('far');
            }
        });
    }

    function resetStarRating() {
        starRatingValue = 0;
        document.getElementById('review_rating').value = '';
        document.getElementById('review_comment').value = '';
        updateStarVisuals(0);
    }

    reviewForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const productId = parseInt(document.getElementById('review_product_id').value);
        const rating = parseInt(document.getElementById('review_rating').value);
        const comment = document.getElementById('review_comment').value;
        const user = JSON.parse(localStorage.getItem('user'));

        if (!rating) {
            alert('Mohon pilih bintang rating!');
            return;
        }

        const product = products.find(p => p.id === productId);
        if (product) {
            if (!product.reviews) product.reviews = [];

            product.reviews.push({
                user: user.name,
                rating: rating,
                comment: comment,
                date: new Date().toLocaleDateString()
            });

            localStorage.setItem('products', JSON.stringify(products));
            alert('Terima kasih atas ulasan Anda!');
            reviewModal.classList.add('hidden');
            renderProducts();
        }
    });


    // --- Order Logic ---
    let currentItem = null;

    function handleCartClick(e) {
        e.preventDefault();
        const btn = e.target.closest('.btn-cart');
        const card = btn.closest('.product-card');
        const id = parseInt(card.dataset.id);
        const product = products.find(p => p.id === id);

        const user = JSON.parse(localStorage.getItem('user'));

        // Removed login check here to allow viewing, but keeping it for ordering is better/safer
        // Or if you want "Add to Cart" to trigger login
        if (!user) {
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

        // Prefill user data
        const user = JSON.parse(localStorage.getItem('user'));
        if (user) {
            document.getElementById('nama').value = user.name;
        }
    }

    orderForm.addEventListener('submit', (e) => {
        e.preventDefault();

        // Collect Data
        const nama = document.getElementById('nama').value;
        const total = document.getElementById('total_harga').value;
        const jumlah = document.getElementById('jumlah').value;

        const newOrder = {
            id: Date.now(),
            date: new Date().toLocaleString(),
            customerName: nama,
            items: `${currentItem.name} (${jumlah}x)`,
            total: total
        };

        // Save
        const currentOrders = JSON.parse(localStorage.getItem('orders')) || [];
        currentOrders.push(newOrder);
        localStorage.setItem('orders', JSON.stringify(currentOrders));

        alert('Pesanan Berhasil Dikirim! Mohon tunggu.');
        orderModal.classList.add('hidden');
        orderForm.reset();
    });

    document.getElementById('jumlah').addEventListener('input', updateTotal);

    function updateTotal() {
        if (!currentItem) return;
        const qty = parseInt(document.getElementById('jumlah').value) || 1;
        const total = qty * currentItem.price;
        document.getElementById('display_total_harga').innerText = formatRupiah(total);
        document.getElementById('total_harga').value = total;
    }

    // --- Utilities ---
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
    }

    // --- Modal Closing Logic ---
    document.getElementById('closeLoginModal').addEventListener('click', () => loginModal.classList.add('hidden'));
    document.getElementById('closeSignupModal').addEventListener('click', () => signupModal.classList.add('hidden'));
    document.getElementById('closeModal').addEventListener('click', () => orderModal.classList.add('hidden'));
    document.getElementById('closeAdminModal').addEventListener('click', () => adminModal.classList.add('hidden'));

    // Toggle Sign Up
    const linkSignup = document.querySelector('a[href="#"]'); // The one in login modal? 
    // Need specific ID for sign up link in login modal to be safe
    // Added id="link-signup" in HTML previously? Let's assume user clicks the "Daftar disini" link

    // Fix: Locate the signup link more precisely
    const signUpLink = document.querySelector('#loginModal a');
    if (signUpLink) {
        signUpLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.classList.add('hidden');
            signupModal.classList.remove('hidden');
        });
    }

    // Header Actions
    btnLoginHeader.addEventListener('click', () => {
        const user = JSON.parse(localStorage.getItem('user'));
        if (user) {
            if (confirm('Yakin ingin logout?')) {
                localStorage.removeItem('user');
                updateLoginUI();
            }
        } else {
            loginModal.classList.remove('hidden');
        }
    });

    document.getElementById('btn-settings').addEventListener('click', () => alert('Fitur pengaturan belum tersedia.'));

    // Mobile Menu
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('hidden'); // Tailwind hidden toggle
            // Or toggle custom class if defined. Previous code used .active
            // Let's stick to standard Tailwind logic if possible or previous logic
            // Previous logic:
            hamburger.classList.toggle('toggle');
            // The nav is hidden md:block. To show it, we need to remove hidden or add a class that overrides it.
            // Simplest:
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

    // Init
    renderProducts();
    updateLoginUI();
});

