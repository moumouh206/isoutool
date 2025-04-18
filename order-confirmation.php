<?php
require_once 'database/db_connect.php';
require_once 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Check if order number is provided
if (!isset($_GET['order'])) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$db = $database->connect();

try {
    // Get order details
    $stmt = $db->prepare('
        SELECT o.*, u.email, u.first_name, u.last_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.order_number = ? AND o.user_id = ?
    ');
    $stmt->execute([$_GET['order'], $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: index.php');
        exit;
    }
    
    // Get order items
    $stmt = $db->prepare('
        SELECT oi.*, p.name, p.reference
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ');
    $stmt->execute([$order['id']]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-center mb-8">
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            
            <h1 class="text-center text-2xl font-bold text-gray-800 mb-8">
                Thank you for your order!
            </h1>
            
            <div class="border-b pb-4 mb-4">
                <h2 class="text-lg font-semibold mb-2">Order Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Order Number</p>
                        <p class="font-medium"><?php echo htmlspecialchars($order['order_number']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Order Date</p>
                        <p class="font-medium"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium">
                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($order['email']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="border-b pb-4 mb-4">
                <h2 class="text-lg font-semibold mb-2">Shipping Address</h2>
                <p class="text-gray-700">
                    <?php echo htmlspecialchars($order['shipping_address_line_1']); ?><br>
                    <?php if ($order['shipping_address_line_2']): ?>
                        <?php echo htmlspecialchars($order['shipping_address_line_2']); ?><br>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($order['shipping_city']); ?><br>
                    <?php echo htmlspecialchars($order['shipping_postal_code']); ?><br>
                    <?php echo htmlspecialchars($order['shipping_country']); ?>
                </p>
            </div>
            
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Order Items</h2>
                <div class="space-y-4">
                    <?php foreach ($orderItems as $item): ?>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p class="text-sm text-gray-600">
                                    Ref: <?php echo htmlspecialchars($item['reference']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    Quantity: <?php echo $item['quantity']; ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">€<?php echo number_format($item['total'], 2); ?></p>
                                <p class="text-sm text-gray-600">
                                    €<?php echo number_format($item['price'], 2); ?> each
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="border-t pt-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal</span>
                        <span>€<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Shipping</span>
                        <span>€<?php echo number_format($order['shipping'], 2); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Tax</span>
                        <span>€<?php echo number_format($order['tax'], 2); ?></span>
                    </div>
                    <div class="flex justify-between font-semibold text-lg border-t pt-2">
                        <span>Total</span>
                        <span>€<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <a href="index.php" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600">
                Continue Shopping
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>