<?php
$page_title = "Commandes";
require_once '../includes/header.php';

// Get the list of orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filter options
$status = isset($_GET['status']) ? $_GET['status'] : '';
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : '';

try {
    // Build the query based on filters
    $whereClause = [];
    $params = [];
    
    if (!empty($status)) {
        $whereClause[] = 'o.status = ?';
        $params[] = $status;
    }
    
    if (!empty($fromDate)) {
        $whereClause[] = 'o.created_at >= ?';
        $params[] = $fromDate . ' 00:00:00';
    }
    
    if (!empty($toDate)) {
        $whereClause[] = 'o.created_at <= ?';
        $params[] = $toDate . ' 23:59:59';
    }
    
    $whereStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';
    
    // Get total orders count
    $countQuery = "SELECT COUNT(*) FROM orders o $whereStr";
    $stmt = $db->prepare($countQuery);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $totalOrders = $stmt->fetchColumn();
    
    // Get orders for current page
    $query = "
        SELECT o.*, u.first_name, u.last_name, u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        $whereStr
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($query);
    
    // Bind parameters for filters and pagination
    $i = 1;
    foreach ($params as $param) {
        $stmt->bindValue($i++, $param);
    }
    $stmt->bindValue($i++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($i++, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $orders = $stmt->fetchAll();
    
    // Calculate total pages
    $totalPages = ceil($totalOrders / $perPage);
    
    // Get available order statuses
    $stmt = $db->query("SELECT DISTINCT status FROM orders");
    $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des commandes : " . $e->getMessage();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Commandes</h1>
    <div class="flex space-x-2">
        <button onclick="window.print()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimer
        </button>
        <a href="<?= SITE_URL ?>/cp/orders/export.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
           class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Exporter CSV
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline"><?= $error ?></span>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6 mb-6">
    <form method="GET" action="" class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
            <select name="status" id="status" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Tous les statuts</option>
                <?php if (isset($statuses)): foreach ($statuses as $statusOption): ?>
                <option value="<?= $statusOption ?>" <?= $status === $statusOption ? 'selected' : '' ?>>
                    <?= ucfirst($statusOption) ?>
                </option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        
        <div>
            <label for="from_date" class="block text-sm font-medium text-gray-700">Date de début</label>
            <input type="date" name="from_date" id="from_date" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   value="<?= htmlspecialchars($fromDate) ?>">
        </div>
        
        <div>
            <label for="to_date" class="block text-sm font-medium text-gray-700">Date de fin</label>
            <input type="date" name="to_date" id="to_date" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   value="<?= htmlspecialchars($toDate) ?>">
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Filtrer
            </button>
            <a href="<?= SITE_URL ?>/cp/orders/" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

<!-- Orders Count -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-4 mb-6">
    <p class="text-gray-700">
        <span class="font-medium"><?= $totalOrders ?></span> commande<?= $totalOrders > 1 ? 's' : '' ?> trouvée<?= $totalOrders > 1 ? 's' : '' ?>
        <?php if (!empty($status)): ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            Statut: <?= ucfirst($status) ?>
        </span>
        <?php endif; ?>
        
        <?php if (!empty($fromDate)): ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            Du: <?= date('d/m/Y', strtotime($fromDate)) ?>
        </span>
        <?php endif; ?>
        
        <?php if (!empty($toDate)): ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            Au: <?= date('d/m/Y', strtotime($toDate)) ?>
        </span>
        <?php endif; ?>
    </p>
</div>

<!-- Orders Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    N° Commande
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Client
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (isset($orders) && !empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $order['id'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                        <?= htmlspecialchars($order['order_number']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if (!empty($order['first_name']) && !empty($order['last_name'])): ?>
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?= htmlspecialchars($order['email']) ?>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-500 italic">
                                Utilisateur supprimé
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                               ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($order['status'] === 'canceled' ? 'bg-red-100 text-red-800' : 
                               'bg-gray-100 text-gray-800')) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?= number_format($order['total'], 2, ',', ' ') ?> €
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= SITE_URL ?>/cp/orders/view.php?id=<?= $order['id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                            Voir détails
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                    Aucune commande trouvée
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
        <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
            Précédent
        </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" 
           class="px-4 py-2 text-sm font-medium <?= $i === $page ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 bg-white hover:bg-gray-50' ?> border border-gray-300">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
            Suivant
        </a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 