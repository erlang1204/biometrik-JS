<?php

$servername = "192.168.1.153";
$username = "remote153";
$password = "Simetri123";
$dbname = "psikotes";
$port = 3308;

try {
    // Tambahkan port ke DSN
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Koneksi berhasil!";
} catch (PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}