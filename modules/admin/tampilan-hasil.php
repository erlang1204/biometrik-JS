<?php
// Hitung nilai
$nilai = 0;
$total_soal = count($jawaban);

foreach ($jawaban as $kunci) {
    $soal_id = $kunci['id'];
    $jawaban_benar = $kunci['jawaban'];

    foreach ($answers as $jawaban_user) {
        if ($jawaban_user['soal_id'] == $soal_id) {
            $jawaban_user = $jawaban_user['jawaban'];

            if (strtolower($jawaban_user) == strtolower($jawaban_benar)) {
                $nilai++;
            }
        }
    }
}

// Hitung nilai akhir
$nilai_persen = ($nilai / $total_soal) * 100;
$nilai_bulat = round($nilai_persen);

// Status kelulusan
$status_lulus = $nilai >= 5 ? "LULUS" : "TIDAK LULUS";
$warna_status = $nilai >= 5 ? "green" : "red";

// Direktori foto
$username = $data_user['username'];

// HTML hasil
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Pemeriksaan Psikologis CFIT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
        }
        table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th {
            background: #f2f2f2;
        }
        th, td {
            padding: 8px;
        }
        .nilai {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 20px;
        }
        .status {
            color: ' . $warna_status . ';
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 20px;
        }
        .foto-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
        }
        .foto-container img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #000;
        }
    </style>
</head>
<body>
    <h2>HASIL PEMERIKSAAN PSIKOLOGIS CFIT</h2>
    <table>
        <tr><td>Nama</td><td>' . htmlspecialchars($data_user['username']) . '</td></tr>
        <tr><td>Email</td><td>' . htmlspecialchars($data_user['email']) . '</td></tr>
        <tr><td>Handphone</td><td>' . htmlspecialchars($data_user['contact_number']) . '</td></tr>
    </table>

    <table>
        <thead>
            <tr><th>No Soal</th><th>Jawaban</th></tr>
        </thead>
        <tbody>';

foreach ($answers as $answer) {
    $html .= '<tr><td>' . htmlspecialchars($answer['soal_id']) . '</td><td>' . htmlspecialchars($answer['jawaban']) . '</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="nilai">Nilai Anda: ' . $nilai_bulat . '</div>
    <div class="status">Status: <strong>' . $status_lulus . '</strong></div>';

$username = $data_user['username'];
$name = $data_user['name'];
$id = $data_user['id'];
$folderName = $name.'_'.$id;

// Tampilkan foto register
$html .= '<h3>Foto Register</h3><div class="foto-container">';

$dataset_dir_register = realpath(__DIR__ . '/../../dataset/' . $folderName);
$dataset_url_base = 'dataset/' . $folderName; // Pastikan folder ini bisa diakses lewat web

if ($dataset_dir_register && is_dir($dataset_dir_register)) {
    $foto_register = glob($dataset_dir_register . "/*.{jpg,jpeg,png}", GLOB_BRACE);

    if (!empty($foto_register)) {
        foreach ($foto_register as $foto_path) {
            $html .= '<img src="file://' . $foto_path . '" style="width:100px; height:100px; object-fit:cover; margin:5px; border:1px solid #ccc;">';
        }
    } else {
        $html .= '<p>Tidak ada foto register.</p>';
    }
} else {
    $html .= '<p>Tidak ada foto register.</p>';
}

$html .= '</div>';

// Tampilkan foto ujian
$html .= '<h3>Foto Ujian</h3><div class="foto-container">';

$dataset_dir_ujian = realpath(__DIR__ . '/../../dataset_ujian/' . $folderName);
$dataset_url_base = 'dataset_ujian/' . $folderName; // Pastikan folder ini bisa diakses via URL

if ($dataset_dir_ujian && is_dir($dataset_dir_ujian)) {
    $foto_ujian = glob($dataset_dir_ujian . "/*.{jpg,jpeg,png}", GLOB_BRACE);

    if (!empty($foto_ujian)) {
        foreach ($foto_ujian as $foto_path) {
            $html .= '<img src="file://' . $foto_path . '" style="width:100px; height:100px; object-fit:cover; margin:5px; border:1px solid #ccc;">';
        }
    } else {
        $html .= '<p>Tidak ada foto ujian.</p>';
    }
} else {
    $html .= '<p>Tidak ada foto ujian.</p>';
}

$html .= '</div>';

'</body>
</html>';

echo $html;
