<?php
/**
 * Baracca - Back-office
 * Déconnexion administrateur
 */

require_once __DIR__ . '/../includes/admin-auth.php';

invest_admin_logout();
header('Location: index.php');
exit;
