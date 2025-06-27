<?php
require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Skill.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Mes Compétences';
$userId = getCurrentUserId();
$skillClass = new Skill();

$errors = [];
$success = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Token de sécurité invalide.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add_skill') {
            $skillId = intval($_POST['skill_id'] ?? 0);
            $level = sanitizeInput($_POST['level'] ?? '');
            
            if ($skillId && $level) {
                $result = $skillClass->addUserSkill($userId, $skillId, $level);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $errors = $result['errors'];
                }
            } else {
                $errors[] = "Veuillez sélectionner une compétence et un niveau.";
            }
        } elseif ($action === 'remove_skill') {
            $skillId = intval($_POST['skill_id'] ?? 0);
            
            if ($skillId) {
                $result = $skillClass->removeUserSkill($userId, $skillId);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $errors = $result['errors'];
                }
            }
        }
    }
}

// Récupérer les données
$userSkills = $skillClass->getUserSkills($userId);
$allSkills = $skillClass->getSkillsByCategory();

// Créer un tableau des IDs des compétences de l'utilisateur
$userSkillIds = array_column($userSkills, 'id');

include __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Mes Compétences
                </h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo sanitizeOutput($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo sanitizeOutput($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($userSkills)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-cogs fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune compétence ajoutée</h5>
                        <p class="text-muted">Commencez par ajouter vos premières compétences depuis la liste ci-contre.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $groupedSkills = [];
                    foreach ($userSkills as $skill) {
                        $groupedSkills[$skill['category']][] = $skill;
                    }
                    ?>
                    
                    <?php foreach ($groupedSkills as $category => $skills): ?>
                        <div class="mb-4">
                            <div class="skill-category mb-3">
                                <?php echo sanitizeOutput($category); ?>
                            </div>
                            
                            <?php foreach ($skills as $skill): ?>
                                <div class="skill-item skill-level-<?php echo $skill['level']; ?> mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="skill-name mb-1">
                                                <?php echo sanitizeOutput($skill['name']); ?>
                                            </div>
                                            <div class="skill-level text-muted">
                                                Niveau : <?php echo ucfirst($skill['level']); ?>
                                            </div>
                                            <div class="skill-progress mt-2">
                                                <div class="skill-progress-bar"></div>
                                            </div>
                                        </div>
                                        
                                        <form method="POST" style="margin-left: 1rem;" 
                                              onsubmit="return confirm('Supprimer cette compétence ?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="action" value="remove_skill">
                                            <input type="hidden" name="skill_id" value="<?php echo $skill['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>Ajouter une compétence
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add_skill">
                    
                    <div class="mb-3">
                        <label for="skill_id" class="form-label">Compétence</label>
                        <select class="form-select" id="skill_id" name="skill_id" required>
                            <option value="">Choisir une compétence...</option>
                            <?php foreach ($allSkills as $category => $skills): ?>
                                <optgroup label="<?php echo sanitizeOutput($category); ?>">
                                    <?php foreach ($skills as $skill): ?>
                                        <?php if (!in_array($skill['id'], $userSkillIds)): ?>
                                            <option value="<?php echo $skill['id']; ?>">
                                                <?php echo sanitizeOutput($skill['name']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level" class="form-label">Niveau</label>
                        <select class="form-select" id="level" name="level" required>
                            <option value="">Choisir un niveau...</option>
                            <option value="debutant">Débutant</option>
                            <option value="intermediaire">Intermédiaire</option>
                            <option value="avance">Avancé</option>
                            <option value="expert">Expert</option>
                        </select>
                        <div class="form-text">
                            <small>
                                <strong>Débutant :</strong> Notions de base<br>
                                <strong>Intermédiaire :</strong> Utilisation courante<br>
                                <strong>Avancé :</strong> Maîtrise approfondie<br>
                                <strong>Expert :</strong> Expertise complète
                            </small>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Légende -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Légende des niveaux
                </h6>
            </div>
            <div class="card-body">
                <div class="skill-item skill-level-debutant mb-2">
                    <div class="skill-name">Débutant</div>
                    <div class="skill-progress">
                        <div class="skill-progress-bar"></div>
                    </div>
                </div>
                
                <div class="skill-item skill-level-intermediaire mb-2">
                    <div class="skill-name">Intermédiaire</div>
                    <div class="skill-progress">
                        <div class="skill-progress-bar"></div>
                    </div>
                </div>
                
                <div class="skill-item skill-level-avance mb-2">
                    <div class="skill-name">Avancé</div>
                    <div class="skill-progress">
                        <div class="skill-progress-bar"></div>
                    </div>
                </div>
                
                <div class="skill-item skill-level-expert">
                    <div class="skill-name">Expert</div>
                    <div class="skill-progress">
                        <div class="skill-progress-bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>