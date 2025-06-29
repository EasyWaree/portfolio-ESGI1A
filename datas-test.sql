-- =====================================================
-- PORTFOLIO PHP - DONNÉES DE TEST COMPLÈTES
-- Projet ESGI 2024/2025
-- =====================================================

-- Nettoyage des données existantes (dans l'ordre des dépendances)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `user_skills`;
TRUNCATE TABLE `projects`;
TRUNCATE TABLE `remember_tokens`;
TRUNCATE TABLE `skills`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- INSERTION DES UTILISATEURS DE TEST
-- =====================================================
-- Tous les mots de passe sont : "password"
-- Hash généré avec password_hash('password', PASSWORD_DEFAULT)

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `role`, `bio`, `phone`, `location`, `website`, `created_at`) VALUES
(1, 'admin', 'admin@portfolio-esgi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alexandre', 'Martin', 'admin', 
'Administrateur système et développeur senior avec 8 ans d\'expérience. Spécialisé dans la gestion de projets web et la supervision d\'équipes de développement. Passionné par les nouvelles technologies et l\'innovation.', 
'+33 1 23 45 67 89', 'Paris, France', 'https://alexandre-martin.dev', '2024-01-15 10:30:00'),

(2, 'johndoe', 'john.doe@webdev-pro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'user', 
'Développeur Full-Stack passionné avec 5 ans d\'expérience dans la création d\'applications web modernes. Expert en PHP, JavaScript et frameworks frontend. Toujours à la recherche de nouveaux défis techniques et de solutions innovantes.', 
'+33 6 12 34 56 78', 'Lyon, France', 'https://johndoe-portfolio.fr', '2024-02-20 14:15:00'),

(3, 'janedoe', 'jane.design@creative-studio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Doe', 'user', 
'Designer UX/UI créative avec 4 ans d\'expérience dans la conception d\'interfaces utilisateur modernes et intuitives. Spécialisée dans la recherche utilisateur, le prototypage et la création d\'expériences digitales mémorables.', 
'+33 7 89 01 23 45', 'Marseille, France', 'https://jane-creative-design.com', '2024-02-25 09:45:00'),

(4, 'mikewilson', 'mike.wilson@backend-expert.dev', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Wilson', 'user', 
'Architecte logiciel et expert backend avec 6 ans d\'expérience. Spécialisé dans la conception d\'API robustes, l\'optimisation de bases de données et les architectures microservices. Passionné par la performance et la scalabilité.', 
'+33 6 55 44 33 22', 'Toulouse, France', 'https://mike-backend-solutions.com', '2024-03-01 16:20:00'),

(5, 'sarahchen', 'sarah.chen@mobile-innovations.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Chen', 'user', 
'Développeuse mobile spécialisée en React Native et Flutter avec 3 ans d\'expérience. Experte en création d\'applications mobiles cross-platform, UX mobile et intégration d\'APIs. Toujours à la pointe des tendances mobiles.', 
'+33 6 77 88 99 00', 'Nice, France', 'https://sarah-mobile-apps.dev', '2024-03-10 11:30:00');

-- =====================================================
-- INSERTION DES COMPÉTENCES PAR CATÉGORIES
-- =====================================================

INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES
-- FRONTEND (1-15)
(1, 'HTML5', 'Frontend', '2024-01-01 10:00:00'),
(2, 'CSS3', 'Frontend', '2024-01-01 10:05:00'),
(3, 'JavaScript ES6+', 'Frontend', '2024-01-01 10:10:00'),
(4, 'TypeScript', 'Frontend', '2024-01-01 10:15:00'),
(5, 'React', 'Frontend', '2024-01-01 10:20:00'),
(6, 'Vue.js', 'Frontend', '2024-01-01 10:25:00'),
(7, 'Angular', 'Frontend', '2024-01-01 10:30:00'),
(8, 'Sass/SCSS', 'Frontend', '2024-01-01 10:35:00'),
(9, 'Bootstrap', 'Frontend', '2024-01-01 10:40:00'),
(10, 'Tailwind CSS', 'Frontend', '2024-01-01 10:45:00'),
(11, 'Webpack', 'Frontend', '2024-01-01 10:50:00'),
(12, 'Vite', 'Frontend', '2024-01-01 10:55:00'),
(13, 'jQuery', 'Frontend', '2024-01-01 11:00:00'),
(14, 'Next.js', 'Frontend', '2024-01-01 11:05:00'),
(15, 'Nuxt.js', 'Frontend', '2024-01-01 11:10:00'),

