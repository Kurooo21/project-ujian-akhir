// Animasi sederhana saat scroll: Menu akan muncul satu per satu
document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll('.menu-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Memberikan delay sedikit antar kartu agar efeknya estetik
                setTimeout(() => {
                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";
                }, index * 150);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        card.style.transition = "all 0.6s ease-out";
        observer.observe(card);
    });

    // Responsive Navbar
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('toggle');
            navLinks.classList.toggle('active');
        });

        // Close menu when link is clicked
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('toggle');
                navLinks.classList.remove('active');
            });
        });
    }
});