<?php
/**
 * Script de setup automatique pour créer tous les fichiers du projet Portfolio PHP
 * À exécuter une seule fois : http://localhost/RenduPHP2025-S2/setup.php
 */

echo "<h1>Setup du projet Portfolio PHP</h1>";

// Créer la structure des dossiers
$directories = [
    'config',
    'classes', 
    'includes',
    'assets',
    'assets/css',
    'assets/js',
    'uploads',
    'uploads/projects'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Dossier créé : $dir<br>";
    } else {
        echo "📁 Dossier existe : $dir<br>";
    }
}

// Donner les permissions au dossier uploads
chmod('uploads', 0777);
chmod('uploads/projects', 0777);
echo "✅ Permissions définies pour uploads/<br>";

// Créer config/database.php
$database_config = '<?php 
define(\'DB_HOST\', \'localhost\');
define(\'DB_PORT\', 3306);
define(\'DB_NAME\', \'projetb2\');
define(\'DB_USER\', \'projetb2\');
define(\'DB_PASS\', \'password\');
?>';

file_put_contents('config/database.php', $database_config);
echo "✅ Fichier créé : config/database.php<br>";

// Créer classes/Database.php
$database_class = '<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    private function __clone() {}
    public function __wakeup() {}
}
?>';

file_put_contents('classes/Database.php', $database_class);
echo "✅ Fichier créé : classes/Database.php<br>";

// Message de fin
echo "<br><h2>🎉 Setup terminé !</h2>";
echo "<p>Maintenant :</p>";
echo "<ol>";
echo "<li>Importez le fichier database.sql dans phpMyAdmin</li>";
echo "<li>Créez les autres fichiers PHP (User.php, Skill.php, etc.)</li>";
echo "<li>Allez sur <a href='index.php'>index.php</a> pour tester</li>";
echo "</ol>";

echo "<h3>📋 Étapes suivantes :</h3>";
echo "<p>1. Ouvrir phpMyAdmin : <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></p>";
echo "<p>2. Importer database.sql</p>";
echo "<p>3. Créer les fichiers manquants (je peux vous aider)</p>";
?>