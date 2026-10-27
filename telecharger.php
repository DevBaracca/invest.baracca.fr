<?php
/**
 * Baracca - Site investissement
 * Espace investisseurs : téléchargement des documents réservés (authentification requise).
 * Les PDF ne sont plus servis directement : assets/documents/ est interdit en accès web (.htaccess).
 */

require_once __DIR__ . '/includes/auth.php';

if (!invest_is_logged_in()) {
    header('Location: investisseurs.php');
    exit;
}

// Liste blanche des documents téléchargeables (aucun chemin relatif n'est possible)
$documentsAutorises = [
    'statuts-baracca.pdf',
    'business-plan-baracca.pdf',
    'contrat-type-baracca.pdf',
    'reglement-interieur-baracca.pdf',
];

$filename = basename((string) ($_GET['f'] ?? ''));
$path = __DIR__ . '/assets/documents/' . $filename;

if (!in_array($filename, $documentsAutorises, true) || !is_file($path)) {
    http_response_code(404);
    exit('Document introuvable.');
}

// Envoi du fichier en pièce jointe
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . (string) filesize($path));
header('Cache-Control: private, no-store');
readfile($path);
exit;
