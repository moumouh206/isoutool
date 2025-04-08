<?php
$page_title = "Avis produits";
require_once '../includes/header.php';

// Handle review deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $review_id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare('DELETE FROM reviews WHERE id = ?');
        $stmt->execute([$review_id]);
        $message = "Avis supprimé avec succès";
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Get product filter if set
$product_filter = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;

// Get the list of reviews with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Get total reviews count (with product filter if applicable)
    if ($product_filter) {
        $stmt = $db->prepare('SELECT COUNT(*) FROM reviews WHERE product_id = ?');
        $stmt->execute([$product_filter]);
    } else {
        $stmt = $db->query('SELECT COUNT(*) FROM reviews');
    }
    $totalReviews = $stmt->fetchColumn();
    
    // Get reviews for current page with product and user info
    $query = '
        SELECT r.*, p.name as product_name, u.first_name, u.last_name, u.email
        FROM reviews r
        LEFT JOIN products p ON r.product_id = p.id
        LEFT JOIN users u ON r.user_id = u.id
    ';
    
    if ($product_filter) {
        $query .= ' WHERE r.product_id = ?';
        $query .= ' ORDER BY r.created_at DESC LIMIT ? OFFSET ?';
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $product_filter, PDO::PARAM_INT);
        $stmt->bindParam(2, $perPage, PDO::PARAM_INT);
        $stmt->bindParam(3, $offset, PDO::PARAM_INT);
    } else {
        $query .= ' ORDER BY r.created_at DESC LIMIT ? OFFSET ?';
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $perPage, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $reviews = $stmt->fetchAll();
    
    // Calculate total pages
    $totalPages = ceil($totalReviews / $perPage);
    
    // Get list of products for the filter dropdown
    $stmt = $db->query('SELECT id, name FROM products ORDER BY name');
    $products = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des avis : " . $e->getMessage();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Avis produits</h1>
    <a href="<?= SITE_URL ?>/cp/reviews/add.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
        Ajouter un avis
    </a>
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

<!-- Filter -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-4 mb-6">
    <form method="GET" action="" class="flex items-end gap-4">
        <div class="w-full">
            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par produit</label>
            <select id="product_id" name="product_id" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous les produits</option>
                <?php if (isset($products)): foreach ($products as $product): ?>
                <option value="<?= $product['id'] ?>" <?= $product_filter == $product['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($product['name']) ?>
                </option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Filtrer
            </button>
            <?php if ($product_filter): ?>
            <a href="<?= SITE_URL ?>/cp/reviews/" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Réinitialiser
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Reviews Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Produit
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Client
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Note
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Titre / Commentaire
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (isset($reviews) && !empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $review['id'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <a href="<?= SITE_URL ?>/cp/products/edit.php?id=<?= $review['product_id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                                <?= htmlspecialchars($review['product_name']) ?>
                            </a>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?php if ($review['user_id']): ?>
                                <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                            <?php else: ?>
                                <span class="italic text-gray-500">Utilisateur supprimé</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?= htmlspecialchars($review['email'] ?? '') ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="font-bold text-gray-900 mr-2"><?= $review['rating'] ?>/5</span>
                            <div class="flex">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $review['rating']): ?>
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if (!empty($review['title'])): ?>
                        <div class="text-sm font-medium text-gray-900 mb-1">
                            <?= htmlspecialchars($review['title']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="text-sm text-gray-500 max-w-xs truncate">
                            <?= htmlspecialchars($review['comment'] ?? '') ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= SITE_URL ?>/cp/reviews/edit.php?id=<?= $review['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            Modifier
                        </a>
                        <a href="<?= SITE_URL ?>/cp/reviews/index.php?delete=<?= $review['id'] ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')" 
                           class="text-red-600 hover:text-red-900">
                            Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                    Aucun avis trouvé
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
        <a href="?page=<?= $page - 1 ?><?= $product_filter ? '&product_id=' . $product_filter : '' ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
            Précédent
        </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?><?= $product_filter ? '&product_id=' . $product_filter : '' ?>" class="px-4 py-2 text-sm font-medium <?= $i === $page ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 bg-white hover:bg-gray-50' ?> border border-gray-300">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?><?= $product_filter ? '&product_id=' . $product_filter : '' ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
            Suivant
        </a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 