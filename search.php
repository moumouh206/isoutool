<?php
require_once 'includes/init.php';

// Get search query
$search_query = htmlspecialchars($_GET['q'] ?? '');
$page_title = 'Recherche: ' . $search_query;
$breadcrumbs = ['Recherche' => null];

// Initialize products array
$products = [];

// If we have a search query, search for products
if (!empty($search_query)) {
    try {
        // Search in products table (this query depends on your database structure)
        $stmt = $db->prepare("
            SELECT * FROM products 
            WHERE name LIKE :search 
            OR description LIKE :search 
            OR reference LIKE :search
            OR brand LIKE :search
            LIMIT 20
        ");
        $stmt->execute(['search' => '%' . $search_query . '%']);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle error (log it, but don't show to user)
        error_log("Search error: " . $e->getMessage());
    }
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-rs-gray mb-6">
        <?= empty($search_query) ? 'Recherche' : 'Résultats pour "' . $search_query . '"' ?>
    </h1>

    <!-- Search form for the search page -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form action="<?= SITE_URL ?>/search.php" method="GET" class="flex">
            <input type="text" name="q" value="<?= $search_query ?>" 
                   placeholder="Rechercher des produits, des marques, des références..."
                   class="flex-1 py-2 px-4 border border-gray-300 rounded-l focus:outline-none focus:ring-2 focus:ring-rs-red">
            <button type="submit" class="bg-rs-red text-white px-6 py-2 rounded-r hover:bg-red-700 transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Rechercher
            </button>
        </form>
    </div>

    <?php if (empty($search_query)): ?>
        <!-- Display when no search query is provided -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-rs-gray mb-4">Catégories populaires</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="#" class="p-4 border rounded-lg hover:bg-rs-light-gray text-center">
                    <span class="block font-medium">Composants électroniques</span>
                </a>
                <a href="#" class="p-4 border rounded-lg hover:bg-rs-light-gray text-center">
                    <span class="block font-medium">Outillage</span>
                </a>
                <a href="#" class="p-4 border rounded-lg hover:bg-rs-light-gray text-center">
                    <span class="block font-medium">Électricité</span>
                </a>
                <a href="#" class="p-4 border rounded-lg hover:bg-rs-light-gray text-center">
                    <span class="block font-medium">Connectique</span>
                </a>
            </div>
        </div>
    <?php elseif (empty($products)): ?>
        <!-- No results found -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <p class="text-gray-600">Aucun résultat trouvé pour "<?= $search_query ?>". Essayez d'autres termes de recherche.</p>
            
            <h3 class="text-lg font-medium mt-6 mb-3">Suggestions :</h3>
            <ul class="list-disc pl-5 text-gray-600">
                <li>Vérifiez l'orthographe des termes de recherche.</li>
                <li>Essayez des mots-clés plus généraux.</li>
                <li>Essayez d'autres catégories.</li>
            </ul>
        </div>
    <?php else: ?>
        <!-- Display search results -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <div class="aspect-w-1 aspect-h-1 mb-4">
                    <img src="<?= !empty($product['image']) ? $product['image'] : SITE_URL . '/public/images/placeholder.jpg' ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         class="object-contain w-full h-40">
                </div>
                <h3 class="font-medium text-lg mb-2">
                    <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" class="text-rs-gray hover:text-rs-red">
                        <?= htmlspecialchars($product['name']) ?>
                    </a>
                </h3>
                <p class="text-gray-600 text-sm mb-2">Réf. <?= htmlspecialchars($product['reference'] ?? 'N/A') ?></p>
                <p class="text-rs-red font-bold mb-3">
                    <?= htmlspecialchars(number_format($product['price'], 2, ',', ' ')) ?> €
                </p>
                <button class="w-full bg-rs-red text-white py-2 rounded hover:bg-red-700 transition">
                    Ajouter au panier
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 