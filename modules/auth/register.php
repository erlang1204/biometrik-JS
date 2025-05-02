<?php
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
    $role = "user";

    //pengambilan gambar
    $nama = $_POST['name'] ?? 'default';

for ($i = 1; $i <= 5; $i++) {
    if (isset($_POST["capturedImage$i"])) {
        $base64_image = $_POST["capturedImage$i"];

        // Pisahkan header dan datanya
        list($type, $data) = explode(';', $base64_image);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        // Buat folder dataset/nama jika belum ada
        $folder = __DIR__ . "/../../dataset/$nama";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        // Simpan gambar
        $file_path = "$folder/{$nama}_$i.png";
        file_put_contents($file_path, $data);
    }
}


    $imagesJson = json_encode($imageFileNames);

    // Enkripsi password
    $a = $_POST['password'];
    $io = substr(md5($a), 0, 16);
    $aes = new AesBase($io);
    $pass_encrypt = bin2hex($aes->encrypt($a));

    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT `username` FROM `tbl_user` WHERE `username` = :username");
    $stmt->execute(['username' => $username]);
    $name_exist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($name_exist)) {
        $verification_code = rand(100000, 999999);

        $insertStmt = $conn->prepare("INSERT INTO `tbl_user` (`name`, `username`, `contact_number`, `email`, `password`, `verification_code`, `role`, `studentImage`) VALUES (:name, :username, :contact_number, :email, :password, :verification_code, :role, :studentImage)");
        $insertStmt->bindParam(':name', $name, PDO::PARAM_STR);
        $insertStmt->bindParam(':username', $username, PDO::PARAM_STR);
        $insertStmt->bindParam(':contact_number', $contact_number, PDO::PARAM_INT);
        $insertStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $insertStmt->bindParam(':password', $pass_encrypt, PDO::PARAM_STR);
        $insertStmt->bindParam(':verification_code', $verification_code, PDO::PARAM_INT);
        $insertStmt->bindParam(':role', $role, PDO::PARAM_STR);
        $insertStmt->bindParam(':studentImage', $imagesJson, PDO::PARAM_STR);
        $insertStmt->execute();

        // Konfigurasi pengiriman email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'erlangbayu7@gmail.com';
        $mail->Password = 'hkee hclw qafm qrvs';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('erlangbayu7@gmail.com', 'PT.Sinar Metrindo Perkasa');
        $mail->addAddress($email);
        $mail->addReplyTo('erlangbayu7@gmail.com', 'PT.Sinar Metrindo Perkasa');

        $mail->isHTML(true);
        $mail->Subject = 'Verification Code';
        $mail->Body = 'Your verification code is: <a href="index.php">' . $verification_code . '</a>';

        // Uncomment baris berikut untuk mengirim email saat live
        // $mail->send();

        $conn->commit();
        $_SESSION['register_user_success'] = "Register Successfully, Please check your email to verify your account";
        header("Location: " . BASE_URL . "");
    } else {
        $conn->rollBack();
        $_SESSION['register_user_exists'] = "User Already Exists";
        header("Location: " . BASE_URL . "");
    }
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['register_errors'] = $e->getMessage();
    header("Location: " . BASE_URL . "");
}
