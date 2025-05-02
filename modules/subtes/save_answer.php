<?php
session_start();

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['soalId']) && isset($_POST['answerId'])) {
    $soalID = sanitizeInput($_POST['soalId']);
    $jawaban = sanitizeInput($_POST['answerId']);
    $user_id = $_SESSION['user_id'];

    // Periksa apakah jawaban sudah ada
    $stmt = $conn->prepare("SELECT COUNT(*) FROM `tbl_jawaban` WHERE `user_id` = :user_id AND `soal_id` = :soalID");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->bindParam(':soalID', $soalID, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    try {
        if ($count > 0) {
            // Jika jawaban sudah ada, lakukan pembaruan
            $stmt = $conn->prepare("UPDATE `tbl_jawaban` SET `jawaban` = :jawaban WHERE `user_id` = :user_id AND `soal_id` = :soalID");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':soalID', $soalID, PDO::PARAM_STR);
            $stmt->bindParam(':jawaban', $jawaban, PDO::PARAM_STR);
            $stmt->execute();
            
            return "Jawaban berhasil diperbarui.";
        } else {
            // Jika jawaban belum ada, lakukan penyisipan
            $stmt = $conn->prepare("INSERT INTO `tbl_jawaban` (`user_id`, `soal_id`, `jawaban`) VALUES (:user_id, :soalID, :jawaban)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':soalID', $soalID, PDO::PARAM_STR);
            $stmt->bindParam(':jawaban', $jawaban, PDO::PARAM_STR);
            $stmt->execute();

            return "Jawaban berhasil disimpan.";
        }
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}