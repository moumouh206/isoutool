<?php
$page_title = "Produits";
require_once '../includes/header.php';

// Handle product deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    try {
        // Check if the product exists in any order
        $stmt = $db->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
        $stmt->execute([$product_id]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            $error = "Impossible de supprimer ce produit car il est référencé dans des commandes";
        } else {
            // First, delete product from product_category table
            $stmt = $db->prepare('DELETE FROM product_category WHERE product_id = ?');
            $stmt->execute([$product_id]);
            
            // Then delete product specifications
            $stmt = $db->prepare('DELETE FROM product_specifications WHERE product_id = ?');
            $stmt->execute([$product_id]);
            
            // Delete product images and records
            $stmt = $db->prepare('SELECT image_path FROM product_images WHERE product_id = ?');
            $stmt->execute([$product_id]);
            $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($images as $image) {
                $image_path = ROOT_PATH . '/public/uploads/products/' . $image;
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $stmt = $db->prepare('DELETE FROM product_images WHERE product_id = ?');
            $stmt->execute([$product_id]);
            
            // Delete reviews
            $stmt = $db->prepare('DELETE FROM reviews WHERE product_id = ?');
            $stmt->execute([$product_id]);
            
            // Finally delete the product
            $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            
            $message = "Produit supprimé avec succès";
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Get filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : '';
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : '';
$stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';

// Get the list of products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

try {
    // Build the query based on filters
    $whereClause = [];
    $params = [];
    
    if (!empty($search)) {
        $whereClause[] = '(p.name LIKE ? OR p.reference LIKE ? OR p.description LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($category_id)) {
        $whereClause[] = 'pc.category_id = ?';
        $params[] = $category_id;
    }
    
    if (!empty($brand_id)) {
        $whereClause[] = 'p.brand_id = ?';
        $params[] = $brand_id;
    }
    
    if ($stock_status === 'in_stock') {
        $whereClause[] = 'p.stock > 0';
    } elseif ($stock_status === 'out_of_stock') {
        $whereClause[] = 'p.stock = 0';
    } elseif ($stock_status === 'low_stock') {
        $whereClause[] = 'p.stock > 0 AND p.stock <= 10';
    }
    
    $whereStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';
    
    // Get categories for filter dropdown
    $stmt = $db->query('SELECT id, name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll();
    
    // Get brands for filter dropdown
    $stmt = $db->query('SELECT id, name FROM brands ORDER BY name');
    $brands = $stmt->fetchAll();
    
    // Get total products count
    $countQuery = "
        SELECT COUNT(DISTINCT p.id) 
        FROM products p
        LEFT JOIN product_category pc ON p.id = pc.product_id
        LEFT JOIN brands b ON p.brand_id = b.id
        $whereStr
    ";
    
    $stmt = $db->prepare($countQuery);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $totalProducts = $stmt->fetchColumn();
    
    // Get products for current page
    $query = "
        SELECT DISTINCT p.*, 
               b.name as brand_name,
               (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as image,
               (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c 
                JOIN product_category pc ON c.id = pc.category_id 
                WHERE pc.product_id = p.id) as categories
        FROM products p
        LEFT JOIN product_category pc ON p.id = pc.product_id
        LEFT JOIN brands b ON p.brand_id = b.id
        $whereStr
        GROUP BY p.id
        ORDER BY p.id DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($query);
    
    // Bind parameters for filters and pagination
    $i = 1;
    foreach ($params as $param) {
        $stmt->bindValue($i++, $param);
    }
    $stmt->bindValue($i++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($i++, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // Calculate total pages
    $totalPages = ceil($totalProducts / $perPage);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des produits : " . $e->getMessage();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Produits</h1>
    <div class="flex space-x-2">
        <a href="<?= SITE_URL ?>/cp/products/export.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
           class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Exporter CSV
        </a>
        <a href="<?= SITE_URL ?>/cp/products/add.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter un produit
        </a>
    </div>
</div>

<?php if (isset($message)): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline"><?= $message ?></span>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline"><?= $error ?></span>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6 mb-6">
    <form method="GET" action="" class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700">Recherche</label>
            <input type="text" name="search" id="search" placeholder="Nom, référence..."
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie</label>
            <select name="category_id" id="category_id" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Toutes les catégories</option>
                <?php if (isset($categories)): foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        
        <div>
            <label for="brand_id" class="block text-sm font-medium text-gray-700">Marque</label>
            <select name="brand_id" id="brand_id" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Toutes les marques</option>
                <?php if (isset($brands)): foreach ($brands as $b): ?>
                <option value="<?= $b['id'] ?>" <?= $brand_id == $b['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b['name']) ?>
                </option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        
        <div>
            <label for="stock_status" class="block text-sm font-medium text-gray-700">Stock</label>
            <select name="stock_status" id="stock_status" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Tous les niveaux</option>
                <option value="in_stock" <?= $stock_status === 'in_stock' ? 'selected' : '' ?>>En stock</option>
                <option value="out_of_stock" <?= $stock_status === 'out_of_stock' ? 'selected' : '' ?>>Épuisé</option>
                <option value="low_stock" <?= $stock_status === 'low_stock' ? 'selected' : '' ?>>Stock faible (<= 10)</option>
            </select>
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" class="flex-grow inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Filtrer
            </button>
            <a href="<?= SITE_URL ?>/cp/products/" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Réinitialiser
            </a>
        </div>
    </form>
</div>

<!-- Products Count -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-4 mb-6">
    <p class="text-gray-700">
        <span class="font-medium"><?= $totalProducts ?></span> produit<?= $totalProducts > 1 ? 's' : '' ?> trouvé<?= $totalProducts > 1 ? 's' : '' ?>
        <?php if (!empty($search)): ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            Recherche: <?= htmlspecialchars($search) ?>
        </span>
        <?php endif; ?>
        
        <?php if (!empty($category_id)): ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            <?php 
                foreach ($categories as $cat) {
                    if ($cat['id'] == $category_id) {
                        echo 'Catégorie: ' . htmlspecialchars($cat['name']);
                        break;
                    }
                }
            ?>
        </span>
        <?php endif; ?>
        
        <?php if (!empty($brand_id)): ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            <?php 
                foreach ($brands as $b) {
                    if ($b['id'] == $brand_id) {
                        echo 'Marque: ' . htmlspecialchars($b['name']);
                        break;
                    }
                }
            ?>
        </span>
        <?php endif; ?>
        
        <?php if (!empty($stock_status)): ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            Stock: 
            <?php 
                switch ($stock_status) {
                    case 'in_stock':
                        echo 'En stock';
                        break;
                    case 'out_of_stock':
                        echo 'Épuisé';
                        break;
                    case 'low_stock':
                        echo 'Stock faible';
                        break;
                }
            ?>
        </span>
        <?php endif; ?>
    </p>
</div>

<!-- Products Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Image
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nom
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Catégories
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Marque
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Prix
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stock
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (isset($products) && !empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $product['id'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if (!empty($product['image'])): ?>
                        <img src="<?= SITE_URL ?>/public/uploads/products/<?= $product['image'] ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="h-12 w-12 object-cover rounded">
                        <?php else: ?>
                        <div class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($product['name']) ?>
                        </div>
                        <div class="text-sm text-gray-500">
                            Réf: <?= htmlspecialchars($product['reference'] ?? 'N/A') ?>
                        </div>
                        <?php if ($product['featured']): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            En vedette
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($product['categories'] ?? 'Non catégorisé') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($product['brand_name'] ?? 'Non défini') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                        <?= number_format($product['price'], 2, ',', ' ') ?> €
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= $product['stock'] > 10 ? 'bg-green-100 text-green-800' : 
                              ($product['stock'] > 0 ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-red-100 text-red-800') ?>">
                            <?= $product['stock'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= SITE_URL ?>/cp/products/edit.php?id=<?= $product['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            Modifier
                        </a>
                        <a href="<?= SITE_URL ?>/cp/products/index.php?delete=<?= $product['id'] ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')" 
                           class="text-red-600 hover:text-red-900">
                            Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                    Aucun produit trouvé
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
<div class="mt-4 flex justify-center">
    <nav class="inline-flex rounded-md shadow">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category_id) ? '&category_id=' . $category_id : '' ?><?= !empty($brand_id) ? '&brand_id=' . $brand_id : '' ?><?= !empty($stock_status) ? '&stock_status=' . $stock_status : '' ?>" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
            Précédent
        </a>
        <?php endif; ?>
        
        <?php 
        // Display a limited number of page links
        $maxLinks = 5;
        $startPage = max(1, min($page - floor($maxLinks / 2), $totalPages - $maxLinks + 1));
        $endPage = min($startPage + $maxLinks - 1, $totalPages);
        
        if ($startPage > 1): 
        ?>
        <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category_id) ? '&category_id=' . $category_id : '' ?><?= !empty($brand_id) ? '&brand_id=' . $brand_id : '' ?><?= !empty($stock_status) ? '&stock_status=' . $stock_status : '' ?>" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
            1
        </a>
        <?php if ($startPage > 2): ?>
        <span class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">...</span>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category_id) ? '&category_id=' . $category_id : '' ?><?= !empty($brand_id) ? '&brand_id=' . $brand_id : '' ?><?= !empty($stock_status) ? '&stock_status=' . $stock_status : '' ?>" 
           class="px-4 py-2 text-sm font-medium <?= $i === $page ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 bg-white hover:bg-gray-50' ?> border border-gray-300">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
        <span class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">...</span>
        <?php endif; ?>
        <a href="?page=<?= $totalPages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category_id) ? '&category_id=' . $category_id : '' ?><?= !empty($brand_id) ? '&brand_id=' . $brand_id : '' ?><?= !empty($stock_status) ? '&stock_status=' . $stock_status : '' ?>" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
            <?= $totalPages ?>
        </a>
        <?php endif; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category_id) ? '&category_id=' . $category_id : '' ?><?= !empty($brand_id) ? '&brand_id=' . $brand_id : '' ?><?= !empty($stock_status) ? '&stock_status=' . $stock_status : '' ?>" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
            Suivant
        </a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 