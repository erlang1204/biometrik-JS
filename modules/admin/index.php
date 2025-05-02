<?php
session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../libs/aes.php';

if (!isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] != "admin") {
        header("Location: " . BASE_URL . "/modules/user/index.php");
        exit();
    }
    header("Location: " . BASE_URL . "");
    exit();
}

?>

<?php
require __DIR__ . '/../../includes/header.php';
?>

<?php
require __DIR__ . '/../../includes/navbar.php';
?>

<?php
require __DIR__ . '/../../modules/admin/home.php';
?>

<?php
require __DIR__ . '/../../includes/footer.php';
?>