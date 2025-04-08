<?php
$page_title = "Utilisateurs";
require_once '../includes/header.php';

// Handle user deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    try {
        // Don't allow deleting yourself
        if ($user_id != $_SESSION['user_id']) {
            $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $message = "Utilisateur supprimé avec succès";
        } else {
            $error = "Vous ne pouvez pas supprimer votre propre compte";
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Get the list of users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Get total users count
    $stmt = $db->query('SELECT COUNT(*) FROM users');
    $totalUsers = $stmt->fetchColumn();
    
    // Get users for current page
    $stmt = $db->prepare('SELECT id, email, first_name, last_name, is_admin, created_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bindParam(1, $perPage, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    // Calculate total pages
    $totalPages = ceil($totalUsers / $perPage);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Utilisateurs</h1>
    <a href="<?= SITE_URL ?>/cp/users/add.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
        Ajouter un utilisateur
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

<!-- Users Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nom
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Rôle
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Inscrit le
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (isset($users) && !empty($users)): ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $user['id'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= $user['is_admin'] ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= $user['is_admin'] ? 'Admin' : 'Utilisateur' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= SITE_URL ?>/cp/users/edit.php?id=<?= $user['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            Modifier
                        </a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="<?= SITE_URL ?>/cp/users/index.php?delete=<?= $user['id'] ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')" 
                           class="text-red-600 hover:text-red-900">
                            Supprimer
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    Aucun utilisateur trouvé
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