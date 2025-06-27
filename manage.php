<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Project.php';
require_once __DIR__ . '/classes/Skill.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$section = $_GET['section'] ?? 'projects';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$userId = getCurrentUserId();
$userClass = new User();
$projectClass = new Project();
$skillClass = new Skill();

$errors = [];
$success = '';
$user = $userClass->getUserById($userId);

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Token de sécurité invalide.";
    } else {
        // Gestion des projets
        if ($section === 'projects') {
            $data = [
                'title' => sanitizeInput($_POST['title'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'external_link' => sanitizeInput($_POST['external_link'] ?? '')
            ];
            
            if ($action === 'add') {
                $result = $projectClass->addProject($userId, $data, $_FILES['image'] ?? null);
            } elseif ($action === 'edit' && $id) {
                $result = $projectClass->updateProject($id, $userId, $data, $_FILES['image'] ?? null);
            } elseif ($action === 'delete' && $id) {
                $result = $projectClass->deleteProject($id, $userId);
                header('Location: manage.php?section=projects');
                exit;
            }
            
            if (isset($result)) {
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                    if ($action !== 'edit') {
                        header('Location: manage.php?section=projects');
                        exit;
                    }
                } else {
                    $errors = $result['errors'];
                }
            }
        }
        
        // Gestion des compétences
        elseif ($section === 'skills') {
            $actionType = $_POST['action'] ?? '';
            
            if ($actionType === 'add_skill') {
                $skillId = intval($_POST['skill_id'] ?? 0);
                $level = sanitizeInput($_POST['level'] ?? '');
                $result = $skillClass->addUserSkill($userId, $skillId, $level);
            } elseif ($actionType === 'remove_skill') {
                $skillId = intval($_POST['skill_id'] ?? 0);
                $result = $skillClass->removeUserSkill($userId, $skillId);
            }
            
            if (isset($result)) {
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $errors = $result['errors'];
                }
            }
        }
        
        // Gestion du profil
        elseif ($section === 'profile') {
            $data = [
                'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'bio' => sanitizeInput($_POST['bio'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'location' => sanitizeInput($_POST['location'] ?? ''),
                'website' => sanitizeInput($_POST['website'] ?? '')
            ];
            
            $result = $userClass->updateProfile($userId, $data);
            if ($result['success']) {
                $success = $result['message'];
                $user = $userClass->getUserById($userId); // Recharger
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

// Récupération des données
$projects = $projectClass->getUserProjects($userId);
$userSkills = $skillClass->getUserSkills($userId);
$allSkills = $skillClass->getSkillsByCategory();
$userSkillIds = array_column($userSkills, 'id');

// Récupération d'un projet pour édition
$editProject = null;
if ($section === 'projects' && $action === 'edit' && $id) {
    $editProject = $projectClass->getProjectById($id);
    if (!$editProject || $editProject['user_id'] != $userId) {
        header('Location: manage.php?section=projects');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="row">
    <!-- Navigation -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Gestion
                </h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="manage.php?section=projects" 
                   class="list-group-item list-group-item-action <?php echo $section === 'projects' ? 'active' : ''; ?>">
                    <i class="fas fa-project-diagram me-2"></i>Mes Projets
                    <span class="badge bg-primary rounded-pill float-end"><?php echo count($projects); ?></span>
                </a>
                <a href="manage.php?section=skills" 
                   class="list-group-item list-group-item-action <?php echo $section === 'skills' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs me-2"></i>Mes Compétences
                    <span class="badge bg-success rounded-pill float-end"><?php echo count($userSkills); ?></span>
                </a>
                <a href="manage.php?section=profile" 
                   class="list-group-item list-group-item-action <?php echo $section === 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user-edit me-2"></i>Mon Profil
                </a>
            </div>
        </div>
        
        <div class="mt-3 text-center">
            <a href="portfolio.php?user=<?php echo $userId; ?>" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-eye me-2"></i>Voir mon portfolio
            </a>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="col-md-9">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($section === 'projects'): ?>
            <!-- SECTION PROJETS -->
            <?php if ($action === 'list'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Mes Projets</h3>
                    <a href="manage.php?section=projects&action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouveau projet
                    </a>
                </div>

                <?php if (empty($projects)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                            <h5>Aucun projet</h5>
                            <p class="text-muted">Commencez par ajouter votre premier projet.</p>
                            <a href="manage.php?section=projects&action=add" class="btn btn-primary">Ajouter un projet</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($projects as $project): ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <?php if ($project['image']): ?>
                                        <img src="uploads/projects/<?php echo htmlspecialchars($project['image']); ?>" 
                                             class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5><?php echo htmlspecialchars($project['title']); ?></h5>
                                        <p class="text-muted small">
                                            <?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>...
                                        </p>
                                        <div class="d-flex justify-content-between">
                                            <a href="manage.php?section=projects&action=edit&id=<?php echo $project['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">Modifier</a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Supprimer ce projet ?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Formulaire projet -->
                <h3><?php echo $action === 'add' ? 'Nouveau' : 'Modifier'; ?> projet</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Titre *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($editProject['title'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($editProject['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lien externe</label>
                        <input type="url" name="external_link" class="form-control" 
                               value="<?php echo htmlspecialchars($editProject['external_link'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($editProject && $editProject['image']): ?>
                            <img src="uploads/projects/<?php echo htmlspecialchars($editProject['image']); ?>" 
                                 class="mt-2" style="max-width: 200px;">
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="manage.php?section=projects" class="btn btn-secondary">Retour</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            <?php endif; ?>

        <?php elseif ($section === 'skills'): ?>
            <!-- SECTION COMPETENCES -->
            <div class="row">
                <div class="col-md-8">
                    <h3>Mes Compétences</h3>
                    
                    <?php if (empty($userSkills)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-cogs fa-4x text-muted mb-3"></i>
                            <h5>Aucune compétence</h5>
                            <p class="text-muted">Ajoutez vos compétences depuis le formulaire ci-contre.</p>
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
                                <h5 class="text-primary"><?php echo htmlspecialchars($category); ?></h5>
                                <?php foreach ($skills as $skill): ?>
                                    <div class="card mb-2">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($skill['name']); ?></strong>
                                                    <small class="text-muted">- <?php echo ucfirst($skill['level']); ?></small>
                                                </div>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="action" value="remove_skill">
                                                    <input type="hidden" name="skill_id" value="<?php echo $skill['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Ajouter une compétence</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="add_skill">
                                
                                <div class="mb-3">
                                    <select name="skill_id" class="form-select" required>
                                        <option value="">Choisir...</option>
                                        <?php foreach ($allSkills as $category => $skills): ?>
                                            <optgroup label="<?php echo htmlspecialchars($category); ?>">
                                                <?php foreach ($skills as $skill): ?>
                                                    <?php if (!in_array($skill['id'], $userSkillIds)): ?>
                                                        <option value="<?php echo $skill['id']; ?>">
                                                            <?php echo htmlspecialchars($skill['name']); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <select name="level" class="form-select" required>
                                        <option value="">Niveau...</option>
                                        <option value="debutant">Débutant</option>
                                        <option value="intermediaire">Intermédiaire</option>
                                        <option value="avance">Avancé</option>
                                        <option value="expert">Expert</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-success w-100">Ajouter</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- SECTION PROFIL -->
            <h3>Mon Profil</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="first_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="last_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Biographie</label>
                    <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Localisation</label>
                        <input type="text" name="location" class="form-control" 
                               value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Site web</label>
                    <input type="url" name="website" class="form-control" 
                           value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>">
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>