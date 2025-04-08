<?php
require_once '../includes/init.php';

$errors = [];
$login_success = false;

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Check if there's a redirect URL
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate login data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        try {
            $stmt = $db->prepare('SELECT id, email, password, first_name, last_name FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_first_name'] = $user['first_name'];
                $_SESSION['user_last_name'] = $user['last_name'];
                
                // Transfer cart items if any
                if (isset($cart)) {
                    $cart->transferSessionCartToUser($user['id']);
                }
                
                // Redirect to home page or dashboard
                header('Location: ' . SITE_URL);
                exit;
            } else {
                $errors[] = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $errors[] = 'Login failed. Please try again.';
        }
    }
}

// Then include the header which will start HTML output
require_once '../includes/header.php';
?>

<!-- Login Form -->
<div class="min-h-screen bg-rs-light-gray flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-8 bg-white p-8 rounded-lg shadow-lg">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-rs-gray">Connexion</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ou
                <a href="<?= SITE_URL ?>/auth/register.php" class="font-medium text-rs-red hover:text-red-700">
                    créer un compte
                </a>
            </p>
        </div>
        <form class="mt-8 space-y-6" action="<?= SITE_URL ?>/auth/login.php" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
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
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox" 
                           class="h-4 w-4 text-rs-red focus:ring-rs-red border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                        Se souvenir de moi
                    </label>
                </div>

                <div class="text-sm">
                    <a href="<?= SITE_URL ?>/auth/forgot-password.php" class="font-medium text-rs-red hover:text-red-700">
                        Mot de passe oublié ?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-rs-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rs-red transition duration-150 ease-in-out">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-white group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Se connecter
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 