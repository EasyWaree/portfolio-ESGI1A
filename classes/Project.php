<?php
class Project {
    private $db;
    private $uploadDir = 'uploads/projects/';
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Créer le dossier d'upload s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function getUserProjects($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getProjectById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAllProjects($limit = null) {
        try {
            $sql = "
                SELECT p.*, u.username, u.first_name, u.last_name 
                FROM projects p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC
            ";
            
            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function addProject($userId, $data, $imageFile = null) {
        $errors = [];
        
        // Validation des champs
        if (empty($data['title'])) {
            $errors[] = "Le titre est requis";
        }
        
        if (empty($data['description'])) {
            $errors[] = "La description est requise";
        }
        
        if (!empty($data['external_link']) && !filter_var($data['external_link'], FILTER_VALIDATE_URL)) {
            $errors[] = "Le lien externe n'est pas valide";
        }
        
        // Gestion de l'upload d'image
        $imageName = null;
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadImage($imageFile);
            if ($uploadResult['success']) {
                $imageName = $uploadResult['filename'];
            } else {
                $errors = array_merge($errors, $uploadResult['errors']);
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO projects (user_id, title, description, image, external_link) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $userId,
                $data['title'],
                $data['description'],
                $imageName,
                $data['external_link'] ?? null
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Projet ajouté avec succès'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de l\'ajout du projet']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    public function updateProject($id, $userId, $data, $imageFile = null) {
        $errors = [];
        
        // Vérifier que le projet appartient à l'utilisateur
        $project = $this->getProjectById($id);
        if (!$project || $project['user_id'] != $userId) {
            return ['success' => false, 'errors' => ['Projet non trouvé ou accès non autorisé']];
        }
        
        // Validation des champs
        if (empty($data['title'])) {
            $errors[] = "Le titre est requis";
        }
        
        if (empty($data['description'])) {
            $errors[] = "La description est requise";
        }
        
        if (!empty($data['external_link']) && !filter_var($data['external_link'], FILTER_VALIDATE_URL)) {
            $errors[] = "Le lien externe n'est pas valide";
        }
        
        // Gestion de l'upload d'image
        $imageName = $project['image']; // Garder l'ancienne image par défaut
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadImage($imageFile);
            if ($uploadResult['success']) {
                // Supprimer l'ancienne image si elle existe
                if ($project['image'] && file_exists($this->uploadDir . $project['image'])) {
                    unlink($this->uploadDir . $project['image']);
                }
                $imageName = $uploadResult['filename'];
            } else {
                $errors = array_merge($errors, $uploadResult['errors']);
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE projects SET 
                title = ?, description = ?, image = ?, external_link = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            
            $result = $stmt->execute([
                $data['title'],
                $data['description'],
                $imageName,
                $data['external_link'] ?? null,
                $id,
                $userId
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Projet modifié avec succès'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de la modification']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    public function deleteProject($id, $userId) {
        try {
            // Vérifier que le projet appartient à l'utilisateur
            $project = $this->getProjectById($id);
            if (!$project || $project['user_id'] != $userId) {
                return ['success' => false, 'errors' => ['Projet non trouvé ou accès non autorisé']];
            }
            
            // Supprimer l'image si elle existe
            if ($project['image'] && file_exists($this->uploadDir . $project['image'])) {
                unlink($this->uploadDir . $project['image']);
            }
            
            // Supprimer le projet
            $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$id, $userId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Projet supprimé avec succès'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de la suppression']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    private function uploadImage($file) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Vérification du type de fichier
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'errors' => ['Format d\'image non autorisé. Utilisez JPG, PNG ou GIF.']];
        }
        
        // Vérification de la taille
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'errors' => ['L\'image est trop volumineuse. Taille maximale : 5MB.']];
        }
        
        // Vérification que c'est bien une image
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            return ['success' => false, 'errors' => ['Le fichier n\'est pas une image valide.']];
        }
        
        // Génération d'un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . strtolower($extension);
        $destination = $this->uploadDir . $filename;
        
        // Upload du fichier
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors de l\'upload de l\'image.']];
        }
    }
    
    public function getProjectsByUser($userId, $limit = null) {
        try {
            $sql = "SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getProjectsCount($userId = null) {
        try {
            if ($userId) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM projects WHERE user_id = ?");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM projects");
            }
            
            $result = $stmt->fetch();
            return $result ? $result['count'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function searchProjects($query, $limit = 20) {
        try {
            $searchTerm = '%' . $query . '%';
            $stmt = $this->db->prepare("
                SELECT p.*, u.username, u.first_name, u.last_name 
                FROM projects p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.title LIKE ? OR p.description LIKE ?
                ORDER BY p.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$searchTerm, $searchTerm, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getProjectsByCategory($category = null) {
        try {
            // Cette méthode pourrait être étendue si on ajoute des catégories aux projets
            $sql = "
                SELECT p.*, u.username, u.first_name, u.last_name 
                FROM projects p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC
            ";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getRecentProjects($days = 30, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.username, u.first_name, u.last_name 
                FROM projects p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY p.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$days, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>