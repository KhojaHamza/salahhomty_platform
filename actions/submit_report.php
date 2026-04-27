<?php
session_start();
require_once '../config/database.php';

// ── Auth ──
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/signin.php?error=login_required');
    exit;
}

// ── Validate ──
$title = trim($_POST['title']    ?? '');
$desc  = trim($_POST['description'] ?? '');


$lat      = $_POST['latitude']  ?? '';
$lng      = $_POST['longitude'] ?? '';
$location = trim($_POST['location'] ?? '');
if (!$title) {
    header('Location: ../?error=Titre+obligatoire.');
    exit;
}

// ── Upload image ──
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (in_array($_FILES['image']['type'], $allowed) && $_FILES['image']['size'] <= 10 * 1024 * 1024) {
        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/' . uniqid('img_') . '.' . $ext;
        $dest     = __DIR__ . '/../' . $filename;
        if (!is_dir(__DIR__ . '/../uploads/')) {
            mkdir(__DIR__ . '/../uploads/', 0755, true);
        }
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $imagePath = $filename;
        }
    }
}

// ── Insert ──
try {
$stmt = $pdo->prepare("
    INSERT INTO reports (user_id, title, description, location, latitude, longitude, image, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'Open')
");
$stmt->execute([
    $_SESSION['user_id'],
    $title,
    $desc     ?: null,
    $location ?: null,
    $lat      ?: null,
    $lng      ?: null,
    $imagePath,
]);
    // ── BF8 : Envoyer email de notification ──────────────────── AJOUTER ───
    $reportId = (int)$pdo->lastInsertId();

    $userStmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    $report = [
        'id'    => $reportId,
        'title' => $title,
    ];

    require_once __DIR__ . '/../includes/EmailService.php';
    (new EmailService())->send('NEW_CLAIM', $user, $report);
    // ─────────────────────────────────────────────────────────────────────────


    header('Location: ../?success=1');
    exit;

} catch (PDOException $e) {
    // Affiche l'erreur exacte pour déboguer
    header('Location: ../?error=' . urlencode($e->getMessage()));
    exit;
}