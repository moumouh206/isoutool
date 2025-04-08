<?php
require_once '../includes/header.php';

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
    
    // Get orders
    $query = "
        SELECT o.*, u.first_name, u.last_name, u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        $whereStr
        ORDER BY o.created_at DESC
    ";
    
    $stmt = $db->prepare($query);
    
    // Bind parameters for filters
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    
    $orders = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=commandes_' . date('Y-m-d') . '.csv');
    
    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Set the UTF-8 BOM (Byte Order Mark)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Column headers
    fputcsv($output, [
        'ID', 
        'Numéro de commande', 
        'Date', 
        'Client', 
        'Email', 
        'Statut', 
        'Méthode de paiement', 
        'Statut paiement', 
        'Sous-total', 
        'Livraison', 
        'TVA', 
        'Total',
        'Adresse de livraison',
        'Adresse de facturation'
    ]);
    
    // Add rows for each order
    foreach ($orders as $order) {
        // Format address
        $shippingAddress = trim(
            ($order['shipping_address_line_1'] ?? '') . ' ' . 
            ($order['shipping_address_line_2'] ?? '') . ', ' . 
            ($order['shipping_postal_code'] ?? '') . ' ' . 
            ($order['shipping_city'] ?? '') . ', ' . 
            ($order['shipping_country'] ?? '')
        );
        
        $billingAddress = trim(
            ($order['billing_address_line_1'] ?? '') . ' ' . 
            ($order['billing_address_line_2'] ?? '') . ', ' . 
            ($order['billing_postal_code'] ?? '') . ' ' . 
            ($order['billing_city'] ?? '') . ', ' . 
            ($order['billing_country'] ?? '')
        );
        
        fputcsv($output, [
            $order['id'],
            $order['order_number'],
            date('d/m/Y H:i', strtotime($order['created_at'])),
            (!empty($order['first_name']) && !empty($order['last_name'])) 
                ? $order['first_name'] . ' ' . $order['last_name'] 
                : 'Utilisateur supprimé',
            $order['email'] ?? '',
            ucfirst($order['status']),
            $order['payment_method'] ?? 'N/A',
            ucfirst($order['payment_status'] ?? 'N/A'),
            number_format($order['subtotal'], 2, ',', ''),
            number_format($order['shipping'], 2, ',', ''),
            number_format($order['tax'], 2, ',', ''),
            number_format($order['total'], 2, ',', ''),
            $shippingAddress,
            $billingAddress
        ]);
    }
    
    // Close the file pointer
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    // Redirect back to orders page with error message
    $_SESSION['error'] = "Erreur lors de l'exportation des commandes : " . $e->getMessage();
    header('Location: ' . SITE_URL . '/cp/orders/');
    exit;
} 