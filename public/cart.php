<?php
require_once __DIR__ . '/../config/config.php';

$page_title = "Panier";
$page_description = "Votre panier d'achat - RS Components France";

// Normally this would come from a database and session
$cart_items = [
    [
        'id' => 123456,
        'reference' => 'RS-123456',
        'name' => 'Microcontrôleur STM32F411RE Nucleo-64',
        'brand' => 'STMicroelectronics',
        'price' => 12.95,
        'quantity' => 2,
        'image' => 'product5.jpg'
    ],
    [
        'id' => 789012,
        'reference' => 'RS-789012',
        'name' => 'Kit Arduino Uno Rev3',
        'brand' => 'Arduino',
        'price' => 24.95,
        'quantity' => 1,
        'image' => 'product3.jpg'
    ]
];

// Calculate totals
$subtotal = 0;
$total_items = 0;

foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
}

$shipping = $subtotal >= 50 ? 0 : 6.95;
$tax_rate = 0.20; // 20% VAT
$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping + $tax;

include_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-rs-gray mb-6">Votre panier (<?= $total_items ?> article<?= $total_items > 1 ? 's' : '' ?>)</h1>

    <?php if(count($cart_items) > 0): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <!-- Cart Header -->
                <div class="bg-rs-light-gray p-4 border-b border-gray-200">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-6">
                            <span class="font-medium text-rs-gray">Produit</span>
                        </div>
                        <div class="col-span-2 text-center">
                            <span class="font-medium text-rs-gray">Prix unitaire</span>
                        </div>
                        <div class="col-span-2 text-center">
                            <span class="font-medium text-rs-gray">Quantité</span>
                        </div>
                        <div class="col-span-2 text-right">
                            <span class="font-medium text-rs-gray">Total</span>
                        </div>
                    </div>
                </div>

                <!-- Cart Items -->
                <?php foreach($cart_items as $item): ?>
                <div class="p-4 border-b border-gray-200">
                    <div class="grid grid-cols-12 gap-4 items-center">
                        <div class="col-span-6">
                            <div class="flex items-center">
                                <div class="w-16 h-16 flex-shrink-0">
                                    <img src="<?= SITE_URL ?>/assets/images/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" class="w-full h-full object-contain">
                                </div>
                                <div class="ml-4">
                                    <a href="#" class="font-medium text-rs-gray hover:text-rs-red"><?= $item['name'] ?></a>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <span>Réf: <?= $item['reference'] ?></span>
                                        <span class="mx-2">|</span>
                                        <span>Marque: <?= $item['brand'] ?></span>
                                    </div>
                                    <div class="flex mt-2">
                                        <button class="text-rs-red hover:underline text-sm">Supprimer</button>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <button class="text-rs-red hover:underline text-sm">Ajouter aux favoris</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-2 text-center">
                            <span class="font-medium text-rs-gray"><?= number_format($item['price'], 2, ',', ' ') ?> € HT</span>
                        </div>
                        <div class="col-span-2 text-center">
                            <div class="flex items-center justify-center">
                                <button class="border border-gray-300 rounded-l px-2 py-1 quantity-minus">
                                    <i class="fas fa-minus text-xs text-gray-500"></i>
                                </button>
                                <input type="number" value="<?= $item['quantity'] ?>" min="1" class="border-t border-b border-gray-300 w-10 text-center text-sm quantity-input">
                                <button class="border border-gray-300 rounded-r px-2 py-1 quantity-plus">
                                    <i class="fas fa-plus text-xs text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-span-2 text-right">
                            <span class="font-medium text-rs-gray"><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> € HT</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Cart Actions -->
                <div class="p-4 flex flex-col sm:flex-row justify-between items-center">
                    <div class="mb-4 sm:mb-0">
                        <a href="<?= SITE_URL ?>" class="flex items-center text-rs-red hover:underline">
                            <i class="fas fa-arrow-left mr-2"></i> Continuer mes achats
                        </a>
                    </div>
                    <div class="flex">
                        <button class="border border-gray-300 rounded px-4 py-2 mr-2 hover:bg-gray-50 text-sm">
                            <i class="far fa-file-pdf mr-1"></i> Devis
                        </button>
                        <button class="border border-gray-300 rounded px-4 py-2 hover:bg-gray-50 text-sm">
                            <i class="fas fa-redo-alt mr-1"></i> Mettre à jour
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recommended Products -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-rs-gray mb-4">Vous pourriez également être intéressé par</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- Product 1 -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <a href="#" class="block">
                            <img src="<?= SITE_URL ?>/assets/images/product4.jpg" alt="Multimètre" class="w-full h-32 object-contain p-2">
                            <div class="p-3 border-t">
                                <h3 class="font-medium text-sm text-rs-gray hover:text-rs-red truncate">Multimètre numérique RS PRO</h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="font-bold text-rs-red text-sm">79,99 € HT</span>
                                    <button class="btn-primary py-1 px-2 text-xs add-to-cart-btn">
                                        <i class="fas fa-cart-plus mr-1"></i> Ajouter
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Product 2 -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <a href="#" class="block">
                            <img src="<?= SITE_URL ?>/assets/images/product6.jpg" alt="Condensateurs" class="w-full h-32 object-contain p-2">
                            <div class="p-3 border-t">
                                <h3 class="font-medium text-sm text-rs-gray hover:text-rs-red truncate">Kit de condensateurs céramiques 100 pcs</h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="font-bold text-rs-red text-sm">14,50 € HT</span>
                                    <button class="btn-primary py-1 px-2 text-xs add-to-cart-btn">
                                        <i class="fas fa-cart-plus mr-1"></i> Ajouter
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Product 3 -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <a href="#" class="block">
                            <img src="<?= SITE_URL ?>/assets/images/product7.jpg" alt="Résistances" class="w-full h-32 object-contain p-2">
                            <div class="p-3 border-t">
                                <h3 class="font-medium text-sm text-rs-gray hover:text-rs-red truncate">Kit de résistances 1/4W 500 pcs</h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="font-bold text-rs-red text-sm">18,75 € HT</span>
                                    <button class="btn-primary py-1 px-2 text-xs add-to-cart-btn">
                                        <i class="fas fa-cart-plus mr-1"></i> Ajouter
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-rs-gray">Récapitulatif de la commande</h2>
                </div>
                <div class="p-4">
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sous-total (HT)</span>
                            <span class="font-medium text-rs-gray"><?= number_format($subtotal, 2, ',', ' ') ?> €</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Frais de livraison (HT)</span>
                            <?php if($shipping > 0): ?>
                                <span class="font-medium text-rs-gray"><?= number_format($shipping, 2, ',', ' ') ?> €</span>
                            <?php else: ?>
                                <span class="font-medium text-green-500">Gratuit</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">TVA (20%)</span>
                            <span class="font-medium text-rs-gray"><?= number_format($tax, 2, ',', ' ') ?> €</span>
                        </div>
                        <div class="pt-3 border-t border-gray-200 flex justify-between">
                            <span class="font-bold text-rs-gray">Total (TTC)</span>
                            <span class="font-bold text-rs-red text-xl"><?= number_format($total, 2, ',', ' ') ?> €</span>
                        </div>
                    </div>

                    <button class="btn-primary w-full py-3 mb-4">
                        <i class="fas fa-lock mr-2"></i> Procéder au paiement
                    </button>

                    <div class="flex items-center justify-center space-x-2 mb-4">
                        <img src="<?= SITE_URL ?>/assets/images/visa.png" alt="Visa" class="h-8">
                        <img src="<?= SITE_URL ?>/assets/images/mastercard.png" alt="Mastercard" class="h-8">
                        <img src="<?= SITE_URL ?>/assets/images/amex.png" alt="American Express" class="h-8">
                        <img src="<?= SITE_URL ?>/assets/images/paypal.png" alt="PayPal" class="h-8">
                    </div>

                    <div class="text-center text-xs text-gray-500">
                        <span>Paiement 100% sécurisé</span>
                    </div>
                </div>
            </div>

            <!-- Promo Code -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-rs-gray">Code promo</h2>
                </div>
                <div class="p-4">
                    <div class="flex">
                        <input type="text" placeholder="Entrez votre code" class="border border-gray-300 rounded-l px-4 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-rs-red">
                        <button class="bg-rs-red text-white px-4 py-2 rounded-r hover:bg-red-700 transition">Appliquer</button>
                    </div>
                </div>
            </div>

            <!-- Need Help -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-rs-gray">Besoin d'aide ?</h2>
                </div>
                <div class="p-4">
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="flex items-center text-rs-gray hover:text-rs-red">
                                <i class="fas fa-phone-alt mr-2 text-rs-red"></i>
                                <span>0 825 034 034</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-rs-gray hover:text-rs-red">
                                <i class="fas fa-envelope mr-2 text-rs-red"></i>
                                <span>service.client@rs-components.fr</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-rs-gray hover:text-rs-red">
                                <i class="fas fa-comments mr-2 text-rs-red"></i>
                                <span>Chat en direct</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-rs-gray hover:text-rs-red">
                                <i class="fas fa-question-circle mr-2 text-rs-red"></i>
                                <span>FAQ</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center bg-rs-light-gray rounded-full">
            <i class="fas fa-shopping-cart text-4xl text-rs-gray"></i>
        </div>
        <h2 class="text-xl font-bold text-rs-gray mb-4">Votre panier est vide</h2>
        <p class="text-gray-600 mb-6">Parcourez notre catalogue et ajoutez des produits à votre panier.</p>
        <a href="<?= SITE_URL ?>" class="btn-primary inline-block">
            <i class="fas fa-shopping-bag mr-2"></i> Explorer nos produits
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
