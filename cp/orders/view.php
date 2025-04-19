<?php
$page_title = "Détails de la commande";
require_once '../includes/header.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/cp/orders/');
    exit;
}

$order_id = (int)$_GET['id'];
$success = false;
$error = null;

// Update order status if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    
    try {
        $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $order_id]);
        $success = true;
    } catch (PDOException $e) {
        $error = 'Erreur lors de la mise à jour du statut : ' . $e->getMessage();
    }
}

// Get order details
try {
    // Get order
    $stmt = $db->prepare('
        SELECT o.*, u.first_name, u.last_name, u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ');
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    // Get order items
    if ($order) {
        $stmt = $db->prepare('
            SELECT oi.*, p.name as product_name, p.reference
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ');
        $stmt->execute([$order_id]);
        $orderItems = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des détails de la commande : " . $e->getMessage();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Détails de la commande</h1>
    <a href="<?= SITE_URL ?>/cp/orders/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Commande mise à jour avec succès</span>
    <a href="<?= SITE_URL ?>/cp/orders/" class="font-bold underline ml-2">Retour à la liste</a>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline"><?= $error ?></span>
</div>
<?php endif; ?>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-6 mb-6">
    <h2 class="text-2xl font-semibold mb-4">Détails de la commande #<?= htmlspecialchars($order['order_number']) ?></h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium border-b pb-2">Informations client</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                <div class="mt-1 text-sm text-gray-900">
                    <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <div class="mt-1 text-sm text-gray-900">
                    <?= htmlspecialchars($order['email']) ?>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Date de commande</label>
                <div class="mt-1 text-sm text-gray-900">
                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                </div>
            </div>
        </div>

        <!-- Order Status and Update -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium border-b pb-2">Mise à jour du statut</h3>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Statut actuel</label>
                <div class="mt-1">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                        <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                           ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           ($order['status'] === 'canceled' ? 'bg-red-100 text-red-800' :
                           ($order['status'] === 'shipped' ? 'bg-blue-100 text-blue-800' :
                           'bg-gray-100 text-gray-800'))) ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </div>
            </div>
            
            <form method="POST">
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700">Nouveau statut</label>
                    <select name="status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>En cours</option>
                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Expédié</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Terminé</option>
                        <option value="canceled" <?= $order['status'] === 'canceled' ? 'selected' : '' ?>>Annulé</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Mettre à jour le statut
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium border-b pb-2">Résumé financier</h3>
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-700">Sous-total :</span>
                <span class="text-sm font-medium"><?= number_format($order['subtotal'] ?? $order['total'], 2, ',', ' ') ?> €</span>
            </div>
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-700">Frais de livraison :</span>
                <span class="text-sm font-medium"><?= number_format($order['shipping'] ?? 0, 2, ',', ' ') ?> €</span>
            </div>
            
            <div class="flex justify-between">
                <span class="text-sm text-gray-700">TVA :</span>
                <span class="text-sm font-medium"><?= number_format($order['tax'] ?? 0, 2, ',', ' ') ?> €</span>
            </div>
            
            <div class="flex justify-between border-t pt-2">
                <span class="text-base font-bold">Total :</span>
                <span class="text-base font-bold"><?= number_format($order['total'], 2, ',', ' ') ?> €</span>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium border-b pb-2">Informations de paiement</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Méthode de paiement</label>
                <div class="mt-1 text-sm text-gray-900">
                    <?= !empty($order['payment_method']) ? htmlspecialchars($order['payment_method']) : 'Non spécifié' ?>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Statut du paiement</label>
                <div class="mt-1">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                        <?= ($order['payment_status'] ?? '') === 'paid' ? 'bg-green-100 text-green-800' : 
                           (($order['payment_status'] ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           'bg-gray-100 text-gray-800') ?>">
                        <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Shipping and Billing Information -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6 mb-6">
    <h2 class="text-2xl font-semibold mb-4">Adresses</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Shipping Address -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium border-b pb-2">Adresse de livraison</h3>
            
            <div class="text-sm text-gray-900">
                <p class="font-medium">Adresse :</p>
                <p><?= htmlspecialchars($order['shipping_address_line_1'] ?? 'Non spécifié') ?></p>
                <?php if (!empty($order['shipping_address_line_2'])): ?>
                    <p><?= htmlspecialchars($order['shipping_address_line_2']) ?></p>
                <?php endif; ?>
                <p>
                    <?= htmlspecialchars($order['shipping_postal_code'] ?? '') ?>
                    <?= htmlspecialchars($order['shipping_city'] ?? 'Non spécifié') ?>
                </p>
                <p><?= htmlspecialchars($order['shipping_country'] ?? 'Non spécifié') ?></p>
            </div>
        </div>
        
        <!-- Billing Address -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium border-b pb-2">Adresse de facturation</h3>
            
            <div class="text-sm text-gray-900">
                <p class="font-medium">Adresse :</p>
                <p><?= htmlspecialchars($order['billing_address_line_1'] ?? 'Non spécifié') ?></p>
                <?php if (!empty($order['billing_address_line_2'])): ?>
                    <p><?= htmlspecialchars($order['billing_address_line_2']) ?></p>
                <?php endif; ?>
                <p>
                    <?= htmlspecialchars($order['billing_postal_code'] ?? '') ?>
                    <?= htmlspecialchars($order['billing_city'] ?? 'Non spécifié') ?>
                </p>
                <p><?= htmlspecialchars($order['billing_country'] ?? 'Non spécifié') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Articles commandés
        </h3>
    </div>
    <div class="border-t border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Produit
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Référence
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Prix unitaire
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Quantité
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (isset($orderItems) && !empty($orderItems)): ?>
                    <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php if (!empty($item['image'])): ?>
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded object-cover" 
                                         src="<?= SITE_URL ?>/public/uploads/products/<?= $item['image'] ?>" 
                                         alt="<?= htmlspecialchars($item['product_name'] ?? 'Produit') ?>">
                                </div>
                                <?php endif; ?>
                                <div class="<?= !empty($item['image']) ? 'ml-4' : '' ?>">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php if (!empty($item['product_name'])): ?>
                                            <a href="<?= SITE_URL ?>/cp/products/edit.php?id=<?= $item['product_id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500 italic">Produit supprimé</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($item['reference'] ?? 'N/A') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                            <?= number_format($item['price'], 2, ',', ' ') ?> €
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                            <?= $item['quantity'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                            <?= number_format($item['total'], 2, ',', ' ') ?> €
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            Aucun article trouvé pour cette commande
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>