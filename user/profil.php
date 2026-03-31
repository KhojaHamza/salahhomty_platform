<?php
session_start();
require_once '../config/database.php';

// 🔒 Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 🧠 Get current user data
$stmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 🟢 UPDATE PROFILE
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // 🟡 Update name + phone
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $user_id]);

    // 🔐 Update password only if filled
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
    }

    $message = "Profile updated successfully!";
    
    // Refresh data
    $stmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - SalahHomty</title>
<style>

body {
    margin: 0;
    font-family: Arial;
    background: linear-gradient(135deg, #1f4037, #2bbbad);
    color: white;
}

.container {
    max-width: 500px;
    margin: 80px auto;
    background: white;
    color: black;
    padding: 30px;
    border-radius: 15px;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
}

button {
    width: 100%;
    padding: 12px;
    background: #2bbbad;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #239c90;
}

.message {
    text-align: center;
    color: green;
    margin-bottom: 10px;
}

.nav {
    background: #1f2a38;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    color: white;
}

.nav a {
    color: white;
    margin-left: 15px;
    text-decoration: none;
}

</style>
</head>

<body>

<!-- NAVBAR -->
<div class="nav">
    <div><b>SalahHomty</b></div>
    <div>
        <a href="../index.php">Home</a>
        <a href="history.php">History</a>
        <a href="helppage.php">Help</a>
        <a href="../auth/logout.php">Logout</a>
    </div>
</div>

<!-- PROFILE FORM -->
<div class="container">
    <h2>My Profile</h2>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

        <label>New Password (optional)</label>
        <input type="password" name="password" placeholder="Leave empty to keep current password">

        <button type="submit">Update Profile</button>

    </form>
</div>

</body>
</html>