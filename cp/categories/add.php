<?php
$page_title = "Ajouter une catégorie";
require_once '../includes/header.php';

$errors = [];
$success = false;

// Get parent categories for dropdown
try {
    $stmt = $db->query('SELECT id, name FROM categories ORDER BY name ASC');
    $parentCategories = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Erreur lors de la récupération des catégories : ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['name'];
    $data = [];
    
    // Validate required fields
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' est requis';
        }
        $data[$field] = $_POST[$field] ?? '';
    }
    
    // Additional data
    $data['description'] = $_POST['description'] ?? '';
    
    // If no errors, proceed with adding category
    if (empty($errors)) {
        try {
            // Check if name already exists
            $stmt = $db->prepare('SELECT id FROM categories WHERE name = :name');
            $stmt->bindParam(':name', $data['name']);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $errors[] = 'Une catégorie avec ce nom existe déjà.';
            } else {
                // Proceed with adding category
                $stmt = $db->prepare('INSERT INTO categories (name, description) VALUES (:name, :description)');
                $stmt->bindParam(':name', $data['name']);
                $stmt->bindParam(':description', $data['description']);
                if (!$stmt->execute()) {
                    $errors[] = 'Erreur lors de la création de la catégorie: ' . $stmt->errorInfo()[2];
                } else {
                    $success = true;
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Erreur lors de la création de la catégorie: ' . $e->getMessage();
        }
    }
}

require_once '../includes/footer.php'; ?> 