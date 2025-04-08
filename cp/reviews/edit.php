<?php
$page_title = "Modifier un avis";
require_once '../includes/header.php';

$errors = [];
$success = false;
$review = null;

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/cp/reviews/');
    exit;
}

$review_id = (int)$_GET['id'];

// Get review details and related data
try {
    // Get review details
    $stmt = $db->prepare('
        SELECT r.*, p.name as product_name, u.first_name, u.last_name, u.email 
        FROM reviews r
        LEFT JOIN products p ON r.product_id = p.id
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ');
    $stmt->execute([$review_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$review) {
        header('Location: ' . SITE_URL . '/cp/reviews/');
        exit;
    }
    
    // Get list of products for dropdown
    $stmt = $db->query('SELECT id, name FROM products ORDER BY name');
    $products = $stmt->fetchAll();
    
    // Get list of users for dropdown
    $stmt = $db->query('SELECT id, email, first_name, last_name FROM users ORDER BY last_name, first_name');
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Erreur lors de la récupération des données : ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['product_id', 'user_id', 'rating'];
    $data = [];
    
    // Validate required fields
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' est requis';
        }
    }
    
    // Collect data
    $data['product_id'] = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $data['user_id'] = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $data['rating'] = !empty($_POST['rating']) ? (int)$_POST['rating'] : null;
    $data['title'] = $_POST['title'] ?? '';
    $data['comment'] = $_POST['comment'] ?? '';
    
    // Validate rating
    if ($data['rating'] < 1 || $data['rating'] > 5) {
        $errors[] = 'La note doit être entre 1 et 5';
    }
    
    // If no errors, proceed with updating review
    if (empty($errors)) {
        try {
            // Check if another review already exists for this product/user combo
            if ($data['product_id'] != $review['product_id'] || $data['user_id'] != $review['user_id']) {
                $stmt = $db->prepare('SELECT id FROM reviews WHERE product_id = ? AND user_id = ? AND id != ?');
                $stmt->execute([$data['product_id'], $data['user_id'], $review_id]);
                if ($stmt->fetch()) {
                    $errors[] = 'Cet utilisateur a déjà donné son avis sur ce produit';
                }
            }
            
            if (empty($errors)) {
                // Update review
                $stmt = $db->prepare('
                    UPDATE reviews 
                    SET product_id = ?, user_id = ?, rating = ?, title = ?, comment = ?
                    WHERE id = ?
                ');
                $stmt->execute([
                    $data['product_id'],
                    $data['user_id'],
                    $data['rating'],
                    $data['title'],
                    $data['comment'],
                    $review_id
                ]);
                
                $success = true;
                
                // Refresh review data
                $stmt = $db->prepare('
                    SELECT r.*, p.name as product_name, u.first_name, u.last_name, u.email 
                    FROM reviews r
                    LEFT JOIN products p ON r.product_id = p.id
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.id = ?
                ');
                $stmt->execute([$review_id]);
                $review = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Modifier un avis</h1>
    <a href="<?= SITE_URL ?>/cp/reviews/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Avis mis à jour avec succès</span>
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

<!-- Add Review Form -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="product_id" class="block text-sm font-medium text-gray-700">Produit</label>
                <select name="product_id" id="product_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Sélectionner un produit</option>
                    <?php if (isset($products)): foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>" <?= (isset($_POST['product_id']) && $_POST['product_id'] == $product['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($product['name']) ?>
                    </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700">Client</label>
                <select name="user_id" id="user_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Sélectionner un client</option>
                    <?php if (isset($users)): foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')') ?>
                    </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
            <div>
                <label for="rating" class="block text-sm font-medium text-gray-700">Note</label>
                <select name="rating" id="rating" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Sélectionner une note</option>
                    <option value="5" <?= (isset($_POST['rating']) && $_POST['rating'] == 5) ? 'selected' : '' ?>>5 étoiles - Excellent</option>
                    <option value="4" <?= (isset($_POST['rating']) && $_POST['rating'] == 4) ? 'selected' : '' ?>>4 étoiles - Très bon</option>
                    <option value="3" <?= (isset($_POST['rating']) && $_POST['rating'] == 3) ? 'selected' : '' ?>>3 étoiles - Bon</option>
                    <option value="2" <?= (isset($_POST['rating']) && $_POST['rating'] == 2) ? 'selected' : '' ?>>2 étoiles - Moyen</option>
                    <option value="1" <?= (isset($_POST['rating']) && $_POST['rating'] == 1) ? 'selected' : '' ?>>1 étoile - Mauvais</option>
                </select>
            </div>
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Titre (optionnel)</label>
                <input type="text" name="title" id="title" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            
            <div class="md:col-span-2">
                <label for="comment" class="block text-sm font-medium text-gray-700">Commentaire (optionnel)</label>
                <textarea name="comment" id="comment" rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Modifier l'avis
            </button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 