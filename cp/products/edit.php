<?php
$page_title = "Modifier un produit";
require_once '../includes/header.php';

$errors = [];
$success = false;
$product = null;

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/cp/products/');
    exit;
}

$product_id = (int)$_GET['id'];

// Get product details
try {
    // Get categories and brands for dropdowns
    $stmt = $db->query('SELECT id, name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->query('SELECT id, name FROM brands ORDER BY name');
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product details
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: ' . SITE_URL . '/cp/products/');
        exit;
    }
    
    // Get product categories
    $stmt = $db->prepare('SELECT category_id FROM product_category WHERE product_id = ?');
    $stmt->execute([$product_id]);
    $product_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errors[] = 'Erreur lors de la récupération des données : ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['name', 'price', 'stock'];
    $data = [];
    
    // Validate required fields
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' est requis';
        }
    }
    
    // Collect data
    $data['name'] = $_POST['name'] ?? '';
    $data['description'] = $_POST['description'] ?? '';
    $data['price'] = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $data['stock'] = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);
    $data['category_id'] = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $data['brand_id'] = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $data['reference'] = $_POST['reference'] ?? '';
    $data['featured'] = isset($_POST['featured']) ? 1 : 0;
    
    // Validate price
    if ($data['price'] === false || $data['price'] < 0) {
        $errors[] = 'Prix invalide';
    }
    
    // Validate stock
    if ($data['stock'] === false || $data['stock'] < 0) {
        $errors[] = 'Stock invalide';
    }
    
    // Handle image upload
    $image_name = $product['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = 'Format d\'image non autorisé. Formats acceptés : ' . implode(', ', $allowed_ext);
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = ROOT_PATH . '/public/uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $image_name = uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $image_name;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = 'Erreur lors de l\'upload de l\'image';
                $image_name = $product['image']; // Keep existing image if upload fails
            } else {
                // Delete old image if exists
                if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
                    unlink($upload_dir . $product['image']);
                }
            }
        }
    }
    
    // If no errors, proceed with updating product
    if (empty($errors)) {
        try {
            // Update product
            $stmt = $db->prepare('
                UPDATE products 
                SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, 
                    brand_id = ?, reference = ?, featured = ?, image = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['price'],
                $data['stock'],
                $data['category_id'],
                $data['brand_id'],
                $data['reference'],
                $data['featured'],
                $image_name,
                $product_id
            ]);
            
            $success = true;
            
            // Refresh product data
            $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            
            // If there was an error and a new image was uploaded, remove it
            if ($image_name != $product['image'] && file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Modifier un produit</h1>
    <a href="<?= SITE_URL ?>/cp/products/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Produit mis à jour avec succès</span>
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

<!-- Edit Product Form -->
<?php if ($product): ?>
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom du produit</label>
                <input type="text" name="name" id="name" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($product['name']) ?>">
            </div>
            
            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700">Référence</label>
                <input type="text" name="reference" id="reference" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($product['reference'] ?? '') ?>">
            </div>
            
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Prix (€)</label>
                <input type="number" step="0.01" min="0" name="price" id="price" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($product['price']) ?>">
            </div>
            
            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                <input type="number" min="0" name="stock" id="stock" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($product['stock']) ?>">
            </div>
            
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie</label>
                <select name="category_id" id="category_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Sélectionner une catégorie</option>
                    <?php if (isset($categories)): foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
            <div>
                <label for="brand_id" class="block text-sm font-medium text-gray-700">Marque</label>
                <select name="brand_id" id="brand_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Sélectionner une marque</option>
                    <?php if (isset($brands)): foreach ($brands as $brand): ?>
                    <option value="<?= $brand['id'] ?>" <?= ($product['brand_id'] == $brand['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($brand['name']) ?>
                    </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            
            <div>
                <label for="featured" class="block text-sm font-medium text-gray-700">Produit recommandé</label>
                <input type="checkbox" name="featured" id="featured" 
                       class="mt-1 block w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                       <?= $product['featured'] ? 'checked' : '' ?>>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Mettre à jour
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 