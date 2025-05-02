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

            // Bandingkan jawaban user dengan kunci jawaban
            if (strtolower($jawaban_user) == strtolower($jawaban_benar)) {
                $nilai++;
            }
        }
    }
}

// Hitung nilai akhir dalam persentase
$nilai_persen = ($nilai / $total_soal) * 12;
$nilai_bulat = round($nilai_persen);

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemeriksaan Psikologis CFIT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>HASIL PEMERIKSAAN PSIKOLOGIS CFIT</h2>
    <table>
        <tr>
            <td>Nama:</td>
            <td>' . htmlspecialchars($data_user['username']) . '</td>
        </tr>
        <tr>
            <td>Email:</td>
            <td>' . htmlspecialchars($data_user['email']) . '</td>
        </tr>
        <tr>
            <td>Handphone:</td>
            <td>' . htmlspecialchars($data_user['contact_number']) . '</td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>Soal</th>
                <th>Jawaban</th>
            </tr>
        </thead>
        <tbody>';

foreach ($answers as $answer) {
    $html .= '
            <tr>
                <td>' . htmlspecialchars($answer['soal_id']) . '</td>
                <td>' . htmlspecialchars($answer['jawaban']) . '</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>
    <p style="font-size: 2rem; color: black;">Nilai: ' . $nilai_bulat . '</p>
</body>
</html>';

echo $html;
