<?php 
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/admin_auth.php';

// Check admin authentication
checkAdminAuth();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Admin - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Top Navigation -->
    <nav class="bg-indigo-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-white font-bold text-xl">ISOU TOOLS Admin</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-sm mr-4">
                        <?= htmlspecialchars($_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name']) ?>
                    </span>
                    <a href="<?= SITE_URL ?>/auth/logout.php" class="text-white bg-indigo-800 hover:bg-indigo-900 px-3 py-2 rounded-md text-sm font-medium">
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex flex-1">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md">
            <div class="py-4 px-2">
                <div class="mb-8 px-4">
                    <a href="<?= SITE_URL ?>/cp/index.php" class="flex items-center space-x-2">
                        <span class="text-lg font-medium text-gray-900">Dashboard</span>
                    </a>
                </div>
                
                <nav class="space-y-1">
                    <a href="<?= SITE_URL ?>/cp/users/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-md">
                        <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Utilisateurs
                    </a>
                    <a href="<?= SITE_URL ?>/cp/products/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-md">
                        <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Produits
                    </a>
                    <a href="<?= SITE_URL ?>/cp/categories/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-md">
                        <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Catégories
                    </a>
                    <a href="<?= SITE_URL ?>/cp/orders/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-md">
                        <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Commandes
                    </a>
                    <a href="<?= SITE_URL ?>/cp/news/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-md">
                        <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        Actualités
                    </a>
                    <a href="<?= SITE_URL ?>/cp/reviews/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-md">
                        <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        Avis
                    </a>
                    <a href="<?= SITE_URL ?>/cp/brands/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-md">
                        <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Marques
                    </a>
                    <a href="<?= SITE_URL ?>" class="flex items-center px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-md">
                        <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Retour au site
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6"> 