<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$username = $_SESSION['username'];
$imageData = $_POST['image'] ?? '';

if (empty($imageData)) {
    http_response_code(400);
    echo "No image data received";
    exit;
}

// Bersihkan data base64
$imageData = str_replace('data:image/png;base64,', '', $imageData);
$imageData = str_replace(' ', '+', $imageData);
$image = base64_decode($imageData);

$folderPath = __DIR__ . "/dataset_login/$username";
if (!file_exists($folderPath)) {
    mkdir($folderPath, 0777, true);
}

// Buat nama file unik
$filename = $folderPath . '/' . time() . '.png';
file_put_contents($filename, $image);

echo "Image saved successfully";
