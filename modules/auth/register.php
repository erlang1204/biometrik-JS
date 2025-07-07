<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

use PHPMailer\PHPMailer\PHPMailer;

require "../../vendor/phpmailer/phpmailer/src/SMTP.php";
require "../../vendor/phpmailer/phpmailer/src/PHPMailer.php";
require "../../vendor/phpmailer/phpmailer/src/Exception.php";
require "../../libs/AesBase.php";
include '../../config/app.php';
include '../../config/database.php';
include '../../includes/functions.php';

try {
    $username = sanitizeInput($_POST['username']);
    $contact_number = sanitizeInput($_POST['contact_number']);
    $email = sanitizeInput($_POST['email']);
    $name = sanitizeInput($_POST['name']);
    $password = $_POST['password'];
    $role = "user";

    $conn->beginTransaction();

    if (!isUsernameExists($conn, $username)) {

        // Upload Gambar
        $uploadedFiles = uploadMultipleImages($name);
        if (!$uploadedFiles) {
            $_SESSION['register_errors'] = "Gagal mengunggah gambar.";
            header("Location: index.php");
            exit;
        }

        $imagesJson = json_encode($uploadedFiles);

        // Enkripsi Password
        $encryptedPassword = encryptPassword($password);
        
        $verification_code = rand(100000, 999999);

        insertNewUser($conn, [
            'name' => $name,
            'username' => $username,
            'contact_number' => $contact_number,
            'email' => $email,
            'password' => $encryptedPassword,
            'verification_code' => $verification_code,
            'role' => $role,
            'studentImage' => $imagesJson
        ]);

        sendVerificationEmail($email, $verification_code);

        $conn->commit();
        $_SESSION['register_user_success'] = "Register Successfully, please check your email.";
    } else {
        $conn->rollBack();
        $_SESSION['register_user_exists'] = "User already exists.";
    }

    header("Location: " . BASE_URL . "");
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['register_errors'] = $e->getMessage();
    header("Location: " . BASE_URL . "");
}


// ===================== FUNGSI-FUNGSI ==========================

function uploadMultipleImages($nama, $jumlah = 5) {
    $folderTujuan = "../../dataset/{$nama}/";
    $uploaded = [];

    for ($i = 1; $i <= $jumlah; $i++) {
        $inputName = "capturedImage$i";
        if (!empty($_FILES[$inputName]['name'])) {
            $filePath = simpanGambarReturnPath($inputName, $folderTujuan, "{$nama}_$i");
            if (!$filePath) return false;
            $uploaded[] = $filePath;
        } else {
            return false;
        }
    }

    return $uploaded;
}

function simpanGambarReturnPath($inputName, $folderTujuan = 'uploads/', $namaGambar, $maxSizeMB = 2) {
    if (!isset($_FILES[$inputName])) return false;

    $file = $_FILES[$inputName];
    $extValid = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $namaBaru = $namaGambar . '.' . $ext;
    $fullPath = $folderTujuan . '/' . $namaBaru;

    if (!in_array($ext, $extValid)) return false;
    if ($file['size'] > ($maxSizeMB * 1024 * 1024)) return false;
    if ($file['error'] !== 0) return false;

    if (!is_dir($folderTujuan)) mkdir($folderTujuan, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) return false;

    return $fullPath;
}

function encryptPassword($password) {
    $key = substr(md5($password), 0, 16);
    $aes = new AesBase($key);
    return bin2hex($aes->encrypt($password));
}

function isUsernameExists($conn, $username) {
    $stmt = $conn->prepare("SELECT username FROM tbl_user WHERE username = :username");
    $stmt->execute(['username' => $username]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function insertNewUser($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO tbl_user (name, username, contact_number, email, password, verification_code, role, studentImage)
        VALUES (:name, :username, :contact_number, :email, :password, :verification_code, :role, :studentImage)
    ");

    $stmt->execute([
        ':name' => $data['name'],
        ':username' => $data['username'],
        ':contact_number' => $data['contact_number'],
        ':email' => $data['email'],
        ':password' => $data['password'],
        ':verification_code' => $data['verification_code'],
        ':role' => $data['role'],
        ':studentImage' => $data['studentImage']
    ]);
}

function sendVerificationEmail($toEmail, $code) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'erlangbayu7@gmail.com';
    $mail->Password = 'hkee hclw qafm qrvs';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('erlangbayu7@gmail.com', 'PT.Sinar Metrindo Perkasa');
    $mail->addAddress($toEmail);
    $mail->addReplyTo('erlangbayu7@gmail.com', 'PT.Sinar Metrindo Perkasa');

    $mail->isHTML(true);
    $mail->Subject = 'Verification Code';
    $mail->Body = 'Your verification code is: <a href="index.php">' . $code . '</a>';

    // $mail->send(); // Aktifkan saat live
}
