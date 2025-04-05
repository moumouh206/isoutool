</main>

    <!-- Newsletter -->
    <section class="bg-rs-gray text-white py-8">
        <div class="container mx-auto px-4">
            <div class="md:flex md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-xl font-bold mb-2">Inscrivez-vous à notre newsletter</h3>
                    <p class="text-sm">Restez informé des dernières nouveautés et promotions</p>
                </div>
                <div class="flex">
                    <input type="email" placeholder="Votre adresse email" class="px-4 py-2 rounded-l w-full md:w-64 focus:outline-none">
                    <button class="bg-rs-red text-white px-4 py-2 rounded-r font-medium hover:bg-red-700 transition">S'inscrire</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-rs-black text-white pt-12 pb-6">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Column 1 -->
                <div>
                    <h4 class="text-lg font-bold mb-4">À propos de RS</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Qui sommes-nous</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Nos engagements</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Carrières</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Relations investisseurs</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Responsabilité sociale</a></li>
                    </ul>
                </div>

                <!-- Column 2 -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Service client</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Centre d'aide</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Nous contacter</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Livraison</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Retours</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Moyens de paiement</a></li>
                    </ul>
                </div>

                <!-- Column 3 -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Ressources</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Centre de documentation</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">DesignSpark</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Webinaires</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Calculatrices</a></li>
                    </ul>
                </div>

                <!-- Column 4 -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Suivez-nous</h4>
                    <div class="flex space-x-4 mb-4">
                        <a href="#" class="bg-gray-700 hover:bg-rs-red w-10 h-10 flex items-center justify-center rounded-full transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-gray-700 hover:bg-rs-red w-10 h-10 flex items-center justify-center rounded-full transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-gray-700 hover:bg-rs-red w-10 h-10 flex items-center justify-center rounded-full transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="bg-gray-700 hover:bg-rs-red w-10 h-10 flex items-center justify-center rounded-full transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                    <h4 class="text-lg font-bold mb-4">Téléchargez l'application</h4>
                    <div class="flex space-x-2">
                        <a href="#" class="block">
                            <img src="<?= SITE_URL ?>/assets/images/app-store.png" alt="App Store" class="h-10">
                        </a>
                        <a href="#" class="block">
                            <img src="<?= SITE_URL ?>/assets/images/google-play.png" alt="Google Play" class="h-10">
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-gray-700">
                <div class="flex flex-col md:flex-row justify-between">
                    <div class="mb-4 md:mb-0">
                        <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> RS Components SAS. Tous droits réservés.</p>
                        <div class="flex flex-wrap mt-2">
                            <a href="#" class="text-xs text-gray-400 mr-4 hover:text-white">Mentions légales</a>
                            <a href="#" class="text-xs text-gray-400 mr-4 hover:text-white">Politique de confidentialité</a>
                            <a href="#" class="text-xs text-gray-400 mr-4 hover:text-white">Cookies</a>
                            <a href="#" class="text-xs text-gray-400 hover:text-white">Conditions d'utilisation</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-400 mr-4">Paiement sécurisé</span>
                        <div class="flex space-x-2">
                            <img src="<?= SITE_URL ?>/assets/images/visa.png" alt="Visa" class="h-6">
                            <img src="<?= SITE_URL ?>/assets/images/mastercard.png" alt="Mastercard" class="h-6">
                            <img src="<?= SITE_URL ?>/assets/images/amex.png" alt="American Express" class="h-6">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