-- BACKEND (16-30)
(16, 'PHP', 'Backend', '2024-01-01 11:15:00'),
(17, 'Node.js', 'Backend', '2024-01-01 11:20:00'),
(18, 'Python', 'Backend', '2024-01-01 11:25:00'),
(19, 'Java', 'Backend', '2024-01-01 11:30:00'),
(20, 'C#', 'Backend', '2024-01-01 11:35:00'),
(21, 'Laravel', 'Backend', '2024-01-01 11:40:00'),
(22, 'Symfony', 'Backend', '2024-01-01 11:45:00'),
(23, 'Express.js', 'Backend', '2024-01-01 11:50:00'),
(24, 'Django', 'Backend', '2024-01-01 11:55:00'),
(25, 'Flask', 'Backend', '2024-01-01 12:00:00'),
(26, 'Spring Boot', 'Backend', '2024-01-01 12:05:00'),
(27, 'ASP.NET Core', 'Backend', '2024-01-01 12:10:00'),
(28, 'FastAPI', 'Backend', '2024-01-01 12:15:00'),
(29, 'GraphQL', 'Backend', '2024-01-01 12:20:00'),
(30, 'REST API', 'Backend', '2024-01-01 12:25:00'),

-- DATABASE (31-40)
(31, 'MySQL', 'Database', '2024-01-01 12:30:00'),
(32, 'PostgreSQL', 'Database', '2024-01-01 12:35:00'),
(33, 'MongoDB', 'Database', '2024-01-01 12:40:00'),
(34, 'Redis', 'Database', '2024-01-01 12:45:00'),
(35, 'SQLite', 'Database', '2024-01-01 12:50:00'),
(36, 'MariaDB', 'Database', '2024-01-01 12:55:00'),
(37, 'Firebase', 'Database', '2024-01-01 13:00:00'),
(38, 'Elasticsearch', 'Database', '2024-01-01 13:05:00'),
(39, 'Oracle', 'Database', '2024-01-01 13:10:00'),
(40, 'SQL Server', 'Database', '2024-01-01 13:15:00'),

-- DEVOPS (41-55)
(41, 'Docker', 'DevOps', '2024-01-01 13:20:00'),
(42, 'Kubernetes', 'DevOps', '2024-01-01 13:25:00'),
(43, 'Git', 'DevOps', '2024-01-01 13:30:00'),
(44, 'GitHub Actions', 'DevOps', '2024-01-01 13:35:00'),
(45, 'GitLab CI/CD', 'DevOps', '2024-01-01 13:40:00'),
(46, 'Jenkins', 'DevOps', '2024-01-01 13:45:00'),
(47, 'AWS', 'DevOps', '2024-01-01 13:50:00'),
(48, 'Azure', 'DevOps', '2024-01-01 13:55:00'),
(49, 'Google Cloud', 'DevOps', '2024-01-01 14:00:00'),
(50, 'Linux/Unix', 'DevOps', '2024-01-01 14:05:00'),
(51, 'Nginx', 'DevOps', '2024-01-01 14:10:00'),
(52, 'Apache', 'DevOps', '2024-01-01 14:15:00'),
(53, 'Terraform', 'DevOps', '2024-01-01 14:20:00'),
(54, 'Ansible', 'DevOps', '2024-01-01 14:25:00'),
(55, 'Prometheus', 'DevOps', '2024-01-01 14:30:00'),

