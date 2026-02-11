// Animasi sederhana saat scroll: Menu akan muncul satu per satu
document.addEventListener("DOMContentLoaded", () => {
    // --- Animation Logic ---
    const cards = document.querySelectorAll('.product-card'); // Changed selector to match HTML class

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        card.style.transition = "all 0.6s ease-out";
        observer.observe(card);
    });

    // --- Responsive Navbar ---
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('toggle');
            navLinks.classList.toggle('active');
        });

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('toggle');
                navLinks.classList.remove('active');
            });
        });
    }

    // --- Checkout & Login Logic ---
    const loginModal = document.getElementById('loginModal');
    const orderModal = document.getElementById('orderModal');
    const closeLoginBtn = document.getElementById('closeLoginModal');
    const closeOrderBtn = document.getElementById('closeModal');
    const loginForm = document.getElementById('loginForm');
    const orderForm = document.getElementById('orderForm');

    const btnLoginHeader = document.getElementById('btn-login');
    const btnSettings = document.getElementById('btn-settings');
    const loginIcon = btnLoginHeader.querySelector('i');

    // Elements in Order Modal
    const inputNamaItem = document.getElementById('pesanan_item');
    const inputHargaSatuan = document.getElementById('harga_satuan');
    const displayHargaSatuan = document.getElementById('display_harga_satuan');
    const inputJumlah = document.getElementById('jumlah');
    const displayTotal = document.getElementById('display_total_harga');
    const inputTotal = document.getElementById('total_harga');

    // Pre-fill fields for Google User
    const inputNamaPelanggan = document.querySelector('input[name="nama"]');

    let currentItem = { name: '', price: 0 };

    // Function to update UI based on login status
    function updateLoginUI() {
        // Check for user object in localStorage
        const user = JSON.parse(localStorage.getItem('user'));

        if (user) {
            loginIcon.classList.remove('fa-sign-in-alt');
            loginIcon.classList.add('fa-sign-out-alt');
            btnLoginHeader.title = "Logout (" + (user.name || user.username) + ")";
        } else {
            loginIcon.classList.remove('fa-sign-out-alt');
            loginIcon.classList.add('fa-sign-in-alt');
            btnLoginHeader.title = "Login";
        }
    }

    // Initialize UI
    updateLoginUI();

    // Header Login/Logout Button
    btnLoginHeader.addEventListener('click', () => {
        const user = JSON.parse(localStorage.getItem('user'));
        if (user) {
            if (confirm('Halo, ' + (user.name || user.username) + '! Apakah Anda yakin ingin logout?')) {
                localStorage.removeItem('user');
                alert('Anda telah logout.');
                updateLoginUI();
            }
        } else {
            showLoginModal();
        }
    });

    // Settings Button
    btnSettings.addEventListener('click', () => {
        alert('Fitur Pengaturan sedang dalam pengembangan.\nSilakan cek kembali nanti!');
    });

    // Buy Button Click Handler
    document.querySelectorAll('.btn-cart').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            // Check login status
            const user = JSON.parse(localStorage.getItem('user'));

            // Get Product Details
            const card = btn.closest('.product-card');
            const name = card.querySelector('h3').innerText;
            const priceText = card.querySelector('.price').innerText;
            const price = parseInt(priceText.replace(/[^0-9]/g, '')); // Remove Rp, dots, etc.

            currentItem = { name, price };

            if (!user) {
                showLoginModal();
            } else {
                showOrderModal();
            }
        });
    });

    // Login Functionality
    function showLoginModal() {
        loginModal.classList.remove('hidden');
    }

    function hideLoginModal() {
        loginModal.classList.add('hidden');
    }

    closeLoginBtn.addEventListener('click', hideLoginModal);

    // Manual Login
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value; // In real app, verify password!
        if (username) {
            const user = { username: username, name: username }; // Simple mock user logic
            localStorage.setItem('user', JSON.stringify(user));
            alert('Login Manual Berhasil! Selamat datang, ' + username + '.');
            updateLoginUI();
            hideLoginModal();

            if (currentItem.name) showOrderModal();
        }
    });

    // Google Login Handler (Global Scope for Callback)
    window.handleCredentialResponse = (response) => {
        const responsePayload = decodeJwtResponse(response.credential);

        console.log("ID: " + responsePayload.sub);
        console.log('Full Name: ' + responsePayload.name);
        console.log('Given Name: ' + responsePayload.given_name);
        console.log('Family Name: ' + responsePayload.family_name);
        console.log("Image URL: " + responsePayload.picture);
        console.log("Email: " + responsePayload.email);

        // Store user info
        const user = {
            name: responsePayload.name,
            email: responsePayload.email,
            picture: responsePayload.picture,
            googleId: responsePayload.sub
        };

        localStorage.setItem('user', JSON.stringify(user));
        alert('Login Google Berhasil! Selamat datang, ' + user.name + '.');

        updateLoginUI();
        hideLoginModal();

        if (currentItem.name) showOrderModal();
    }

    // JWT Decoder
    function decodeJwtResponse(token) {
        var base64Url = token.split('.')[1];
        var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function (c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    }

    // Order Functionality
    function showOrderModal() {
        orderModal.classList.remove('hidden');

        // Auto-fill Name if available from Google Login
        const user = JSON.parse(localStorage.getItem('user'));
        if (user && user.name) {
            if (inputNamaPelanggan) inputNamaPelanggan.value = user.name;
        }

        // Populate Form
        inputNamaItem.value = currentItem.name;
        inputHargaSatuan.value = currentItem.price;
        displayHargaSatuan.innerText = formatRupiah(currentItem.price);
        inputJumlah.value = 1;
        updateTotal();
    }

    function hideOrderModal() {
        orderModal.classList.add('hidden');
    }

    closeOrderBtn.addEventListener('click', hideOrderModal);

    // Calculate Total dynamically
    inputJumlah.addEventListener('input', updateTotal);
    inputJumlah.addEventListener('change', updateTotal);

    function updateTotal() {
        const qty = parseInt(inputJumlah.value) || 1;
        const total = qty * currentItem.price;

        displayTotal.innerText = formatRupiah(total);
        inputTotal.value = total;
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
    }

    // Handle outside click to close modals
    window.addEventListener('click', (e) => {
        if (e.target === loginModal) hideLoginModal();
        if (e.target === orderModal) hideOrderModal();
    });
});
