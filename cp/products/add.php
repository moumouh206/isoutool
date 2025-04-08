<?php
$page_title = "Ajouter un produit";
require_once '../includes/header.php';

$errors = [];
$success = false;

// Get categories and brands for dropdowns
try {
    $stmt = $db->query('SELECT id, name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->query('SELECT id, name FROM brands ORDER BY name');
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $data['brand_id'] = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $data['reference'] = $_POST['reference'] ?? '';
    $data['featured'] = isset($_POST['featured']) ? 1 : 0;
    
    // Get selected categories
    $selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    // Generate slug from name
    $data['slug'] = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\-]/', '', $data['name'])));
    
    // Validate price
    if ($data['price'] === false || $data['price'] < 0) {
        $errors[] = 'Prix invalide';
    }
    
    // Validate stock
    if ($data['stock'] === false || $data['stock'] < 0) {
        $errors[] = 'Stock invalide';
    }
    
    // Check if the reference already exists
    if (!empty($data['reference'])) {
        $stmt = $db->prepare('SELECT id FROM products WHERE reference = ?');
        $stmt->execute([$data['reference']]);
        if ($stmt->fetch()) {
            $errors[] = 'Cette référence est déjà utilisée par un autre produit';
        }
    }
    
    // Handle image upload
    $image_name = null;
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
                $image_name = null;
            }
        }
    }
    
    // If no errors, proceed with adding product
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Check if slug already exists
            $stmt = $db->prepare('SELECT id FROM products WHERE slug = ?');
            $stmt->execute([$data['slug']]);
            if ($stmt->fetch()) {
                // Make slug unique by appending a number
                $baseSlug = $data['slug'];
                $counter = 1;
                do {
                    $data['slug'] = $baseSlug . '-' . $counter;
                    $stmt = $db->prepare('SELECT id FROM products WHERE slug = ?');
                    $stmt->execute([$data['slug']]);
                    $slugExists = $stmt->fetch();
                    $counter++;
                } while ($slugExists);
            }
            
            // Insert new product
            $stmt = $db->prepare('
                INSERT INTO products (name, slug, description, price, stock, brand_id, reference, featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $data['name'],
                $data['slug'],
                $data['description'],
                $data['price'],
                $data['stock'],
                $data['brand_id'],
                $data['reference'],
                $data['featured']
            ]);
            
            $product_id = $db->lastInsertId();
            
            // Add product categories
            if (!empty($selected_categories)) {
                $insert_cats = $db->prepare('INSERT INTO product_category (product_id, category_id) VALUES (?, ?)');
                foreach ($selected_categories as $category_id) {
                    $insert_cats->execute([$product_id, (int)$category_id]);
                }
            }
            
            // Add product image if exists
            if ($image_name) {
                $stmt = $db->prepare('
                    INSERT INTO product_images (product_id, image_path, is_primary)
                    VALUES (?, ?, 1)
                ');
                $stmt->execute([$product_id, $image_name]);
            }
            
            $db->commit();
            $success = true;
            
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = 'Erreur lors de l\'ajout : ' . $e->getMessage();
            
            // If there was an error, remove the uploaded image
            if ($image_name && file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Ajouter un produit</h1>
    <a href="<?= SITE_URL ?>/cp/products/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Produit ajouté avec succès</span>
    <a href="<?= SITE_URL ?>/cp/products/" class="font-bold underline ml-2">Retour à la liste</a>
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

<!-- Add Product Form -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom du produit*</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            
            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700">Référence</label>
                <input type="text" name="reference" id="reference" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['reference'] ?? '') ?>">
            </div>
            
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Prix (€)*</label>
                <input type="number" step="0.01" min="0" name="price" id="price" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
            </div>
            
            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700">Stock*</label>
                <input type="number" min="0" name="stock" id="stock" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>">
            </div>
            
            <div>
                <label for="categories" class="block text-sm font-medium text-gray-700">Catégories</label>
                <select name="categories[]" id="categories" multiple
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        size="5">
                    <?php if (isset($categories)): foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= (isset($_POST['categories']) && in_array($category['id'], $_POST['categories'])) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endforeach; endif; ?>
                </select>
                <p class="mt-1 text-xs text-gray-500">Maintenir Ctrl pour sélectionner plusieurs catégories</p>
            </div>
            
            <div>
                <label for="brand_id" class="block text-sm font-medium text-gray-700">Marque</label>
                <select name="brand_id" id="brand_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Sélectionner une marque</option>
                    <?php if (isset($brands)): foreach ($brands as $brand): ?>
                    <option value="<?= $brand['id'] ?>" <?= (isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($brand['name']) ?>
                    </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">Image principale</label>
                <input type="file" name="image" id="image" 
                       class="mt-1 block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100">
                <p class="mt-1 text-xs text-gray-500">Formats acceptés: JPG, JPEG, PNG, GIF, WEBP.</p>
            </div>
            
            <div class="flex items-center mt-4">
                <input id="featured" name="featured" type="checkbox" 
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                <label for="featured" class="ml-2 block text-sm text-gray-900">
                    Produit en vedette
                </label>
            </div>
        </div>
        
        <div class="mt-6">
            <p class="text-sm text-gray-500 mb-4">* Champs obligatoires</p>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Ajouter le produit
            </button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 