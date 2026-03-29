<?php
// actions/submit_report.php
session_start();

require_once '../config/database.php';

// ── Only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// ── Get user_id (nullable — allow guests) ──
$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

// ── Sanitise text fields ──
$location    = isset($_POST['location'])    ? trim($_POST['location'])    : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$latitude    = isset($_POST['latitude'])    && $_POST['latitude']  !== '' ? (float) $_POST['latitude']  : null;
$longitude   = isset($_POST['longitude'])   && $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null;

// ── Require at least a location ──
if ($location === '' && $latitude === null) {
    header('Location: ../index.php?error=' . urlencode('Please enter a location or use GPS before submitting.'));
    exit;
}

// ── Handle image upload ──
$imagePath = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

    $file     = $_FILES['image'];
    $maxBytes = 10 * 1024 * 1024; // 10 MB

    // Size check
    if ($file['size'] > $maxBytes) {
        header('Location: ../index.php?error=' . urlencode('Image must be under 10 MB.'));
        exit;
    }

    // MIME check (use finfo for reliability)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($mimeType, $allowed, true)) {
        header('Location: ../index.php?error=' . urlencode('Only JPG, PNG, WEBP, or GIF images are accepted.'));
        exit;
    }

    // Build upload directory (relative to project root)
    $uploadDir = __DIR__ . '/../uploads/reports/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Unique filename
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $filename = uniqid('report_', true) . '.' . strtolower($ext);
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        header('Location: ../index.php?error=' . urlencode('Failed to save the uploaded image. Please try again.'));
        exit;
    }

    // Store relative path in DB
    $imagePath = 'uploads/reports/' . $filename;
}

// ── Insert into database ──
try {
    $stmt = $pdo->prepare("
        INSERT INTO reports (user_id, image, latitude, longitude, description, created_at)
        VALUES (:user_id, :image, :latitude, :longitude, :description, NOW())
    ");

    $stmt->execute([
        ':user_id'     => $user_id,
        ':image'       => $imagePath,
        ':latitude'    => $latitude,
        ':longitude'   => $longitude,
        ':description' => $description !== '' ? $description : null,
    ]);

    header('Location: ../index.php?success=1');
    exit;

} catch (PDOException $e) {
    // In production, log the error rather than exposing it
    error_log('submit_report PDO error: ' . $e->getMessage());
    header('Location: ../index.php?error=' . urlencode('A database error occurred. Please try again later.'));
    exit;
}