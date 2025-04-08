<?php
// Admin authentication middleware
function checkAdminAuth() {
    // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        // Store the requested URL for redirection after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header('Location: ' . SITE_URL . '/cp/login.php');
        exit;
    }
} 