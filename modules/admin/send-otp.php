<?php
session_start();

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../libs/aes.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user_id = $_GET['user_id'];

// Ambil data pengguna
$stmt = $conn->prepare("SELECT * FROM `tbl_user` WHERE `id` = :id LIMIT 1");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$data_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kirim email dengan lampiran PDF
$mail = new PHPMailer(true);
$username = $data_user['username'];
$email = $data_user['email'];
$verification_code = $data_user['verification_code'];

try {
    // Server settings
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'erlangbayu7@gmail.com';
    $mail->Password = 'hlrv hthv rwgl uaby'; // Pastikan ini benar dan aman
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    // Recipients
    $mail->setFrom('erlangbayu7@gmail.com', 'PT. Sinar Metrindo Perkasa');
    $mail->addAddress($email, $username);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Verification Code';
    $mail->Body    = 'Your verification code is: ' . $verification_code;

    $mail->send();

    // Menampilkan alert dan redirect
    echo "
    <script>
        alert('OTP Berhasil Di Kirim.');
        window.location.href = '" . BASE_URL . "/modules/admin/index.php';
    </script>
   ";
} catch (Exception $e) {
    // error
    echo "
        <script>
            alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');
            window.location.href = '" . BASE_URL . "/modules/admin/index.php';
        </script>
    ";
}