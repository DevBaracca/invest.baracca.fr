<?php
// Attendu avant inclusion : $pageTitle (optionnel), $adminPage (section active)
$adminPage = $adminPage ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= isset($pageTitle) && $pageTitle !== '' ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Administration Baracca</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
  <header class="admin-header">
    <div class="admin-header-inner">
      <a href="../index.php" class="admin-logo">Baracca <span>— Administration</span></a>
      <nav class="admin-nav">
        <a href="index.php"<?= $adminPage === 'index' ? ' class="active"' : '' ?>>Tableau de bord</a>
        <a href="stats.php"<?= $adminPage === 'stats' ? ' class="active"' : '' ?>>Chiffres clés</a>
        <a href="documents.php"<?= $adminPage === 'documents' ? ' class="active"' : '' ?>>Documents</a>
        <a href="investisseurs.php"<?= $adminPage === 'investisseurs' ? ' class="active"' : '' ?>>Accès investisseurs</a>
      </nav>
      <a href="logout.php" class="admin-logout">Se déconnecter</a>
    </div>
  </header>
  <main class="admin-main">
