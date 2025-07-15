<?php

require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE `tbl_user` SET `finish_test` = 1 WHERE `id` = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    // header("Location: " . BASE_URL . "/modules/subtes/home.php");
      echo json_encode(["success" => true]);
    exit();
    exit();
}