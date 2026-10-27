<?php
/**
 * Baracca - Site investissement
 * Espace investisseurs : déconnexion
 */
require_once __DIR__ . '/includes/auth.php';
invest_logout();
header('Location: investisseurs.php');
exit;
