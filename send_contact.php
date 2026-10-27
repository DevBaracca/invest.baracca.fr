<?php
/**
 * Baracca - Site investissement
 * Envoi du formulaire de contact vers invest@baracca.fr
 */

require_once __DIR__ . '/config.php';

// ============================================
// Limitation de débit (par IP) : 3 messages par heure
// ============================================

function contact_rate_limit_file(): string
{
    return INVEST_DATA_DIR . '/contact-rate-limit.json';
}

function contact_rate_limit_times(string $ip): array
{
    $file = contact_rate_limit_file();
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) {
        return [];
    }
    $now = time();
    $times = array_filter($data[$ip] ?? [], function ($t) use ($now) {
        return (int) $t > $now - 3600;
    });
    return array_values($times);
}

function contact_rate_limit_record(string $ip): void
{
    if (!is_dir(INVEST_DATA_DIR)) {
        mkdir(INVEST_DATA_DIR, 0775, true);
    }
    $file = contact_rate_limit_file();
    $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
    if (!is_array($data)) {
        $data = [];
    }
    $times = contact_rate_limit_times($ip);
    $times[] = time();
    $data[$ip] = array_slice($times, -3);
    file_put_contents($file, json_encode($data), LOCK_EX);
}

// ============================================
// Traitement du formulaire
// ============================================

$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

// 3 messages par heure et par adresse IP
if (count(contact_rate_limit_times($ip)) >= 3) {
    header('Location: contact.php?contact=rate');
    exit();
}

// Récupération des champs
$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
// Nettoyage du sujet (protection contre l'injection d'en-têtes)
$subject = mb_substr(trim(str_replace(["\r", "\n", "\t"], ' ', (string) ($_POST['subject'] ?? ''))), 0, 50);
$message = trim((string) ($_POST['message'] ?? ''));

// Sujets proposés par le formulaire
$subjectLabels = [
    'investissement' => 'Investissement',
    'projet' => 'Question sur le projet',
    'module' => 'Demande d\'information sur un module',
    'autre' => 'Autre',
];
$subjectLabel = $subjectLabels[$subject] ?? 'Autre';

// Validation (un email validé protège aussi le Reply-To contre les injections)
if ($name === '' || mb_strlen($name) > 100
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $message === '' || mb_strlen($message) > 5000
) {
    header('Location: contact.php?contact=error');
    exit();
}

// Envoi de l'email
$to = 'invest@baracca.fr';
$mailSubject = 'Nouveau message de contact - ' . $subject;
$mailMessage = "Nouveau message envoyé depuis le formulaire de contact du site Baracca :\n\n";
$mailMessage .= 'Nom : ' . $name . "\n";
$mailMessage .= 'Email : ' . $email . "\n";
if ($phone !== '') {
    $mailMessage .= 'Téléphone : ' . $phone . "\n";
}
$mailMessage .= 'Sujet : ' . $subjectLabel . "\n\n";
$mailMessage .= "Message :\n" . $message . "\n";

$headers = "From: no-reply@baracca.fr\r\n";
$headers .= 'Reply-To: ' . $email . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$mailSent = mail($to, $mailSubject, $mailMessage, $headers);

if ($mailSent) {
    contact_rate_limit_record($ip);
    header('Location: contact.php?contact=success');
} else {
    header('Location: contact.php?contact=error');
}
exit();