-- DESIGN (56-70)
(56, 'Figma', 'Design', '2024-01-01 14:35:00'),
(57, 'Adobe Photoshop', 'Design', '2024-01-01 14:40:00'),
(58, 'Adobe Illustrator', 'Design', '2024-01-01 14:45:00'),
(59, 'Adobe XD', 'Design', '2024-01-01 14:50:00'),
(60, 'Sketch', 'Design', '2024-01-01 14:55:00'),
(61, 'UI/UX Design', 'Design', '2024-01-01 15:00:00'),
(62, 'Prototyping', 'Design', '2024-01-01 15:05:00'),
(63, 'Design Systems', 'Design', '2024-01-01 15:10:00'),
(64, 'User Research', 'Design', '2024-01-01 15:15:00'),
(65, 'Wireframing', 'Design', '2024-01-01 15:20:00'),
(66, 'Adobe After Effects', 'Design', '2024-01-01 15:25:00'),
(67, 'Blender', 'Design', '2024-01-01 15:30:00'),
(68, 'InVision', 'Design', '2024-01-01 15:35:00'),
(69, 'Canva', 'Design', '2024-01-01 15:40:00'),
(70, 'Principle', 'Design', '2024-01-01 15:45:00'),

-- MOBILE (71-80)
(71, 'React Native', 'Mobile', '2024-01-01 15:50:00'),
(72, 'Flutter', 'Mobile', '2024-01-01 15:55:00'),
(73, 'Swift (iOS)', 'Mobile', '2024-01-01 16:00:00'),
(74, 'Kotlin (Android)', 'Mobile', '2024-01-01 16:05:00'),
(75, 'Dart', 'Mobile', '2024-01-01 16:10:00'),
(76, 'Xamarin', 'Mobile', '2024-01-01 16:15:00'),
(77, 'Ionic', 'Mobile', '2024-01-01 16:20:00'),
(78, 'PhoneGap/Cordova', 'Mobile', '2024-01-01 16:25:00'),
(79, 'Android Studio', 'Mobile', '2024-01-01 16:30:00'),
(80, 'Xcode', 'Mobile', '2024-01-01 16:35:00'),

-- TOOLS (81-95)
(81, 'Visual Studio Code', 'Tools', '2024-01-01 16:40:00'),
(82, 'IntelliJ IDEA', 'Tools', '2024-01-01 16:45:00'),
(83, 'Postman', 'Tools', '2024-01-01 16:50:00'),
(84, 'Insomnia', 'Tools', '2024-01-01 16:55:00'),
(85, 'Jira', 'Tools', '2024-01-01 17:00:00'),
(86, 'Trello', 'Tools', '2024-01-01 17:05:00'),
(87, 'Slack', 'Tools', '2024-01-01 17:10:00'),
(88, 'Notion', 'Tools', '2024-01-01 17:15:00'),
(89, 'PHPStorm', 'Tools', '2024-01-01 17:20:00'),
(90, 'WebStorm', 'Tools', '2024-01-01 17:25:00'),
(91, 'Sublime Text', 'Tools', '2024-01-01 17:30:00'),
(92, 'Vim/Neovim', 'Tools', '2024-01-01 17:35:00'),
(93, 'TablePlus', 'Tools', '2024-01-01 17:40:00'),
(94, 'Sequel Pro', 'Tools', '2024-01-01 17:45:00'),
(95, 'Sourcetree', 'Tools', '2024-01-01 17:50:00');

-- =====================================================
-- ASSIGNATION DES COMPÉTENCES AUX UTILISATEURS
-- =====================================================

-- ADMIN (Alexandre Martin) - Compétences diversifiées d'administration
INSERT INTO `user_skills` (`user_id`, `skill_id`, `level`, `created_at`) VALUES
(1, 16, 'expert', '2024-01-16 10:00:00'),    -- PHP
(1, 31, 'expert', '2024-01-16 10:05:00'),    -- MySQL
(1, 43, 'expert', '2024-01-16 10:10:00'),    -- Git
(1, 50, 'expert', '2024-01-16 10:15:00'),    -- Linux/Unix
(1, 41, 'avance', '2024-01-16 10:20:00'),    -- Docker
(1, 47, 'avance', '2024-01-16 10:25:00'),    -- AWS
(1, 3, 'avance', '2024-01-16 10:30:00'),     -- JavaScript
(1, 21, 'expert', '2024-01-16 10:35:00'),    -- Laravel
(1, 85, 'avance', '2024-01-16 10:40:00'),    -- Jira
(1, 81, 'expert', '2024-01-16 10:45:00');    -- VS Code

