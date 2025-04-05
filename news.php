<?php
require_once __DIR__ . '/includes/init.php';

$page_title = "Actualités";
$page_description = "Découvrez les dernières actualités de RS Components France";

// Check if we're viewing a single news item
if (isset($_GET['slug'])) {
    $stmt = $db->prepare("SELECT * FROM news WHERE slug = :slug");
    $stmt->execute(['slug' => $_GET['slug']]);
    $news_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($news_item) {
        $page_title = $news_item['title'];
        $page_description = substr(strip_tags($news_item['content']), 0, 160);
    } else {
        header('Location: ' . SITE_URL . '/news.php');
        exit;
    }
} else {
    // Fetch all news for the news listing page
    $stmt = $db->query("SELECT * FROM news ORDER BY published_at DESC");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include_once __DIR__ . '/includes/header.php';
?>

<?php if (isset($news_item)): ?>
<!-- Single News Item -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="mb-8">
                <span class="text-sm text-gray-500">
                    <?= date('d F Y', strtotime($news_item['published_at'])) ?>
                </span>
                <h1 class="text-3xl font-bold text-rs-gray mt-2 mb-4">
                    <?= htmlspecialchars($news_item['title']) ?>
                </h1>
            </div>
            
            <div class="mb-8">
                <img src="<?= SITE_URL ?>/assets/images/news/<?= htmlspecialchars($news_item['image']) ?>" 
                     alt="<?= htmlspecialchars($news_item['title']) ?>" 
                     class="w-full h-96 object-cover rounded-lg">
            </div>
            
            <div class="prose max-w-none">
                <?= nl2br(htmlspecialchars($news_item['content'])) ?>
            </div>
            
            <div class="mt-8">
                <a href="<?= SITE_URL ?>/news.php" class="text-rs-red hover:text-red-700 font-medium">
                    ← Retour aux actualités
                </a>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<!-- News Listing -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-rs-gray mb-8">Actualités</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($news as $item): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="<?= SITE_URL ?>/assets/images/news/<?= htmlspecialchars($item['image']) ?>" 
                     alt="<?= htmlspecialchars($item['title']) ?>" 
                     class="w-full h-48 object-cover">
                <div class="p-4">
                    <span class="text-sm text-gray-500">
                        <?= date('d F Y', strtotime($item['published_at'])) ?>
                    </span>
                    <h3 class="text-xl font-bold text-rs-gray mt-2 mb-3">
                        <?= htmlspecialchars($item['title']) ?>
                    </h3>
                    <p class="text-gray-600 mb-4">
                        <?= htmlspecialchars($item['content']) ?>
                    </p>
                    <a href="<?= SITE_URL ?>/news.php?slug=<?= $item['slug'] ?>" 
                       class="text-rs-red hover:text-red-700 font-medium">
                        Lire la suite →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?> 