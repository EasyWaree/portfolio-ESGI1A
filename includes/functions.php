<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier l'expiration de la session (30 minutes d'inactivité)
if (isset($_SESSION['last_activity'])) {
    $inactive = 1800; // 30 minutes en secondes
    $session_life = time() - $_SESSION['last_activity'];
    
    if ($session_life > $inactive) {
        session_destroy();
        session_start();
        $_SESSION['expired'] = true;
        header('Location: auth.php');
        exit;
    }
}

// Mettre à jour l'heure de dernière activité
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

// Vérifier le token "Se souvenir de moi" si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../classes/Database.php';
    require_once __DIR__ . '/../classes/User.php';
    
    $userClass = new User();
    $userClass->checkRememberToken();
}

// ===== FONCTIONS D'AUTHENTIFICATION =====

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: auth.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Accès non autorisé. Vous devez être administrateur.";
        header('Location: dashboard.php');
        exit;
    }
}

function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function logout() {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../classes/Database.php';
    require_once __DIR__ . '/../classes/User.php';
    
    $userClass = new User();
    $userClass->logout();
}

// ===== FONCTIONS DE SÉCURITÉ =====

function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return trim(stripslashes(htmlspecialchars($data)));
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ===== FONCTIONS D'AFFICHAGE =====

function displayAlert($type, $message) {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' .
           htmlspecialchars($message) .
           '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    return date($format, strtotime($datetime));
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'À l\'instant';
    if ($time < 3600) return floor($time/60) . ' min';
    if ($time < 86400) return floor($time/3600) . ' h';
    if ($time < 2592000) return floor($time/86400) . ' j';
    if ($time < 31536000) return floor($time/2592000) . ' mois';
    return floor($time/31536000) . ' ans';
}

function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// ===== FONCTIONS DE VALIDATION =====

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function validatePassword($password, $minLength = 6) {
    return strlen($password) >= $minLength;
}

function validateRequired($value) {
    return !empty(trim($value));
}

// ===== FONCTIONS D'UPLOAD =====

function validateImage($file, $maxSize = 5242880) { // 5MB par défaut
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Erreur lors de l'upload.";
        return ['valid' => false, 'errors' => $errors];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        $errors[] = "Format non autorisé. Utilisez JPG, PNG ou GIF.";
    }
    
    if ($file['size'] > $maxSize) {
        $errors[] = "Fichier trop volumineux (max " . ($maxSize/1024/1024) . "MB).";
    }
    
    $imageInfo = getimagesize($file['tmp_name']);
    if (!$imageInfo) {
        $errors[] = "Le fichier n'est pas une image valide.";
    }
    
    return ['valid' => empty($errors), 'errors' => $errors];
}

function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . strtolower($extension);
}

// ===== FONCTIONS UTILITAIRES =====

function redirect($url, $statusCode = 302) {
    header("Location: $url", true, $statusCode);
    exit;
}

function getSkillLevelPercentage($level) {
    $levels = [
        'debutant' => 25,
        'intermediaire' => 50,
        'avance' => 75,
        'expert' => 100
    ];
    return $levels[$level] ?? 0;
}

function getSkillLevelBadgeClass($level) {
    $classes = [
        'debutant' => 'bg-info',
        'intermediaire' => 'bg-warning',
        'avance' => 'bg-success',
        'expert' => 'bg-primary'
    ];
    return $classes[$level] ?? 'bg-secondary';
}

function generateBreadcrumb($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    $count = count($items);
    
    foreach ($items as $index => $item) {
        if ($index === $count - 1) {
            $html .= '<li class="breadcrumb-item active">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

// ===== FONCTIONS DE DEBUG (à supprimer en production) =====

function debug($data, $die = false) {
    echo '<pre style="background: #f4f4f4; padding: 10px; border: 1px solid #ddd; margin: 10px 0;">';
    print_r($data);
    echo '</pre>';
    if ($die) die();
}

function logError($message, $file = 'error.log') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    error_log($logMessage, 3, $file);
}

// ===== CONSTANTES UTILES =====

define('SKILL_LEVELS', [
    'debutant' => 'Débutant',
    'intermediaire' => 'Intermédiaire', 
    'avance' => 'Avancé',
    'expert' => 'Expert'
]);

define('SKILL_CATEGORIES', [
    'Frontend', 'Backend', 'Database', 'DevOps', 
    'Design', 'Tools', 'System', 'Mobile'
]);

define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
define('SESSION_TIMEOUT', 1800); // 30 minutes

// ===== AUTO-LOADER SIMPLE =====

function autoloadClasses($className) {
    $file = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('autoloadClasses');
?>