-- JOHN DOE - Développeur Full-Stack (Frontend + Backend)
INSERT INTO `user_skills` (`user_id`, `skill_id`, `level`, `created_at`) VALUES
(2, 1, 'expert', '2024-02-21 09:00:00'),     -- HTML5
(2, 2, 'expert', '2024-02-21 09:05:00'),     -- CSS3
(2, 3, 'expert', '2024-02-21 09:10:00'),     -- JavaScript
(2, 4, 'avance', '2024-02-21 09:15:00'),     -- TypeScript
(2, 5, 'expert', '2024-02-21 09:20:00'),     -- React
(2, 14, 'avance', '2024-02-21 09:25:00'),    -- Next.js
(2, 16, 'expert', '2024-02-21 09:30:00'),    -- PHP
(2, 21, 'avance', '2024-02-21 09:35:00'),    -- Laravel
(2, 17, 'avance', '2024-02-21 09:40:00'),    -- Node.js
(2, 23, 'avance', '2024-02-21 09:45:00'),    -- Express.js
(2, 31, 'avance', '2024-02-21 09:50:00'),    -- MySQL
(2, 33, 'intermediaire', '2024-02-21 09:55:00'), -- MongoDB
(2, 43, 'avance', '2024-02-21 10:00:00'),    -- Git
(2, 41, 'avance', '2024-02-21 10:05:00'),    -- Docker
(2, 30, 'expert', '2024-02-21 10:10:00'),    -- REST API
(2, 9, 'avance', '2024-02-21 10:15:00'),     -- Bootstrap
(2, 10, 'avance', '2024-02-21 10:20:00'),    -- Tailwind CSS
(2, 81, 'expert', '2024-02-21 10:25:00'),    -- VS Code
(2, 83, 'avance', '2024-02-21 10:30:00');    -- Postman

-- JANE DOE - Designer UX/UI
INSERT INTO `user_skills` (`user_id`, `skill_id`, `level`, `created_at`) VALUES
(3, 56, 'expert', '2024-02-26 09:00:00'),    -- Figma
(3, 57, 'expert', '2024-02-26 09:05:00'),    -- Adobe Photoshop
(3, 58, 'avance', '2024-02-26 09:10:00'),    -- Adobe Illustrator
(3, 59, 'avance', '2024-02-26 09:15:00'),    -- Adobe XD
(3, 61, 'expert', '2024-02-26 09:20:00'),    -- UI/UX Design
(3, 62, 'expert', '2024-02-26 09:25:00'),    -- Prototyping
(3, 63, 'avance', '2024-02-26 09:30:00'),    -- Design Systems
(3, 64, 'avance', '2024-02-26 09:35:00'),    -- User Research
(3, 65, 'expert', '2024-02-26 09:40:00'),    -- Wireframing
(3, 1, 'avance', '2024-02-26 09:45:00'),     -- HTML5
(3, 2, 'avance', '2024-02-26 09:50:00'),     -- CSS3
(3, 8, 'avance', '2024-02-26 09:55:00'),     -- Sass/SCSS
(3, 3, 'intermediaire', '2024-02-26 10:00:00'), -- JavaScript
(3, 5, 'intermediaire', '2024-02-26 10:05:00'), -- React
(3, 9, 'avance', '2024-02-26 10:10:00'),     -- Bootstrap
(3, 10, 'expert', '2024-02-26 10:15:00'),    -- Tailwind CSS
(3, 66, 'intermediaire', '2024-02-26 10:20:00'), -- After Effects
(3, 69, 'avance', '2024-02-26 10:25:00');    -- Canva

