<?php
require_once __DIR__ . '/../config/config.php';

// Normally this would come from a database
$product = [
    'id' => 123456,
    'reference' => 'RS-123456',
    'name' => 'Microcontrôleur STM32F411RE Nucleo-64',
    'brand' => 'STMicroelectronics',
    'price' => 12.95,
    'discount_price' => null,
    'stock' => 156,
    'description' => 'Carte de développement STM32 Nucleo-64 avec microcontrôleur STM32F411RE ARM Cortex-M4. Compatible Arduino, cette carte est idéale pour le prototypage rapide et le développement d\'applications embarquées.',
    'specifications' => [
        'Microcontrôleur' => 'STM32F411RE',
        'Architecture' => 'ARM Cortex-M4',
        'Fréquence' => '100 MHz',
        'Mémoire Flash' => '512 KB',
        'Mémoire RAM' => '128 KB',
        'Tension d\'alimentation' => '3.3V / 5V',
        'GPIO' => '50 pins',
        'Interfaces' => 'USB, SPI, I2C, UART, ADC, PWM',
        'Dimensions' => '70 x 82 mm'
    ],
    'features' => [
        'Compatible avec l\'écosystème Arduino',
        'Programmation via USB (ST-LINK/V2-1)',
        'Connecteurs d\'extension pour shields Arduino',
        'Alimentation via USB ou externe',
        'LED programmable',
        'Bouton reset et bouton utilisateur'
    ],
    'rating' => 4.7,
    'reviews' => 42,
    'images' => [
        'product5.jpg',
        'product5-2.jpg',
        'product5-3.jpg',
        'product5-4.jpg'
    ]
];

$page_title = $product['name'];
$page_description = "Achetez " . $product['name'] . " au meilleur prix chez RS Components France";

// Set breadcrumbs for navigation
$breadcrumbs = [
    'Produits par catégorie' => '#',
    'Composants électroniques' => '#',
    'Microcontrôleurs' => '#',
    $product['name'] => ''
];

