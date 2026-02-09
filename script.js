document.addEventListener('DOMContentLoaded', () => {
    // Mobile Menu Toggle
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('toggle');
        });
    }

    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            navLinks.classList.remove('active'); // Close menu on click

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Scroll Animation (Fade In Up)
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const elementsToAnimate = document.querySelectorAll('.product-card, .section-title, .hero-text, .contact-left, .contact-right');
    elementsToAnimate.forEach(el => {
        el.classList.add('fade-start');
        observer.observe(el);
    });
});
