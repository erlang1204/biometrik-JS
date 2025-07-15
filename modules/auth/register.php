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
     $verification_code = rand(100000, 999999);
    $encryptedPassword = encryptPassword($password);
    

    $conn->beginTransaction();

    if (!isUsernameExists($conn, $username)) {

         // Sementara insert user dummy (dengan gambar kosong) agar dapat ID
        $data = [
            'name' => $name,
            'username' => $username,
            'contact_number' => $contact_number,
            'email' => $email,
            'password' => $encryptedPassword,
            'verification_code' => $verification_code,
            'role' => $role,
            'studentImage' => ''
        ];
        
        $id = insertNewUser($conn, $data);
        // Upload Gambar
        $uploadedFiles = uploadMultipleImages($name,$id);
        if (!$uploadedFiles) {
            $_SESSION['register_errors'] = "Gagal mengunggah gambar.";
            header("Location: index.php");
            exit;
        }

        $imagesJson = json_encode($uploadedFiles);

        // Enkripsi Password
        $encryptedPassword = encryptPassword($password);
        
        $verification_code = rand(100000, 999999);

        // insertNewUser($conn, [
        //     'name' => $name,
        //     'username' => $username,
        //     'contact_number' => $contact_number,
        //     'email' => $email,
        //     'password' => $encryptedPassword,
        //     'verification_code' => $verification_code,
        //     'role' => $role,
        //     'studentImage' => $imagesJson
        // ]);

         // Update record dengan path gambar setelah upload berhasil
        // $stmt = $conn->prepare("UPDATE tbl_user SET studentImage = :studentImage WHERE id = :id");
        // $stmt->execute([
        //     ':studentImage' => $imagesJson,
        //     ':id' => $id
        // ]);
        sendVerificationEmail($email, $verification_code);

        $conn->commit();
        $_SESSION['register_user_success'] = "Register Successfully.";
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

function uploadMultipleImages($nama, $id,$jumlah = 5) {
    $folderTujuan = "../../dataset/{$nama}_{$id}/";
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
    if (!in_array($ext, $extValid)) return false;
    if ($file['size'] > ($maxSizeMB * 1024 * 1024)) return false;
    if ($file['error'] !== 0) return false;

    // Ubah nama file jadi lowercase dan berekstensi .png
    $namaBaru = strtolower($namaGambar) . '.png';
    $fullPath = rtrim($folderTujuan, '/') . '/' . $namaBaru;

    if (!is_dir($folderTujuan)) mkdir($folderTujuan, 0755, true);

    // Buka gambar sesuai format asli
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $srcImage = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'png':
            $srcImage = imagecreatefrompng($file['tmp_name']);
            break;
        case 'gif':
            $srcImage = imagecreatefromgif($file['tmp_name']);
            break;
        default:
            return false;
    }

    if (!$srcImage) return false;

    // Simpan gambar dalam format PNG
    $saved = imagepng($srcImage, $fullPath);
    imagedestroy($srcImage);

    return $saved ? $fullPath : false;
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

    return $conn->lastInsertId();
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
