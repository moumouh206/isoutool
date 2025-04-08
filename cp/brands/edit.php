<?php
$page_title = "Modifier une marque";
require_once '../includes/header.php';

$errors = [];
$success = false;
$brand = null;

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/cp/brands/');
    exit;
}

$brand_id = (int)$_GET['id'];

// Get brand details
try {
    $stmt = $db->prepare('SELECT * FROM brands WHERE id = ?');
    $stmt->execute([$brand_id]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$brand) {
        header('Location: ' . SITE_URL . '/cp/brands/');
        exit;
    }
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
    $data['website'] = $_POST['website'] ?? '';
    
    // Validate website URL (if provided)
    if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Format d\'URL invalide';
    }
    
    // Handle logo upload
    $logo_name = $brand['logo']; // Keep existing logo by default
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $file_name = $_FILES['logo']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = 'Format d\'image non autorisé. Formats acceptés : ' . implode(', ', $allowed_ext);
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = ROOT_PATH . '/public/uploads/brands/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $logo_name = uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $logo_name;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                $errors[] = 'Erreur lors de l\'upload du logo';
                $logo_name = $brand['logo']; // Keep existing logo if upload fails
            } else {
                // Delete old logo if exists
                if (!empty($brand['logo']) && file_exists($upload_dir . $brand['logo'])) {
                    unlink($upload_dir . $brand['logo']);
                }
            }
        }
    }
    
    // If no errors, proceed with updating brand
    if (empty($errors)) {
        try {
            // Check if name already exists (for another brand)
            if ($data['name'] !== $brand['name']) {
                $stmt = $db->prepare('SELECT id FROM brands WHERE name = ? AND id != ?');
                $stmt->execute([$data['name'], $brand_id]);
                if ($stmt->fetch()) {
                    $errors[] = 'Une marque avec ce nom existe déjà';
                    $success = false;
                    
                    // Delete newly uploaded logo if exists
                    if ($logo_name != $brand['logo'] && file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            }
            
            if (empty($errors)) {
                // Update brand
                $stmt = $db->prepare('
                    UPDATE brands 
                    SET name = ?, description = ?, website = ?, logo = ?
                    WHERE id = ?
                ');
                $stmt->execute([
                    $data['name'],
                    $data['description'],
                    $data['website'],
                    $logo_name,
                    $brand_id
                ]);
                
                $success = true;
                
                // Refresh brand data
                $stmt = $db->prepare('SELECT * FROM brands WHERE id = ?');
                $stmt->execute([$brand_id]);
                $brand = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            
            // If there was an error and a new logo was uploaded, remove it
            if ($logo_name != $brand['logo'] && file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Modifier une marque</h1>
    <a href="<?= SITE_URL ?>/cp/brands/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Marque mise à jour avec succès</span>
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

<!-- Edit Brand Form -->
<?php if ($brand): ?>
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom de la marque</label>
                <input type="text" name="name" id="name" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($brand['name']) ?>">
            </div>
            
            <div>
                <label for="website" class="block text-sm font-medium text-gray-700">Site Web</label>
                <input type="url" name="website" id="website" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($brand['website'] ?? '') ?>">
            </div>
            
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?= htmlspecialchars($brand['description'] ?? '') ?></textarea>
            </div>
            
            <div class="md:col-span-2">
                <?php if (!empty($brand['logo'])): ?>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo actuel</label>
                    <img src="<?= SITE_URL ?>/public/uploads/brands/<?= $brand['logo'] ?>" 
                         alt="<?= htmlspecialchars($brand['name']) ?>" 
                         class="h-20 w-auto object-contain border p-2 rounded">
                </div>
                <?php endif; ?>
                
                <label for="logo" class="block text-sm font-medium text-gray-700">Nouveau logo</label>
                <input type="file" name="logo" id="logo" 
                       class="mt-1 block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100">
                <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF, WebP ou SVG. Dimension recommandée: 200x200px</p>
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