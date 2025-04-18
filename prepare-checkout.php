<?php
require_once 'includes/init.php';

// Make sure user has items in cart
$cartItems = $cart->getItems();
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Prepare session for checkout
$_SESSION['cart'] = [];
foreach ($cartItems as $item) {
    $_SESSION['cart'][$item['product_id']] = [
        'product_id' => $item['product_id'],
        'name' => $item['name'],
        'price' => $item['price'],
        'quantity' => $item['quantity'],
        'total_price' => $item['total_price'],
        'reference' => $item['reference']
    ];
}

// Redirect to checkout
header('Location: checkout.php');
exit;