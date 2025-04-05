<?php
require_once '../includes/init.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['email', 'password', 'confirm_password', 'first_name', 'last_name'];
    $data = [];
    
    // Validate required fields
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
        $data[$field] = $_POST[$field] ?? '';
    }
    
    // Validate email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Validate password
    if (!empty($data['password'])) {
        if (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            } else {
                // Insert new user
                $stmt = $db->prepare('
                    INSERT INTO users (email, password, first_name, last_name)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([
                    $data['email'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $data['first_name'],
                    $data['last_name']
                ]);
                
                $success = true;
                // We'll show a success message instead of redirecting
            }
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<!-- Register Form -->
<div class="min-h-screen bg-rs-light-gray flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <?php if ($success): ?>
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 max-w-md w-full bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-md" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <p class="font-bold">Inscription réussie !</p>
        </div>
        <p class="text-sm">Votre compte a été créé avec succès. Vous allez être redirigé vers la page de connexion...</p>
    </div>
    <script>
        // Redirect after 5 seconds
        setTimeout(function() {
            window.location.href = '<?= SITE_URL ?>/auth/login.php?registered=1';
        }, 5000);
    </script>
    <?php endif; ?>

    <div class="max-w-xl w-full space-y-8 bg-white p-8 rounded-lg shadow-lg">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-rs-gray">Créer un compte</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ou
                <a href="<?= SITE_URL ?>/auth/login.php" class="font-medium text-rs-red hover:text-red-700">
                    se connecter
                </a>
            </p>
        </div>
        <?php if (!$success): ?>
        <form class="mt-8 space-y-6" action="<?= SITE_URL ?>/auth/register.php" method="POST">
            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom</label>
                    <input id="first_name" name="first_name" type="text" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-rs-red focus:border-rs-red focus:z-10 sm:text-sm"
                           placeholder="Votre prénom">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Nom</label>
                    <input id="last_name" name="last_name" type="text" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-rs-red focus:border-rs-red focus:z-10 sm:text-sm"
                           placeholder="Votre nom">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-rs-red focus:border-rs-red focus:z-10 sm:text-sm"
                           placeholder="Votre email">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-rs-red focus:border-rs-red focus:z-10 sm:text-sm"
                           placeholder="Votre mot de passe">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                    <input id="confirm_password" name="confirm_password" type="password" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-rs-red focus:border-rs-red focus:z-10 sm:text-sm"
                           placeholder="Confirmez votre mot de passe">
                </div>
            </div>

            <div class="flex items-center">
                <input id="terms" name="terms" type="checkbox" required
                       class="h-4 w-4 text-rs-red focus:ring-rs-red border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-900">
                    J'accepte les <a href="<?= SITE_URL ?>/terms.php" class="text-rs-red hover:text-red-700">conditions d'utilisation</a>
                </label>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-rs-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rs-red transition duration-150 ease-in-out">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-white group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Créer un compte
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="text-center py-8">
            <h3 class="text-xl font-medium text-green-600">Votre compte a été créé avec succès !</h3>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 