<?php
require_once __DIR__ . '/includes/init.php';

$page_title = "Accueil";
$page_description = "RS Components France - Solutions industrielles et composants électroniques";

// Fetch featured products
$stmt = $db->query("
    SELECT p.*, b.name as brand_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE p.featured = 1 
    ORDER BY p.created_at DESC 
    LIMIT 4
");
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch brands
$stmt = $db->query("SELECT * FROM brands ORDER BY name ASC");
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest news
$stmt = $db->query("
    SELECT * FROM news 
    ORDER BY published_at DESC 
    LIMIT 3
");
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Slider -->
<section class="bg-white">
    <div class="relative" id="hero-slider">
        <!-- Main slider -->
        <div class="overflow-hidden">
            <div class="relative">
                <div class="slider-container">
                    <!-- Slide 1 -->
                    <div class="slider-slide active">
                        <img src="https://placehold.co/1920x500/1a365d/ffffff?text=Solutions+industrielles" alt="Solutions industrielles" class="w-full h-auto md:h-96 object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center">
                            <div class="container mx-auto px-4">
                                <div class="max-w-xl text-white">
                                    <h1 class="text-3xl md:text-4xl font-bold mb-4 animate-fade-in">Solutions industrielles et composants électroniques</h1>
                                    <p class="mb-6 animate-fade-in-up">Plus de 500 000 produits disponibles avec livraison le jour même pour les commandes passées avant 18h</p>
                                    <a href="<?= SITE_URL ?>/categories.php" class="btn-primary animate-fade-in-up">Découvrir nos produits</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2 -->
                    <div class="slider-slide">
                        <img src="https://placehold.co/1920x500/1a365d/ffffff?text=Promotions" alt="Promotions" class="w-full h-auto md:h-96 object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center">
                            <div class="container mx-auto px-4">
                                <div class="max-w-xl text-white">
                                    <h1 class="text-3xl md:text-4xl font-bold mb-4 animate-fade-in">Promotions du moment</h1>
                                    <p class="mb-6 animate-fade-in-up">Profitez de nos offres spéciales sur une large sélection de produits</p>
                                    <a href="<?= SITE_URL ?>/products.php?sort=price_asc" class="btn-primary animate-fade-in-up">Voir les promotions</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3 -->
                    <div class="slider-slide">
                        <img src="https://placehold.co/1920x500/1a365d/ffffff?text=Nouveautés" alt="Nouveautés" class="w-full h-auto md:h-96 object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center">
                            <div class="container mx-auto px-4">
                                <div class="max-w-xl text-white">
                                    <h1 class="text-3xl md:text-4xl font-bold mb-4 animate-fade-in">Nouveautés</h1>
                                    <p class="mb-6 animate-fade-in-up">Découvrez nos dernières arrivées et innovations</p>
                                    <a href="<?= SITE_URL ?>/products.php?sort=newest" class="btn-primary animate-fade-in-up">Voir les nouveautés</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 4 -->
                    <div class="slider-slide">
                        <img src="https://placehold.co/1920x500/1a365d/ffffff?text=Support+technique" alt="Support technique" class="w-full h-auto md:h-96 object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center">
                            <div class="container mx-auto px-4">
                                <div class="max-w-xl text-white">
                                    <h1 class="text-3xl md:text-4xl font-bold mb-4 animate-fade-in">Support technique</h1>
                                    <p class="mb-6 animate-fade-in-up">Une équipe d'experts à votre disposition pour vous accompagner</p>
                                    <a href="<?= SITE_URL ?>/support.php" class="btn-primary animate-fade-in-up">Contactez-nous</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slider controls -->
        <button class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-70 rounded-full p-2 transition slider-prev">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>
        <button class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-70 rounded-full p-2 transition slider-next">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>

        <!-- Slider indicators -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 slider-indicators">
            <button class="w-3 h-3 rounded-full bg-white bg-opacity-100 transition-all duration-300"></button>
            <button class="w-3 h-3 rounded-full bg-white bg-opacity-50 transition-all duration-300"></button>
            <button class="w-3 h-3 rounded-full bg-white bg-opacity-50 transition-all duration-300"></button>
            <button class="w-3 h-3 rounded-full bg-white bg-opacity-50 transition-all duration-300"></button>
        </div>
    </div>
</section>

<style>
.slider-container {
    position: relative;
    width: 100%;
    height: 500px;
}

.slider-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
    visibility: hidden;
}

.slider-slide.active {
    opacity: 1;
    visibility: visible;
}

#hero-slider {
    position: relative;
    height: 500px;
    overflow: hidden;
}

