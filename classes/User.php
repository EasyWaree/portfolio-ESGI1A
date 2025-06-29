<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register($data) {
        $errors = [];
        
        // Validation des champs
        if (empty($data['username'])) {
            $errors[] = "Le nom d'utilisateur est requis";
        } elseif (strlen($data['username']) < 3) {
            $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
        }
        
        if (empty($data['email'])) {
            $errors[] = "L'email est requis";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide";
        }
        
        if (empty($data['password'])) {
            $errors[] = "Le mot de passe est requis";
        } elseif (strlen($data['password']) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }
        
        if (empty($data['first_name'])) {
            $errors[] = "Le prénom est requis";
        }
        
        if (empty($data['last_name'])) {
            $errors[] = "Le nom est requis";
        }
        
        // Vérifier si l'utilisateur ou l'email existe déjà
        if (empty($errors)) {
            if ($this->usernameExists($data['username'])) {
                $errors[] = "Ce nom d'utilisateur est déjà utilisé";
            }
            
            if ($this->emailExists($data['email'])) {
                $errors[] = "Cet email est déjà utilisé";
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Créer l'utilisateur
        try {
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['first_name'],
                $data['last_name']
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Inscription réussie !'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de l\'inscription']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    public function login($username, $password, $remember = false) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Démarrer la session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Gestion du "Se souvenir de moi"
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    // Supprimer les anciens tokens
                    $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Créer un nouveau token
                    $stmt = $this->db->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                    $stmt->execute([$user['id'], hash('sha256', $token), $expires]);
                    
                    // Définir le cookie
                    setcookie('remember_token', $token, strtotime('+30 days'), '/', '', false, true);
                }
                
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'error' => 'Nom d\'utilisateur ou mot de passe incorrect'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Erreur de connexion'];
        }
    }
    
    public function checkRememberToken() {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $hashedToken = hash('sha256', $token);
            
            try {
                $stmt = $this->db->prepare("
                    SELECT u.* FROM users u 
                    JOIN remember_tokens rt ON u.id = rt.user_id 
                    WHERE rt.token = ? AND rt.expires_at > NOW()
                ");
                $stmt->execute([$hashedToken]);
                $user = $stmt->fetch();
                
                if ($user) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();
                    return true;
                }
            } catch (PDOException $e) {
                // Token invalide, le supprimer
                setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            }
        }
        return false;
    }
    
    public function logout() {
        session_start();
        
        // Supprimer le token de souvenir si il existe
        if (isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        // Détruire la session
        session_destroy();
        session_start();
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateProfile($userId, $data) {
        $errors = [];
        
        // Validation
        if (empty($data['first_name'])) {
            $errors[] = "Le prénom est requis";
        }
        
        if (empty($data['last_name'])) {
            $errors[] = "Le nom est requis";
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide";
        }
        
        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors[] = "L'URL du site web n'est pas valide";
        }
        
        // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
        if (!empty($data['email'])) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $userId]);
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé";
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE users SET 
                first_name = ?, last_name = ?, email = ?, bio = ?, 
                phone = ?, location = ?, website = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['bio'] ?? '',
                $data['phone'] ?? '',
                $data['location'] ?? '',
                $data['website'] ?? '',
                $userId
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Profil mis à jour avec succès'];
            } else {
                return ['success' => false, 'errors' => ['Erreur lors de la mise à jour']];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Erreur de base de données']];
        }
    }
    
    private function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }
    
    private function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
    
    public function isAdmin($userId) {
        try {
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            return $user && $user['role'] === 'admin';
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>