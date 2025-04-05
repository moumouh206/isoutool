<?php
require_once __DIR__ . '/../config/config.php';

$page_title = "Nous contacter";
$page_description = "Contactez l'équipe RS Components France pour toute question ou demande d'assistance";

include_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden p-6 mb-8">
        <h1 class="text-2xl font-bold text-rs-gray mb-4">Nous contacter</h1>
        <p class="text-gray-700 mb-6">Vous avez une question ou besoin d'assistance ? Notre équipe est à votre disposition pour vous aider. Contactez-nous par téléphone, email ou en remplissant le formulaire ci-dessous.</p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Contact Information -->
            <div>
                <h2 class="text-xl font-bold text-rs-gray mb-4">Informations de contact</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="font-bold text-rs-gray mb-2">Service client</h3>
                        <div class="flex items-start mb-2">
                            <i class="fas fa-phone-alt text-rs-red mt-1 mr-3"></i>
                            <div>
                                <p class="text-gray-700">0.825.034.034</p>
                                <p class="text-xs text-gray-500">(Service 0,15€ TTC/min + prix de l'appel)</p>
                            </div>
                        </div>
                        <div class="flex items-start mb-2">
                            <i class="fas fa-envelope text-rs-red mt-1 mr-3"></i>
                            <div>
                                <p class="text-gray-700">service.client@rs-components.fr</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-clock text-rs-red mt-1 mr-3"></i>
                            <div>
                                <p class="text-gray-700">Du lundi au vendredi</p>
                                <p class="text-gray-700">De 8h30 à 18h00</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-rs-gray mb-2">Siège social</h3>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-rs-red mt-1 mr-3"></i>
                            <div>
                                <p class="text-gray-700">RS Components SAS</p>
                                <p class="text-gray-700">Rue Norman King, CS40453</p>
                                <p class="text-gray-700">60031 Beauvais Cedex</p>
                                <p class="text-gray-700">France</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-rs-gray mb-2">Support technique</h3>
                        <div class="flex items-start mb-2">
                            <i class="fas fa-phone-alt text-rs-red mt-1 mr-3"></i>
                            <div>
                                <p class="text-gray-700">0.825.034.035</p>
                                <p class="text-xs text-gray-500">(Service 0,15€ TTC/min + prix de l'appel)</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-rs-red mt-1 mr-3"></i>
                            <div>
                                <p class="text-gray-700">support.technique@rs-components.fr</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="font-bold text-rs-gray mb-4">Suivez-nous</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-gray-200 hover:bg-rs-red hover:text-white w-10 h-10 flex items-center justify-center rounded-full transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-gray-200 hover:bg-rs-red hover:text-white w-10 h-10 flex items-center justify-center rounded-full transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-gray-200 hover:bg-rs-red hover:text-white w-10 h-10 flex items-center justify-center rounded-full transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="bg-gray-200 hover:bg-rs-red hover:text-white w-10 h-10 flex items-center justify-center rounded-full transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-rs-light-gray p-6 rounded-lg">
                <h2 class="text-xl font-bold text-rs-gray mb-4">Envoyez-nous un message</h2>

                <form action="#" method="post" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="firstName" class="block text-gray-700 font-medium mb-1">Prénom <span class="text-rs-red">*</span></label>
                            <input type="text" id="firstName" name="firstName" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rs-red">
                        </div>
                        <div>
                            <label for="lastName" class="block text-gray-700 font-medium mb-1">Nom <span class="text-rs-red">*</span></label>
                            <input type="text" id="lastName" name="lastName" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rs-red">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-gray-700 font-medium mb-1">Email <span class="text-rs-red">*</span></label>
                            <input type="email" id="email" name="email" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rs-red">
                        </div>
                        <div>
                            <label for="phone" class="block text-gray-700 font-medium mb-1">Téléphone</label>
                            <input type="tel" id="phone" name="phone" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rs-red">
                        </div>
                    </div>

                    <div>
                        <label for="company" class="block text-gray-700 font-medium mb-1">Société</label>
                        <input type="text" id="company" name="company" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rs-red">
                    </div>

                    <div>
                        <label for="subject" class="block text-gray-700 font-medium mb-1">Sujet <span class="text-rs-red">*</span></label>
                        <select id="subject" name="subject" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rs-red">
                            <option value="">Sélectionnez un sujet</option>
                            <option value="commande">Question sur une commande</option>
                            <option value="produit">Information produit</option>
                            <option value="technique">Support technique</option>
                            <option value="compte">Gestion de compte</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div>
                        <label for="message" class="block text-gray-700 font-medium mb-1">Message <span class="text-rs-red">*</span></label>
                        <textarea id="message" name="message" rows="5" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rs-red"></textarea>
                    </div>

                    <div class="flex items-start">
                        <input type="checkbox" id="privacy" name="privacy" required class="mt-1 mr-2">
                        <label for="privacy" class="text-sm text-gray-600">
                            J'accepte que mes données soient traitées conformément à la <a href="#" class="text-rs-red hover:underline">politique de confidentialité</a> de RS Components <span class="text-rs-red">*</span>
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary px-6 py-2">
                            <i class="fas fa-paper-plane mr-2"></i> Envoyer le message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Offices Map -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden p-6">
        <h2 class="text-xl font-bold text-rs-gray mb-4">Nos agences en France</h2>
        <div class="aspect-w-16 aspect-h-9 h-96 mb-6">
            <!-- Placeholder for a map - in a real implementation, this would be a Google Maps embed or similar -->
            <div class="w-full h-full bg-rs-light-gray flex items-center justify-center">
                <span class="text-gray-500">Carte des agences RS Components en France</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Office 1 -->
            <div class="border border-gray-200 rounded p-4">
                <h3 class="font-bold text-rs-gray mb-2">Paris</h3>
                <p class="text-gray-700 mb-1">123 Avenue des Champs-Élysées</p>
                <p class="text-gray-700 mb-1">75008 Paris</p>
                <p class="text-gray-700 mb-4">France</p>
                <p class="text-gray-700"><i class="fas fa-phone-alt text-rs-red mr-2"></i> 01 45 67 89 10</p>
            </div>

            <!-- Office 2 -->
            <div class="border border-gray-200 rounded p-4">
                <h3 class="font-bold text-rs-gray mb-2">Lyon</h3>
                <p class="text-gray-700 mb-1">456 Rue de la République</p>
                <p class="text-gray-700 mb-1">69002 Lyon</p>
                <p class="text-gray-700 mb-4">France</p>
                <p class="text-gray-700"><i class="fas fa-phone-alt text-rs-red mr-2"></i> 04 78 90 12 34</p>
            </div>

            <!-- Office 3 -->
            <div class="border border-gray-200 rounded p-4">
                <h3 class="font-bold text-rs-gray mb-2">Marseille</h3>
                <p class="text-gray-700 mb-1">789 Boulevard Michelet</p>
                <p class="text-gray-700 mb-1">13008 Marseille</p>
                <p class="text-gray-700 mb-4">France</p>
                <p class="text-gray-700"><i class="fas fa-phone-alt text-rs-red mr-2"></i> 04 91 23 45 67</p>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
