<?php

session_start();

require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . "/../../libs/aes.php";
require __DIR__ . "/../../libs/AesBase.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: " . BASE_URL . "");
    exit();
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $contact_number = sanitizeInput($_POST['contact_number']);

    $pass_encrypt = "";
    if(!empty($_POST['password'])) {
        $a = $_POST['password']; //kunci utk enkripsi PLAIN TEXT
        $io = substr(md5($a), 0, 16); //keyy
        $aes = new AesBase($io); // pembuatan objek dari class Aes, dengan parameter kunci dekrip
        $pass_encrypt = bin2hex($aes->encrypt($a));
    }


    $stmt = $conn->prepare("SELECT `password` FROM `tbl_user` WHERE `id` = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $password = empty($pass_encrypt) ? $data['password'] : $pass_encrypt;

    if (!empty($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE `tbl_user` SET `name` = :name, `email` = :email, `contact_number` = :contact_number, `password` = :password, `username` = :username WHERE `id` = :id");
        $stmt->execute(['name' => $name, 'email' => $email, 'contact_number' => $contact_number, 'password' => $password, 'username' => $username, 'id' => $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['update_profile_success'] = "Profile updated successfully";
            header("Location: " . BASE_URL . "");
            exit();
        }
    }
}