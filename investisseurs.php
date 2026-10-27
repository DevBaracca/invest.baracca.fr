<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/blog.php';
require_once __DIR__ . '/includes/stats.php';
require_once __DIR__ . '/includes/documents.php';

$stats = invest_stats();

// ============================================
// Connexion
// ============================================
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    if (invest_rate_limit_is_blocked()) {
        $minutes = (int) ceil(invest_rate_limit_blocked_seconds() / 60);
        $loginError = 'Trop de tentatives de connexion. Réessayez dans ' . $minutes . ' min.';
    } elseif (invest_login((string) $_POST['email'], (string) $_POST['password'])) {
        invest_rate_limit_success();
        header('Location: investisseurs.php');
        exit;
    } else {
        invest_rate_limit_failure();
        $loginError = 'Email ou mot de passe incorrect.';
    }
}

$pageTitle = 'Espace Investisseurs - Baracca';
$pageDescription = 'Espace réservé aux investisseurs de Baracca. Accédez aux documents exclusifs et au suivi financier.';
include 'includes/head.html';
?>

<?php if (!invest_is_logged_in()): ?>
  <!-- Formulaire de connexion : sans authentification, le contenu réservé n'est pas envoyé au navigateur -->
  <div class="password-prompt">
    <div class="password-container">
      <h2>Espace Investisseurs</h2>
      <p>Cet espace est réservé aux investisseurs de Baracca. Veuillez entrer votre email et votre mot de passe pour accéder aux documents.</p>
      <?php if ($loginError !== ''): ?>
        <p style="color: #b3402a;"><em><?= htmlspecialchars($loginError) ?></em></p>
      <?php endif; ?>
      <form method="post" action="investisseurs.php">
        <input type="email" name="email" placeholder="Votre email" required autofocus>
        <input type="password" name="password" placeholder="Votre mot de passe" required>
        <button type="submit" class="btn-cta">Accéder</button>
      </form>
      <p style="margin-top: var(--spacing-md); font-size: var(--font-size-small); color: var(--gray);">
        <em>Ces identifiants vous ont été communiqués lors de votre investissement.</em>
      </p>
    </div>
  </div>
