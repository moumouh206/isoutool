<?php
$page_title = "Actualités";
require_once '../includes/header.php';

// Handle news deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $news_id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare('DELETE FROM news WHERE id = ?');
        $stmt->execute([$news_id]);
        $message = "Actualité supprimée avec succès";
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Get the list of news articles with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Get total news count
    $stmt = $db->query('SELECT COUNT(*) FROM news');
    $totalNews = $stmt->fetchColumn();
    
    // Get news for current page
    $stmt = $db->prepare('
        SELECT id, title, slug, image, published_at, created_at 
        FROM news 
        ORDER BY published_at DESC 
        LIMIT ? OFFSET ?
    ');
    $stmt->bindParam(1, $perPage, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $newsItems = $stmt->fetchAll();
    
    // Calculate total pages
    $totalPages = ceil($totalNews / $perPage);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des actualités : " . $e->getMessage();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Actualités</h1>
    <a href="<?= SITE_URL ?>/cp/news/add.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
        Ajouter une actualité
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

<!-- News Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Image
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Titre
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Slug
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date de publication
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (isset($newsItems) && !empty($newsItems)): ?>
                <?php foreach ($newsItems as $news): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $news['id'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if (!empty($news['image'])): ?>
                        <img src="<?= SITE_URL ?>/public/uploads/news/<?= $news['image'] ?>" 
                             alt="<?= htmlspecialchars($news['title']) ?>" 
                             class="h-10 w-10 object-cover rounded">
                        <?php else: ?>
                        <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($news['title']) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($news['slug']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d/m/Y', strtotime($news['published_at'])) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= SITE_URL ?>/cp/news/edit.php?id=<?= $news['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            Modifier
                        </a>
                        <a href="<?= SITE_URL ?>/cp/news/index.php?delete=<?= $news['id'] ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')" 
                           class="text-red-600 hover:text-red-900">
                            Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    Aucune actualité trouvée
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