<?php
/**
 * Baracca - Back-office
 * Connexion administrateur + tableau de bord
 */

require_once __DIR__ . '/../includes/admin-auth.php';

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    if (invest_admin_rate_limit_is_blocked()) {
        $minutes = (int) ceil(invest_admin_rate_limit_blocked_seconds() / 60);
        $loginError = 'Trop de tentatives de connexion. Réessayez dans ' . $minutes . ' min.';
    } elseif (invest_admin_login((string) $_POST['username'], (string) $_POST['password'])) {
        invest_admin_rate_limit_success();
        header('Location: index.php');
        exit;
    } else {
        invest_admin_rate_limit_failure();
        $loginError = 'Identifiants incorrects.';
    }
}

if (!invest_admin_is_logged_in()) {
    $pageTitle = 'Connexion';
    include __DIR__ . '/../includes/admin-head.php';
    ?>
    <div class="admin-card admin-login">
      <h1>Administration Baracca</h1>
      <p class="admin-hint">Back-office du site invest.baracca.fr</p>
      <?php if ($loginError !== ''): ?>
        <p class="admin-message admin-message-error"><?= htmlspecialchars($loginError) ?></p>
      <?php endif; ?>
      <form method="post" action="index.php">
        <div class="admin-form-row">
          <label for="username">Identifiant</label>
          <input type="text" id="username" name="username" required autofocus autocomplete="username">
        </div>
        <div class="admin-form-row">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-cta">Se connecter</button>
      </form>
    </div>
    <?php
    include __DIR__ . '/../includes/admin-footer.php';
    exit;
}

invest_admin_require_auth();
$pageTitle = 'Tableau de bord';
$adminPage = 'index';
include __DIR__ . '/../includes/admin-head.php';
?>
    <div class="admin-card">
      <h1>Tableau de bord</h1>
      <p>Gérez les contenus du site depuis cette interface.</p>
    </div>
    <div class="admin-grid">
      <div class="admin-card">
        <h2>Chiffres clés</h2>
        <p>Modifiez les valeurs affichées sur le site : euros collectés, objectif, investisseurs, modules déployés, modules en production, demandes en attente.</p>
        <a href="stats.php" class="btn btn-secondary">Gérer les chiffres</a>
      </div>
      <div class="admin-card">
        <h2>Documents</h2>
        <p>Uploadez et gérez les documents de l'espace investisseurs (PDF, Excel, Word, images).</p>
        <a href="documents.php" class="btn btn-secondary">Gérer les documents</a>
      </div>
      <div class="admin-card">
        <h2>Accès investisseurs</h2>
        <p>Créez et gérez les comptes ayant accès à l'espace investisseurs (email + mot de passe).</p>
        <a href="investisseurs.php" class="btn btn-secondary">Gérer les accès</a>
      </div>
    </div>
<?php
include __DIR__ . '/../includes/admin-footer.php';