<?php else: ?>
  <!-- Contenu réservé (envoyé uniquement après authentification) -->
  <div id="investor-content">
    <!-- Hero Section -->
    <section class="hero" style="min-height: 50vh;">
      <div class="container">
        <div class="hero-content fade-in">
          <?php $currentUser = invest_current_user(); ?>
          <h1>Bienvenue<?= $currentUser !== null && $currentUser['name'] !== '' ? ', ' . htmlspecialchars($currentUser['name']) : '' ?> dans l'espace investisseurs</h1>
          <p class="hero-subtitle">Retrouvez ici tous les documents et informations réservés aux membres de la coopérative Baracca.</p>
          <p style="margin-top: var(--spacing-sm); font-size: var(--font-size-small);"><a href="deconnexion.php">Se déconnecter</a></p>
        </div>
      </div>
    </section>

    <!-- Suivi Financier Section -->
    <section class="section">
      <div class="container">
        <h2 class="section-title fade-in">Suivi financier</h2>
        <div class="fade-in" style="max-width: 800px; margin: 0 auto;">
          <p class="section-subtitle">Suivez en temps réel l'avancement de la levée de fonds et l'utilisation des investissements.</p>
          
          <div class="stats-row" style="margin: var(--spacing-xl) 0;">
            <div class="stat-item">
              <span class="stat-number" data-target="<?= (int) $stats['euros_collectes'] ?>">0</span>
              <span class="stat-label">Euros collectés</span>
            </div>
            <div class="stat-item">
              <span class="stat-number" data-target="<?= (int) $stats['objectif'] ?>"><?= invest_format_montant((int) $stats['objectif']) ?></span>
              <span class="stat-label">Objectif</span>
            </div>
            <div class="stat-item">
              <span class="stat-number" data-target="<?= (int) $stats['investisseurs'] ?>">0</span>
              <span class="stat-label">Investisseurs</span>
            </div>
            <div class="stat-item">
              <span class="stat-number" data-target="<?= (int) $stats['modules_production'] ?>">0</span>
              <span class="stat-label">Modules en production</span>
            </div>
          </div>
          
          <!-- Barre de progression -->
          <div class="progress-block" style="margin: var(--spacing-xl) 0;">
            <div class="progress-labels">
              <span><strong id="investor-amount-raised"><?= invest_format_montant((int) $stats['euros_collectes']) ?></strong> € collectés</span>
              <span><strong><?= invest_format_montant((int) $stats['objectif']) ?></strong> € (objectif)</span>
            </div>
            <div class="progress-track">
              <div id="investor-progress-bar" class="progress-fill" style="width: <?= invest_progress_percent($stats) ?>%;"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Documents Section -->
    <section class="section section-light">
      <div class="container">
        <h2 class="section-title fade-in">Documents</h2>
        <p class="section-subtitle fade-in">Téléchargez les documents officiels de la coopérative Baracca.</p>

        <?php $documents = invest_liste_documents(); ?>
        <?php if ($documents === []): ?>
        <p class="fade-in" style="text-align: center; color: var(--gray);"><em>Aucun document disponible pour le moment.</em></p>
        <?php else: ?>
        <div class="feature-grid fade-in">
          <?php foreach ($documents as $doc): ?>
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
              </svg>
            </div>
            <h3><?= htmlspecialchars($doc['fichier']) ?></h3>
            <p><?= strtoupper(htmlspecialchars($doc['extension'])) ?> — <?= htmlspecialchars(invest_format_taille($doc['taille'])) ?></p>
            <a href="telecharger.php?f=<?= urlencode($doc['fichier']) ?>" class="btn btn-secondary" style="margin-top: var(--spacing-sm);">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: var(--spacing-xs);">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7,10 12,15 17,10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Télécharger
            </a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Actualités Section -->
    <section class="section">
      <div class="container">
        <h2 class="section-title fade-in">Actualités</h2>
        <p class="section-subtitle fade-in">Découvrez les dernières nouvelles de Baracca, publiées sur <a href="<?= invest_blog_url('/index.php') ?>">notre blog</a>.</p>
        
        <?php $actus = invest_blog_articles(3); ?>
        <?php if (!empty($actus)): ?>
        <div class="feature-grid fade-in">
          <?php foreach ($actus as $actu): ?>
          <div class="feature-card">
            <p style="font-size: var(--font-size-small); color: var(--gray); margin-bottom: var(--spacing-xs);">
              <?= invest_format_date((string) ($actu['date'] ?? '')) ?>
            </p>
            <h3><?= htmlspecialchars((string) ($actu['titre'] ?? '')) ?></h3>
            <p><?= htmlspecialchars((string) ($actu['extrait'] ?? '')) ?></p>
            <a href="<?= invest_blog_url('/article.php?slug=' . urlencode((string) ($actu['slug'] ?? ''))) ?>" class="btn btn-secondary" style="margin-top: var(--spacing-sm);">Lire l'article</a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="fade-in" style="text-align: center; color: var(--gray);"><em>Les actualités du blog sont momentanément indisponibles.</em></p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Prochaines Étapes Section -->
    <section class="section section-light">
      <div class="container">
        <h2 class="section-title fade-in">Prochaines étapes</h2>
        <div class="fade-in" style="max-width: 800px; margin: 0 auto;">
          <p class="section-subtitle">Voici les grandes étapes à venir pour Baracca.</p>
          
          <div style="margin: var(--spacing-xl) 0;">
            <div class="timeline-item">
              <div class="timeline-marker">06/24</div>
              <div class="timeline-content">
                <h3>Finalisation de la levée de fonds</h3>
                <p>Nous prévoyons d'atteindre notre objectif de 300 000 € d'ici la fin du mois de juin.</p>
              </div>
            </div>
            
            <div class="timeline-item">
              <div class="timeline-marker">09/24</div>
              <div class="timeline-content">
                <h3>Lancement de la production</h3>
                <p>Dès que la levée de fonds sera terminée, nous lancerons la production en série de nos modules.</p>
              </div>
            </div>
            
            <div class="timeline-item">
              <div class="timeline-marker">12/24</div>
              <div class="timeline-content">
                <h3>Premières livraisons</h3>
                <p>Les premiers modules seront livrés à nos clients avant la fin de l'année.</p>
              </div>
            </div>
            
            <div class="timeline-item">
              <div class="timeline-marker">2025</div>
              <div class="timeline-content">
                <h3>Expansion nationale</h3>
                <p>Nous prévoyons d'étendre notre activité à l'ensemble du territoire français.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section">
      <div class="container">
        <div class="fade-in" style="text-align: center; max-width: 700px; margin: 0 auto;">
          <h2>Besoin d'aide ?</h2>
          <p style="margin-bottom: var(--spacing-xl);">
            Pour toute question sur votre investissement ou sur les documents, n'hésitez pas à nous contacter.
          </p>
          <a href="contact.php" class="btn-cta">Nous contacter</a>
        </div>
      </div>
    </section>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