-- MIKE WILSON - Expert Backend/DevOps
INSERT INTO `user_skills` (`user_id`, `skill_id`, `level`, `created_at`) VALUES
(4, 18, 'expert', '2024-03-02 09:00:00'),    -- Python
(4, 24, 'expert', '2024-03-02 09:05:00'),    -- Django
(4, 28, 'avance', '2024-03-02 09:10:00'),    -- FastAPI
(4, 32, 'expert', '2024-03-02 09:15:00'),    -- PostgreSQL
(4, 33, 'avance', '2024-03-02 09:20:00'),    -- MongoDB
(4, 34, 'avance', '2024-03-02 09:25:00'),    -- Redis
(4, 41, 'expert', '2024-03-02 09:30:00'),    -- Docker
(4, 42, 'avance', '2024-03-02 09:35:00'),    -- Kubernetes
(4, 47, 'expert', '2024-03-02 09:40:00'),    -- AWS
(4, 50, 'expert', '2024-03-02 09:45:00'),    -- Linux/Unix
(4, 43, 'expert', '2024-03-02 09:50:00'),    -- Git
(4, 44, 'avance', '2024-03-02 09:55:00'),    -- GitHub Actions
(4, 29, 'avance', '2024-03-02 10:00:00'),    -- GraphQL
(4, 30, 'expert', '2024-03-02 10:05:00'),    -- REST API
(4, 38, 'avance', '2024-03-02 10:10:00'),    -- Elasticsearch
(4, 53, 'avance', '2024-03-02 10:15:00'),    -- Terraform
(4, 54, 'intermediaire', '2024-03-02 10:20:00'), -- Ansible
(4, 82, 'avance', '2024-03-02 10:25:00'),    -- IntelliJ IDEA
(4, 83, 'expert', '2024-03-02 10:30:00');    -- Postman

-- SARAH CHEN - Développeuse Mobile
INSERT INTO `user_skills` (`user_id`, `skill_id`, `level`, `created_at`) VALUES
(5, 71, 'expert', '2024-03-11 09:00:00'),    -- React Native
(5, 72, 'expert', '2024-03-11 09:05:00'),    -- Flutter
(5, 75, 'avance', '2024-03-11 09:10:00'),    -- Dart
(5, 73, 'avance', '2024-03-11 09:15:00'),    -- Swift (iOS)
(5, 74, 'avance', '2024-03-11 09:20:00'),    -- Kotlin (Android)
(5, 79, 'avance', '2024-03-11 09:25:00'),    -- Android Studio
(5, 80, 'avance', '2024-03-11 09:30:00'),    -- Xcode
(5, 3, 'expert', '2024-03-11 09:35:00'),     -- JavaScript
(5, 4, 'avance', '2024-03-11 09:40:00'),     -- TypeScript
(5, 5, 'expert', '2024-03-11 09:45:00'),     -- React
(5, 17, 'avance', '2024-03-11 09:50:00'),    -- Node.js
(5, 37, 'avance', '2024-03-11 09:55:00'),    -- Firebase
(5, 30, 'avance', '2024-03-11 10:00:00'),    -- REST API
(5, 43, 'avance', '2024-03-11 10:05:00'),    -- Git
(5, 56, 'intermediaire', '2024-03-11 10:10:00'), -- Figma
(5, 61, 'intermediaire', '2024-03-11 10:15:00'), -- UI/UX Design
(5, 81, 'avance', '2024-03-11 10:20:00'),    -- VS Code
(5, 83, 'avance', '2024-03-11 10:25:00');    -- Postman

-- =====================================================
-- INSERTION DES PROJETS (3+ projets par utilisateur)
-- =====================================================

-- PROJETS DE L'ADMIN (Alexandre Martin)
INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `image`, `external_link`, `created_at`) VALUES
(1, 1, 'Plateforme de Gestion d\'Équipe', 
'Système complet de gestion d\'équipes et de projets développé en Laravel. Inclut la gestion des utilisateurs, attribution des tâches, suivi des performances, tableau de bord analytique et système de notifications en temps réel.', 
'team_management.jpg', 'https://github.com/alexmartin/team-management-platform', '2024-01-20 14:30:00'),

