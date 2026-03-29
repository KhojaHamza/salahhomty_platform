<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 1. Validation basique
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=Tous+les+champs+sont+requis");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=Email+invalide");
        exit;
    }

    // 2. Recherche de l'utilisateur en base
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Vérification du mot de passe
    if (!$user || !password_verify($password, $user['password'])) {
        header("Location: login.php?error=Email+ou+mot+de+passe+incorrect");
        exit;
    }

    // 4. Connexion réussie → création de la session
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email']= $user['email'];

    header("Location: dashboard.php");
    exit;
}>