#hero-slider img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Animation classes */
.animate-fade-in {
    animation: fadeIn 0.8s ease-out;
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Slider indicator styles */
.slider-indicators button {
    transition: all 0.3s ease;
}

.slider-indicators button.active {
    background-color: white;
    opacity: 1;
    transform: scale(1.2);
}

.slider-indicators button:not(.active) {
    background-color: white;
    opacity: 0.5;
    transform: scale(1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('hero-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.slider-slide');
    const prevBtn = slider.querySelector('.slider-prev');
    const nextBtn = slider.querySelector('.slider-next');
    const indicators = slider.querySelectorAll('.slider-indicators button');
    
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        // Remove active class from all slides
        slides.forEach(slide => {
            slide.classList.remove('active');
            // Reset animations
            const elements = slide.querySelectorAll('.animate-fade-in, .animate-fade-in-up');
            elements.forEach(el => {
                el.style.animation = 'none';
                el.offsetHeight; // Trigger reflow
                el.style.animation = null;
            });
        });

        // Remove active class from all indicators
        indicators.forEach(indicator => {
            indicator.classList.remove('active');
            indicator.style.opacity = '0.5';
            indicator.style.transform = 'scale(1)';
        });
        
        // Add active class to current slide
        slides[index].classList.add('active');
        
        // Add active class to current indicator
        indicators[index].classList.add('active');
        indicators[index].style.opacity = '1';
        indicators[index].style.transform = 'scale(1.2)';
        
        currentSlide = index;
    }

    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }

    function prevSlide() {
        const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }

    function startSlider() {
        stopSlider();
        slideInterval = setInterval(nextSlide, 5000);
    }

    function stopSlider() {
        if (slideInterval) {
            clearInterval(slideInterval);
        }
    }

    // Event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            stopSlider();
            prevSlide();
            startSlider();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            stopSlider();
            nextSlide();
            startSlider();
        });
    }

    if (indicators) {
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                stopSlider();
                showSlide(index);
                startSlider();
            });
        });
    }

    // Initialize the slider
    showSlide(0);
    startSlider();

    // Pause on hover
    slider.addEventListener('mouseenter', stopSlider);
    slider.addEventListener('mouseleave', startSlider);
});
</script>

<!-- Categories Grid -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-8 text-rs-gray text-center">Nos catégories de produits</h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($categories as $category): ?>
            <a href="<?= SITE_URL ?>/categories.php?slug=<?= $category['slug'] ?>" class="group">
                <div class="bg-rs-light-gray rounded-lg p-4 text-center transition transform group-hover:-translate-y-1 group-hover:shadow-md">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-microchip text-4xl text-rs-red"></i>
                    </div>
                    <h3 class="font-medium text-rs-gray group-hover:text-rs-red transition"><?= htmlspecialchars($category['name']) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-12 bg-rs-light-gray">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-rs-gray">Produits populaires</h2>
            <a href="<?= SITE_URL ?>/products.php" class="text-rs-red hover:text-red-700 font-medium">Voir tous les produits</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($featured_products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="<?= SITE_URL ?>/product-detail.php?slug=<?= $product['slug'] ?>" class="block">
                    <div class="relative">
                        <img src="<?= SITE_URL ?>/assets/images/products/<?= $product['reference'] ?>.jpg" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-full h-48 object-contain p-4">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="favorites.php?action=add&id=<?= $product['id'] ?>" 
                           class="absolute top-2 right-2 text-red-500 hover:text-red-700 bg-white bg-opacity-80 rounded-full p-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 border-t">
                        <div class="flex justify-between mb-1">
                            <span class="text-xs <?= $product['stock'] > 0 ? 'text-green-600' : 'text-red-600' ?> font-medium">
                                <?= $product['stock'] > 0 ? 'En stock' : 'Rupture de stock' ?>
                            </span>
                            <span class="text-xs text-gray-500">Réf: <?= $product['reference'] ?></span>
                        </div>
                        <h3 class="font-medium text-rs-gray mb-2 hover:text-rs-red transition">
                            <?= htmlspecialchars($product['name']) ?>
                        </h3>
                        <div class="text-sm text-gray-500 mb-3">Marque: <?= htmlspecialchars($product['brand_name']) ?></div>
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-lg font-bold text-rs-red"><?= number_format($product['price'], 2, ',', ' ') ?> €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                            </div>
                            <?php if ($product['stock'] > 0): ?>
                            <form method="POST" action="cart.php" class="inline">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-primary py-1 px-3 text-sm">
                                    <i class="fas fa-cart-plus mr-1"></i> Ajouter
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Brands -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-8 text-rs-gray text-center">Nos marques partenaires</h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($brands as $brand): ?>
            <div class="p-4 flex items-center justify-center border border-gray-200 rounded">
                <img src="<?= SITE_URL ?>/assets/images/brands/<?= $brand['slug'] ?>.png" 
                     alt="<?= htmlspecialchars($brand['name']) ?>" 
                     class="max-h-12">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- News Section -->
<section class="py-12 bg-rs-light-gray">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-rs-gray">Actualités</h2>
            <a href="<?= SITE_URL ?>/news.php" class="text-rs-red hover:text-red-700 font-medium">Voir toutes les actualités</a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($news as $item): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="<?= SITE_URL ?>/assets/images/news/<?= htmlspecialchars($item['image']) ?>" 
                     alt="<?= htmlspecialchars($item['title']) ?>" 
                     class="w-full h-48 object-cover">
                <div class="p-4">
                    <span class="text-sm text-gray-500">
                        <?= date('d F Y', strtotime($item['published_at'])) ?>
                    </span>
                    <h3 class="text-xl font-bold text-rs-gray mt-2 mb-3">
                        <?= htmlspecialchars($item['title']) ?>
                    </h3>
                    <p class="text-gray-600 mb-4">
                        <?= htmlspecialchars($item['content']) ?>
                    </p>
                    <a href="<?= SITE_URL ?>/news.php?slug=<?= $item['slug'] ?>" 
                       class="text-rs-red hover:text-red-700 font-medium">
                        Lire la suite →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
