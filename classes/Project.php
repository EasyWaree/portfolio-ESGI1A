<?php
class Project {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Ajouter un projet avec upload d'image sécurisé
     */
    public function addProject($userId, $data, $imageFile = null) {
        $errors = [];
        
        // Validation des données
        if (empty($data['title'])) {
            $errors[] = "Le titre est requis.";
        }
        
        if (empty($data['description'])) {
            $errors[] = "La description est requise.";
        }
        
        if (!empty($data['external_link']) && !filter_var($data['external_link'], FILTER_VALIDATE_URL)) {
            $errors[] = "Le lien externe doit être une URL valide.";
        }
        
        // Validation de l'image
        $imageName = null;
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $imageValidation = $this->validateAndProcessImage($imageFile);
            if ($imageValidation['success']) {
                $imageName = $imageValidation['filename'];
            } else {
                $errors = array_merge($errors, $imageValidation['errors']);
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "INSERT INTO projects (user_id, title, description, image, external_link, created_at) 
                    VALUES (:user_id, :title, :description, :image, :external_link, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $userId,
                'title' => $data['title'],
                'description' => $data['description'],
                'image' => $imageName,
                'external_link' => $data['external_link'] ?: null
            ]);
            
            if ($result) {
                $projectId = $this->db->lastInsertId();
                $this->logProjectActivity($userId, 'project_created', $projectId);
                
                return [
                    'success' => true, 
                    'message' => 'Projet ajouté avec succès.',
                    'project_id' => $projectId
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur ajout projet: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de l\'ajout du projet.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Modifier un projet
     */
    public function updateProject($projectId, $userId, $data, $imageFile = null) {
        $errors = [];
        
        // Vérifier que le projet appartient à l'utilisateur
        $project = $this->getProjectById($projectId);
        if (!$project || $project['user_id'] != $userId) {
            return ['success' => false, 'errors' => ['Projet non trouvé ou accès non autorisé.']];
        }
        
        // Validation des données
        if (empty($data['title'])) {
            $errors[] = "Le titre est requis.";
        }
        
        if (empty($data['description'])) {
            $errors[] = "La description est requise.";
        }
        
        if (!empty($data['external_link']) && !filter_var($data['external_link'], FILTER_VALIDATE_URL)) {
            $errors[] = "Le lien externe doit être une URL valide.";
        }
        
        // Gestion de l'image
        $imageName = $project['image']; // Garder l'ancienne image par défaut
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $imageValidation = $this->validateAndProcessImage($imageFile);
            if ($imageValidation['success']) {
                // Supprimer l'ancienne image
                if ($project['image']) {
                    $this->deleteImage($project['image']);
                }
                $imageName = $imageValidation['filename'];
            } else {
                $errors = array_merge($errors, $imageValidation['errors']);
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "UPDATE projects SET 
                    title = :title,
                    description = :description,
                    image = :image,
                    external_link = :external_link,
                    updated_at = NOW()
                    WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'title' => $data['title'],
                'description' => $data['description'],
                'image' => $imageName,
                'external_link' => $data['external_link'] ?: null,
                'id' => $projectId,
                'user_id' => $userId
            ]);
            
            if ($result) {
                $this->logProjectActivity($userId, 'project_updated', $projectId);
                return ['success' => true, 'message' => 'Projet modifié avec succès.'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur modification projet: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de la modification.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Supprimer un projet
     */
    public function deleteProject($projectId, $userId) {
        try {
            // Récupérer le projet pour supprimer l'image
            $project = $this->getProjectById($projectId);
            if (!$project || $project['user_id'] != $userId) {
                return ['success' => false, 'errors' => ['Projet non trouvé ou accès non autorisé.']];
            }
            
            $sql = "DELETE FROM projects WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id' => $projectId,
                'user_id' => $userId
            ]);
            
            if ($result) {
                // Supprimer l'image
                if ($project['image']) {
                    $this->deleteImage($project['image']);
                }
                
                $this->logProjectActivity($userId, 'project_deleted', $projectId);
                return ['success' => true, 'message' => 'Projet supprimé avec succès.'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur suppression projet: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de la suppression.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Récupérer un projet par ID
     */
    public function getProjectById($id) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name, u.username 
                    FROM projects p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur getProjectById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer les projets d'un utilisateur
     */
    public function getUserProjects($userId, $limit = null) {
        try {
            $sql = "SELECT * FROM projects WHERE user_id = :user_id ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT :limit";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getUserProjects: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer tous les projets publics avec pagination
     */
    public function getAllProjectsPaginated($page = 1, $perPage = 12) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT p.*, u.first_name, u.last_name, u.username 
                    FROM projects p 
                    JOIN users u ON p.user_id = u.id 
                    ORDER BY p.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $projects = $stmt->fetchAll();
            
            // Compter le total
            $countSql = "SELECT COUNT(*) as total FROM projects";
            $countStmt = $this->db->query($countSql);
            $total = $countStmt->fetch()['total'];
            
            return [
                'projects' => $projects,
                'total' => $total,
                'pages' => ceil($total / $perPage),
                'current_page' => $page
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur getAllProjectsPaginated: " . $e->getMessage());
            return ['projects' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
        }
    }
    
    /**
     * Rechercher des projets
     */
    public function searchProjects($query, $limit = 20) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name, u.username 
                    FROM projects p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.title LIKE :query 
                    OR p.description LIKE :query 
                    ORDER BY p.created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Erreur searchProjects: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les projets les plus récents
     */
    public function getRecentProjects($limit = 6) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name, u.username 
                    FROM projects p 
                    JOIN users u ON p.user_id = u.id 
                    ORDER BY p.created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Erreur getRecentProjects: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Dupliquer un projet
     */
    public function duplicateProject($projectId, $userId) {
        try {
            $project = $this->getProjectById($projectId);
            if (!$project || $project['user_id'] != $userId) {
                return ['success' => false, 'errors' => ['Projet non trouvé ou accès non autorisé.']];
            }
            
            $data = [
                'title' => $project['title'] . ' (Copie)',
                'description' => $project['description'],
                'external_link' => $project['external_link']
            ];
            
            // Dupliquer l'image si elle existe
            $imageFile = null;
            if ($project['image']) {
                $imageName = $this->duplicateImage($project['image']);
                if ($imageName) {
                    $data['image'] = $imageName;
                }
            }
            
            $sql = "INSERT INTO projects (user_id, title, description, image, external_link, created_at) 
                    VALUES (:user_id, :title, :description, :image, :external_link, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $userId,
                'title' => $data['title'],
                'description' => $data['description'],
                'image' => $data['image'] ?? null,
                'external_link' => $data['external_link']
            ]);
            
            if ($result) {
                $newProjectId = $this->db->lastInsertId();
                $this->logProjectActivity($userId, 'project_duplicated', $newProjectId);
                
                return [
                    'success' => true, 
                    'message' => 'Projet dupliqué avec succès.',
                    'project_id' => $newProjectId
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur duplicateProject: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Erreur lors de la duplication.']];
        }
        
        return ['success' => false, 'errors' => ['Erreur inconnue.']];
    }
    
    /**
     * Exporter les projets en JSON
     */
    public function exportUserProjects($userId) {
        try {
            $projects = $this->getUserProjects($userId);
            
            $export = [
                'export_date' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
                'projects' => $projects
            ];
            
            $filename = "projects_export_" . $userId . "_" . date('Y-m-d') . ".json";
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
            error_log("Erreur exportUserProjects: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de l\'export.'];
        }
    }
    
    // Méthodes privées
    
    /**
     * Valider et traiter l'upload d'image
     */
    private function validateAndProcessImage($file) {
        $errors = [];
        
        // Vérifications de base
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Erreur lors de l'upload de l'image.";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Vérifier la taille (5MB max)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $errors[] = "L'image ne doit pas dépasser 5MB.";
        }
        
        // Vérifier le type MIME réel
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = "Format d'image non autorisé. Utilisez JPG, PNG, WebP ou GIF.";
        }
        
        // Vérifier les dimensions
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            $errors[] = "Fichier image invalide.";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Génération du nom de fichier sécurisé
        $extension = $this->getExtensionFromMime($mimeType);
        $filename = uniqid('project_', true) . '.' . $extension;
        $uploadPath = 'uploads/projects/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $destination = $uploadPath . $filename;
        
        // Redimensionner l'image si nécessaire
        if ($this->resizeImage($file['tmp_name'], $destination, 1200, 800)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            $errors[] = "Erreur lors du traitement de l'image.";
            return ['success' => false, 'errors' => $errors];
        }
    }
    
    /**
     * Redimensionner une image
     */
    private function resizeImage($source, $destination, $maxWidth = 1200, $maxHeight = 800) {
        try {
            $imageInfo = getimagesize($source);
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $type = $imageInfo[2];
            
            // Calculer les nouvelles dimensions
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            if ($ratio >= 1) {
                // L'image est déjà assez petite, on la copie juste
                return move_uploaded_file($source, $destination);
            }
            
            $newWidth = intval($width * $ratio);
            $newHeight = intval($height * $ratio);
            
            // Créer l'image source
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($source);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($source);
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = imagecreatefromwebp($source);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($source);
                    break;
                default:
                    return false;
            }
            
            if (!$sourceImage) {
                return false;
            }
            
            // Créer l'image destination
            $destImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Préserver la transparence pour PNG et GIF
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
                imagealphablending($destImage, false);
                imagesavealpha($destImage, true);
                $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
                imagefilledrectangle($destImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Redimensionner
            imagecopyresampled(
                $destImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight, $width, $height
            );
            
            // Sauvegarder
            $result = false;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $result = imagejpeg($destImage, $destination, 85);
                    break;
                case IMAGETYPE_PNG:
                    $result = imagepng($destImage, $destination, 6);
                    break;
                case IMAGETYPE_WEBP:
                    $result = imagewebp($destImage, $destination, 85);
                    break;
                case IMAGETYPE_GIF:
                    $result = imagegif($destImage, $destination);
                    break;
            }
            
            // Nettoyer la mémoire
            imagedestroy($sourceImage);
            imagedestroy($destImage);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Erreur resizeImage: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer une image
     */
    private function deleteImage($filename) {
        $filepath = 'uploads/projects/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
    
    /**
     * Dupliquer une image
     */
    private function duplicateImage($originalFilename) {
        try {
            $originalPath = 'uploads/projects/' . $originalFilename;
            if (!file_exists($originalPath)) {
                return null;
            }
            
            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $newFilename = uniqid('project_', true) . '.' . $extension;
            $newPath = 'uploads/projects/' . $newFilename;
            
            if (copy($originalPath, $newPath)) {
                return $newFilename;
            }
            
        } catch (Exception $e) {
            error_log("Erreur duplicateImage: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Obtenir l'extension à partir du type MIME
     */
    private function getExtensionFromMime($mimeType) {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];
        
        return $extensions[$mimeType] ?? 'jpg';
    }
    
    /**
     * Enregistrer l'activité du projet
     */
    private function logProjectActivity($userId, $action, $projectId) {
        try {
            $sql = "INSERT INTO project_activities (user_id, project_id, action, ip_address, created_at) 
                    VALUES (:user_id, :project_id, :action, :ip, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'project_id' => $projectId,
                'action' => $action,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (PDOException $e) {
            error_log("Erreur logProjectActivity: " . $e->getMessage());
        }
    }
}
?>