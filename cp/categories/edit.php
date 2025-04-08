<?php
$page_title = "Modifier une catégorie";
require_once '../includes/header.php';

$errors = [];
$success = false;
$category = null;

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/cp/categories/');
    exit;
}

$category_id = (int)$_GET['id'];

// Get category details
try {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        header('Location: ' . SITE_URL . '/cp/categories/');
        exit;
    }
    
    // Get parent categories for dropdown
    $stmt = $db->prepare('SELECT id, name FROM categories WHERE id != ? ORDER BY name ASC');
    $stmt->execute([$category_id]); // Exclude current category to prevent circular reference
    $parentCategories = $stmt->fetchAll();
    
    // Get product count for this category
    $stmt = $db->prepare('SELECT COUNT(*) FROM product_category WHERE category_id = ?');
    $stmt->execute([$category_id]);
    $productCount = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $errors[] = 'Erreur lors de la récupération des informations : ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['name'];
    $data = [];
    
    // Validate required fields
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' est requis';
        }
        $data[$field] = $_POST[$field] ?? '';
    }
    
    // Additional data
    $data['description'] = $_POST['description'] ?? '';
    $data['parent_id'] = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    // Generate slug from name if name changed
    if ($data['name'] !== $category['name']) {
        $data['slug'] = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\-]/', '', $data['name'])));
    } else {
        $data['slug'] = $category['slug'];
    }
    
    // Make sure we're not creating a circular reference with parent_id
    if ($data['parent_id'] == $category_id) {
        $errors[] = 'Une catégorie ne peut pas être sa propre catégorie parent';
    }
    
    // If no errors, proceed with updating category
    if (empty($errors)) {
        try {
            // Check if name already exists (for another category)
            if ($data['name'] !== $category['name']) {
                $stmt = $db->prepare('SELECT id FROM categories WHERE name = ? AND id != ?');
                $stmt->execute([$data['name'], $category_id]);
                if ($stmt->fetch()) {
                    $errors[] = 'Une catégorie avec ce nom existe déjà';
                    $success = false;
                }
            }
            
            // Check if slug already exists (for another category)
            if ($data['slug'] !== $category['slug']) {
                $stmt = $db->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
                $stmt->execute([$data['slug'], $category_id]);
                if ($stmt->fetch()) {
                    // Make slug unique by appending a number
                    $baseSlug = $data['slug'];
                    $counter = 1;
                    do {
                        $data['slug'] = $baseSlug . '-' . $counter;
                        $stmt = $db->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
                        $stmt->execute([$data['slug'], $category_id]);
                        $slugExists = $stmt->fetch();
                        $counter++;
                    } while ($slugExists);
                }
            }
            
            if (empty($errors)) {
                // Update category
                $stmt = $db->prepare('
                    UPDATE categories 
                    SET name = ?, slug = ?, description = ?, parent_id = ?
                    WHERE id = ?
                ');
                $stmt->execute([
                    $data['name'],
                    $data['slug'],
                    $data['description'],
                    $data['parent_id'],
                    $category_id
                ]);
                
                $success = true;
                
                // Refresh category data
                $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
                $stmt->execute([$category_id]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Modifier une catégorie</h1>
    <a href="<?= SITE_URL ?>/cp/categories/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Catégorie mise à jour avec succès</span>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
    <ul class="list-disc pl-5">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Category Info -->
<div class="bg-gray-50 p-4 rounded-md mb-6">
    <div class="font-medium text-gray-700">Informations de la catégorie</div>
    <div class="mt-2 text-sm text-gray-500">
        <p>ID: <?= $category['id'] ?></p>
        <p>Slug: <?= htmlspecialchars($category['slug']) ?></p>
        <p>Nombre de produits: <?= isset($productCount) ? $productCount : '0' ?></p>
    </div>
</div>

<!-- Edit Category Form -->
<?php if ($category): ?>
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom de la catégorie</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($category['name']) ?>">
            </div>
            
            <div>
                <label for="parent_id" class="block text-sm font-medium text-gray-700">Catégorie parent (optionnel)</label>
                <select name="parent_id" id="parent_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Aucune catégorie parent</option>
                    <?php if (isset($parentCategories)): foreach ($parentCategories as $parent): ?>
                    <option value="<?= $parent['id'] ?>" <?= $category['parent_id'] == $parent['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($parent['name']) ?>
                    </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description (optionnel)</label>
                <textarea name="description" id="description" rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
            </div>
        </div>
        
        <div class="mt-6 flex justify-between">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Mettre à jour
            </button>
            
            <?php if (isset($productCount) && $productCount === 0): ?>
            <a href="<?= SITE_URL ?>/cp/categories/index.php?delete=<?= $category['id'] ?>" 
               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')" 
               class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                Supprimer
            </a>
            <?php else: ?>
            <button type="button" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md cursor-not-allowed" 
                    title="Cette catégorie ne peut pas être supprimée car elle contient des produits">
                Supprimer
            </button>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 