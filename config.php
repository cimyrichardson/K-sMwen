<?php
// Configuration de l'application
define('APP_NAME', 'KèsMwen');
define('APP_VERSION', '1.0.0');
define('APP_LANG', 'fr'); // Par défaut

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'kesmwen_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration SMTP pour les emails
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'contact@example.com');
define('SMTP_PASS', 'password');
define('SMTP_FROM', 'no-reply@kesmwen.ht');

// Sécurité
define('PEPPER', 'votre_pepper_unique_ici');

// Chemins
define('BASE_URL', 'http://localhost/kesmwen');
define('UPLOADS_DIR', __DIR__ . '/public/assets/uploads/');

// Désactiver l'affichage des erreurs en production
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Session
session_start();
?>