include_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Product Detail -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 p-6">
            <!-- Product Images -->
            <div class="lg:col-span-1">
                <div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                    <img src="<?= SITE_URL ?>/assets/images/<?= $product['images'][0] ?>" alt="<?= $product['name'] ?>" class="w-full h-auto" id="main-product-image">
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <?php foreach($product['images'] as $index => $image): ?>
                    <div class="border border-gray-200 rounded-lg overflow-hidden <?= $index === 0 ? 'border-rs-red' : '' ?>">
                        <img src="<?= SITE_URL ?>/assets/images/<?= $image ?>" alt="<?= $product['name'] ?>" class="w-full h-auto cursor-pointer product-thumbnail">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="lg:col-span-2">
                <div class="mb-6">
                    <div class="flex items-center mb-2">
                        <span class="text-sm text-gray-500 mr-4">Réf: <?= $product['reference'] ?></span>
                        <div class="flex items-center">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= floor($product['rating'])): ?>
                                    <i class="fas fa-star text-yellow-400"></i>
                                <?php elseif($i - 0.5 <= $product['rating']): ?>
                                    <i class="fas fa-star-half-alt text-yellow-400"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-yellow-400"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span class="text-sm text-gray-500 ml-2">(<?= $product['reviews'] ?> avis)</span>
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold text-rs-gray mb-2"><?= $product['name'] ?></h1>
                    <p class="text-gray-600 mb-4">Marque: <a href="#" class="text-rs-red hover:underline"><?= $product['brand'] ?></a></p>
                    <p class="text-gray-700 mb-6"><?= $product['description'] ?></p>

                    <!-- Price and Stock -->
                    <div class="mb-6">
                        <?php if($product['discount_price']): ?>
                            <div class="flex items-center mb-1">
                                <span class="text-xl font-bold text-rs-red"><?= number_format($product['discount_price'], 2, ',', ' ') ?> €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                                <span class="text-sm text-gray-500 line-through ml-2"><?= number_format($product['price'], 2, ',', ' ') ?> € HT</span>
                            </div>
                            <span class="bg-rs-red text-white text-xs px-2 py-1 rounded">Économisez <?= number_format($product['price'] - $product['discount_price'], 2, ',', ' ') ?> €</span>
                        <?php else: ?>
                            <div class="flex items-center mb-1">
                                <span class="text-2xl font-bold text-rs-red"><?= number_format($product['price'], 2, ',', ' ') ?> €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                            </div>
                        <?php endif; ?>

                        <div class="mt-2">
                            <?php if($product['stock'] > 50): ?>
                                <span class="flex items-center text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i> En stock (<?= $product['stock'] ?> disponibles)
                                </span>
                            <?php elseif($product['stock'] > 0): ?>
                                <span class="flex items-center text-yellow-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Stock limité (<?= $product['stock'] ?> disponibles)
                                </span>
                            <?php else: ?>
                                <span class="flex items-center text-red-600">
                                    <i class="fas fa-times-circle mr-1"></i> Rupture de stock
                                </span>
                            <?php endif; ?>
                            <p class="text-sm text-gray-600 mt-1">Livraison sous 24h pour toute commande passée avant 18h</p>
                        </div>
                    </div>

                    <!-- Add to Cart -->
                    <div class="mb-6">
                        <div class="flex items-center">
                            <div class="mr-4 flex">
                                <button class="border border-gray-300 px-3 py-2 rounded-l quantity-minus">
                                    <i class="fas fa-minus text-gray-500"></i>
                                </button>
                                <input type="number" value="1" min="1" class="border-t border-b border-gray-300 w-16 text-center quantity-input">
                                <button class="border border-gray-300 px-3 py-2 rounded-r quantity-plus">
                                    <i class="fas fa-plus text-gray-500"></i>
                                </button>
                            </div>
                            <button class="btn-primary flex-grow py-2 add-to-cart-btn">
                                <i class="fas fa-cart-plus mr-2"></i> Ajouter au panier
                            </button>
                        </div>
                        <div class="flex mt-4">
                            <button class="flex items-center justify-center bg-white border border-gray-300 rounded py-2 px-4 mr-2 hover:bg-gray-50 transition">
                                <i class="far fa-heart mr-2 text-rs-gray"></i> Ajouter aux favoris
                            </button>
                            <button class="flex items-center justify-center bg-white border border-gray-300 rounded py-2 px-4 hover:bg-gray-50 transition">
                                <i class="fas fa-file-download mr-2 text-rs-gray"></i> Télécharger la fiche technique
                            </button>
                        </div>
                    </div>

                    <!-- Delivery Options -->
                    <div class="bg-rs-light-gray p-4 rounded">
                        <h3 class="font-bold text-rs-gray mb-2">Options de livraison</h3>
                        <ul class="space-y-2">
                            <li class="flex items-center text-sm">
                                <i class="fas fa-truck text-rs-red mr-2"></i> Livraison standard: 1-2 jours ouvrés (gratuite dès 50€ HT)
                            </li>
                            <li class="flex items-center text-sm">
                                <i class="fas fa-shipping-fast text-rs-red mr-2"></i> Livraison express: Lendemain avant 13h (9,90€ HT)
                            </li>
                            <li class="flex items-center text-sm">
                                <i class="fas fa-store text-rs-red mr-2"></i> Retrait en agence: Disponible sous 2h
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-t border-gray-200 px-6 py-4">
            <div class="flex border-b border-gray-200">
                <button class="py-2 px-4 text-rs-red border-b-2 border-rs-red font-medium">Caractéristiques</button>
                <button class="py-2 px-4 text-gray-600 hover:text-rs-red font-medium">Documentation</button>
                <button class="py-2 px-4 text-gray-600 hover:text-rs-red font-medium">Avis (<?= $product['reviews'] ?>)</button>
                <button class="py-2 px-4 text-gray-600 hover:text-rs-red font-medium">FAQ</button>
            </div>

            <!-- Tab Content -->
            <div class="py-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Specifications -->
                    <div>
                        <h2 class="text-lg font-bold text-rs-gray mb-4">Spécifications techniques</h2>
                        <table class="w-full">
                            <tbody>
                                <?php foreach($product['specifications'] as $key => $value): ?>
                                <tr class="border-b border-gray-200">
                                    <td class="py-2 text-gray-600 font-medium"><?= $key ?></td>
                                    <td class="py-2 text-gray-800"><?= $value ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Features -->
                    <div>
                        <h2 class="text-lg font-bold text-rs-gray mb-4">Caractéristiques principales</h2>
                        <ul class="space-y-2">
                            <?php foreach($product['features'] as $feature): ?>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                <span class="text-gray-800"><?= $feature ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-rs-gray mb-6">Produits similaires</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Product 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="#" class="block">
                    <img src="<?= SITE_URL ?>/assets/images/product11.jpg" alt="Produit similaire 1" class="w-full h-48 object-contain p-4">
                    <div class="p-4 border-t">
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-green-600 font-medium">En stock</span>
                            <span class="text-xs text-gray-500">Réf: RS-123789</span>
                        </div>
                        <h3 class="font-medium text-rs-gray mb-2 hover:text-rs-red transition">Microcontrôleur ESP32 DevKit</h3>
                        <div class="text-sm text-gray-500 mb-3">Marque: Espressif</div>
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-lg font-bold text-rs-red">8,99 €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                            </div>
                            <button class="btn-primary py-1 px-3 text-sm add-to-cart-btn">
                                <i class="fas fa-cart-plus mr-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Product 2 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="#" class="block">
                    <img src="<?= SITE_URL ?>/assets/images/product12.jpg" alt="Produit similaire 2" class="w-full h-48 object-contain p-4">
                    <div class="p-4 border-t">
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-green-600 font-medium">En stock</span>
                            <span class="text-xs text-gray-500">Réf: RS-456123</span>
                        </div>
                        <h3 class="font-medium text-rs-gray mb-2 hover:text-rs-red transition">Carte de développement Arduino Mega 2560</h3>
                        <div class="text-sm text-gray-500 mb-3">Marque: Arduino</div>
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-lg font-bold text-rs-red">34,95 €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                            </div>
                            <button class="btn-primary py-1 px-3 text-sm add-to-cart-btn">
                                <i class="fas fa-cart-plus mr-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Product 3 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="#" class="block">
                    <img src="<?= SITE_URL ?>/assets/images/product13.jpg" alt="Produit similaire 3" class="w-full h-48 object-contain p-4">
                    <div class="p-4 border-t">
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-green-600 font-medium">En stock</span>
                            <span class="text-xs text-gray-500">Réf: RS-789456</span>
                        </div>
                        <h3 class="font-medium text-rs-gray mb-2 hover:text-rs-red transition">Microcontrôleur PIC18F4550</h3>
                        <div class="text-sm text-gray-500 mb-3">Marque: Microchip</div>
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-lg font-bold text-rs-red">7,50 €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                            </div>
                            <button class="btn-primary py-1 px-3 text-sm add-to-cart-btn">
                                <i class="fas fa-cart-plus mr-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Product 4 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="#" class="block">
                    <img src="<?= SITE_URL ?>/assets/images/product14.jpg" alt="Produit similaire 4" class="w-full h-48 object-contain p-4">
                    <div class="p-4 border-t">
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-yellow-600 font-medium">Stock limité</span>
                            <span class="text-xs text-gray-500">Réf: RS-321654</span>
                        </div>
                        <h3 class="font-medium text-rs-gray mb-2 hover:text-rs-red transition">Carte Raspberry Pi 4 Modèle B 2GB</h3>
                        <div class="text-sm text-gray-500 mb-3">Marque: Raspberry Pi</div>
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-lg font-bold text-rs-red">45,90 €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                            </div>
                            <button class="btn-primary py-1 px-3 text-sm add-to-cart-btn">
                                <i class="fas fa-cart-plus mr-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Recently Viewed -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-rs-gray mb-6">Récemment consultés</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Product 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="#" class="block">
                    <img src="<?= SITE_URL ?>/assets/images/product3.jpg" alt="Kit Arduino Uno" class="w-full h-48 object-contain p-4">
                    <div class="p-4 border-t">
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-green-600 font-medium">En stock</span>
                            <span class="text-xs text-gray-500">Réf: RS-345678</span>
                        </div>
                        <h3 class="font-medium text-rs-gray mb-2 hover:text-rs-red transition">Kit Arduino Uno Rev3</h3>
                        <div class="text-sm text-gray-500 mb-3">Marque: Arduino</div>
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-lg font-bold text-rs-red">24,95 €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                            </div>
                            <button class="btn-primary py-1 px-3 text-sm add-to-cart-btn">
                                <i class="fas fa-cart-plus mr-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Product 2 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="#" class="block">
                    <img src="<?= SITE_URL ?>/assets/images/product4.jpg" alt="Multimètre" class="w-full h-48 object-contain p-4">
                    <div class="p-4 border-t">
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-green-600 font-medium">En stock</span>
                            <span class="text-xs text-gray-500">Réf: RS-901234</span>
                        </div>
                        <h3 class="font-medium text-rs-gray mb-2 hover:text-rs-red transition">Multimètre numérique RS PRO</h3>
                        <div class="text-sm text-gray-500 mb-3">Marque: RS PRO</div>
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-lg font-bold text-rs-red">79,99 €</span>
                                <span class="text-xs text-gray-500 ml-1">HT</span>
                            </div>
                            <button class="btn-primary py-1 px-3 text-sm add-to-cart-btn">
                                <i class="fas fa-cart-plus mr-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
