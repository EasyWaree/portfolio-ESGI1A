<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Skill.php';
require_once __DIR__ . '/includes/functions.php';

requireAdmin();

$section = $_GET['section'] ?? 'skills';
$skillClass = new Skill();

$errors = [];
$success = '';
$editSkill = null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Token de sécurité invalide.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? '') ?: sanitizeInput($_POST['custom_category'] ?? '');
            
            $result = $skillClass->addSkill($name, $category);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $errors = $result['errors'];
            }
        } elseif ($action === 'edit') {
            $id = intval($_POST['id'] ?? 0);
            $name = sanitizeInput($_POST['name'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? '') ?: sanitizeInput($_POST['custom_category'] ?? '');
            
            $result = $skillClass->updateSkill($id, $name, $category);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $errors = $result['errors'];
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            
            $result = $skillClass->deleteSkill($id);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

// Récupérer la compétence à éditer
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editSkill = $skillClass->getSkillById($editId);
}

// Récupérer toutes les compétences
$skills = $skillClass->getAllSkills();
$skillsByCategory = $skillClass->getSkillsByCategory();

include __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="fas fa-shield-alt me-2"></i>
            <strong>Interface d'administration</strong> - Vous êtes connecté en tant qu'administrateur.
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistiques -->
    <div class="col-md-3">
        <div class="card bg-primary text-white mb-3">
            <div class="card-body text-center">
                <i class="fas fa-cogs fa-2x mb-2"></i>
                <h4><?php echo count($skills); ?></h4>
                <small>Compétences</small>
            </div>
        </div>
        
        <div class="card bg-success text-white mb-3">
            <div class="card-body text-center">
                <i class="fas fa-tags fa-2x mb-2"></i>
                <h4><?php echo count($skillsByCategory); ?></h4>
                <small>Catégories</small>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Répartition par catégorie</h6>
            </div>
            <div class="card-body">
                <?php foreach ($skillsByCategory as $category => $categorySkills): ?>
                    <div class="d-flex justify-content-between mb-1">
                        <small><?php echo htmlspecialchars($category); ?></small>
                        <span class="badge bg-secondary"><?php echo count($categorySkills); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Gestion des compétences -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Gestion des Compétences
                </h5>
            </div>
            <div class="card-body">
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

                <?php if (empty($skills)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-cogs fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune compétence</h5>
                        <p class="text-muted">Commencez par ajouter des compétences.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Créée</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($skills as $skill): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($skill['name']); ?></strong></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($skill['category']); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($skill['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?edit=<?php echo $skill['id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Supprimer cette compétence ?')">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $skill['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Formulaire d'ajout/modification -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-<?php echo $editSkill ? 'edit' : 'plus'; ?> me-2"></i>
                    <?php echo $editSkill ? 'Modifier' : 'Ajouter'; ?> une compétence
                </h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="<?php echo $editSkill ? 'edit' : 'add'; ?>">
                    <?php if ($editSkill): ?>
                        <input type="hidden" name="id" value="<?php echo $editSkill['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($editSkill['name'] ?? ''); ?>" 
                               required placeholder="Ex: JavaScript, PHP...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catégorie *</label>
                        <select name="category" class="form-select">
                            <option value="">Choisir...</option>
                            <option value="Frontend" <?php echo ($editSkill && $editSkill['category'] === 'Frontend') ? 'selected' : ''; ?>>Frontend</option>
                            <option value="Backend" <?php echo ($editSkill && $editSkill['category'] === 'Backend') ? 'selected' : ''; ?>>Backend</option>
                            <option value="Database" <?php echo ($editSkill && $editSkill['category'] === 'Database') ? 'selected' : ''; ?>>Database</option>
                            <option value="DevOps" <?php echo ($editSkill && $editSkill['category'] === 'DevOps') ? 'selected' : ''; ?>>DevOps</option>
                            <option value="Design" <?php echo ($editSkill && $editSkill['category'] === 'Design') ? 'selected' : ''; ?>>Design</option>
                            <option value="Tools" <?php echo ($editSkill && $editSkill['category'] === 'Tools') ? 'selected' : ''; ?>>Tools</option>
                            <option value="System" <?php echo ($editSkill && $editSkill['category'] === 'System') ? 'selected' : ''; ?>>System</option>
                            <option value="Mobile" <?php echo ($editSkill && $editSkill['category'] === 'Mobile') ? 'selected' : ''; ?>>Mobile</option>
                        </select>
                        <small class="form-text text-muted">Ou créez une nouvelle catégorie :</small>
                        <input type="text" name="custom_category" class="form-control mt-1" 
                               placeholder="Nouvelle catégorie...">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-<?php echo $editSkill ? 'primary' : 'success'; ?>">
                            <i class="fas fa-<?php echo $editSkill ? 'save' : 'plus'; ?> me-2"></i>
                            <?php echo $editSkill ? 'Modifier' : 'Ajouter'; ?>
                        </button>
                        
                        <?php if ($editSkill): ?>
                            <a href="admin.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.querySelector('select[name="category"]');
    const customCategoryInput = document.querySelector('input[name="custom_category"]');
    
    if (customCategoryInput && categorySelect) {
        customCategoryInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                categorySelect.value = '';
                categorySelect.required = false;
                this.required = true;
            } else {
                categorySelect.required = true;
                this.required = false;
            }
        });
        
        categorySelect.addEventListener('change', function() {
            if (this.value !== '') {
                customCategoryInput.value = '';
                customCategoryInput.required = false;
                this.required = true;
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>