(2, 1, 'API de Monitoring Système', 
'API robuste de monitoring et supervision de serveurs développée en PHP avec intégration Prometheus. Collecte de métriques système, alertes automatisées, tableaux de bord personnalisables et rapports de performance détaillés.', 
'monitoring_api.jpg', 'https://github.com/alexmartin/system-monitoring-api', '2024-02-05 11:15:00'),

(3, 1, 'Infrastructure DevOps Automatisée', 
'Architecture DevOps complète avec Docker, Kubernetes et CI/CD. Déploiement automatisé, orchestration de containers, monitoring avancé et gestion des environnements multi-cloud avec Terraform et Ansible.', 
'devops_infrastructure.jpg', 'https://github.com/alexmartin/devops-automation', '2024-02-18 16:45:00'),

(4, 1, 'Centre de Formation en Ligne', 
'Plateforme e-learning complète avec gestion des cours, quizz interactifs, suivi de progression, système de certification et interface administrateur avancée. Support multi-média et gamification.', 
NULL, 'https://formation-center-demo.com', '2024-03-01 10:20:00');

-- PROJETS DE JOHN DOE (Développeur Full-Stack)
INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `image`, `external_link`, `created_at`) VALUES
(5, 2, 'E-commerce Modern & Responsive', 
'Boutique en ligne complète développée avec React/Next.js et Laravel API. Gestion produits, panier avancé, paiements Stripe/PayPal, système de reviews, recommandations IA et interface admin complète avec analytics.', 
'ecommerce_modern.jpg', 'https://github.com/johndoe/modern-ecommerce', '2024-02-25 09:30:00'),

(6, 2, 'Application de Gestion de Tâches', 
'App collaborative de gestion de projets avec React et Node.js. Drag & drop, notifications temps réel, calendrier intégré, rapports automatisés, intégration Slack et système de permissions granulaires.', 
'task_manager_app.jpg', 'https://taskmanager-demo.johndoe.dev', '2024-03-08 14:20:00'),

(7, 2, 'Dashboard Analytics Avancé', 
'Tableau de bord interactif avec visualisations de données en temps réel. Charts dynamiques, filtres avancés, export multi-formats, intégration API et système d\'alertes personnalisables.', 
'analytics_dashboard.jpg', 'https://github.com/johndoe/analytics-dashboard', '2024-03-15 11:45:00'),

(8, 2, 'API RESTful pour IoT', 
'API scalable pour objets connectés avec authentification JWT, gestion de capteurs, stockage de données temporelles, alertes en temps réel et interface de configuration dynamique.', 
NULL, 'https://github.com/johndoe/iot-api-platform', '2024-03-22 16:10:00');

-- PROJETS DE JANE DOE (Designer UX/UI)
INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `image`, `external_link`, `created_at`) VALUES
(9, 3, 'Redesign App Bancaire Mobile', 
'Refonte complète UX/UI d\'une application bancaire mobile. Recherche utilisateur approfondie, personas, wireframes, prototypes interactifs, tests utilisateurs et design system complet. Focus accessibilité et sécurité.', 
'banking_app_redesign.jpg', 'https://behance.net/janedoe/banking-app-redesign', '2024-02-28 10:15:00'),

(10, 3, 'Design System pour SaaS', 
'Création d\'un design system complet pour plateforme SaaS B2B. Composants réutilisables, guidelines UX, tokens de design, documentation interactive et outils pour développeurs.', 
'design_system_saas.jpg', 'https://www.figma.com/janedoe/saas-design-system', '2024-03-05 15:30:00'),

(11, 3, 'Interface E-learning Interactive', 
'Conception UX/UI pour plateforme d\'apprentissage en ligne. Parcours utilisateur optimisé, gamification, interface adaptative, système de progression visuel et outils collaboratifs pour étudiants.', 
'elearning_interface.jpg', 'https://behance.net/janedoe/elearning-platform', '2024-03-12 13:45:00'),

(12, 3, 'App Fitness & Wellness', 
'Design d\'application mobile de fitness avec suivi personnalisé. Interface intuitive, tracking d\'activités, coaching virtuel, communauté intégrée et système de récompenses motivant.', 
'fitness_app_design.jpg', 'https://dribbble.com/janedoe/fitness-wellness-app', '2024-03-18 09:20:00');

