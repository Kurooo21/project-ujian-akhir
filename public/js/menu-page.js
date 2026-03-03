// ========================================================================
// Chi-Pok - Menu Page Script
// ========================================================================
// Renders all products on the dedicated /menu page (no carousel, full grid)

let products = PRODUCTS_DATA;
let currentCategory = 'semua';

// Helper: Format Rupiah
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
}

document.addEventListener("DOMContentLoaded", () => {
    const menuGrid = document.getElementById('full-menu-grid');
    const productCount = document.getElementById('product-count');

    function getFilteredProducts() {
        if (currentCategory === 'semua') return products;
        return products.filter(p => (p.category || 'makanan') === currentCategory);
    }

    function renderMenuProducts() {
        menuGrid.innerHTML = '';
        const filtered = getFilteredProducts();

        // Update count
        productCount.textContent = `Menampilkan ${filtered.length} menu`;

        if (filtered.length === 0) {
            menuGrid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <i class="fas fa-utensils text-6xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-400 text-lg">Belum ada menu di kategori ini.</p>
                </div>`;
            return;
        }

        filtered.forEach((product, index) => {
            const card = document.createElement('div');
            card.className = "bg-white rounded-2xl p-4 text-center shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-xl relative group flex flex-col justify-between overflow-hidden border border-gray-100";

            // Rating HTML
            let ratingHtml = '';
            if (product.reviews && product.reviews.length > 0) {
                const totalReviews = product.reviews.length;
                const sum = product.reviews.reduce((acc, curr) => acc + parseInt(curr.rating), 0);
                const avgRating = (sum / totalReviews).toFixed(1);
                let stars = '';
                const fullStars = Math.floor(avgRating);
                const halfStar = avgRating % 1 >= 0.5 ? 1 : 0;
                for (let i = 0; i < fullStars; i++) stars += '<i class="fas fa-star text-yellow-500"></i>';
                if (halfStar) stars += '<i class="fas fa-star-half-alt text-yellow-500"></i>';
                ratingHtml = `
                    <div class="flex justify-center items-center gap-1 text-sm mt-2">
                        <div class="flex text-yellow-500">${stars}</div>
                        <span class="font-bold text-text-dark ml-1">${avgRating}</span>
                        <span class="text-gray-400 text-xs">(${totalReviews})</span>
                    </div>`;
            } else {
                ratingHtml = `<div class="flex justify-center items-center gap-1 text-sm mt-2 text-gray-400"><i class="far fa-star"></i> <span class="text-xs">Belum ada ulasan</span></div>`;
            }

            // Badge
            let badgeHtml = '';
            if (product.badge) {
                badgeHtml = `<div class="absolute top-2 left-2 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10 tracking-wide shadow-sm">${product.badge}</div>`;
            }

            // Category
            const categoryIcon = (product.category || 'makanan') === 'minuman' ? 'fa-glass-water' : 'fa-drumstick-bite';
            const categoryLabel = (product.category || 'makanan') === 'minuman' ? 'Minuman' : 'Makanan';
            const categoryColor = (product.category || 'makanan') === 'minuman' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600';

            const imgSrc = product.image.startsWith('http') ? product.image : '/' + product.image;

            card.innerHTML = `
                <div class="w-full aspect-square bg-gray-50 rounded-xl mb-4 flex items-center justify-center p-4 relative overflow-hidden">
                    ${badgeHtml}
                    <img src="${imgSrc}" alt="${product.name}" class="w-full h-full object-contain drop-shadow transition-transform duration-500 group-hover:scale-110">
                </div>
                <div class="flex flex-col flex-grow">
                    <div class="flex justify-center mb-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${categoryColor}">
                            <i class="fas ${categoryIcon} text-[8px]"></i> ${categoryLabel}
                        </span>
                    </div>
                    <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-2 leading-tight">${product.name}</h3>
                    <p class="text-gray-500 text-xs mb-3 line-clamp-2 h-8">${product.desc}</p>
                    ${ratingHtml}
                    <div class="flex justify-center items-center mt-auto pt-4 border-t border-gray-50">
                        <span class="font-extrabold text-lg text-red-600">${formatRupiah(product.price)}</span>
                    </div>
                </div>`;

            // Animation
            card.style.opacity = "0";
            card.style.transform = "translateY(20px)";
            setTimeout(() => {
                card.style.transition = "all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)";
                card.style.opacity = "1";
                card.style.transform = "translateY(0)";
            }, index * 60);

            menuGrid.appendChild(card);
        });
    }

    // Category tab click
    const tabs = document.querySelectorAll('.menu-category-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            currentCategory = tab.dataset.category;

            tabs.forEach(t => {
                t.classList.remove('bg-primary-red', 'text-white', 'active-tab');
                t.classList.add('bg-white', 'text-text-dark', 'border', 'border-gray-200');
            });
            tab.classList.add('bg-primary-red', 'text-white', 'active-tab');
            tab.classList.remove('bg-white', 'text-text-dark', 'border', 'border-gray-200');

            renderMenuProducts();
        });
    });

    // Init
    renderMenuProducts();
});
