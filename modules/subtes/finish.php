<?php

session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE `tbl_user` SET `finish_test` = 1 WHERE `id` = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: " . BASE_URL . "/modules/subtes/index.php");
    exit();
}