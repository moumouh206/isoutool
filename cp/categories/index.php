<?php
$page_title = "Catégories";
require_once '../includes/header.php';

// Handle category deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    try {
        // Check if there are any products in this category
        $stmt = $db->prepare('SELECT COUNT(*) FROM product_category WHERE category_id = ?');
        $stmt->execute([$category_id]);
        $productCount = $stmt->fetchColumn();
        
        if ($productCount > 0) {
            $error = "Impossible de supprimer cette catégorie car elle contient des produits";
        } else {
            // Check if there are any subcategories
            $stmt = $db->prepare('SELECT COUNT(*) FROM categories WHERE parent_id = ?');
            $stmt->execute([$category_id]);
            $subcategoryCount = $stmt->fetchColumn();
            
            if ($subcategoryCount > 0) {
                $error = "Impossible de supprimer cette catégorie car elle contient des sous-catégories";
            } else {
                // Delete the category
                $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
                $stmt->execute([$category_id]);
                $message = "Catégorie supprimée avec succès";
            }
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Get the list of categories with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Get total categories count
    $stmt = $db->query('SELECT COUNT(*) FROM categories');
    $totalCategories = $stmt->fetchColumn();
    
    // Get categories for current page
    $stmt = $db->prepare('
        SELECT c.*, 
               p.name as parent_name,
               (SELECT COUNT(*) FROM product_category WHERE category_id = c.id) as product_count
        FROM categories c
        LEFT JOIN categories p ON c.parent_id = p.id
        ORDER BY c.name ASC
        LIMIT ? OFFSET ?
    ');
    $stmt->bindParam(1, $perPage, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    // Calculate total pages
    $totalPages = ceil($totalCategories / $perPage);
    
    // Get parent categories for dropdown
    $stmt = $db->query('SELECT id, name FROM categories ORDER BY name ASC');
    $parentCategories = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des catégories : " . $e->getMessage();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Catégories</h1>
    <a href="<?= SITE_URL ?>/cp/categories/add.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
        Ajouter une catégorie
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

<!-- Categories Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nom
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Slug
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Catégorie parent
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Produits
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (isset($categories) && !empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $category['id'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($category['name']) ?>
                        </div>
                        <?php if (!empty($category['description'])): ?>
                        <div class="text-sm text-gray-500 truncate max-w-xs">
                            <?= htmlspecialchars(substr($category['description'], 0, 50)) ?>
                            <?= strlen($category['description']) > 50 ? '...' : '' ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($category['slug']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $category['parent_id'] ? htmlspecialchars($category['parent_name']) : '-' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $category['product_count'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= SITE_URL ?>/cp/categories/edit.php?id=<?= $category['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            Modifier
                        </a>
                        <a href="<?= SITE_URL ?>/cp/categories/index.php?delete=<?= $category['id'] ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')" 
                           class="text-red-600 hover:text-red-900">
                            Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    Aucune catégorie trouvée
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
        <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
            Précédent
        </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>" class="px-4 py-2 text-sm font-medium <?= $i === $page ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 bg-white hover:bg-gray-50' ?> border border-gray-300">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
            Suivant
        </a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 