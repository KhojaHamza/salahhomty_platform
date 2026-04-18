<?php
// actions/submit_comment.php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// ── Auth ──
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour commenter.']);
    exit;
}

// ── Validate ──
$report_id = (int)($_POST['report_id'] ?? 0);
$content   = trim($_POST['content']   ?? '');

if (!$report_id || mb_strlen($content) < 2) {
    echo json_encode(['success' => false, 'error' => 'Commentaire trop court.']);
    exit;
}

if (mb_strlen($content) > 1000) {
    echo json_encode(['success' => false, 'error' => 'Commentaire trop long (max 1000 caractères).']);
    exit;
}

// ── Check report exists ──
$check = $pdo->prepare("SELECT id FROM reports WHERE id = ?");
$check->execute([$report_id]);
if (!$check->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Rapport introuvable.']);
    exit;
}

// ── Insert ──
$stmt = $pdo->prepare("INSERT INTO comments (report_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$report_id, $_SESSION['user_id'], $content]);
$comment_id = $pdo->lastInsertId();

// ── Return comment with user name for live display ──
$row = $pdo->prepare("
    SELECT c.id, c.content, c.created_at, u.name AS user_name,
        u.name AS user_name, u.role
    FROM comments c
    LEFT JOIN users u ON u.id = c.user_id
    WHERE c.id = ?
");
$row->execute([$comment_id]);
$comment = $row->fetch(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'comment' => $comment]);