<?php
session_start();                        // ← une seule fois ici
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../user/signup.php");
    exit;
}

$name             = trim($_POST['name']             ?? '');
$email            = trim($_POST['email']            ?? '');
$phone            = trim($_POST['phone']            ?? '');
$password         = trim($_POST['password']         ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    header("Location: ../user/signup.php?error=Tous+les+champs+sont+requis");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../user/signup.php?error=Format+email+invalide");
    exit;
}

if (strlen($password) < 8) {
    header("Location: ../user/signup.php?error=Mot+de+passe+minimum+8+caracteres");
    exit;
}

if ($password !== $confirm_password) {
    header("Location: ../user/signup.php?error=Les+mots+de+passe+ne+correspondent+pas");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header("Location: ../user/signup.php?error=Cet+email+est+deja+utilise");
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
$result = $stmt->execute([$name, $email, $phone, $passwordHash]);

if (!$result) {
    header("Location: ../user/signup.php?error=Erreur+creation+compte");
    exit;
}

$_SESSION['user_id']    = $pdo->lastInsertId();
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;

header("Location: ../index.php?welcome=1");
exit;
?>