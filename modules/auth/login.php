<?php
session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../libs/aes.php';
require __DIR__ . '/../../libs/AesBase.php';

if($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: " . BASE_URL . "");
    exit();
} else if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    // Query untuk mengambil data user berdasarkan username
    $stmt = $conn->prepare("SELECT `id`, `name`, `password`, `role` FROM `tbl_user` WHERE `username` = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $role = $row['role'];
        $name = $row['name'];
        $user_password = $row['password'];

        // Memverifikasi password
        // Enkripsi password untuk membandingkan dengan yang tersimpan di database
        $io = substr(md5($password), 0, 16);
        $aes = new AesBase($io);
        $hasil = bin2hex($aes->encrypt($password));
        
        if (($hasil) === $user_password) {
            // Jika login berhasil, mulai session dan simpan informasi penting
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $name;

            // Redirect ke halaman sesuai dengan peran pengguna
            if ($role == 'admin') {
                header("Location: ".BASE_URL."/modules/admin/index.php");
                exit();
            } else {
                header("Location: ".BASE_URL. "/modules/auth/verification.php");
                exit();
            }
        } else {
            // Jika password tidak cocok
            $_SESSION['login_error'] = "Login failed, incorrect Username or Password";
            header("Location: " . BASE_URL . "");
            exit();
        }
    } else {
        // Jika username tidak ditemukan
        $_SESSION['login_error'] = "Login failed, incorrect Username or Password";
        header("Location: " . BASE_URL . "");
        exit();
    }
}