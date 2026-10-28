<?php
/**
 * Baracca - Site investissement
 * Configuration générale
 */

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Back-office : identifiants de connexion administrateur.
// L'espace investisseurs, lui, fonctionne avec des comptes individuels
// (email + mot de passe) gérés depuis le back-office (data/users.json).
define('INVEST_ADMIN_USERNAME', 'admin');
// Hash bcrypt du mot de passe administrateur — à changer après la première connexion.
// Pour définir un nouveau mot de passe, générez son hash avec :
//   php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);"
// puis remplacez la valeur ci-dessous.
define('INVEST_ADMIN_PASSWORD_HASH', '$2y$12$ggZv1OhTcaaIzsPnu3tIP.CZy46c8uWl/jXwv.VPvElnAHrIiMOTa');

// Chemins
define('INVEST_DATA_DIR', __DIR__ . '/data');

// Limitation de débit des connexions à l'espace investisseurs (par adresse IP)
define('INVEST_LOGIN_MAX_ATTEMPTS', 5);     // échecs autorisés...
define('INVEST_LOGIN_WINDOW_SECONDS', 900);  // ...dans une fenêtre de 15 minutes...
define('INVEST_LOGIN_BLOCK_SECONDS', 900);   // ...avant un blocage de 15 minutes
