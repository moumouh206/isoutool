<?php
$page_title = "Modifier une actualité";
require_once '../includes/header.php';

$errors = [];
$success = false;
$news = null;

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/cp/news/');
    exit;
}

$news_id = (int)$_GET['id'];

// Get news details
try {
    $stmt = $db->prepare('SELECT * FROM news WHERE id = ?');
    $stmt->execute([$news_id]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$news) {
        header('Location: ' . SITE_URL . '/cp/news/');
        exit;
    }
} catch (PDOException $e) {
    $errors[] = 'Erreur lors de la récupération des informations : ' . $e->getMessage();
}

// Handle form submission
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
    
    // Generate or use provided slug
    $data['slug'] = !empty($_POST['slug']) ? $_POST['slug'] : createSlug($data['title']);
    
    // Handle image upload
    $image_name = $news['image']; // Keep existing image by default
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
                $image_name = $news['image']; // Keep existing image if upload fails
            } else {
                // Delete old image if exists
                if (!empty($news['image']) && file_exists($upload_dir . $news['image'])) {
                    unlink($upload_dir . $news['image']);
                }
            }
        }
    }
    
    // If no errors, proceed with updating news
    if (empty($errors)) {
        try {
            // Check if slug already exists (for another news item)
            if ($data['slug'] !== $news['slug']) {
                $stmt = $db->prepare('SELECT id FROM news WHERE slug = ? AND id != ?');
                $stmt->execute([$data['slug'], $news_id]);
                if ($stmt->fetch()) {
                    $errors[] = 'Une actualité avec ce slug existe déjà';
                    
                    // Delete newly uploaded image if exists
                    if ($image_name != $news['image'] && file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                    
                    $success = false;
                }
            }
            
            if (empty($errors)) {
                // Update news
                $stmt = $db->prepare('
                    UPDATE news 
                    SET title = ?, slug = ?, content = ?, image = ?, published_at = ?
                    WHERE id = ?
                ');
                $stmt->execute([
                    $data['title'],
                    $data['slug'],
                    $data['content'],
                    $image_name,
                    $data['published_at'],
                    $news_id
                ]);
                
                $success = true;
                
                // Refresh news data
                $stmt = $db->prepare('SELECT * FROM news WHERE id = ?');
                $stmt->execute([$news_id]);
                $news = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            
            // If there was an error and a new image was uploaded, remove it
            if ($image_name != $news['image'] && file_exists($upload_path)) {
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
    <h1 class="text-2xl font-semibold text-gray-900">Modifier une actualité</h1>
    <a href="<?= SITE_URL ?>/cp/news/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Actualité mise à jour avec succès</span>
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

<!-- Edit News Form -->
<?php if ($news): ?>
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700">Titre</label>
                <input type="text" name="title" id="title" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($news['title']) ?>"
                       onkeyup="if(!document.getElementById('slug').value) document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/[\s-]+/g, '-').trim('-')">
            </div>
            
            <div class="md:col-span-2">
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" id="slug" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($news['slug']) ?>">
                <p class="mt-1 text-xs text-gray-500">Identificateur unique utilisé dans l'URL</p>
            </div>
            
            <div class="md:col-span-2">
                <label for="content" class="block text-sm font-medium text-gray-700">Contenu</label>
                <textarea name="content" id="content" rows="10" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?= htmlspecialchars($news['content']) ?></textarea>
            </div>
            
            <div>
                <label for="published_at" class="block text-sm font-medium text-gray-700">Date de publication</label>
                <input type="date" name="published_at" id="published_at" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars(substr($news['published_at'], 0, 10)) ?>">
            </div>
            
            <div>
                <?php if (!empty($news['image'])): ?>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image actuelle</label>
                    <img src="<?= SITE_URL ?>/public/uploads/news/<?= $news['image'] ?>" 
                         alt="<?= htmlspecialchars($news['title']) ?>" 
                         class="h-20 w-auto object-contain border p-2 rounded">
                </div>
                <?php endif; ?>
                
                <label for="image" class="block text-sm font-medium text-gray-700">Nouvelle image</label>
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
                Mettre à jour
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 