<?php
// actions/cancel_request.php
// Endpoint public — annulation via lien email (token 2h)

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/EmailService.php';

$token = trim($_GET['token'] ?? '');

// ── Token vide ────────────────────────────────────────────────────────────
if (empty($token)) {
    header('Location: ../index.php?cancel_error=invalid');
    exit;
}

// ── Vérifier le token en base ─────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT ct.*, u.id AS uid, u.name, u.email
    FROM cancellation_tokens ct
    INNER JOIN users u ON u.id = ct.user_id
    WHERE ct.token = ?
    LIMIT 1
");
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// ── Token inexistant ──────────────────────────────────────────────────────
if (!$row) {
    header('Location: ../index.php?cancel_error=invalid');
    exit;
}

// ── Token expiré ──────────────────────────────────────────────────────────
if (strtotime($row['expires_at']) < time()) {
    header('Location: ../index.php?cancel_error=expired');
    exit;
}

// ── Token déjà utilisé ────────────────────────────────────────────────────
if ((int)$row['is_used'] === 1) {
    header('Location: ../index.php?cancel_error=used');
    exit;
}

// ── Identifier la table selon le type ────────────────────────────────────
if ($row['object_type'] === 'NEW_CLAIM') {
    $table      = 'reports';
    $emailType  = 'CANCEL_SERVICE';
} elseif ($row['object_type'] === 'NEW_SERVICE') {
    $table      = 'service_requests';
    $emailType  = 'CANCEL_SERVICE';
} else {
    header('Location: ../index.php?cancel_error=invalid');
    exit;
}

// ── Vérifier que l'objet existe et est encore annulable ──────────────────
$objStmt = $pdo->prepare("SELECT * FROM $table WHERE id = ? AND user_id = ? LIMIT 1");
$objStmt->execute([$row['object_id'], $row['uid']]);
$object = $objStmt->fetch(PDO::FETCH_ASSOC);

if (!$object) {
    header('Location: ../index.php?cancel_error=invalid');
    exit;
}

if ($object['status'] === 'cancelled') {
    header('Location: ../index.php?cancel_error=used');
    exit;
}

// ── Annuler l'objet ───────────────────────────────────────────────────────
$pdo->prepare("
    UPDATE $table
    SET status = 'cancelled', updated_at = NOW()
    WHERE id = ?
")->execute([$row['object_id']]);

// ── Invalider le token ────────────────────────────────────────────────────
$pdo->prepare("
    UPDATE cancellation_tokens
    SET is_used = 1
    WHERE token = ?
")->execute([$token]);

// ── Envoyer email de confirmation ─────────────────────────────────────────
$user = [
    'id'    => $row['uid'],
    'name'  => $row['name'],
    'email' => $row['email'],
];

(new EmailService())->send($emailType, $user, $object);

// ── Rediriger avec succès ─────────────────────────────────────────────────
header('Location: ../index.php?cancel_success=1');
exit;
?>