<?php
// auth/login.php
session_start();

// ── Déjà connecté ──
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// ── Uniquement POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/signin.php");
    exit;
}

require_once '../config/database.php';

$email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// ── Validation basique ──
if (empty($email) || empty($password)) {
    header("Location: ../pages/signin.php?error=champs_vides");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../pages/signin.php?error=email_invalide");
    exit;
}

try {
    // ── Chercher l'utilisateur par email ──
    $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => strtolower($email)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ── Email introuvable ──
    if (!$user) {
        header("Location: ../pages/signin.php?error=email_not_found");
        exit;
    }

    // ── Vérification du mot de passe ──
    // Supporte : hash bcrypt (password_hash) ET mot de passe en clair (legacy)
    $passwordOk = false;

    if (password_verify($password, $user['password'])) {
        // Mot de passe hashé correct
        $passwordOk = true;
    } elseif ($user['password'] === $password) {
        // Legacy : mot de passe stocké en clair (migration)
        $passwordOk = true;
        // Re-hasher automatiquement pour sécuriser
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $upd = $pdo->prepare("UPDATE users SET password = :hash WHERE id = :id");
        $upd->execute([':hash' => $newHash, ':id' => $user['id']]);
    }

    if (!$passwordOk) {
        header("Location: ../pages/signin.php?error=wrong_password");
        exit;
    }

    // ── Succès : créer la session ──
    session_regenerate_id(true); // Prevent session fixation
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    header("Location: ../index.php");
    exit;

} catch (PDOException $e) {
    error_log('login.php PDO error: ' . $e->getMessage());
    header("Location: ../pages/signin.php?error=server_error");
    exit;
}
?>