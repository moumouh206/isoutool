<?php
require_once '../includes/header.php';

// Get filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : '';
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : '';
$stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';

try {
    // Build the query based on filters
    $whereClause = [];
    $params = [];
    
    if (!empty($search)) {
        $whereClause[] = '(p.name LIKE ? OR p.reference LIKE ? OR p.description LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($category_id)) {
        $whereClause[] = 'pc.category_id = ?';
        $params[] = $category_id;
    }
    
    if (!empty($brand_id)) {
        $whereClause[] = 'p.brand_id = ?';
        $params[] = $brand_id;
    }
    
    if ($stock_status === 'in_stock') {
        $whereClause[] = 'p.stock > 0';
    } elseif ($stock_status === 'out_of_stock') {
        $whereClause[] = 'p.stock = 0';
    } elseif ($stock_status === 'low_stock') {
        $whereClause[] = 'p.stock > 0 AND p.stock <= 10';
    }
    
    $whereStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';
    
    // Get products
    $query = "
        SELECT DISTINCT p.*, 
               b.name as brand_name,
               (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c 
                JOIN product_category pc ON c.id = pc.category_id 
                WHERE pc.product_id = p.id) as categories
        FROM products p
        LEFT JOIN product_category pc ON p.id = pc.product_id
        LEFT JOIN brands b ON p.brand_id = b.id
        $whereStr
        GROUP BY p.id
        ORDER BY p.id DESC
    ";
    
    $stmt = $db->prepare($query);
    
    // Bind parameters for filters
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    
    $products = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=produits_' . date('Y-m-d') . '.csv');
    
    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Set the UTF-8 BOM (Byte Order Mark)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Column headers
    fputcsv($output, [
        'ID', 
        'Référence', 
        'Nom', 
        'Description', 
        'Prix', 
        'Stock', 
        'Catégories', 
        'Marque', 
        'En vedette',
        'Date de création',
        'Date de mise à jour'
    ]);
    
    // Add rows for each product
    foreach ($products as $product) {
        fputcsv($output, [
            $product['id'],
            $product['reference'] ?? '',
            $product['name'],
            $product['description'] ?? '',
            number_format($product['price'], 2, ',', ''),
            $product['stock'],
            $product['categories'] ?? '',
            $product['brand_name'] ?? '',
            $product['featured'] ? 'Oui' : 'Non',
            date('d/m/Y H:i', strtotime($product['created_at'])),
            date('d/m/Y H:i', strtotime($product['updated_at']))
        ]);
    }
    
    // Close the file pointer
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    // Redirect back to products page with error message
    $_SESSION['error'] = "Erreur lors de l'exportation des produits : " . $e->getMessage();
    header('Location: ' . SITE_URL . '/cp/products/');
    exit;
} 