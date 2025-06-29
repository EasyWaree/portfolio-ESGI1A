<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/User.php';

$action = $_GET['action'] ?? 'login';
$userClass = new User();

if (isset($_SESSION['user_id']) && $action !== 'logout') {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Token de sécurité invalide.";
    } else {
        if ($action === 'login') {
            $username = htmlspecialchars(trim($_POST['username'] ?? ''));
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            if (empty($username)) $errors[] = "Le nom d'utilisateur est requis";
            if (empty($password)) $errors[] = "Le mot de passe est requis";
            
            if (empty($errors)) {
                $result = $userClass->login($username, $password, $remember);
                if ($result['success']) {
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $errors[] = $result['error'];
                }
            }
        } elseif ($action === 'register') {
            $formData = [
                'username' => htmlspecialchars(trim($_POST['username'] ?? '')),
                'email' => htmlspecialchars(trim($_POST['email'] ?? '')),
                'first_name' => htmlspecialchars(trim($_POST['first_name'] ?? '')),
                'last_name' => htmlspecialchars(trim($_POST['last_name'] ?? '')),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];
            
            $result = $userClass->register($formData);
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                header('Location: auth.php?action=login');
                exit;
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

if ($action === 'logout') {
    $userClass->logout();
    $_SESSION['success'] = 'Vous avez été déconnecté avec succès.';
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'register' ? 'Inscription' : 'Connexion'; ?> - Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .auth-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { max-width: 500px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card auth-card">
                        <div class="card-header bg-primary text-white text-center">
                            <h4 class="mb-0">
                                <i class="fas fa-<?php echo $action === 'register' ? 'user-plus' : 'sign-in-alt'; ?> me-2"></i>
                                <?php echo $action === 'register' ? 'Inscription' : 'Connexion'; ?>
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success">
                                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if ($action === 'login'): ?>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nom d'utilisateur ou Email</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Mot de passe</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" name="remember">
                                        <label class="form-check-label">Se souvenir de moi</label>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Se connecter</button>
                                    </div>
                                </form>

                                <hr>
                                <div class="text-center">
                                    <a href="auth.php?action=register">Créer un compte</a> | 
                                    <a href="index.php">Retour à l'accueil</a>
                                </div>

                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Prénom *</label>
                                            <input type="text" class="form-control" name="first_name" 
                                                   value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nom *</label>
                                            <input type="text" class="form-control" name="last_name" 
                                                   value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nom d'utilisateur *</label>
                                        <input type="text" class="form-control" name="username" 
                                               value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>" required minlength="3">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Mot de passe *</label>
                                            <input type="password" class="form-control" name="password" required minlength="6">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirmer *</label>
                                            <input type="password" class="form-control" name="confirm_password" required>
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Créer mon compte</button>
                                    </div>
                                </form>

                                <hr>
                                <div class="text-center">
                                    <a href="auth.php?action=login">Déjà un compte ?</a> | 
                                    <a href="index.php">Retour à l'accueil</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>