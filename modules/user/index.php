<?php
session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../libs/aes.php';

if (!isset($_SESSION['user_verified']) || $_SESSION['user_id'] != true) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: ".BASE_URL."");
        exit();
    }
    header("Location: ".BASE_URL."/modules/auth/verification.php");
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
require __DIR__ . '/../../modules/user/home.php';
?>

<?php
require __DIR__ . '/../../includes/footer.php';
?>