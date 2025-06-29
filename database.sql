-- Création de la base de données et de l'utilisateur
CREATE DATABASE IF NOT EXISTS `projetb2` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Création de l'utilisateur et attribution des droits
CREATE USER IF NOT EXISTS 'projetb2'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON `projetb2`.* TO 'projetb2'@'localhost';
FLUSH PRIVILEGES;

-- Utilisation de la base de données
USE `projetb2`;

-- Table des utilisateurs
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `bio` text,
  `phone` varchar(20),
  `location` varchar(100),
  `website` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des compétences
CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `category` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison utilisateur-compétences
CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `level` enum('debutant','intermediaire','avance','expert') NOT NULL DEFAULT 'debutant',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_skill_unique` (`user_id`,`skill_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des projets
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255),
  `external_link` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des sessions "Se souvenir de moi"
CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des données de test

-- Utilisateurs de test (tous les mots de passe sont 'password')
-- Hash généré avec password_hash('password', PASSWORD_DEFAULT)
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`, `bio`, `phone`, `location`, `website`) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'Administrateur du système de portfolio. Responsable de la gestion des compétences et de la supervision de la plateforme.', '+33123456789', 'Paris, France', 'https://admin-portfolio.com'),
('johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'user', 'Développeur Full-Stack passionné par les nouvelles technologies. Spécialisé dans le développement web moderne avec PHP, JavaScript et les frameworks actuels.', '+33987654321', 'Lyon, France', 'https://johndoe-dev.com'),
('janedoe', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Doe', 'user', 'Designer UX/UI créative avec 5 ans d\'expérience. Passionnée par la création d\'interfaces utilisateur modernes et intuitives.', '+33456789123', 'Marseille, France', 'https://janedoe-design.com');

-- Compétences prédéfinies par catégorie
INSERT INTO `skills` (`name`, `category`) VALUES
-- Frontend
('HTML/CSS', 'Frontend'),
('JavaScript', 'Frontend'),
('React', 'Frontend'),
('Vue.js', 'Frontend'),
('Angular', 'Frontend'),
('Sass/SCSS', 'Frontend'),
('Bootstrap', 'Frontend'),
('Tailwind CSS', 'Frontend'),

-- Backend
('PHP', 'Backend'),
('Node.js', 'Backend'),
('Python', 'Backend'),
('Java', 'Backend'),
('Laravel', 'Backend'),
('Symfony', 'Backend'),
('Express.js', 'Backend'),
('Django', 'Backend'),

-- Database
('MySQL', 'Database'),
('PostgreSQL', 'Database'),
('MongoDB', 'Database'),
('Redis', 'Database'),
('SQLite', 'Database'),

-- DevOps
('Docker', 'DevOps'),
('Git', 'DevOps'),
('Linux', 'DevOps'),
('AWS', 'DevOps'),
('Azure', 'DevOps'),
('CI/CD', 'DevOps'),

-- Design
('Photoshop', 'Design'),
('Figma', 'Design'),
('UI/UX Design', 'Design'),
('Illustrator', 'Design'),
('Sketch', 'Design'),

-- Tools
('VS Code', 'Tools'),
('IntelliJ', 'Tools'),
('Postman', 'Tools'),
('Jira', 'Tools'),

-- Mobile
('React Native', 'Mobile'),
('Flutter', 'Mobile'),
('Swift', 'Mobile'),
('Kotlin', 'Mobile');

-- Compétences des utilisateurs
INSERT INTO `user_skills` (`user_id`, `skill_id`, `level`) VALUES
-- John Doe (ID 2) - Développeur Full-Stack
(2, 1, 'expert'),     -- HTML/CSS
(2, 2, 'expert'),     -- JavaScript
(2, 9, 'expert'),     -- PHP
(2, 3, 'avance'),     -- React
(2, 15, 'avance'),    -- Laravel
(2, 17, 'expert'),    -- MySQL
(2, 21, 'expert'),    -- Docker
(2, 22, 'expert'),    -- Git
(2, 23, 'avance'),    -- Linux
(2, 33, 'intermediaire'), -- VS Code

-- Jane Doe (ID 3) - Designer UX/UI
(3, 1, 'expert'),     -- HTML/CSS
(3, 2, 'avance'),     -- JavaScript
(3, 3, 'avance'),     -- React
(3, 4, 'intermediaire'), -- Vue.js
(3, 6, 'expert'),     -- Sass/SCSS
(3, 7, 'expert'),     -- Bootstrap
(3, 27, 'expert'),    -- Photoshop
(3, 28, 'expert'),    -- Figma
(3, 29, 'expert'),    -- UI/UX Design
(3, 30, 'avance'),    -- Illustrator
(3, 31, 'avance');    -- Sketch

-- Projets d'exemple
INSERT INTO `projects` (`user_id`, `title`, `description`, `image`, `external_link`) VALUES
-- Projets de John Doe
(2, 'E-commerce Platform', 'Plateforme e-commerce complète développée en PHP avec Laravel. Gestion des produits, commandes, paiements Stripe, interface d\'administration complète, système de notifications et tableau de bord analytique.', 'ecommerce.jpg', 'https://github.com/johndoe/ecommerce-platform'),
(2, 'Task Management App', 'Application de gestion de tâches développée en React avec Node.js. Fonctionnalités de drag & drop, notifications en temps réel, collaboration en équipe, calendrier intégré et reporting avancé.', 'taskmanager.jpg', 'https://github.com/johndoe/task-manager'),
(2, 'Weather Dashboard', 'Dashboard météo responsive utilisant l\'API OpenWeather. Affichage des conditions actuelles, prévisions sur 7 jours, graphiques interactifs, géolocalisation automatique et alertes météo.', 'weather.jpg', 'https://github.com/johndoe/weather-dashboard'),
(2, 'API RESTful Blog', 'API RESTful complète pour un système de blog avec authentification JWT, gestion des rôles, système de commentaires, upload d\'images et documentation Swagger.', NULL, 'https://github.com/johndoe/blog-api'),

-- Projets de Jane Doe
(3, 'Mobile Banking App Design', 'Conception complète d\'une application bancaire mobile avec focus sur l\'expérience utilisateur et l\'accessibilité. Prototype interactif, tests utilisateurs, design system complet et guidelines d\'implémentation.', 'banking-app.jpg', 'https://behance.net/janedoe/banking-app'),
(3, 'Restaurant Website Redesign', 'Refonte complète du site web d\'un restaurant local avec nouveau système de réservation en ligne, menu interactif, galerie photos optimisée et intégration réseaux sociaux.', 'restaurant.jpg', 'https://behance.net/janedoe/restaurant-redesign'),
(3, 'Fitness Tracker Interface', 'Interface utilisateur moderne pour application de fitness avec dashboard personnalisé, suivi des objectifs, graphiques de progression, système de récompenses et coaching virtuel.', 'fitness.jpg', 'https://behance.net/janedoe/fitness-tracker'),
(3, 'E-learning Platform UI', 'Design d\'interface pour plateforme d\'apprentissage en ligne. Parcours utilisateur optimisé, système de gamification, interface adaptative et outils de collaboration entre étudiants.', NULL, 'https://behance.net/janedoe/elearning-ui');