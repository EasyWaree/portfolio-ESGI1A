<?php
class Skill {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Ajouter une nouvelle compétence (admin seulement)
     */
    public function addSkill($name, $category) {
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = "Le nom de la compétence est requis.";
        }
        
        if (empty($category)) {
            $errors[] = "La catégorie est requise.";
        }
        
        if (strlen($name) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères.";
        }
        
        if (strlen($name) > 100) {
            $errors[] = "Le nom ne peut pas dépasser 100 caractères.";
        }
        
        // Vérifier l'unicité
        if ($this->skillExists($name)) {
            $errors[] = "Cette compétence existe déjà.";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "INSERT INTO skills (name, category, created_at) VALUES (:name, :category, NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'name' => trim($name),
                'category' => trim($category)
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Compétence ajoutée avec succès.'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur ajout compétence: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de l\'ajout.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Modifier une compétence
     */
    public function updateSkill($id, $name, $category) {
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = "Le nom de la compétence est requis.";
        }
        
        if (empty($category)) {
            $errors[] = "La catégorie est requise.";
        }
        
        if (strlen($name) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères.";
        }
        
        // Vérifier l'unicité (sauf pour la compétence actuelle)
        if ($this->skillExistsExcept($name, $id)) {
            $errors[] = "Cette compétence existe déjà.";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "UPDATE skills SET name = :name, category = :category WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'name' => trim($name),
                'category' => trim($category),
                'id' => $id
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Compétence modifiée avec succès.'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur modification compétence: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de la modification.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Supprimer une compétence
     */
    public function deleteSkill($id) {
        try {
            // Vérifier si la compétence est utilisée
            $usageCount = $this->getSkillUsageCount($id);
            if ($usageCount > 0) {
                return [
                    'success' => false, 
                    'errors' => ["Cette compétence est utilisée par $usageCount utilisateur(s) et ne peut pas être supprimée."]
                ];
            }
            
            $sql = "DELETE FROM skills WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id' => $id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Compétence supprimée avec succès.'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur suppression compétence: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de la suppression.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Ajouter une compétence à un utilisateur
     */
    public function addUserSkill($userId, $skillId, $level) {
        $errors = [];
        
        // Validation
        if (!$skillId || !is_numeric($skillId)) {
            $errors[] = "Compétence invalide.";
        }
        
        $validLevels = ['debutant', 'intermediaire', 'avance', 'expert'];
        if (!in_array($level, $validLevels)) {
            $errors[] = "Niveau de compétence invalide.";
        }
        
        // Vérifier que la compétence existe
        if (!$this->getSkillById($skillId)) {
            $errors[] = "Compétence non trouvée.";
        }
        
        // Vérifier que l'utilisateur n'a pas déjà cette compétence
        if ($this->userHasSkill($userId, $skillId)) {
            $errors[] = "Vous avez déjà cette compétence.";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "INSERT INTO user_skills (user_id, skill_id, level, created_at) 
                    VALUES (:user_id, :skill_id, :level, NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $userId,
                'skill_id' => $skillId,
                'level' => $level
            ]);
            
            if ($result) {
                $this->logSkillActivity($userId, 'skill_added', $skillId);
                return ['success' => true, 'message' => 'Compétence ajoutée à votre profil.'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur ajout compétence utilisateur: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de l\'ajout.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Modifier le niveau d'une compétence utilisateur
     */
    public function updateUserSkillLevel($userId, $skillId, $level) {
        $validLevels = ['debutant', 'intermediaire', 'avance', 'expert'];
        if (!in_array($level, $validLevels)) {
            return ['success' => false, 'errors' => ['Niveau invalide.']];
        }
        
        try {
            $sql = "UPDATE user_skills SET level = :level WHERE user_id = :user_id AND skill_id = :skill_id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'level' => $level,
                'user_id' => $userId,
                'skill_id' => $skillId
            ]);
            
            if ($result) {
                $this->logSkillActivity($userId, 'skill_level_updated', $skillId);
                return ['success' => true, 'message' => 'Niveau de compétence mis à jour.'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur mise à jour niveau: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de la mise à jour.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Supprimer une compétence d'un utilisateur
     */
    public function removeUserSkill($userId, $skillId) {
        try {
            $sql = "DELETE FROM user_skills WHERE user_id = :user_id AND skill_id = :skill_id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $userId,
                'skill_id' => $skillId
            ]);
            
            if ($result) {
                $this->logSkillActivity($userId, 'skill_removed', $skillId);
                return ['success' => true, 'message' => 'Compétence supprimée de votre profil.'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur suppression compétence utilisateur: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de la suppression.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Récupérer une compétence par ID
     */
    public function getSkillById($id) {
        try {
            $sql = "SELECT * FROM skills WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur getSkillById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer toutes les compétences
     */
    public function getAllSkills() {
        try {
            $sql = "SELECT * FROM skills ORDER BY category, name";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getAllSkills: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les compétences par catégorie
     */
    public function getSkillsByCategory() {
        try {
            $sql = "SELECT * FROM skills ORDER BY category, name";
            $stmt = $this->db->query($sql);
            $skills = $stmt->fetchAll();
            
            $grouped = [];
            foreach ($skills as $skill) {
                $grouped[$skill['category']][] = $skill;
            }
            
            return $grouped;
        } catch (PDOException $e) {
            error_log("Erreur getSkillsByCategory: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les compétences d'un utilisateur
     */
    public function getUserSkills($userId) {
        try {
            $sql = "SELECT s.*, us.level, us.created_at as added_at
                    FROM skills s 
                    JOIN user_skills us ON s.id = us.skill_id 
                    WHERE us.user_id = :user_id 
                    ORDER BY s.category, s.name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getUserSkills: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les compétences d'un utilisateur groupées par catégorie
     */
    public function getUserSkillsByCategory($userId) {
        try {
            $skills = $this->getUserSkills($userId);
            
            $grouped = [];
            foreach ($skills as $skill) {
                $grouped[$skill['category']][] = $skill;
            }
            
            return $grouped;
        } catch (Exception $e) {
            error_log("Erreur getUserSkillsByCategory: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Rechercher des compétences
     */
    public function searchSkills($query, $category = null, $limit = 50) {
        try {
            $sql = "SELECT * FROM skills WHERE name LIKE :query";
            $params = ['query' => "%$query%"];
            
            if ($category) {
                $sql .= " AND category = :category";
                $params['category'] = $category;
            }
            
            $sql .= " ORDER BY name LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur searchSkills: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les catégories de compétences
     */
    public function getCategories() {
        try {
            $sql = "SELECT DISTINCT category FROM skills ORDER BY category";
            $stmt = $this->db->query($sql);
            return array_column($stmt->fetchAll(), 'category');
        } catch (PDOException $e) {
            error_log("Erreur getCategories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les compétences les plus populaires
     */
    public function getPopularSkills($limit = 10) {
        try {
            $sql = "SELECT s.*, COUNT(us.skill_id) as usage_count
                    FROM skills s 
                    LEFT JOIN user_skills us ON s.id = us.skill_id 
                    GROUP BY s.id 
                    ORDER BY usage_count DESC, s.name 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getPopularSkills: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les statistiques des compétences
     */
    public function getSkillsStats() {
        try {
            $stats = [];
            
            // Total des compétences
            $sql = "SELECT COUNT(*) as total FROM skills";
            $stmt = $this->db->query($sql);
            $stats['total_skills'] = $stmt->fetch()['total'];
            
            // Total des assignations
            $sql = "SELECT COUNT(*) as total FROM user_skills";
            $stmt = $this->db->query($sql);
            $stats['total_assignments'] = $stmt->fetch()['total'];
            
            // Compétences par catégorie
            $sql = "SELECT category, COUNT(*) as count FROM skills GROUP BY category ORDER BY count DESC";
            $stmt = $this->db->query($sql);
            $stats['by_category'] = $stmt->fetchAll();
            
            // Niveaux les plus fréquents
            $sql = "SELECT level, COUNT(*) as count FROM user_skills GROUP BY level ORDER BY count DESC";
            $stmt = $this->db->query($sql);
            $stats['by_level'] = $stmt->fetchAll();
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Erreur getSkillsStats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Importer des compétences en masse
     */
    public function importSkills($skillsData) {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        
        try {
            $this->db->beginTransaction();
            
            foreach ($skillsData as $skill) {
                if (empty($skill['name']) || empty($skill['category'])) {
                    $skipped++;
                    continue;
                }
                
                // Vérifier si la compétence existe déjà
                if ($this->skillExists($skill['name'])) {
                    $skipped++;
                    continue;
                }
                
                $sql = "INSERT INTO skills (name, category, created_at) VALUES (:name, :category, NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    'name' => trim($skill['name']),
                    'category' => trim($skill['category'])
                ]);
                
                if ($result) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'message' => "$imported compétences importées, $skipped ignorées."
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erreur importSkills: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['Erreur lors de l\'import des compétences.']
            ];
        }
    }
    
    /**
     * Exporter les compétences au format JSON
     */
    public function exportSkills() {
        try {
            $skills = $this->getAllSkills();
            
            $export = [
                'export_date' => date('Y-m-d H:i:s'),
                'total_skills' => count($skills),
                'skills' => $skills
            ];
            
            $filename = "skills_export_" . date('Y-m-d') . ".json";
            $filepath = "exports/" . $filename;
            
            // Créer le dossier exports s'il n'existe pas
            if (!is_dir('exports')) {
                mkdir('exports', 0755, true);
            }
            
            file_put_contents($filepath, json_encode($export, JSON_PRETTY_PRINT));
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            ];
            
        } catch (Exception $e) {
            error_log("Erreur exportSkills: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de l\'export.'];
        }
    }
    
    /**
     * Suggérer des compétences basées sur le profil utilisateur
     */
    public function suggestSkills($userId, $limit = 5) {
        try {
            // Récupérer les compétences de l'utilisateur
            $userSkills = $this->getUserSkills($userId);
            $userCategories = array_unique(array_column($userSkills, 'category'));
            $userSkillIds = array_column($userSkills, 'id');
            
            if (empty($userCategories)) {
                // Si l'utilisateur n'a pas de compétences, suggérer les plus populaires
                return $this->getPopularSkills($limit);
            }
            
            // Suggérer des compétences dans les mêmes catégories
            $placeholders = str_repeat('?,', count($userCategories) - 1) . '?';
            $excludePlaceholders = str_repeat('?,', count($userSkillIds) - 1) . '?';
            
            $sql = "SELECT s.*, COUNT(us.skill_id) as popularity
                    FROM skills s 
                    LEFT JOIN user_skills us ON s.id = us.skill_id 
                    WHERE s.category IN ($placeholders)";
            
            $params = $userCategories;
            
            if (!empty($userSkillIds)) {
                $sql .= " AND s.id NOT IN ($excludePlaceholders)";
                $params = array_merge($params, $userSkillIds);
            }
            
            $sql .= " GROUP BY s.id ORDER BY popularity DESC, s.name LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Erreur suggestSkills: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir la progression des compétences d'un utilisateur
     */
    public function getUserSkillsProgress($userId) {
        try {
            $skills = $this->getUserSkills($userId);
            
            $progress = [
                'total' => count($skills),
                'by_level' => [
                    'debutant' => 0,
                    'intermediaire' => 0,
                    'avance' => 0,
                    'expert' => 0
                ],
                'by_category' => [],
                'completion_score' => 0
            ];
            
            foreach ($skills as $skill) {
                $progress['by_level'][$skill['level']]++;
                
                if (!isset($progress['by_category'][$skill['category']])) {
                    $progress['by_category'][$skill['category']] = 0;
                }
                $progress['by_category'][$skill['category']]++;
                
                // Calcul du score (debutant=1, intermediaire=2, avance=3, expert=4)
                $levelScores = ['debutant' => 1, 'intermediaire' => 2, 'avance' => 3, 'expert' => 4];
                $progress['completion_score'] += $levelScores[$skill['level']];
            }
            
            return $progress;
            
        } catch (Exception $e) {
            error_log("Erreur getUserSkillsProgress: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Comparer les compétences entre deux utilisateurs
     */
    public function compareUserSkills($userId1, $userId2) {
        try {
            $skills1 = $this->getUserSkills($userId1);
            $skills2 = $this->getUserSkills($userId2);
            
            $skillsMap1 = [];
            $skillsMap2 = [];
            
            foreach ($skills1 as $skill) {
                $skillsMap1[$skill['id']] = $skill['level'];
            }
            
            foreach ($skills2 as $skill) {
                $skillsMap2[$skill['id']] = $skill['level'];
            }
            
            $common = [];
            $unique1 = [];
            $unique2 = [];
            
            // Compétences communes
            foreach ($skillsMap1 as $skillId => $level1) {
                if (isset($skillsMap2[$skillId])) {
                    $skill = $this->getSkillById($skillId);
                    $common[] = [
                        'skill' => $skill,
                        'user1_level' => $level1,
                        'user2_level' => $skillsMap2[$skillId]
                    ];
                } else {
                    $unique1[] = $this->getSkillById($skillId);
                }
            }
            
            // Compétences uniques au user2
            foreach ($skillsMap2 as $skillId => $level2) {
                if (!isset($skillsMap1[$skillId])) {
                    $unique2[] = $this->getSkillById($skillId);
                }
            }
            
            return [
                'common' => $common,
                'unique_user1' => $unique1,
                'unique_user2' => $unique2
            ];
            
        } catch (Exception $e) {
            error_log("Erreur compareUserSkills: " . $e->getMessage());
            return [];
        }
    }
    
    // Méthodes privées
    
    /**
     * Vérifier si une compétence existe
     */
    private function skillExists($name) {
        try {
            $sql = "SELECT id FROM skills WHERE LOWER(name) = LOWER(:name)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['name' => trim($name)]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Vérifier si une compétence existe (sauf pour un ID donné)
     */
    private function skillExistsExcept($name, $exceptId) {
        try {
            $sql = "SELECT id FROM skills WHERE LOWER(name) = LOWER(:name) AND id != :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['name' => trim($name), 'id' => $exceptId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Vérifier si un utilisateur a une compétence
     */
    private function userHasSkill($userId, $skillId) {
        try {
            $sql = "SELECT id FROM user_skills WHERE user_id = :user_id AND skill_id = :skill_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId, 'skill_id' => $skillId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Obtenir le nombre d'utilisateurs utilisant une compétence
     */
    private function getSkillUsageCount($skillId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM user_skills WHERE skill_id = :skill_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['skill_id' => $skillId]);
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Enregistrer l'activité des compétences
     */
    private function logSkillActivity($userId, $action, $skillId) {
        try {
            $sql = "INSERT INTO skill_activities (user_id, skill_id, action, ip_address, created_at) 
                    VALUES (:user_id, :skill_id, :action, :ip, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'skill_id' => $skillId,
                'action' => $action,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (PDOException $e) {
            error_log("Erreur logSkillActivity: " . $e->getMessage());
        }
    }
}
?>