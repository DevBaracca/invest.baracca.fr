<?php
/**
 * Baracca - Site investissement
 * Configuration générale
 */

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Espace investisseurs : hash bcrypt du mot de passe d'accès.
// Le mot de passe n'est jamais stocké en clair, seule son empreinte l'est.
// Pour définir un nouveau mot de passe, générez son hash avec :
//   php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);"
// puis remplacez la valeur ci-dessous.
define('INVEST_PASSWORD_HASH', '$2y$12$5UMPfbUdbr.0hyPKwcj6z.y04PzvptT5d8qthg1oEWhhewsqyt9TO');

// Chemins
define('INVEST_DATA_DIR', __DIR__ . '/data');

// Limitation de débit des connexions à l'espace investisseurs (par adresse IP)
define('INVEST_LOGIN_MAX_ATTEMPTS', 5);     // échecs autorisés...
define('INVEST_LOGIN_WINDOW_SECONDS', 900);  // ...dans une fenêtre de 15 minutes...
define('INVEST_LOGIN_BLOCK_SECONDS', 900);   // ...avant un blocage de 15 minutes
