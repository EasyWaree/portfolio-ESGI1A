<?php
class Skill {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllSkills() {
        try {
            $stmt = $this->db->query("SELECT * FROM skills ORDER BY category, name");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getSkillsByCategory() {
        try {
            $stmt = $this->db->query("SELECT * FROM skills ORDER BY category, name");
            $skills = $stmt->fetchAll();
            
            $categorized = [];
            foreach ($skills as $skill) {
                $categorized[$skill['category']][] = $skill;
            }
            
            return $categorized;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getUserSkills($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, us.level 
                FROM skills s 
                JOIN user_skills us ON s.id = us.skill_id 
                WHERE us.user_id = ? 
                ORDER BY s.category, s.name
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function addSkill($name, $category) {
        $errors = [];
        
        if (empty($name)) {
            $errors[] = "Le nom de la compétence est requis";
        }
        
        if (empty($category)) {
            $errors[] = "La catégorie est requise";
        }
        
        // Vérifier si la compétence existe déjà
        if (!empty($name)) {
            $stmt = $this->db->prepare("SELECT id FROM skills WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                $errors[] = "Cette compétence existe déjà";
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare("INSERT INTO skills (name, category) VALUES (?, ?)");
            $result = $stmt->execute([$name, $category]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Compétence ajoutée avec succès'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de l\'ajout']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    public function updateSkill($id, $name, $category) {
        $errors = [];
        
        if (empty($name)) {
            $errors[] = "Le nom de la compétence est requis";
        }
        
        if (empty($category)) {
            $errors[] = "La catégorie est requise";
        }
        
        // Vérifier si la compétence existe déjà (sauf pour celle qu'on modifie)
        if (!empty($name)) {
            $stmt = $this->db->prepare("SELECT id FROM skills WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            if ($stmt->fetch()) {
                $errors[] = "Cette compétence existe déjà";
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE skills SET name = ?, category = ? WHERE id = ?");
            $result = $stmt->execute([$name, $category, $id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Compétence modifiée avec succès'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de la modification']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    public function deleteSkill($id) {
        try {
            // Supprimer d'abord les liaisons utilisateur-compétences
            $stmt = $this->db->prepare("DELETE FROM user_skills WHERE skill_id = ?");
            $stmt->execute([$id]);
            
            // Puis supprimer la compétence
            $stmt = $this->db->prepare("DELETE FROM skills WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Compétence supprimée avec succès'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de la suppression']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    public function addUserSkill($userId, $skillId, $level) {
        $validLevels = ['debutant', 'intermediaire', 'avance', 'expert'];
        
        if (!in_array($level, $validLevels)) {
            return ['success' => false, 'errors' => ['Niveau de compétence invalide']];
        }
        
        try {
            // Vérifier si l'association existe déjà
            $stmt = $this->db->prepare("SELECT id FROM user_skills WHERE user_id = ? AND skill_id = ?");
            $stmt->execute([$userId, $skillId]);
            
            if ($stmt->fetch()) {
                // Mettre à jour le niveau
                $stmt = $this->db->prepare("UPDATE user_skills SET level = ? WHERE user_id = ? AND skill_id = ?");
                $result = $stmt->execute([$level, $userId, $skillId]);
            } else {
                // Ajouter la compétence
                $stmt = $this->db->prepare("INSERT INTO user_skills (user_id, skill_id, level) VALUES (?, ?, ?)");
                $result = $stmt->execute([$userId, $skillId, $level]);
            }
            
            if ($result) {
                return ['success' => true, 'message' => 'Compétence ajoutée à votre profil'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de l\'ajout']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    public function removeUserSkill($userId, $skillId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_skills WHERE user_id = ? AND skill_id = ?");
            $result = $stmt->execute([$userId, $skillId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Compétence supprimée de votre profil'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de la suppression']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    public function getSkillById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM skills WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getUserSkillLevel($userId, $skillId) {
        try {
            $stmt = $this->db->prepare("SELECT level FROM user_skills WHERE user_id = ? AND skill_id = ?");
            $stmt->execute([$userId, $skillId]);
            $result = $stmt->fetch();
            return $result ? $result['level'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getSkillStats() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    s.category,
                    COUNT(*) as skill_count,
                    COUNT(us.skill_id) as usage_count
                FROM skills s
                LEFT JOIN user_skills us ON s.id = us.skill_id
                GROUP BY s.category
                ORDER BY s.category
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getTopSkills($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    s.name,
                    s.category,
                    COUNT(us.skill_id) as user_count
                FROM skills s
                LEFT JOIN user_skills us ON s.id = us.skill_id
                GROUP BY s.id, s.name, s.category
                ORDER BY user_count DESC, s.name
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>