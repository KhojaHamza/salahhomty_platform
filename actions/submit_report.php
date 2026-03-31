<?php
// actions/submit_report.php  (updated with auto-assignment)
session_start();
require_once '../config/database.php';
require_once 'auto_assign.php';  

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$user_id     = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$location    = isset($_POST['location'])    ? trim($_POST['location'])    : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$latitude    = isset($_POST['latitude'])  && $_POST['latitude']  !== '' ? (float)$_POST['latitude']  : null;
$longitude   = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
$category_id = isset($_POST['category_id']) && (int)$_POST['category_id'] > 0 ? (int)$_POST['category_id'] : null;

if ($location === '' && $latitude === null) {
    header('Location: ../index.php?error=' . urlencode('Please enter a location or use GPS before submitting.'));
    exit;
}


$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file    = $_FILES['image'];
    $maxBytes = 10 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        header('Location: ../index.php?error=' . urlencode('Image must be under 10 MB.'));
        exit;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ['image/jpeg','image/png','image/webp','image/gif'], true)) {
        header('Location: ../index.php?error=' . urlencode('Only JPG, PNG, WEBP or GIF accepted.'));
        exit;
    }
    $uploadDir = __DIR__ . '/../uploads/reports/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg');
    $filename = uniqid('report_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        header('Location: ../index.php?error=' . urlencode('Failed to save image. Please try again.'));
        exit;
    }
    $imagePath = 'uploads/reports/' . $filename;
}

// insert reports
try {
    $stmt = $pdo->prepare("
        INSERT INTO reports (user_id, image, latitude, longitude, description, category_id, status, created_at)
        VALUES (:user_id, :image, :latitude, :longitude, :description, :category_id, 'Open', NOW())
    ");
    $stmt->execute([
        ':user_id'     => $user_id,
        ':image'       => $imagePath,
        ':latitude'    => $latitude,
        ':longitude'   => $longitude,
        ':description' => $description !== '' ? $description : null,
        ':category_id' => $category_id,
    ]);

    $newReportId = (int)$pdo->lastInsertId();

    // agin to approprate agent
    autoAssignReport($pdo, $newReportId);

    header('Location: ../index.php?success=1');
    exit;

} catch (PDOException $e) {
    error_log('submit_report: ' . $e->getMessage());
    header('Location: ../index.php?error=' . urlencode('A database error occurred. Please try again.'));
    exit;
}
?>