-- PROJETS DE MIKE WILSON (Expert Backend/DevOps)
INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `image`, `external_link`, `created_at`) VALUES
(13, 4, 'Architecture Microservices Scalable', 
'Conception et implémentation d\'architecture microservices avec Django/FastAPI. Service mesh, API Gateway, monitoring distribué, gestion des erreurs et auto-scaling avec Kubernetes.', 
'microservices_architecture.jpg', 'https://github.com/mikewilson/scalable-microservices', '2024-03-03 11:00:00'),

(14, 4, 'Pipeline CI/CD Enterprise', 
'Infrastructure DevOps complète avec GitLab CI/CD, Docker, Kubernetes et monitoring. Déploiements automatisés, tests intégrés, rollback automatique et métriques de performance en temps réel.', 
'cicd_pipeline.jpg', 'https://github.com/mikewilson/enterprise-cicd', '2024-03-10 14:25:00'),

(15, 4, 'API Gateway & Rate Limiting', 
'Gateway API haute performance avec authentification, rate limiting, caching intelligent, logging centralisé et analytics en temps réel. Support multi-protocoles et load balancing avancé.', 
'api_gateway.jpg', 'https://github.com/mikewilson/api-gateway-solution', '2024-03-17 16:40:00'),

(16, 4, 'Système de Cache Distribué', 
'Solution de cache distribué avec Redis Cluster pour applications haute charge. Stratégies de cache intelligentes, invalidation automatique, monitoring et optimisations de performance.', 
NULL, 'https://github.com/mikewilson/distributed-cache-system', '2024-03-24 12:15:00');

-- PROJETS DE SARAH CHEN (Développeuse Mobile)
INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `image`, `external_link`, `created_at`) VALUES
(17, 5, 'App de Livraison Cross-Platform', 
'Application mobile de livraison développée en React Native. Géolocalisation temps réel, tracking des commandes, paiements intégrés, notifications push et interface livreur/client optimisée.', 
'delivery_app.jpg', 'https://github.com/sarahchen/delivery-app-react-native', '2024-03-12 10:30:00'),

(18, 5, 'Application de Méditation Flutter', 
'App de bien-être et méditation en Flutter avec contenu audio, sessions guidées, suivi de progression, communauté d\'utilisateurs et recommandations personnalisées basées sur l\'IA.', 
'meditation_app.jpg', 'https://github.com/sarahchen/meditation-flutter-app', '2024-03-19 14:15:00'),

(19, 5, 'Scanner de Documents IA', 
'Application mobile de scan et traitement de documents avec IA. OCR avancé, reconnaissance de formes, classification automatique, export multi-formats et stockage cloud sécurisé.', 
'document_scanner.jpg', 'https://github.com/sarahchen/ai-document-scanner', '2024-03-25 09:45:00'),

(20, 5, 'App de Réseautage Professionnel', 
'Plateforme mobile de networking professionnel avec matching intelligent, événements géolocalisés, chat intégré, partage de cartes de visite digitales et analytics de connections.', 
NULL, 'https://github.com/sarahchen/professional-networking-app', '2024-03-30 11:20:00');

-- =====================================================
-- TOKENS DE "SE SOUVENIR DE MOI" (EXEMPLES)
-- =====================================================

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 2, 'a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456', '2024-04-30 12:00:00', '2024-03-30 12:00:00'),
(2, 3, 'b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456a1', '2024-04-25 15:30:00', '2024-03-25 15:30:00');

-- =====================================================
-- STATISTIQUES ET VÉRIFICATIONS
-- =====================================================

-- Affichage des statistiques finales
SELECT 'UTILISATEURS' as Type, COUNT(*) as Total FROM users
UNION ALL
SELECT 'COMPÉTENCES' as Type, COUNT(*) as Total FROM skills
UNION ALL
SELECT 'PROJETS' as Type, COUNT(*) as Total FROM projects
UNION ALL
SELECT 'ASSIGNATIONS COMPÉTENCES' as Type, COUNT(*) as Total FROM user_skills;

