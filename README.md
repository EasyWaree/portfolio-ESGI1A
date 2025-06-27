# Portfolio PHP - Projet ESGI 2024/2025

## Installation rapide (2 étapes)

### 1. Base de données
- Ouvrir phpMyAdmin : http://localhost/phpmyadmin
- Importer le fichier `database.sql`
- ✅ Créera automatiquement la base `projetb2` + utilisateur + données de test

### 2. Tester
- Ouvrir : http://localhost/nom-du-dossier/
- ✅ Le site fonctionne immédiatement

## Comptes de test

| Rôle | Utilisateur | Mot de passe |
|------|-------------|--------------|
| **Admin** | admin | password |
| **Utilisateur** | johndoe | password |
| **Utilisateur** | janedoe | password |

## Fonctionnalités implémentées

### ✅ Authentification et gestion des comptes
- Inscription avec validation complète des champs
- Connexion sécurisée avec sessions et "Se souvenir de moi"
- Gestion des rôles (Admin / Utilisateur)
- Mise à jour des informations utilisateur
- Déconnexion sécurisée avec destruction de session
- Expiration automatique après 30 minutes d'inactivité

### ✅ Gestion des compétences
- L'administrateur peut ajouter, modifier et supprimer des compétences
- Un utilisateur peut sélectionner ses compétences parmi celles proposées
- Niveau de compétence défini sur une échelle (débutant → expert)
- Catégorisation par domaines

### ✅ Gestion des projets
- Ajout, modification et suppression de projets
- Chaque projet contient : titre, description, image, lien externe
- Upload sécurisé des images avec restrictions de format et taille
- Affichage structuré des projets sur le portfolio

### ✅ Sécurité
- Protection contre XSS et injections SQL
- Utilisation de `password_hash()` pour le stockage sécurisé des mots de passe
- Gestion des erreurs utilisateur avec conservation des champs remplis
- Expiration automatique de la session après inactivité
- Tokens CSRF sur tous les formulaires

### ✅ Gestion des rôles
- Deux types d'utilisateurs : Administrateur et Utilisateur
- Sécurisation des accès pour empêcher un utilisateur d'accéder à l'interface administrateur

## Configuration technique

### Respect des obligations
- Fichier de configuration : `/config/database.php` ✅
- Base de données : `projetb2` ✅  
- Utilisateur BDD : `projetb2` / `password` ✅
- Tous les mots de passe de test : `password` ✅

### Technologies utilisées
- **Backend :** PHP 7.4+ avec POO
- **Base de données :** MySQL 5.7+ avec PDO
- **Frontend :** Bootstrap 5 + CSS3 + JavaScript
- **Sécurité :** Protection XSS, CSRF, SQL injection

## Structure du projet

```
portfolio-php/
├── config/database.php          # Configuration BDD obligatoire
├── classes/                     # Classes métier (POO)
├── includes/                    # Fonctions communes
├── uploads/projects/            # Upload sécurisé
├── index.php                    # Page d'accueil
├── auth.php                     # Authentification
├── dashboard.php                # Tableau de bord
├── portfolio.php                # Portfolio public
├── manage.php                   # Gestion portfolio
├── admin.php                    # Administration
├── database.sql                 # Script création BDD
└── README.md                    # Ce fichier
```

## Données de test incluses

- 3 utilisateurs complets (1 admin + 2 utilisateurs)
- 15+ compétences réparties en catégories
- 6+ projets d'exemple avec descriptions
- Données cohérentes et réalistes

---

**Projet réalisé dans le cadre du cursus ESGI 2024/2025**  
**Respect intégral du cahier des charges**
