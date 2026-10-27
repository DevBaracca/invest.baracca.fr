<?php
/**
 * Baracca - Back-office
 * Upload et gestion des documents de l'espace investisseurs
 */

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/documents.php';

invest_admin_require_auth();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!invest_admin_csrf_verify($_POST['csrf'] ?? null)) {
        $message = ['error', 'Jeton de sécurité invalide.'];
    } elseif (isset($_POST['upload'])) {
        $erreur = invest_upload_document($_FILES['document'] ?? []);
        $message = $erreur === '' ? ['success', 'Document ajouté.'] : ['error', $erreur];
    } elseif (isset($_POST['supprimer'])) {
        $fichier = (string) ($_POST['fichier'] ?? '');
        $message = invest_supprime_document($fichier)
            ? ['success', 'Document supprimé.']
            : ['error', 'Impossible de supprimer ce document.'];
    }
}

$documents = invest_liste_documents();
$pageTitle = 'Documents';
$adminPage = 'documents';
include __DIR__ . '/../includes/admin-head.php';
?>
    <div class="admin-card">
      <h1>Documents de l'espace investisseurs</h1>
      <p>Ces documents apparaissent dans l'espace investisseurs et sont téléchargeables par les personnes connectées.</p>
      <?php if ($message !== ''): ?>
        <p class="admin-message admin-message-<?= $message[0] ?>"><?= htmlspecialchars($message[1]) ?></p>
      <?php endif; ?>
      <form method="post" action="documents.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(invest_admin_csrf_token()) ?>">
        <input type="hidden" name="upload" value="1">
        <div class="admin-form-inline">
          <div class="admin-form-row">
            <label for="document">Nouveau document</label>
            <input type="file" id="document" name="document" required
                   accept=".pdf,.xls,.xlsx,.doc,.docx,.csv,.png,.jpg,.jpeg,.gif,.webp,.zip">
            <p class="admin-hint">PDF, Excel, Word, CSV, images ou ZIP — 10 Mo maximum.</p>
          </div>
          <div>
            <button type="submit" class="btn btn-cta">Uploader</button>
          </div>
        </div>
      </form>
    </div>
    <div class="admin-card">
      <h2>Documents en ligne (<?= count($documents) ?>)</h2>
      <?php if ($documents === []): ?>
        <p class="admin-hint">Aucun document pour le moment.</p>
      <?php else: ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Fichier</th>
              <th>Taille</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documents as $doc): ?>
              <tr>
                <td><?= htmlspecialchars($doc['fichier']) ?></td>
                <td><?= htmlspecialchars(invest_format_taille($doc['taille'])) ?></td>
                <td>
                  <form method="post" action="documents.php">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(invest_admin_csrf_token()) ?>">
                    <input type="hidden" name="fichier" value="<?= htmlspecialchars($doc['fichier']) ?>">
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
