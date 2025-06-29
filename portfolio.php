<?php
require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'includes/functions.php';

$userId = $_GET['user'] ?? null;

if (!$userId) {
    header('Location: index.php');
    exit;
}

$userClass = new User();
$user = $userClass->getUserById($userId);

if (!$user) {
    $_SESSION['error'] = 'Utilisateur non trouvé.';
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio de <?php echo sanitizeOutput($user['first_name'] . ' ' . $user['last_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .portfolio-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #2c3e50;
            margin: 0 auto 2rem;
        }
    </style>
</head>
<body>
    <div class="portfolio-header">
        <div class="container">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h1 class="display-4 mb-3">
                <?php echo sanitizeOutput($user['first_name'] . ' ' . $user['last_name']); ?>
            </h1>
            
            <?php if ($user['bio']): ?>
                <p class="lead mb-3">
                    <?php echo sanitizeOutput($user['bio']); ?>
                </p>
            <?php endif; ?>
            
            <div class="row justify-content-center">
                <?php if ($user['location']): ?>
                    <div class="col-auto mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <?php echo sanitizeOutput($user['location']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($user['email']): ?>
                    <div class="col-auto mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:<?php echo sanitizeOutput($user['email']); ?>" class="text-white">
                            Contact
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($user['website']): ?>
                    <div class="col-auto mb-2">
                        <i class="fas fa-globe me-2"></i>
                        <a href="<?php echo sanitizeOutput($user['website']); ?>" target="_blank" class="text-white">
                            Site web
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="card">
            <div class="card-body text-center">
                <h3>Portfolio de <?php echo sanitizeOutput($user['first_name']); ?></h3>
                <p class="text-muted mb-4">
                    <?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?> depuis <?php echo date('F Y', strtotime($user['created_at'])); ?>
                </p>
                
                <div class="row">
                    <div class="col-md-4">
                        <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                        <h5>Profil professionnel</h5>
                        <p class="text-muted">Informations personnelles et coordonnées</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-cogs fa-3x text-success mb-3"></i>
                        <h5>Compétences</h5>
                        <p class="text-muted">Technologies et expertises</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-project-diagram fa-3x text-info mb-3"></i>
                        <h5>Projets</h5>
                        <p class="text-muted">Réalisations et travaux</p>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Portfolio en construction</strong><br>
                    Les projets et compétences seront bientôt disponibles.
                </div>
                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Retour à l'accueil
                    </a>
                    <?php if (isLoggedIn() && getCurrentUserId() == $userId): ?>
                        <a href="dashboard.php" class="btn btn-success ms-2">
                            <i class="fas fa-edit me-2"></i>Modifier mon portfolio
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-light py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Portfolio ESGI</p>
        </div>
    </footer>
</body>
</html>