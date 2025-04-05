<?php
require_once __DIR__ . '/../config/config.php';

$page_title = "Mentions légales";
$page_description = "Mentions légales et conditions générales d'utilisation - RS Components France";

include_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden p-6">
        <h1 class="text-2xl font-bold text-rs-gray mb-6">Mentions légales</h1>

        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-rs-gray mb-3">1. Informations légales</h2>
                <p class="text-gray-700 mb-2">Conformément aux dispositions des articles 6-III et 19 de la Loi n° 2004-575 du 21 juin 2004 pour la Confiance dans l'économie numérique, dite L.C.E.N., nous portons à la connaissance des utilisateurs et visiteurs du site les informations suivantes :</p>

                <h3 class="font-bold text-rs-gray mb-2">1.1 Informations légales :</h3>
                <p class="text-gray-700 mb-2">Statut du propriétaire : Société</p>
                <p class="text-gray-700 mb-2">Raison sociale : RS Components SAS</p>
                <p class="text-gray-700 mb-2">Adresse : Rue Norman King, CS40453, 60031 Beauvais Cedex</p>
                <p class="text-gray-700 mb-2">Téléphone : 0.825.034.034 (Service 0,15€ TTC/min + prix de l'appel)</p>
                <p class="text-gray-700 mb-2">SIRET : 327 014 973 00073</p>
                <p class="text-gray-700 mb-2">Numéro de TVA intracommunautaire : FR 89 327 014 973</p>
                <p class="text-gray-700 mb-2">Adresse de courrier électronique : service.client@rs-components.fr</p>

                <h3 class="font-bold text-rs-gray mb-2 mt-4">1.2 Directeur de la publication :</h3>
                <p class="text-gray-700 mb-2">Monsieur Jean Dupont, Directeur Général</p>

                <h3 class="font-bold text-rs-gray mb-2 mt-4">1.3 Hébergeur :</h3>
                <p class="text-gray-700 mb-2">Raison sociale : OVH SAS</p>
                <p class="text-gray-700 mb-2">Adresse : 2 rue Kellermann - 59100 Roubaix - France</p>
                <p class="text-gray-700 mb-2">Téléphone : 09 72 10 10 10</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-rs-gray mb-3">2. Présentation et principe</h2>
                <p class="text-gray-700 mb-2">Est désigné ci-après : Utilisateur, tout internaute se connectant et utilisant le site susnommé : <a href="<?= SITE_URL ?>" class="text-rs-red hover:underline">fr.rs-online.com</a>.</p>
                <p class="text-gray-700 mb-2">Le site <a href="<?= SITE_URL ?>" class="text-rs-red hover:underline">fr.rs-online.com</a> regroupe un ensemble de services, dans l'état, mis à la disposition des utilisateurs. Il est précisé que les utilisateurs doivent rester courtois et faire preuve de bonne foi tant envers les autres utilisateurs qu'envers le webmaster du site. Le site est mis à jour régulièrement par le webmaster.</p>
                <p class="text-gray-700 mb-2">RS Components SAS s'efforce de fournir sur le site des informations les plus précises possibles. Toutefois, il ne pourra être tenu responsable des omissions, des inexactitudes et des carences dans la mise à jour, qu'elles soient de son fait ou du fait des tiers partenaires qui lui fournissent ces informations.</p>
                <p class="text-gray-700 mb-2">Toutes les informations indiquées sur le site sont données à titre indicatif, et sont susceptibles d'évoluer. Par ailleurs, les renseignements figurant sur le site ne sont pas exhaustifs. Ils sont donnés sous réserve de modifications ayant été apportées depuis leur mise en ligne.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-rs-gray mb-3">3. Accessibilité</h2>
                <p class="text-gray-700 mb-2">Le site Internet est normalement accessible à tout moment aux utilisateurs. Une interruption pour raison de maintenance technique peut être toutefois décidée par RS Components SAS, qui s'efforcera alors de communiquer préalablement aux utilisateurs les dates et heures de l'intervention.</p>
                <p class="text-gray-700 mb-2">Le site <a href="<?= SITE_URL ?>" class="text-rs-red hover:underline">fr.rs-online.com</a> est mis à jour régulièrement par le propriétaire. De la même façon, les mentions légales peuvent être modifiées à tout moment : elles s'imposent néanmoins à l'utilisateur qui est invité à s'y référer le plus souvent possible afin d'en prendre connaissance.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-rs-gray mb-3">4. Propriété intellectuelle et contrefaçons</h2>
                <p class="text-gray-700 mb-2">RS Components SAS est propriétaire des droits de propriété intellectuelle et détient les droits d'usage sur tous les éléments accessibles sur le site, notamment les textes, images, graphismes, logo, icônes, sons, logiciels…</p>
                <p class="text-gray-700 mb-2">Toute reproduction, représentation, modification, publication, adaptation totale ou partielle des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable à l'email : service.client@rs-components.fr.</p>
                <p class="text-gray-700 mb-2">Toute exploitation non autorisée du site ou de l'un quelconque de ces éléments qu'il contient sera considérée comme constitutive d'une contrefaçon et poursuivie conformément aux dispositions des articles L.335-2 et suivants du Code de Propriété Intellectuelle.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-rs-gray mb-3">5. Cookies</h2>
                <p class="text-gray-700 mb-2">L'Utilisateur est informé que lors de ses visites sur le site, un cookie peut s'installer automatiquement sur son logiciel de navigation.</p>
                <p class="text-gray-700 mb-2">Un cookie est un élément qui ne permet pas d'identifier l'Utilisateur mais sert à enregistrer des informations relatives à la navigation de celui-ci sur le site Internet. L'Utilisateur pourra désactiver ce cookie par l'intermédiaire des paramètres figurant au sein de son logiciel de navigation.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-rs-gray mb-3">6. Protection des données personnelles</h2>
                <p class="text-gray-700 mb-2">En France, les données personnelles sont notamment protégées par la loi n° 78-87 du 6 janvier 1978, la loi n° 2004-801 du 6 août 2004, l'article L. 226-13 du Code pénal et la Directive européenne du 24 octobre 1995.</p>
                <p class="text-gray-700 mb-2">Conformément au Règlement Général sur la Protection des Données (RGPD), RS Components s'engage à protéger la vie privée de ses utilisateurs.</p>
                <p class="text-gray-700 mb-2">Pour toute information complémentaire sur la protection des données personnelles, nous vous invitons à consulter notre <a href="#" class="text-rs-red hover:underline">Politique de confidentialité</a>.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-rs-gray mb-3">7. Loi applicable et juridiction compétente</h2>
                <p class="text-gray-700 mb-2">Les présentes conditions du site sont régies par les lois françaises et toute contestation ou litiges qui pourraient naître de l'interprétation ou de l'exécution de celles-ci seront de la compétence exclusive des tribunaux dont dépend le siège social de la société. La langue de référence, pour le règlement de contentieux éventuels, est le français.</p>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
