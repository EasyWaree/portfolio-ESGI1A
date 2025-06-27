<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'projetb2');
define('DB_USER', 'projetb2');
define('DB_PASS', 'password');

// Démarrer la session
session_start();

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données. Veuillez importer database.sql d'abord.");
}

// Fonctions utilitaires
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Récupérer quelques projets pour l'affichage
$recentProjects = [];
try {
    $stmt = $pdo->query("
        SELECT p.*, u.first_name, u.last_name 
        FROM projects p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 6
    ");
    $recentProjects = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table pas encore créée, on ignore
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Professionnel - ESGI 2024/2025</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .hero { background: rgba(255,255,255,0.95); border-radius: 15px; padding: 3rem; margin: 2rem 0; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card { border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-5px); }
        .project-card img { height: 200px; object-fit: cover; }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero text-center">
            <h1 class="display-4 mb-4">
                <i class="fas fa-briefcase text-primary me-3"></i>
                Portfolio Professionnel
            </h1>
            <p class="lead mb-4">
                Plateforme de gestion de portfolio en ligne<br>
                <small class="text-muted">Projet ESGI 2024/2025 - PHP/MySQL</small>
            </p>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                            <h5>Profil Personnalisé</h5>
                            <p class="text-muted">Créez votre profil professionnel avec bio, coordonnées et informations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-cogs fa-3x text-success mb-3"></i>
                            <h5>Gestion des Compétences</h5>
                            <p class="text-muted">Ajoutez vos compétences techniques avec niveaux (débutant à expert)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-project-diagram fa-3x text-info mb-3"></i>
                            <h5>Portfolio de Projets</h5>
                            <p class="text-muted">Présentez vos projets avec descriptions, images et liens</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!isLoggedIn()): ?>
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <a href="auth.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                    </a>
                    <a href="auth.php?action=register" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>S'inscrire
                    </a>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <a href="dashboard.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-tachometer-alt me-2"></i>Mon Tableau de Bord
                    </a>
                    <a href="portfolio.php?user=<?php echo $_SESSION['user_id']; ?>" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-eye me-2"></i>Voir mon Portfolio
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="p-3 bg-light rounded">
                <h6><i class="fas fa-key me-2"></i>Comptes de test :</h6>
                <div class="row">
                    <div class="col-md-4">
                        <small><strong>Admin :</strong><br>admin / password</small>
                    </div>
                    <div class="col-md-4">
                        <small><strong>Développeur :</strong><br>johndoe / password</small>
                    </div>
                    <div class="col-md-4">
                        <small><strong>Designer :</strong><br>janedoe / password</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projets récents -->
        <?php if (!empty($recentProjects)): ?>
        <div class="mb-5">
            <h2 class="text-white text-center mb-4">Projets de la Communauté</h2>
            <div class="row g-4">
                <?php foreach ($recentProjects as $project): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card project-card h-100">
                        <?php if ($project['image']): ?>
                            <img src="uploads/projects/<?php echo sanitizeOutput($project['image']); ?>" 
                                 alt="<?php echo sanitizeOutput($project['title']); ?>" 
                                 class="card-img-top">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo sanitizeOutput($project['title']); ?></h5>
                            <p class="card-text text-muted">
                                <?php echo sanitizeOutput(substr($project['description'], 0, 100)); ?>
                                <?php if (strlen($project['description']) > 100): ?>...<?php endif; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Par <?php echo sanitizeOutput($project['first_name'] . ' ' . $project['last_name']); ?>
                                </small>
                                <?php if ($project['external_link']): ?>
                                    <a href="<?php echo sanitizeOutput($project['external_link']); ?>" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-1"></i>Voir
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="text-center text-white mb-4">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Portfolio ESGI - Projet PHP/MySQL complet</p>
            <small>Base de données : <?php echo DB_NAME; ?> | Statut : <?php echo !empty($recentProjects) ? 'Connectée ✅' : 'En attente d\'import SQL ⏳'; ?></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>