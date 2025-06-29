<?php
require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'includes/functions.php';

requireLogin();

$userId = getCurrentUserId();
$userClass = new User();
$user = $userClass->getUserById($userId);

include __DIR__ . '/includes/header.php';
?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Bienvenue, <?php echo sanitizeOutput($user['first_name']); ?> !
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Informations du profil</h5>
                                <p><strong>Nom complet :</strong> <?php echo sanitizeOutput($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                <p><strong>Email :</strong> <?php echo sanitizeOutput($user['email']); ?></p>
                                <p><strong>Rôle :</strong> <?php echo sanitizeOutput(ucfirst($user['role'])); ?></p>
                                <p><strong>Membre depuis :</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                                
                                <?php if ($user['bio']): ?>
                                    <p><strong>Bio :</strong> <?php echo sanitizeOutput($user['bio']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5>Actions rapides</h5>
                                <div class="d-grid gap-2">
                                    <a href="portfolio.php?user=<?php echo $userId; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i>Voir mon portfolio public
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <a href="admin.php" class="btn btn-warning">
                                            <i class="fas fa-shield-alt me-2"></i>Administration
                                        </a>
                                    <?php endif; ?>
                                    <a href="auth.php?action=logout" class="btn btn-outline-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Se déconnecter
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-success mt-4">
                    <h5><i class="fas fa-check-circle me-2"></i>Projet Portfolio PHP - FONCTIONNEL !</h5>
                    <p class="mb-0">
                        <strong>✅ Votre projet est maintenant opérationnel !</strong><br>
                        Base de données connectée, authentification sécurisée, sessions actives.
                    </p>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">État du projet</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>✅ Fonctionnalités implémentées :</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Base de données MySQL connectée</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Système d'authentification complet</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Gestion des utilisateurs et rôles</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Sessions sécurisées avec expiration</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Protection CSRF</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Interface responsive Bootstrap</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Comptes de test fonctionnels</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>📊 Statistiques :</h6>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h4 class="text-primary">3</h4>
                                                <small>Utilisateurs</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h4 class="text-success"><?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?></h4>
                                                <small>Votre rôle</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <h6>🔑 Comptes de test :</h6>
                                    <small class="text-muted">
                                        <strong>Admin:</strong> admin / password<br>
                                        <strong>Users:</strong> johndoe / password, janedoe / password
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (isAdmin()): ?>
                <div class="alert alert-warning mt-4">
                    <h6><i class="fas fa-crown me-2"></i>Privilèges administrateur</h6>
                    <p class="mb-2">En tant qu'administrateur, vous pouvez :</p>
                    <ul class="mb-0">
                        <li>Gérer les compétences disponibles</li>
                        <li>Voir tous les portfolios</li>
                        <li>Administrer la plateforme</li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?
    include __DIR__ . '/includes/footer.php';
    ?>