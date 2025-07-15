<?php
// Set header agar response selalu dalam bentuk JSON
header("Content-Type: application/json");

// Ambil dan decode data JSON
$data = json_decode(file_get_contents("php://input"), true);

// Validasi input
if (!isset($data['image']) || !isset($data['username'])) {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap."]);
    exit;
}

$imageData = $data['image'];
$username = preg_replace('/[^a-zA-Z0-9_]/', '', $data['username']); // sanitize nama folder
$name = preg_replace('/[^a-zA-Z0-9_]/', '', $data['name']); // sanitize nama folder
$id = preg_replace('/[^a-zA-Z0-9_]/', '', $data['id']); // sanitize nama folder
$soal = preg_replace('/[^a-zA-Z0-9_]/', '', $data['soal']); // sanitize nama folder
$folderName = $name.'_'.$id;


$folder = dirname(__DIR__,2) . "/dataset_ujian/$folderName";
if (!file_exists($folder)) {
    if (!mkdir($folder, 0777, true)) {
        echo json_encode(["success" => false, "message" => "Gagal membuat folder."]);
        exit;
    }
}

// Hitung jumlah file PNG yang sudah ada
$existingFiles = glob("$folder/*.png");
$fileCount = count($existingFiles) + 1;



// Bersihkan header base64 (jika ada)
$base64Str = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);

// Decode base64 menjadi data biner
$imageDecoded = base64_decode($base64Str);

if ($imageDecoded === false) {
    echo json_encode(["success" => false, "message" => "Gagal decode gambar base64."]);
    exit;
}

// Tentukan path file
$filePath = "$folder/{$name}_$soal.png";
$countSoal = count(glob(dirname($filePath)));

if($countSoal > 0)
{
    @unlink($filePath);
    if (file_put_contents($filePath, $imageDecoded)) {
        echo json_encode(["success" => true, "file" => $filePath]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menyimpan gambar."]);
    }
}
else
{
    if (file_put_contents($filePath, $imageDecoded)) {
        echo json_encode(["success" => true, "file" => $filePath]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menyimpan gambar."]);
    }
}



