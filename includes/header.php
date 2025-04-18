<?php
ob_start();
require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME ?></title>
    <meta name="description" content="<?= isset($page_description) ? $page_description : SITE_DESCRIPTION ?>">
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/input.css">
    <script src="<?= SITE_URL ?>/public/js/script.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-rs-light-gray min-h-screen flex flex-col">
    <!-- Top Bar -->
    <div class="bg-rs-red text-white py-1">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="#" class="text-sm hover:underline">Centre d'aide</a>
                <a href="#" class="text-sm hover:underline">Nous contacter</a>
            </div>
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-sm">Bonjour, <?= htmlspecialchars($_SESSION['user_first_name'] ?? 'utilisateur') ?></span>
                    <a href="<?= SITE_URL ?>/auth/logout.php" class="text-sm hover:underline">Se déconnecter</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/auth/register.php" class="text-sm hover:underline">Créer un compte</a>
                    <a href="<?= SITE_URL ?>/auth/login.php" class="text-sm hover:underline">Se connecter</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="<?= SITE_URL ?>" class="flex items-center">
                    <div class="bg-rs-red text-white p-2 font-bold text-2xl mr-2">ISOU</div>
                    <span class="text-rs-gray font-semibold hidden md:block">TOOLS</span>
                </a>

                <!-- Search Bar -->
                <div class="flex-1 mx-8">
                    <form action="<?= SITE_URL ?>/search.php" method="GET" class="relative w-full">
                        <input type="text" name="q" placeholder="Rechercher des produits, des marques, des références..."
                               class="w-full py-2 px-4 border border-gray-300 rounded-l focus:outline-none focus:ring-2 focus:ring-rs-red">
                        <button type="submit" class="absolute right-0 top-0 bottom-0 bg-rs-red text-white px-4 rounded-r hover:bg-red-700 transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Navigation Icons -->
                <div class="flex items-center space-x-6">
                    <a href="<?= SITE_URL ?>/favorites.php" class="text-rs-gray hover:text-rs-red">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </a>
                    <a href="<?= SITE_URL ?>/cart.php" class="text-rs-gray hover:text-rs-red">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </a>
                    <button class="md:hidden text-rs-gray hover:text-rs-red" id="mobile-menu-button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <nav class="bg-white border-t border-gray-200 hidden md:block">
            <div class="container mx-auto px-4">
                <ul class="flex">
                    <li class="group relative">
                        <a href="#" class="block py-3 px-4 font-medium text-rs-gray hover:text-rs-red border-b-2 border-transparent hover:border-rs-red">
                            Produits par catégorie
                        </a>
                        <div class="absolute left-0 top-full bg-white shadow-lg rounded-b w-64 hidden group-hover:block z-10">
                            <ul class="py-2">
                                <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Composants électroniques</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Automatismes industriels</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Outillage</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Électricité</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Connectique</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Mécanique</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Test et mesure</a></li>
                            </ul>
                        </div>
                    </li>
                    <li><a href="#" class="block py-3 px-4 font-medium text-rs-gray hover:text-rs-red border-b-2 border-transparent hover:border-rs-red">Marques</a></li>
                    <li><a href="#" class="block py-3 px-4 font-medium text-rs-gray hover:text-rs-red border-b-2 border-transparent hover:border-rs-red">Promotions</a></li>
                    <li><a href="#" class="block py-3 px-4 font-medium text-rs-gray hover:text-rs-red border-b-2 border-transparent hover:border-rs-red">Nouveautés</a></li>
                    <li><a href="#" class="block py-3 px-4 font-medium text-rs-gray hover:text-rs-red border-b-2 border-transparent hover:border-rs-red">Solutions</a></li>
                    <li><a href="#" class="block py-3 px-4 font-medium text-rs-gray hover:text-rs-red border-b-2 border-transparent hover:border-rs-red">Services</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Mobile Search (visible on mobile only) -->
    <div class="md:hidden p-4 bg-white border-t border-gray-200">
        <div class="relative">
            <form action="<?= SITE_URL ?>/search.php" method="GET" class="relative w-full">
                <input type="text" name="q" placeholder="Rechercher..."
                       class="w-full py-2 px-4 border border-gray-300 rounded-l focus:outline-none focus:ring-2 focus:ring-rs-red">
                <button type="submit" class="absolute right-0 top-0 bottom-0 bg-rs-red text-white px-4 rounded-r hover:bg-red-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Mobile Menu (hidden by default) -->
    <div class="md:hidden hidden bg-white border-t border-gray-200" id="mobile-menu">
        <ul class="py-2">
            <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Produits par catégorie</a></li>
            <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Marques</a></li>
            <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Promotions</a></li>
            <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Nouveautés</a></li>
            <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Solutions</a></li>
            <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Services</a></li>
            <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Centre d'aide</a></li>
            <li><a href="#" class="block px-4 py-2 hover:bg-rs-light-gray">Nous contacter</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?= SITE_URL ?>/auth/logout.php" class="block px-4 py-2 hover:bg-rs-light-gray">Se déconnecter</a></li>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/auth/login.php" class="block px-4 py-2 hover:bg-rs-light-gray">Se connecter</a></li>
                <li><a href="<?= SITE_URL ?>/auth/register.php" class="block px-4 py-2 hover:bg-rs-light-gray">Créer un compte</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main Content -->
    <main class="flex-grow"><?php if(isset($breadcrumbs)): ?>
        <div class="bg-white border-b border-gray-200">
            <div class="container mx-auto px-4 py-2">
                <div class="flex items-center text-sm">
                    <a href="<?= SITE_URL ?>" class="text-rs-gray hover:text-rs-red">Accueil</a>
                    <?php foreach($breadcrumbs as $label => $url): ?>
                        <span class="mx-2 text-gray-400">/</span>
                        <?php if($url): ?>
                            <a href="<?= $url ?>" class="text-rs-gray hover:text-rs-red"><?= $label ?></a>
                        <?php else: ?>
                            <span class="text-rs-gray"><?= $label ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
