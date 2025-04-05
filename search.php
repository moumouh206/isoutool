<?php
require_once __DIR__ . '/includes/init.php';

$page_title = "Recherche";
$page_description = "Recherchez parmi nos produits";

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build search query
$search_query = "
    SELECT p.*, b.name as brand_name, b.slug as brand_slug,
           GROUP_CONCAT(DISTINCT c.name) as category_names
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_category pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    WHERE p.name LIKE :query OR p.description LIKE :query OR p.reference LIKE :query
    GROUP BY p.id, p.reference, p.name, p.slug, p.description, p.price, p.stock, p.featured, b.name, b.slug
    ORDER BY p.name ASC
    LIMIT :offset, :per_page
";

$params = [
    ':query' => "%$query%",
    ':offset' => $offset,
    ':per_page' => $per_page
];

// Get total count
$count_query = "
    SELECT COUNT(DISTINCT p.id) as total
    FROM products p
    WHERE p.name LIKE :query OR p.description LIKE :query OR p.reference LIKE :query
";

$stmt = $db->prepare($count_query);
$stmt->execute([':query' => "%$query%"]);
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$stmt = $db->prepare($search_query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Résultats de recherche pour "<?= htmlspecialchars($query) ?>"</h1>

    <?php if (empty($products)): ?>
    <div class="text-center py-12">
        <p class="text-gray-600">Aucun produit trouvé pour votre recherche.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($products as $product): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <img src="<?= SITE_URL ?>/assets/images/products/<?= htmlspecialchars($product['reference']) ?>.jpg" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="w-full h-48 object-cover">
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-sm text-gray-500"><?= htmlspecialchars($product['reference']) ?></span>
                    <span class="text-sm font-medium <?= $product['stock'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $product['stock'] > 0 ? 'En stock' : 'Rupture de stock' ?>
                    </span>
                </div>
                <h3 class="text-lg font-semibold text-rs-gray mb-2">
                    <?= htmlspecialchars($product['name']) ?>
                </h3>
                <p class="text-sm text-gray-600 mb-2">
                    <?= htmlspecialchars($product['brand_name']) ?>
                </p>
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-rs-red">
                        <?= number_format($product['price'], 2, ',', ' ') ?> €
                    </span>
                    <a href="?action=add&id=<?= $product['id'] ?>" 
                       class="bg-rs-red text-white py-2 px-4 rounded hover:bg-red-700">
                        Ajouter au panier
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="mt-8 flex justify-center">
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>" 
               class="px-4 py-2 border rounded hover:bg-gray-100">
                Précédent
            </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $i ?>" 
               class="px-4 py-2 border rounded <?= $i === $page ? 'bg-rs-red text-white' : 'hover:bg-gray-100' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>" 
               class="px-4 py-2 border rounded hover:bg-gray-100">
                Suivant
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?> 