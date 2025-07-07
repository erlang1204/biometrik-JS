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
$foto_register_dir = realpath(__DIR__ . '/../../dataset/' . $username);
$foto_ujian_dir    = realpath(__DIR__ . '/../../dataset_ujian/' . $username);

$foto_register = glob($foto_register_dir . "/*.{jpg,jpeg,png}", GLOB_BRACE);
$foto_ujian    = glob($foto_ujian_dir . "/*.{jpg,jpeg,png}", GLOB_BRACE);

// Fungsi helper untuk konversi ke base64
function imgToBase64($path) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/' . $type . ';base64,' . base64_encode($data);
}

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

// Tampilkan foto register
$html .= '<h3>Foto Register</h3><div class="foto-container">';
if ($foto_register) {
    foreach ($foto_register as $foto) {
        $base64 = imgToBase64($foto);
        $html .= '<img src="' . $base64 . '">';
    }
} else {
    $html .= '<p>Tidak ada foto register</p>';
}
$html .= '</div>';

// Tampilkan foto ujian
$html .= '<h3>Foto Ujian</h3><div class="foto-container">';
if ($foto_ujian) {
    foreach ($foto_ujian as $foto) {
        $base64 = imgToBase64($foto);
        $html .= '<img src="' . $base64 . '">';
    }
} else {
    $html .= '<p>Tidak ada foto ujian</p>';
}
$html .= '</div>
</body>
</html>';

echo $html;
