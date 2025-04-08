<?php
$page_title = "Ajouter une actualité";
require_once '../includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['title', 'content', 'published_at'];
    $data = [];
    
    // Validate required fields
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' est requis';
        }
        $data[$field] = $_POST[$field] ?? '';
    }
    
    // Generate slug from title if not provided
    $data['slug'] = !empty($_POST['slug']) ? $_POST['slug'] : createSlug($data['title']);
    
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
            $upload_dir = ROOT_PATH . '/public/uploads/news/';
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
    
    // If no errors, proceed with adding news
    if (empty($errors)) {
        try {
            // Check if slug already exists
            $stmt = $db->prepare('SELECT id FROM news WHERE slug = ?');
            $stmt->execute([$data['slug']]);
            if ($stmt->fetch()) {
                $errors[] = 'Une actualité avec ce slug existe déjà';
                
                // Delete uploaded image if exists
                if ($image_name && file_exists($upload_path)) {
                    unlink($upload_path);
                }
            } else {
                // Insert new news
                $stmt = $db->prepare('
                    INSERT INTO news (title, slug, content, image, published_at)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $data['title'],
                    $data['slug'],
                    $data['content'],
                    $image_name,
                    $data['published_at']
                ]);
                
                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de l\'ajout : ' . $e->getMessage();
            
            // Delete uploaded image if exists
            if ($image_name && file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
    }
}

// Helper function to create a slug from a string
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Ajouter une actualité</h1>
    <a href="<?= SITE_URL ?>/cp/news/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Actualité ajoutée avec succès</span>
    <a href="<?= SITE_URL ?>/cp/news/" class="font-bold underline ml-2">Retour à la liste</a>
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

<!-- Add News Form -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700">Titre</label>
                <input type="text" name="title" id="title" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                       onkeyup="document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/[\s-]+/g, '-').trim('-')">
            </div>
            
            <div class="md:col-span-2">
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" id="slug" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>">
                <p class="mt-1 text-xs text-gray-500">Le slug sera généré automatiquement à partir du titre si non renseigné</p>
            </div>
            
            <div class="md:col-span-2">
                <label for="content" class="block text-sm font-medium text-gray-700">Contenu</label>
                <textarea name="content" id="content" rows="10" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
            </div>
            
            <div>
                <label for="published_at" class="block text-sm font-medium text-gray-700">Date de publication</label>
                <input type="date" name="published_at" id="published_at" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['published_at'] ?? date('Y-m-d')) ?>">
            </div>
            
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                <input type="file" name="image" id="image" 
                       class="mt-1 block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100">
                <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF, WebP. Taille recommandée: 1200x600px</p>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Ajouter l'actualité
            </button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 