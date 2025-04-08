<?php
$page_title = "Dashboard";
require_once 'includes/header.php';

// Get some statistics for the dashboard
try {
    // Count users
    $stmt = $db->query('SELECT COUNT(*) as count FROM users');
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count products
    $stmt = $db->query('SELECT COUNT(*) as count FROM products');
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count orders
    $stmt = $db->query('SELECT COUNT(*) as count FROM orders');
    $orderCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count categories
    $stmt = $db->query('SELECT COUNT(*) as count FROM categories');
    $categoryCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count brands
    $stmt = $db->query('SELECT COUNT(*) as count FROM brands');
    $brandCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count reviews
    $stmt = $db->query('SELECT COUNT(*) as count FROM reviews');
    $reviewCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count news
    $stmt = $db->query('SELECT COUNT(*) as count FROM news');
    $newsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get recent orders
    $stmt = $db->query('SELECT o.*, u.first_name, u.last_name FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC LIMIT 5');
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent reviews
    $stmt = $db->query('SELECT r.*, p.name as product_name, u.first_name, u.last_name 
                        FROM reviews r
                        JOIN products p ON r.product_id = p.id
                        LEFT JOIN users u ON r.user_id = u.id
                        ORDER BY r.created_at DESC LIMIT 5');
    $recentReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent products
    $stmt = $db->query('SELECT p.*, b.name as brand_name
                        FROM products p
                        LEFT JOIN brands b ON p.brand_id = b.id
                        ORDER BY p.created_at DESC LIMIT 5');
    $recentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Handle error
    $error = "Database error: " . $e->getMessage();
}
?>

<h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>

<?php if (isset($error)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mt-4" role="alert">
    <span class="block sm:inline"><?= $error ?></span>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Users -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Utilisateurs
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                <?= isset($userCount) ? $userCount : '0' ?>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a href="<?= SITE_URL ?>/cp/users/" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Voir tous les utilisateurs
                </a>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Produits
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                <?= isset($productCount) ? $productCount : '0' ?>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a href="<?= SITE_URL ?>/cp/products/" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Voir tous les produits
                </a>
            </div>
        </div>
    </div>

    <!-- Orders -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Commandes
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                <?= isset($orderCount) ? $orderCount : '0' ?>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a href="<?= SITE_URL ?>/cp/orders/" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Voir toutes les commandes
                </a>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Catégories
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                <?= isset($categoryCount) ? $categoryCount : '0' ?>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a href="<?= SITE_URL ?>/cp/categories/" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Voir toutes les catégories
                </a>
            </div>
        </div>
    </div>

    <!-- Brands -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Marques
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                <?= isset($brandCount) ? $brandCount : '0' ?>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a href="<?= SITE_URL ?>/cp/brands/" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Voir toutes les marques
                </a>
            </div>
        </div>
    </div>

    <!-- Reviews -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Avis
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                <?= isset($reviewCount) ? $reviewCount : '0' ?>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a href="<?= SITE_URL ?>/cp/reviews/" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Voir tous les avis
                </a>
            </div>
        </div>
    </div>

    <!-- News -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Actualités
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                <?= isset($newsCount) ? $newsCount : '0' ?>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a href="<?= SITE_URL ?>/cp/news/" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Voir toutes les actualités
                </a>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 mt-8 lg:grid-cols-2">
    <!-- Recent Orders -->
    <div>
        <h2 class="text-lg font-medium text-gray-900">Commandes récentes</h2>
        <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
            <?php if (isset($recentOrders) && !empty($recentOrders)): ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($recentOrders as $order): ?>
                <li>
                    <a href="<?= SITE_URL ?>/cp/orders/view.php?id=<?= $order['id'] ?>" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-indigo-600 truncate">
                                        Commande #<?= $order['id'] ?>
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                            ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                            'bg-gray-100 text-gray-800') ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm text-gray-500">
                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        <?php if (!empty($order['first_name']) && !empty($order['last_name'])): ?>
                                            <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?>
                                        <?php else: ?>
                                            <span class="italic">Utilisateur supprimé</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <p>
                                        <?= number_format($order['total'], 2, ',', ' ') ?> €
                                    </p>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="px-4 py-5 sm:px-6">
                <p class="text-gray-500">Aucune commande récente.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div>
        <h2 class="text-lg font-medium text-gray-900">Avis récents</h2>
        <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
            <?php if (isset($recentReviews) && !empty($recentReviews)): ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($recentReviews as $review): ?>
                <li>
                    <a href="<?= SITE_URL ?>/cp/reviews/edit.php?id=<?= $review['id'] ?>" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-indigo-600 truncate">
                                        <?= htmlspecialchars($review['product_name']) ?>
                                    </p>
                                    <div class="ml-3 flex">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            <?php else: ?>
                                                <svg class="h-4 w-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm text-gray-500">
                                        <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        <?php if (!empty($review['first_name']) && !empty($review['last_name'])): ?>
                                            <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                                        <?php else: ?>
                                            <span class="italic">Utilisateur supprimé</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <p class="truncate max-w-xs">
                                        <?= !empty($review['title']) ? htmlspecialchars($review['title']) : 
                                            (!empty($review['comment']) ? htmlspecialchars(substr($review['comment'], 0, 50)) . '...' : 'Aucun commentaire') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="px-4 py-5 sm:px-6">
                <p class="text-gray-500">Aucun avis récent.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Products -->
    <div>
        <h2 class="text-lg font-medium text-gray-900">Produits récents</h2>
        <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
            <?php if (isset($recentProducts) && !empty($recentProducts)): ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($recentProducts as $product): ?>
                <li>
                    <a href="<?= SITE_URL ?>/cp/products/edit.php?id=<?= $product['id'] ?>" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-indigo-600 truncate">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $product['stock'] > 10 ? 'bg-green-100 text-green-800' : 
                                            ($product['stock'] > 0 ? 'bg-yellow-100 text-yellow-800' : 
                                            'bg-red-100 text-red-800') ?>">
                                            <?= $product['stock'] > 0 ? 'En stock (' . $product['stock'] . ')' : 'Épuisé' ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm text-gray-500">
                                        <?= date('d/m/Y', strtotime($product['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        <?= !empty($product['brand_name']) ? htmlspecialchars($product['brand_name']) : 'Sans marque' ?>
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <p>
                                        <?= number_format($product['price'], 2, ',', ' ') ?> €
                                    </p>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="px-4 py-5 sm:px-6">
                <p class="text-gray-500">Aucun produit récent.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 