-- Vérification des compétences par utilisateur
SELECT 
    u.username,
    u.first_name,
    u.last_name,
    u.role,
    COUNT(us.skill_id) as nb_competences
FROM users u
LEFT JOIN user_skills us ON u.id = us.user_id
GROUP BY u.id, u.username, u.first_name, u.last_name, u.role
ORDER BY u.role DESC, nb_competences DESC;

-- Vérification des projets par utilisateur
SELECT 
    u.username,
    u.first_name,
    u.last_name,
    COUNT(p.id) as nb_projets
FROM users u
LEFT JOIN projects p ON u.id = p.user_id
GROUP BY u.id, u.username, u.first_name, u.last_name
ORDER BY nb_projets DESC;

-- Répartition des compétences par catégorie
SELECT 
    s.category,
    COUNT(s.id) as nb_competences_disponibles,
    COUNT(us.skill_id) as nb_assignations
FROM skills s
LEFT JOIN user_skills us ON s.id = us.skill_id
GROUP BY s.category
ORDER BY nb_competences_disponibles DESC;

-- Répartition des niveaux de compétences
SELECT 
    level,
    COUNT(*) as nombre
FROM user_skills
GROUP BY level
ORDER BY 
    CASE level
        WHEN 'debutant' THEN 1
        WHEN 'intermediaire' THEN 2
        WHEN 'avance' THEN 3
        WHEN 'expert' THEN 4
    END;

-- =====================================================
-- COMMENTAIRES ET DOCUMENTATION
-- =====================================================

/*
RÉSUMÉ DES DONNÉES DE TEST CRÉÉES :

👥 UTILISATEURS (5 au total) :
- 1 Administrateur : Alexandre Martin (admin/password)
- 4 Utilisateurs normaux : John Doe, Jane Doe, Mike Wilson, Sarah Chen (tous password)

🛠️ COMPÉTENCES (95 au total) :
- Frontend : 15 compétences (HTML, CSS, React, etc.)
- Backend : 15 compétences (PHP, Node.js, Python, etc.)
- Database : 10 compétences (MySQL, MongoDB, Redis, etc.)
- DevOps : 15 compétences (Docker, AWS, Kubernetes, etc.)
- Design : 15 compétences (Figma, Photoshop, UX/UI, etc.)
- Mobile : 10 compétences (React Native, Flutter, Swift, etc.)
- Tools : 15 compétences (VS Code, Postman, Jira, etc.)

📊 ASSIGNATIONS DE COMPÉTENCES :
- Admin : 10 compétences (diversifiées)
- John Doe : 19 compétences (Full-Stack)
- Jane Doe : 18 compétences (Design + Frontend)
- Mike Wilson : 19 compétences (Backend + DevOps)
- Sarah Chen : 18 compétences (Mobile + Frontend)

📁 PROJETS (20 au total - 4 par utilisateur) :
- Projets réalistes avec descriptions détaillées
- Liens GitHub et démonstrations
- Variété de technologies et domaines
- Images d'illustration pour certains projets

🔧 FONCTIONNALITÉS TESTABLES :
- Connexion avec tous les comptes
- Gestion des compétences (ajout, modification, suppression)
- Gestion des projets (CRUD complet)
- Interface admin pour gérer les compétences
- Portfolios publics consultables
- Système "Se souvenir de moi"

🎯 CONFORMITÉ SUJET ESGI :
✅ Base de données : projetb2
✅ Utilisateur : projetb2 / password
✅ Fichier config : /config/database.php
✅ Mots de passe test : "password"
✅ 3+ utilisateurs dont 1 admin
✅ 3+ projets par utilisateur
✅ Compétences prédéfinies variées
✅ Système de rôles fonctionnel

UTILISATION :
1. Importer ce fichier dans phpMyAdmin
2. Tester la connexion avec les comptes fournis
3. Tous les mots de passe sont "password"
4. L'admin peut gérer les compétences
5. Les utilisateurs peuvent gérer leur portfolio
*/