<?php
/**
 * Baracca - Back-office
 * Modification des chiffres clés affichés sur le site
 */

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/stats.php';

invest_admin_require_auth();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!invest_admin_csrf_verify($_POST['csrf'] ?? null)) {
        $message = ['error', 'Jeton de sécurité invalide.'];
    } else {
        $stats = [
            'euros_collectes' => (int) ($_POST['euros_collectes'] ?? 0),
            'objectif' => (int) ($_POST['objectif'] ?? 0),
            'investisseurs' => (int) ($_POST['investisseurs'] ?? 0),
            'modules_production' => (int) ($_POST['modules_production'] ?? 0),
            'modules_deployes' => (int) ($_POST['modules_deployes'] ?? 0),
            'demandes_attente' => (int) ($_POST['demandes_attente'] ?? 0),
        ];
        if ($stats['objectif'] <= 0) {
            $message = ['error', 'L\'objectif doit être supérieur à 0.'];
        } elseif (invest_save_stats($stats)) {
            $message = ['success', 'Les chiffres clés ont été enregistrés.'];
        } else {
            $message = ['error', 'Impossible d\'enregistrer les chiffres.'];
        }
    }
}

$stats = invest_stats();
$pageTitle = 'Chiffres clés';
$adminPage = 'stats';
include __DIR__ . '/../includes/admin-head.php';
?>
    <div class="admin-card">
      <h1>Chiffres clés</h1>
      <p>Ces valeurs sont affichées sur l'accueil, la page « Investir » et l'espace investisseurs.</p>
      <?php if ($message !== ''): ?>
        <p class="admin-message admin-message-<?= $message[0] ?>"><?= htmlspecialchars($message[1]) ?></p>
      <?php endif; ?>
      <form method="post" action="stats.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(invest_admin_csrf_token()) ?>">
        <div class="admin-grid">
          <div class="admin-form-row">
            <label for="euros_collectes">Euros collectés (€)</label>
            <input type="number" min="0" step="1" id="euros_collectes" name="euros_collectes" value="<?= (int) $stats['euros_collectes'] ?>" required>
          </div>
          <div class="admin-form-row">
            <label for="objectif">Objectif de levée (€)</label>
            <input type="number" min="1" step="1" id="objectif" name="objectif" value="<?= (int) $stats['objectif'] ?>" required>
          </div>
          <div class="admin-form-row">
            <label for="investisseurs">Investisseurs</label>
            <input type="number" min="0" step="1" id="investisseurs" name="investisseurs" value="<?= (int) $stats['investisseurs'] ?>" required>
          </div>
          <div class="admin-form-row">
            <label for="modules_deployes">Modules déployés</label>
            <input type="number" min="0" step="1" id="modules_deployes" name="modules_deployes" value="<?= (int) $stats['modules_deployes'] ?>" required>
          </div>
          <div class="admin-form-row">
            <label for="modules_production">Modules en production</label>
            <input type="number" min="0" step="1" id="modules_production" name="modules_production" value="<?= (int) $stats['modules_production'] ?>" required>
          </div>
          <div class="admin-form-row">
            <label for="demandes_attente">Demandes en attente</label>
            <input type="number" min="0" step="1" id="demandes_attente" name="demandes_attente" value="<?= (int) $stats['demandes_attente'] ?>" required>
          </div>
        </div>
        <button type="submit" class="btn btn-cta">Enregistrer</button>
      </form>
    </div>
<?php
include __DIR__ . '/../includes/admin-footer.php';
