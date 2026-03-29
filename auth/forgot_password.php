<?php
// auth/forgot_password.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

require_once '../config/database.php';

$email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Always respond with success — never reveal if email exists (security)
    if ($user) {
        // Generate a secure token
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        // Store token in DB (create table if not exists — see SQL below)
        $del = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
        $del->execute([':email' => $email]);

        $ins = $pdo->prepare("
            INSERT INTO password_resets (email, token, expires_at, created_at)
            VALUES (:email, :token, :expires_at, NOW())
        ");
        $ins->execute([
            ':email'      => $email,
            ':token'      => $token,
            ':expires_at' => $expiresAt,
        ]);

        // ── Send reset email ──
        // Detect base URL automatically
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'];
        // Adjust the path to match your project structure
        $resetUrl = $protocol . '://' . $host . '/salahhomty_platform/auth/reset_password.php?token=' . $token;

        $to      = $email;
        $subject = 'Reset your SalahHomty password';
        $name    = htmlspecialchars($user['name']);
        $message = "Hello $name,\n\n"
                 . "We received a request to reset your SalahHomty password.\n\n"
                 . "Click the link below to reset it (valid for 1 hour):\n"
                 . "$resetUrl\n\n"
                 . "If you did not request this, please ignore this email.\n\n"
                 . "— The SalahHomty Team";

        $headers = "From: no-reply@salahhomty.com\r\nReply-To: no-reply@salahhomty.com\r\n";
        mail($to, $subject, $message, $headers);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log('forgot_password.php PDO error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>