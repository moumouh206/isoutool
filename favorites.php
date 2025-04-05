<?php
require_once __DIR__ . '/includes/init.php';

$page_title = "Mes Favoris";
$page_description = "Vos produits favoris";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=favorites');
    exit;
}

// Handle add/remove favorite
if (isset($_GET['action']) && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    if ($_GET['action'] === 'add') {
        try {
            $stmt = $db->prepare("INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $product_id]);
        } catch (PDOException $e) {
            // Handle error
        }
    } elseif ($_GET['action'] === 'remove') {
        try {
            $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } catch (PDOException $e) {
            // Handle error
        }
    }
    
    // Redirect back to prevent form resubmission
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Get user's favorites
$query = "
    SELECT p.*, b.name as brand_name, b.slug as brand_slug,
           GROUP_CONCAT(DISTINCT c.name) as category_names
    FROM favorites f
    JOIN products p ON f.product_id = p.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_category pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    WHERE f.user_id = ?
    GROUP BY p.id, p.reference, p.name, p.slug, p.description, p.price, p.stock, p.featured, b.name, b.slug
    ORDER BY f.created_at DESC
";

$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Mes Favoris</h1>

    <?php if (empty($favorites)): ?>
    <div class="text-center py-12">
        <p class="text-gray-600">Vous n'avez pas encore de produits favoris.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($favorites as $product): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <img src="<?= SITE_URL ?>/assets/images/products/<?= htmlspecialchars($product['reference']) ?>.jpg" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="w-full h-48 object-cover">
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-sm text-gray-500"><?= htmlspecialchars($product['reference']) ?></span>
                    <a href="?action=remove&id=<?= $product['id'] ?>" 
                       class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
                <h3 class="text-lg font-semibold text-rs-gray mb-2">
                    <?= htmlspecialchars($product['name']) ?>
                </h3>
                <p class="text-sm text-gray-600 mb-2">
                    <?= htmlspecialchars($product['brand_name']) ?>
                </p>
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-rs-red">
                        <?= number_format($product['price'], 2, ',', ' ') ?> €
                    </span>
                    <a href="?action=add&id=<?= $product['id'] ?>" 
                       class="bg-rs-red text-white py-2 px-4 rounded hover:bg-red-700">
                        Ajouter au panier
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?> 