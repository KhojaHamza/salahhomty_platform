<?php
// config/database.php
// Connexion à la base de données avec PDO

$host   = 'localhost';
$dbname = 'salahhomty';
$user   = 'root';
$pass   = ''; // vide par défaut sur XAMPP

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => 'Connexion échouée : ' . $e->getMessage()]));
}
?>