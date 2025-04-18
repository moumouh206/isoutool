<?php
require_once 'includes/init.php';
require_once 'includes/header.php';

// Debugging logs
error_log("Checkout page accessed");

// Check database connection
if (!isset($db)) {
    error_log("Database connection is not initialized.");
} else {
    error_log("Database connection is active.");
}

// Check session data
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    error_log("Cart is empty or not set.");
} else {
    error_log("Cart contains " . count($_SESSION['cart']) . " items.");
}

if (!isset($_SESSION['user_id'])) {
    error_log("User ID is not set in session.");
} else {
    error_log("User ID: " . $_SESSION['user_id']);
}

// Redirect to cart if it's empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Log POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST data: " . print_r($_POST, true));
}

// Log cart details
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    error_log("Cart details: " . print_r($_SESSION['cart'], true));
}

// Log database queries and results
try {
    $stmt = $db->prepare('SELECT 1');
    $stmt->execute();
    error_log("Database query test successful.");
} catch (Exception $e) {
    error_log("Database query test failed: " . $e->getMessage());
}

// Initialize variables
$errors = [];
$success = false;
$order_id = null;

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = [
        'shipping_name', 'shipping_address', 'shipping_city', 'shipping_state', 
        'shipping_zip', 'shipping_country', 'billing_name', 'billing_address', 
        'billing_city', 'billing_state', 'billing_zip', 'billing_country'
    ];
    
    $data = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
        $data[$field] = $_POST[$field] ?? '';
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        error_log("Starting order processing...");
        try {
            $db->beginTransaction();
            error_log("Transaction started.");
            
            // Create order
            // Generate a unique order number with year, month, day and random digits
            $order_number = 'ORD-' . date('Ymd') . '-' . mt_rand(1000, 9999);
            
            $stmt = $db->prepare('
                INSERT INTO orders (
                    user_id, order_number, created_at, shipping_address_line_1, 
                    shipping_city, shipping_postal_code, shipping_country,
                    billing_address_line_1, billing_city, 
                    billing_postal_code, billing_country, total, status
                ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            
            // Log total calculation
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            error_log("Total amount calculated: $total");
            
            $user_id = $_SESSION['user_id'] ?? null;
            $stmt->execute([
                $user_id,
                $order_number,
                $data['shipping_address'],
                $data['shipping_city'],
                $data['shipping_zip'],
                $data['shipping_country'],
                $data['billing_address'],
                $data['billing_city'],
                $data['billing_zip'],
                $data['billing_country'],
                $total,
                'pending'
            ]);
            
            $order_id = $db->lastInsertId();
            
            // Add order items and update stock
            foreach ($_SESSION['cart'] as $product_id => $item) {
                // Check stock availability
                $stmt = $db->prepare('SELECT stock FROM products WHERE id = ? FOR UPDATE');
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                error_log("Stock for product $product_id: " . print_r($product, true));
                
                if (!$product || $product['stock'] < $item['quantity']) {
                    throw new Exception('Insufficient stock for one or more items');
                }
                
                // Add order item
                $stmt = $db->prepare('
                    INSERT INTO order_items (
                        order_id, product_id, quantity, price
                    ) VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([
                    $order_id,
                    $product_id,
                    $item['quantity'],
                    $item['price']
                ]);
                
                // Update stock
                $stmt = $db->prepare('
                    UPDATE products 
                    SET stock = stock - ? 
                    WHERE id = ?
                ');
                $stmt->execute([$item['quantity'], $product_id]);
            }
            
            $db->commit();
            error_log("Order processing completed successfully.");
            $success = true;
            
            // Save order number in session for the confirmation page
            $_SESSION['last_order_number'] = $order_number;
            
            // Clear the cart completely
            unset($_SESSION['cart']);
            if (isset($cart)) {
                $cart->clear(); // Also clear cart from database if using Cart class
            }
            
            // Redirect to order confirmation
            header("Location: order-confirmation.php?order=" . $order_number);
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Order processing failed: " . $e->getMessage());
            $errors[] = 'Order processing failed: ' . $e->getMessage();
        }
    }
    
    // Log errors during order processing
    if (!empty($errors)) {
        foreach ($errors as $error) {
            error_log("Error: " . $error);
        }
    }
}

// Get cart items for display
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $cart_items[] = $item;
        $total += $item['price'] * $item['quantity'];
    }
}
?>

<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Checkout Form -->
            <div class="md:w-2/3">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-6">Checkout</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <ul class="text-sm text-red-700">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <!-- Shipping Information -->
                        <div>
                            <h3 class="text-lg font-medium mb-4">Shipping Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" name="shipping_name" value="<?php echo isset($_POST['shipping_name']) ? htmlspecialchars($_POST['shipping_name']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <input type="text" name="shipping_address" value="<?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City</label>
                                    <input type="text" name="shipping_city" value="<?php echo isset($_POST['shipping_city']) ? htmlspecialchars($_POST['shipping_city']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">State</label>
                                    <input type="text" name="shipping_state" value="<?php echo isset($_POST['shipping_state']) ? htmlspecialchars($_POST['shipping_state']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ZIP Code</label>
                                    <input type="text" name="shipping_zip" value="<?php echo isset($_POST['shipping_zip']) ? htmlspecialchars($_POST['shipping_zip']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Country</label>
                                    <input type="text" name="shipping_country" value="<?php echo isset($_POST['shipping_country']) ? htmlspecialchars($_POST['shipping_country']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Billing Information -->
                        <div>
                            <h3 class="text-lg font-medium mb-4">Billing Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" name="billing_name" value="<?php echo isset($_POST['billing_name']) ? htmlspecialchars($_POST['billing_name']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <input type="text" name="billing_address" value="<?php echo isset($_POST['billing_address']) ? htmlspecialchars($_POST['billing_address']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City</label>
                                    <input type="text" name="billing_city" value="<?php echo isset($_POST['billing_city']) ? htmlspecialchars($_POST['billing_city']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">State</label>
                                    <input type="text" name="billing_state" value="<?php echo isset($_POST['billing_state']) ? htmlspecialchars($_POST['billing_state']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ZIP Code</label>
                                    <input type="text" name="billing_zip" value="<?php echo isset($_POST['billing_zip']) ? htmlspecialchars($_POST['billing_zip']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Country</label>
                                    <input type="text" name="billing_country" value="<?php echo isset($_POST['billing_country']) ? htmlspecialchars($_POST['billing_country']) : ''; ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="md:w-1/3">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium mb-4">Order Summary</h3>
                    
                    <div class="space-y-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="flex justify-between">
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($item['name']); ?></p>
                                    <p class="text-sm text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                                </div>
                                <p class="font-medium">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="border-t pt-4">
                            <div class="flex justify-between font-bold">
                                <p>Total</p>
                                <p>$<?php echo number_format($total, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>