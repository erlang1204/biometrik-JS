<?php

session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] != "admin") {
        header("Location: " . BASE_URL . "/modules/user/index.php");
        exit();
    }
    header("Location: " . BASE_URL . "");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM `tbl_user` WHERE `id` = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo "success";
}