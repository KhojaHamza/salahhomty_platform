<?php
// pages/dashboard.php
session_start();

// ── Protéger la page : si non connecté, rediriger ──
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php?error=non_connecte");
    exit;
}

$userName  = htmlspecialchars($_SESSION['user_name']);
$userEmail = htmlspecialchars($_SESSION['user_email']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – SalahHomty</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">🔧 <strong>SalahHomty</strong></div>
    <div class="nav-links">
        <span>👋 Bonjour, <strong><?= $userName ?></strong></span>
        <a href="../auth/logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>

<div class="dashboard">
    <h1>Tableau de bord</h1>
    <p>Connecté en tant que : <strong><?= $userEmail ?></strong></p>
    <!-- Ton contenu dashboard ici -->
</div>

</body>
</html>