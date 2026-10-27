<?php
/**
 * Baracca - Site investissement
 * Documents téléchargeables de l'espace investisseurs,
 * uploadés depuis le back-office.
 */

function invest_documents_dir(): string
{
    return __DIR__ . '/../assets/documents';
}

// Extensions autorisées : pdf, excel, word, images, csv, zip
function invest_documents_extensions(): array
{
    return ['pdf', 'xls', 'xlsx', 'doc', 'docx', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'zip'];
}

function invest_document_extension(string $filename): string
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function invest_liste_documents(): array
{
    $dir = invest_documents_dir();
    $documents = [];
    foreach (glob($dir . '/*') ?: [] as $path) {
        if (!is_file($path)) {
            continue;
        }
        $filename = basename($path);
        if (!in_array(invest_document_extension($filename), invest_documents_extensions(), true)) {
            continue;
        }
        $documents[] = [
            'fichier' => $filename,
            'taille' => (int) filesize($path),
            'extension' => invest_document_extension($filename),
        ];
    }
    usort($documents, function ($a, $b) {
        return strcmp($a['fichier'], $b['fichier']);
    });
    return $documents;
}

// Type MIME selon l'extension
function invest_document_content_type(string $filename): string
{
    $types = [
        'pdf' => 'application/pdf',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'csv' => 'text/csv',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'zip' => 'application/zip',
    ];
    return $types[invest_document_extension($filename)] ?? 'application/octet-stream';
}

// Taille lisible : Ko / Mo
function invest_format_taille(int $octets): string
{
    if ($octets >= 1048576) {
        return round($octets / 1048576, 1) . ' Mo';
    }
    return max(1, (int) round($octets / 1024)) . ' Ko';
}

// Upload d'un document (back-office) — retourne '' si OK, sinon le message d'erreur
function invest_upload_document(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return 'Aucun fichier sélectionné.';
    }
    if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
        return 'Erreur lors de l\'envoi du fichier.';
    }
    if (($file['size'] ?? 0) > 10 * 1048576) {
        return 'Le fichier dépasse la taille maximale de 10 Mo.';
    }
    $filename = basename((string) $file['name']);
    $extension = invest_document_extension($filename);
    if (!in_array($extension, invest_documents_extensions(), true)) {
        return 'Extension non autorisée (autorisées : ' . implode(', ', invest_documents_extensions()) . ').';
    }
    // Nom de fichier nettoyé (lettres, chiffres, tirets)
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $base = strtolower((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $base));
    $base = trim($base, '-');
    if ($base === '') {
        $base = 'document';
    }
    $filename = $base . '.' . $extension;
    $dir = invest_documents_dir();
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    // Unicité du nom si le fichier existe déjà
    $path = $dir . '/' . $filename;
    $i = 1;
    while (is_file($path)) {
        $path = $dir . '/' . $base . '-' . $i . '.' . $extension;
        $i++;
    }
    if (!move_uploaded_file((string) $file['tmp_name'], $path)) {
        return 'Impossible d\'enregistrer le fichier.';
    }
    return '';
}

function invest_supprime_document(string $filename): bool
{
    $filename = basename($filename);
    if (!in_array(invest_document_extension($filename), invest_documents_extensions(), true)) {
        return false;
    }
    $path = invest_documents_dir() . '/' . $filename;
    return is_file($path) && unlink($path);
}
