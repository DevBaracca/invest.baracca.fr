<?php
/**
 * Baracca - Back-office
 * Gestion des comptes ayant accès à l'espace investisseurs
 */

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/users.php';

invest_admin_require_auth();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!invest_admin_csrf_verify($_POST['csrf'] ?? null)) {
        $message = ['error', 'Jeton de sécurité invalide.'];
    } elseif (isset($_POST['ajouter'])) {
        $email = (string) ($_POST['email'] ?? '');
        $name = (string) ($_POST['name'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = ['error', 'Adresse email invalide.'];
        } elseif (strlen($password) < 8) {
            $message = ['error', 'Le mot de passe doit contenir au moins 8 caractères.'];
        } elseif (!invest_add_user($email, $name, $password)) {
            $message = ['error', 'Cet email est déjà utilisé.'];
        } else {
            $message = ['success', 'Accès créé pour ' . $email . '. Communiquez-lui son mot de passe.'];
        }
    } elseif (isset($_POST['supprimer'])) {
        $message = invest_delete_user((string) ($_POST['id'] ?? ''))
            ? ['success', 'Accès supprimé.']
            : ['error', 'Utilisateur introuvable.'];
    } elseif (isset($_POST['reinit'])) {
        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 8) {
            $message = ['error', 'Le mot de passe doit contenir au moins 8 caractères.'];
        } else {
            $message = invest_set_user_password((string) ($_POST['id'] ?? ''), $password)
                ? ['success', 'Mot de passe réinitialisé.']
                : ['error', 'Utilisateur introuvable.'];
        }
    }
}

$users = invest_load_users();
$pageTitle = 'Accès investisseurs';
$adminPage = 'investisseurs';
include __DIR__ . '/../includes/admin-head.php';
?>
    <div class="admin-card">
      <h1>Accès investisseurs</h1>
      <p>Chaque personne autorisée se connecte à l'espace investisseurs avec son propre email et son mot de passe.</p>
      <?php if ($message !== ''): ?>
        <p class="admin-message admin-message-<?= $message[0] ?>"><?= htmlspecialchars($message[1]) ?></p>
      <?php endif; ?>
      <form method="post" action="investisseurs.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(invest_admin_csrf_token()) ?>">
        <div class="admin-form-inline">
          <div class="admin-form-row">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autocomplete="off">
          </div>
          <div class="admin-form-row">
            <label for="name">Nom (facultatif)</label>
            <input type="text" id="name" name="name" autocomplete="off">
          </div>
          <div class="admin-form-row">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
            <p class="admin-hint">8 caractères minimum.</p>
          </div>
          <div>
            <button type="submit" name="ajouter" value="1" class="btn btn-cta">Créer l'accès</button>
          </div>
        </div>
      </form>
    </div>
    <div class="admin-card">
      <h2>Personnes autorisées (<?= count($users) ?>)</h2>
      <?php if ($users === []): ?>
        <p class="admin-hint">Aucun accès créé pour le moment.</p>
      <?php else: ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Email</th>
              <th>Nom</th>
              <th>Créé le</th>
              <th>Nouveau mot de passe</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?= htmlspecialchars((string) ($user['email'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($user['name'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($user['created'] ?? '')) ?></td>
                <td>
                  <form method="post" action="investisseurs.php" class="admin-form-inline">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(invest_admin_csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($user['id'] ?? '')) ?>">
                    <input type="password" name="password" placeholder="Nouveau mot de passe" minlength="8" autocomplete="new-password">
                    <button type="submit" name="reinit" value="1" class="btn btn-secondary">Réinitialiser</button>
                  </form>
                </td>
                <td>
                  <form method="post" action="investisseurs.php">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(invest_admin_csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($user['id'] ?? '')) ?>">
                    <button type="submit" name="supprimer" value="1" class="btn-danger">Supprimer</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
<?php
include __DIR__ . '/../includes/admin-footer.php';
