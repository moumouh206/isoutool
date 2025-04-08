<?php
require_once '../../includes/init.php';
require_once '../includes/admin_auth.php';

// Check admin authentication
checkAdminAuth();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/cp/orders/');
    exit;
}

$order_id = (int)$_GET['id'];
$error = null;

// Get order details
try {
    $db = (new Database())->connect();
    
    // Get order
    $stmt = $db->prepare('
        SELECT o.*, u.first_name, u.last_name, u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ');
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: ' . SITE_URL . '/cp/orders/');
        exit;
    }
    
    // Get order items
    $stmt = $db->prepare('
        SELECT oi.*, p.name as product_name, p.reference
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ');
    $stmt->execute([$order_id]);
    $orderItems = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des détails de la commande : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande #<?= htmlspecialchars($order['order_number']) ?> - Facture</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/style.css">
    <style>
        @media print {
            body {
                font-size: 12pt;
                color: black;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            a {
                text-decoration: none;
                color: black;
            }
        }
        .print-header {
            padding: 20px 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 30px;
        }
    </style>
</head>
<body class="bg-white">
    <div class="max-w-4xl mx-auto p-6 print-container">
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= $error ?></span>
        </div>
        <?php else: ?>
        <div class="print-header flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold"><?= SITE_NAME ?></h1>
                <p class="text-gray-600">Facture</p>
            </div>
            <div class="text-right">
                <p class="font-bold">Commande #<?= htmlspecialchars($order['order_number']) ?></p>
                <p>Date: <?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
            </div>
        </div>
        
        <div class="mb-8 grid grid-cols-2 gap-8">
            <div>
                <h2 class="text-lg font-bold mb-2">Adresse de facturation</h2>
                <?php if (!empty($order['first_name']) && !empty($order['last_name'])): ?>
                    <p class="font-medium"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
                <?php else: ?>
                    <p class="italic">Utilisateur supprimé</p>
                <?php endif; ?>
                <p><?= htmlspecialchars($order['billing_address_line_1'] ?? '') ?></p>
                <?php if (!empty($order['billing_address_line_2'])): ?>
                    <p><?= htmlspecialchars($order['billing_address_line_2']) ?></p>
                <?php endif; ?>
                <p><?= htmlspecialchars(($order['billing_postal_code'] ?? '') . ' ' . ($order['billing_city'] ?? '')) ?></p>
                <p><?= htmlspecialchars($order['billing_country'] ?? '') ?></p>
                <p><?= htmlspecialchars($order['email'] ?? '') ?></p>
            </div>
            
            <div>
                <h2 class="text-lg font-bold mb-2">Adresse de livraison</h2>
                <?php if (!empty($order['first_name']) && !empty($order['last_name'])): ?>
                    <p class="font-medium"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
                <?php else: ?>
                    <p class="italic">Utilisateur supprimé</p>
                <?php endif; ?>
                <p><?= htmlspecialchars($order['shipping_address_line_1'] ?? '') ?></p>
                <?php if (!empty($order['shipping_address_line_2'])): ?>
                    <p><?= htmlspecialchars($order['shipping_address_line_2']) ?></p>
                <?php endif; ?>
                <p><?= htmlspecialchars(($order['shipping_postal_code'] ?? '') . ' ' . ($order['shipping_city'] ?? '')) ?></p>
                <p><?= htmlspecialchars($order['shipping_country'] ?? '') ?></p>
            </div>
        </div>
        
        <div class="mb-8">
            <table class="min-w-full border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border-b border-gray-300 py-2 px-4 text-left">Produit</th>
                        <th class="border-b border-gray-300 py-2 px-4 text-left">Référence</th>
                        <th class="border-b border-gray-300 py-2 px-4 text-right">Prix unitaire</th>
                        <th class="border-b border-gray-300 py-2 px-4 text-right">Quantité</th>
                        <th class="border-b border-gray-300 py-2 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($orderItems) && !empty($orderItems)): ?>
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td class="border-b border-gray-300 py-2 px-4">
                                <?= htmlspecialchars($item['product_name'] ?? 'Produit supprimé') ?>
                            </td>
                            <td class="border-b border-gray-300 py-2 px-4">
                                <?= htmlspecialchars($item['reference'] ?? 'N/A') ?>
                            </td>
                            <td class="border-b border-gray-300 py-2 px-4 text-right">
                                <?= number_format($item['price'], 2, ',', ' ') ?> €
                            </td>
                            <td class="border-b border-gray-300 py-2 px-4 text-right">
                                <?= $item['quantity'] ?>
                            </td>
                            <td class="border-b border-gray-300 py-2 px-4 text-right">
                                <?= number_format($item['total'], 2, ',', ' ') ?> €
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="border-b border-gray-300 py-2 px-4 text-center">
                                Aucun article trouvé pour cette commande
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="border-b border-gray-300"></td>
                        <td class="border-b border-gray-300 py-2 px-4 text-right font-medium">Sous-total</td>
                        <td class="border-b border-gray-300 py-2 px-4 text-right">
                            <?= number_format($order['subtotal'], 2, ',', ' ') ?> €
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border-b border-gray-300"></td>
                        <td class="border-b border-gray-300 py-2 px-4 text-right font-medium">Livraison</td>
                        <td class="border-b border-gray-300 py-2 px-4 text-right">
                            <?= number_format($order['shipping'], 2, ',', ' ') ?> €
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border-b border-gray-300"></td>
                        <td class="border-b border-gray-300 py-2 px-4 text-right font-medium">TVA</td>
                        <td class="border-b border-gray-300 py-2 px-4 text-right">
                            <?= number_format($order['tax'], 2, ',', ' ') ?> €
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border-b border-gray-300"></td>
                        <td class="border-b border-gray-300 py-2 px-4 text-right font-bold">Total</td>
                        <td class="border-b border-gray-300 py-2 px-4 text-right font-bold">
                            <?= number_format($order['total'], 2, ',', ' ') ?> €
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="mt-8 mb-8">
            <h2 class="text-lg font-bold mb-2">Information de paiement</h2>
            <p>Méthode de paiement: <?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></p>
            <p>Statut de paiement: <?= ucfirst($order['payment_status'] ?? 'N/A') ?></p>
        </div>
        
        <?php if (!empty($order['notes'])): ?>
        <div class="mb-8">
            <h2 class="text-lg font-bold mb-2">Notes</h2>
            <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
        </div>
        <?php endif; ?>
        
        <div class="mt-8 text-center text-gray-600">
            <p>Merci de votre achat!</p>
            <p class="text-sm"><?= SITE_NAME ?> - <?= date('Y') ?></p>
        </div>
        
        <div class="mt-8 no-print text-center">
            <button onclick="window.print();" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Imprimer cette facture
            </button>
            <a href="<?= SITE_URL ?>/cp/orders/view.php?id=<?= $order_id ?>" class="ml-4 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Retourner à la commande
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-print when the page loads
        window.onload = function() {
            // Small delay to ensure everything is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html> 