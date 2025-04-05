<?php
require_once 'includes/init.php';
require_once 'includes/header.php';

$message = '';
$messageType = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    if (isset($_POST['product_id'])) {
                        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                        $cart->addItem($_POST['product_id'], $quantity);
                        $message = 'Item added to cart successfully';
                        $messageType = 'success';
                    }
                    break;

                case 'update':
                    if (isset($_POST['quantity']) && isset($_POST['product_id'])) {
                        $cart->updateQuantity($_POST['product_id'], (int)$_POST['quantity']);
                        $message = 'Cart updated successfully';
                        $messageType = 'success';
                    }
                    break;
                    
                case 'remove':
                    if (isset($_POST['product_id'])) {
                        $cart->removeItem($_POST['product_id']);
                        $message = 'Item removed from cart';
                        $messageType = 'success';
                    }
                    break;
                    
                case 'clear':
                    $cart->clear();
                    $message = 'Cart cleared';
                    $messageType = 'success';
                    break;
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get cart items
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-8">Shopping Cart</h1>
    
    <?php if (!empty($message)): ?>
        <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($cartItems)): ?>
        <div class="text-center py-8">
            <p class="text-gray-500 mb-4">Your cart is empty</p>
            <a href="index.php" class="inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Cart Items -->
            <div class="md:w-2/3">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Ref: <?php echo htmlspecialchars($item['reference']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            €<?php echo number_format($item['price'], 2); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" class="flex items-center space-x-2">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                                   min="1" max="<?php echo $item['stock']; ?>"
                                                   class="w-20 px-2 py-1 border rounded">
                                            <button type="submit" class="text-blue-600 hover:text-blue-900">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            €<?php echo number_format($item['total_price'], 2); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 flex justify-between items-center">
                    <a href="index.php" class="text-blue-600 hover:text-blue-900">
                        Continue Shopping
                    </a>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="text-red-600 hover:text-red-900">
                            Clear Cart
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="md:w-1/3">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Cart Summary</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span>€<?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="border-t pt-4">
                            <div class="flex justify-between font-semibold">
                                <span>Total</span>
                                <span>€<?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                        </div>
                        <a href="checkout.php" class="block w-full bg-blue-500 text-white text-center px-6 py-3 rounded-md hover:bg-blue-600">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 