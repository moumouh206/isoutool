<?php
require_once __DIR__ . '/includes/init.php';

$page_title = "Catégories";
$page_description = "Découvrez notre large gamme de catégories de produits électroniques";

// Get category slug from URL
$category_slug = isset($_GET['category']) ? $_GET['category'] : null;

// Get filter parameters
$brand = isset($_GET['brand']) ? $_GET['brand'] : null;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Debug: Check table structure
$debug_query = "SHOW COLUMNS FROM products";
$debug_stmt = $db->query($debug_query);
$columns = $debug_stmt->fetchAll(PDO::FETCH_COLUMN);

// Initialize category variable
$category = null;

// Get category details if slug is provided
if ($category_slug) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = :slug");
    $stmt->execute([':slug' => $category_slug]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update page title and description based on category
if ($category) {
    $page_title = $category['name'];
    $page_description = $category['description'] ?? "Découvrez notre sélection de produits dans la catégorie " . $category['name'];
} else {
    $page_title = "Toutes les catégories";
    $page_description = "Découvrez notre large gamme de produits électroniques et industriels";
}

// Build the base query
$query = "
    SELECT p.id, p.reference, p.name, p.slug, p.description, p.price, p.stock, p.featured,
           b.name as brand_name, b.slug as brand_slug,
           GROUP_CONCAT(DISTINCT c.name) as category_names
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_category pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
";

$where_clauses = [];
$params = [];

// Add category filter if specified
if ($category_slug) {
    $where_clauses[] = "c.slug = :category_slug";
    $params[':category_slug'] = $category_slug;
}

// Add brand filter if specified
if ($brand) {
    $where_clauses[] = "b.slug = :brand_slug";
    $params[':brand_slug'] = $brand;
}

// Add price range filter if specified
if ($min_price !== null) {
    $where_clauses[] = "p.price >= :min_price";
    $params[':min_price'] = $min_price;
}
if ($max_price !== null) {
    $where_clauses[] = "p.price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Add WHERE clause if we have any filters
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add GROUP BY
$query .= " GROUP BY p.id, p.reference, p.name, p.slug, p.description, p.price, p.stock, p.featured, b.name, b.slug";

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $query .= " ORDER BY p.name ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM products";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $per_page);

// Add pagination
$query .= " LIMIT :offset, :per_page";
$params[':offset'] = $offset;
$params[':per_page'] = $per_page;

// Get products
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all brands for the filter
$stmt = $db->query("SELECT * FROM brands ORDER BY name ASC");
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for the sidebar
$stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full md:w-1/8">
            <div class="bg-white p-3 rounded-lg shadow-md">
                <h3 class="text-base font-semibold mb-3">Catégories</h3>
                <ul class="space-y-1">
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="?category=<?= htmlspecialchars($cat['slug']) ?>" 
                           class="block py-1 px-2 rounded text-sm <?= ($category_slug === $cat['slug']) ? 'bg-rs-red text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                </div>

            <div class="bg-white p-3 rounded-lg shadow-md mt-4">
                <h3 class="text-base font-semibold mb-3">Filtres</h3>
                
                <!-- Brand Filter -->
                <div class="mb-3">
                    <h4 class="font-medium mb-1 text-sm">Marques</h4>
                    <select name="brand" class="w-full p-1 border rounded text-sm" onchange="this.form.submit()">
                        <option value="">Toutes les marques</option>
                        <?php foreach ($brands as $b): ?>
                        <option value="<?= htmlspecialchars($b['slug']) ?>" <?= ($brand === $b['slug']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Price Range Filter -->
                <div class="mb-3">
                    <h4 class="font-medium mb-1 text-sm">Prix</h4>
                    <div class="flex gap-1">
                        <input type="number" name="min_price" placeholder="Min" 
                               value="<?= $min_price ?>" class="w-1/2 p-1 border rounded text-sm">
                        <input type="number" name="max_price" placeholder="Max" 
                               value="<?= $max_price ?>" class="w-1/2 p-1 border rounded text-sm">
                    </div>
                </div>

                <!-- Sort Filter -->
                <div class="mb-3">
                    <h4 class="font-medium mb-1 text-sm">Trier par</h4>
                    <select name="sort" class="w-full p-1 border rounded text-sm" onchange="this.form.submit()">
                        <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Plus récent</option>
                        <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Prix croissant</option>
                        <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Prix décroissant</option>
                        <option value="name" <?= ($sort === 'name') ? 'selected' : '' ?>>Nom</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-rs-red text-white py-1 px-3 rounded text-sm hover:bg-red-700">
                    Appliquer les filtres
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-7/8">
            <h1 class="text-3xl font-bold mb-6">
                <?= $category ? htmlspecialchars($category['name']) : 'Toutes les catégories' ?>
            </h1>

            <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <p class="text-gray-600">Aucun produit trouvé avec ces critères.</p>
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
                    <a href="?page=<?= $page - 1 ?>&category=<?= $category_slug ?>&brand=<?= $brand ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 border rounded hover:bg-gray-100">
                        Précédent
                    </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&category=<?= $category_slug ?>&brand=<?= $brand ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 border rounded <?= $i === $page ? 'bg-rs-red text-white' : 'hover:bg-gray-100' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&category=<?= $category_slug ?>&brand=<?= $brand ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 border rounded hover:bg-gray-100">
                        Suivant
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
