/**
 * Hero Slider - Auto-playing image slideshow for the header
 */
document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.hero-slide');
    const prevBtn = document.getElementById('hero-prev');
    const nextBtn = document.getElementById('hero-next');
    const dotsContainer = document.getElementById('hero-dots');

    if (slides.length === 0) return;

    let currentSlide = 0;
    let autoPlayInterval;
    const AUTO_PLAY_DELAY = 4000; // 4 detik

    // Tampilkan navigasi jika ada lebih dari 1 slide
    if (slides.length > 1) {
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');
        dotsContainer.classList.remove('hidden');

        // Buat dots
        slides.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.className = 'hero-dot w-3 h-3 rounded-full transition-all duration-300 border border-white/50 ' +
                (index === 0 ? 'bg-white scale-125' : 'bg-white/40 hover:bg-white/60');
            dot.addEventListener('click', () => goToSlide(index));
            dotsContainer.appendChild(dot);
        });
    }

    function goToSlide(index) {
        // Sembunyikan slide saat ini
        slides[currentSlide].style.opacity = '0';

        // Tampilkan slide baru
        currentSlide = index;
        slides[currentSlide].style.opacity = '1';

        // Update dots
        updateDots();

        // Reset auto-play timer
        resetAutoPlay();
    }

    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        goToSlide(next);
    }

    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        goToSlide(prev);
    }

    function updateDots() {
        const dots = dotsContainer.querySelectorAll('.hero-dot');
        dots.forEach((dot, index) => {
            if (index === currentSlide) {
                dot.className = 'hero-dot w-3 h-3 rounded-full transition-all duration-300 border border-white/50 bg-white scale-125';
            } else {
                dot.className = 'hero-dot w-3 h-3 rounded-full transition-all duration-300 border border-white/50 bg-white/40 hover:bg-white/60';
            }
        });
    }

    function startAutoPlay() {
        if (slides.length > 1) {
            autoPlayInterval = setInterval(nextSlide, AUTO_PLAY_DELAY);
        }
    }

    function resetAutoPlay() {
        clearInterval(autoPlayInterval);
        startAutoPlay();
    }

    // Event listeners
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);

    // Pause auto-play saat hover di hero section
    const heroSection = document.getElementById('home');
    if (heroSection && slides.length > 1) {
        heroSection.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
        heroSection.addEventListener('mouseleave', startAutoPlay);
    }

    // Mulai auto-play
    startAutoPlay();
});
