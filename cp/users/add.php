<?php
$page_title = "Ajouter un utilisateur";
require_once '../includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['email', 'password', 'confirm_password', 'first_name', 'last_name'];
    $data = [];
    
    // Validate required fields
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' est requis';
        }
        $data[$field] = $_POST[$field] ?? '';
    }
    
    // Validate email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format d\'email invalide';
    }
    
    // Validate password
    if (!empty($data['password'])) {
        if (strlen($data['password']) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }
    }
    
    // Additional data
    $data['is_admin'] = isset($_POST['is_admin']) ? 1 : 0;
    
    // If no errors, proceed with adding user
    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'Cet email est déjà utilisé';
            } else {
                // Insert new user
                $stmt = $db->prepare('
                    INSERT INTO users (email, password, first_name, last_name, is_admin)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $data['email'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $data['first_name'],
                    $data['last_name'],
                    $data['is_admin']
                ]);
                
                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de l\'ajout : ' . $e->getMessage();
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Ajouter un utilisateur</h1>
    <a href="<?= SITE_URL ?>/cp/users/" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
        Retour à la liste
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Utilisateur ajouté avec succès</span>
    <a href="<?= SITE_URL ?>/cp/users/" class="font-bold underline ml-2">Retour à la liste</a>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
    <ul class="list-disc pl-5">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Add User Form -->
<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" name="first_name" id="first_name" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
            </div>
            
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="last_name" id="last_name" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input type="password" name="password" id="password" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 caractères</p>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            
            <div class="flex items-center mt-4">
                <input id="is_admin" name="is_admin" type="checkbox" 
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       <?= isset($_POST['is_admin']) ? 'checked' : '' ?>>
                <label for="is_admin" class="ml-2 block text-sm text-gray-900">
                    Administrateur
                </label>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Ajouter l'utilisateur
            </button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 