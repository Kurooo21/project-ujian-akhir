/**
 * ========================================================================
 * HERO SLIDER - Slideshow Otomatis untuk Gambar Header
 * ========================================================================
 *
 * File ini mengatur fitur slider/slideshow gambar di bagian hero (atas halaman).
 * Fitur utama:
 * - Ganti slide otomatis setiap 1 detik
 * - Tombol navigasi kiri/kanan (prev/next)
 * - Titik-titik (dots) untuk navigasi langsung ke slide tertentu
 * - Pause otomatis saat mouse hover di area hero
 *
 * CARA KERJA:
 * 1. Ambil semua elemen slide dari HTML (class 'hero-slide')
 * 2. Buat dots navigasi sesuai jumlah slide
 * 3. Jalankan auto-play (ganti slide otomatis)
 * 4. Tambahkan event listener untuk tombol prev/next dan hover
 */

// Tunggu sampai seluruh halaman HTML selesai dimuat sebelum menjalankan script
// 'DOMContentLoaded' = event yang terjadi ketika HTML sudah siap (tapi gambar/css mungkin belum)
document.addEventListener('DOMContentLoaded', function () {

    // ====================================================================
    // AMBIL ELEMEN-ELEMEN HTML YANG DIBUTUHKAN
    // ====================================================================

    // Ambil semua slide (div dengan class 'hero-slide') dan simpan dalam array-like object
    const slides = document.querySelectorAll('.hero-slide');

    // Ambil tombol navigasi prev (kiri) dan next (kanan) berdasarkan ID
    const prevBtn = document.getElementById('hero-prev');
    const nextBtn = document.getElementById('hero-next');

    // Ambil container dots (titik navigasi) berdasarkan ID
    const dotsContainer = document.getElementById('hero-dots');

    // Jika tidak ada slide sama sekali, hentikan script (tidak perlu slider)
    if (slides.length === 0) return;

    // ====================================================================
    // VARIABEL UNTUK MENGONTROL SLIDER
    // ====================================================================

    let currentSlide = 0;           // Index slide yang sedang aktif (mulai dari 0)
    let autoPlayInterval;           // Menyimpan ID interval untuk auto-play (agar bisa di-stop)
    const AUTO_PLAY_DELAY = 5000;   // Jeda antar slide dalam milidetik (1000ms = 1 detik)
    // Ubah angka ini jika ingin mempercepat/memperlambat slide

    // ====================================================================
    // BUAT DOTS NAVIGASI (jika ada lebih dari 1 slide)
    // ====================================================================

    if (slides.length > 1) {
        // Tampilkan tombol prev, next, dan dots (awalnya hidden di HTML)
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');
        dotsContainer.classList.remove('hidden');

        // Loop setiap slide untuk membuat 1 dot per slide
        slides.forEach((_, index) => {
            // Buat elemen <button> untuk dot
            const dot = document.createElement('button');

            // Set class CSS untuk dot
            // Dot pertama (index 0) dibuat aktif (bg-white, lebih besar)
            // Dot lainnya dibuat transparan (bg-white/40)
            dot.className = 'hero-dot w-3 h-3 rounded-full transition-all duration-300 border border-white/50 ' +
                (index === 0 ? 'bg-white scale-125' : 'bg-white/40 hover:bg-white/60');

            // Saat dot diklik, pindah ke slide yang sesuai
            dot.addEventListener('click', () => goToSlide(index));

            // Tambahkan dot ke dalam container dots
            dotsContainer.appendChild(dot);
        });
    }

    // ====================================================================
    // FUNGSI-FUNGSI UTAMA SLIDER
    // ====================================================================

    /**
     * goToSlide(index) - Pindah ke slide tertentu
     * @param {number} index - Nomor slide tujuan (dimulai dari 0)
     *
     * Cara kerja:
     * 1. Sembunyikan slide saat ini (opacity = 0, jadi transparan/hilang)
     * 2. Tampilkan slide baru (opacity = 1, jadi terlihat)
     * 3. Update tampilan dots
     * 4. Reset timer auto-play
     */
    function goToSlide(index) {
        // Sembunyikan slide yang sedang aktif dengan mengubah opacity jadi 0
        slides[currentSlide].style.opacity = '0';

        // Set slide baru sebagai slide aktif
        currentSlide = index;

        // Tampilkan slide baru dengan mengubah opacity jadi 1
        slides[currentSlide].style.opacity = '1';

        // Update tampilan dots (yang aktif dibuat terang)
        updateDots();

        // Reset auto-play supaya timer mulai ulang
        resetAutoPlay();
    }

    /**
     * nextSlide() - Pindah ke slide berikutnya
     *
     * Menggunakan operator modulo (%) agar kembali ke slide pertama
     * setelah slide terakhir. Contoh: jika ada 3 slide (0,1,2)
     * dan currentSlide = 2, maka (2 + 1) % 3 = 0 (kembali ke awal)
     */
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        goToSlide(next);
    }

    /**
     * prevSlide() - Pindah ke slide sebelumnya
     *
     * Menambahkan slides.length sebelum modulo untuk menghindari angka negatif.
     * Contoh: jika currentSlide = 0 dan ada 3 slide,
     * maka (0 - 1 + 3) % 3 = 2 (ke slide terakhir)
     */
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        goToSlide(prev);
    }

    /**
     * updateDots() - Update tampilan visual dots
     *
     * Dot yang aktif: warna putih penuh, ukuran lebih besar (scale-125)
     * Dot yang tidak aktif: warna putih transparan, ukuran normal
     */
    function updateDots() {
        // Ambil semua dots dari container
        const dots = dotsContainer.querySelectorAll('.hero-dot');

        dots.forEach((dot, index) => {
            if (index === currentSlide) {
                // Dot aktif: warna solid putih, ukuran besar
                dot.className = 'hero-dot w-3 h-3 rounded-full transition-all duration-300 border border-white/50 bg-white scale-125';
            } else {
                // Dot tidak aktif: warna transparan, ukuran normal
                dot.className = 'hero-dot w-3 h-3 rounded-full transition-all duration-300 border border-white/50 bg-white/40 hover:bg-white/60';
            }
        });
    }

    /**
     * startAutoPlay() - Mulai auto-play slider
     *
     * setInterval() memanggil fungsi nextSlide() setiap AUTO_PLAY_DELAY milidetik
     * Hanya berjalan jika ada lebih dari 1 slide
     */
    function startAutoPlay() {
        if (slides.length > 1) {
            autoPlayInterval = setInterval(nextSlide, AUTO_PLAY_DELAY);
        }
    }

    /**
     * resetAutoPlay() - Reset timer auto-play
     *
     * Dipanggil setiap kali user klik navigasi secara manual
     * agar timer mulai ulang dari awal (jadi tidak langsung pindah slide)
     */
    function resetAutoPlay() {
        clearInterval(autoPlayInterval);  // Hentikan interval yang sedang berjalan
        startAutoPlay();                   // Mulai interval baru
    }

    // ====================================================================
    // EVENT LISTENERS (Pendengar Aksi Pengguna)
    // ====================================================================

    // Saat tombol prev (kiri) diklik, pindah ke slide sebelumnya
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);

    // Saat tombol next (kanan) diklik, pindah ke slide berikutnya
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);

    // ====================================================================
    // PAUSE SAAT HOVER (Mouse di atas Hero Section)
    // ====================================================================

    // Ambil elemen hero section
    const heroSection = document.getElementById('home');

    if (heroSection && slides.length > 1) {
        // Saat mouse masuk ke area hero → pause auto-play (berhenti ganti slide)
        heroSection.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));

        // Saat mouse keluar dari area hero → lanjutkan auto-play
        heroSection.addEventListener('mouseleave', startAutoPlay);
    }

    // ====================================================================
    // MULAI AUTO-PLAY SAAT HALAMAN DIMUAT
    // ====================================================================
    startAutoPlay();
});
