<?php
session_start();

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../libs/aes.php';
require __DIR__ . '/../../libs/aesctr.php';


use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user_id = $_POST['user_id'];

// Ambil data pengguna
$stmt = $conn->prepare("SELECT * FROM `tbl_user` WHERE `id` = :id LIMIT 1");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$data_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil jawaban pengguna
$stmt = $conn->prepare("SELECT * FROM `tbl_jawaban` WHERE `user_id` = :id ORDER BY CAST(soal_id AS UNSIGNED)");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil kunci jawaban
$stmt_kunci = $conn->prepare("SELECT id, jawaban FROM `tbl_soal` ORDER BY CAST(id AS UNSIGNED)");
$stmt_kunci->execute();
$jawaban = $stmt_kunci->fetchAll(PDO::FETCH_ASSOC);

// Simpan file ke pdf (asli)
$simpan_hasil_pdf = simpanHasilPDF($data_user, $answers, $jawaban);
if ($simpan_hasil_pdf['status']) {
    // Enkripsi file pdf
    $enkripsi_file_pdf = enkripsiFilePDF($data_user, $simpan_hasil_pdf['data']);
    if ($enkripsi_file_pdf['status']) {
        // Kirim hasil ke user
        $kirim_hasil_user = kirimHasilUser($data_user, $enkripsi_file_pdf['data']);
        if ($kirim_hasil_user['status']) {
            echo "<script>
                    alert('Berhasil ke kirim');
                    window.location.href = '" . BASE_URL . "/modules/admin/cek-hasil.php?id=" . $user_id . "';
            </script>";
        }
    }
}

function simpanHasilPDF($data_user, $answers, $jawaban)
{
    // Mengatur opsi Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    // Membuat instance Dompdf
    $dompdf = new Dompdf($options);

    // Memulai buffer output untuk menangkap output HTML dari tampilan_hasil.php
    ob_start();

    // kirim variable $data_user, $answers, $jawaban
    require __DIR__ . '/tampilan-hasil.php';
    $html = ob_get_clean();

    // Memuat HTML ke Dompdf
    $dompdf->loadHtml($html);

    // (Optional) Mengatur ukuran kertas dan orientasi
    $dompdf->setPaper('A4', 'portrait');

    // Merender HTML menjadi PDF
    $dompdf->render();

    // Mengambil output PDF sebagai string
    $output = $dompdf->output();

    $waktu_sekarang = str_replace(':', '-', date('Y-m-d H:i:s'));
    $file_name = $data_user['username'] . '-' . $waktu_sekarang . '.pdf';
    $save_path = __DIR__ . "/../../uploads/before-enkrip/" . $file_name;

    // Menyimpan file PDF ke server
    $result = [
        'status' => false,
        'data' => ''
    ];
    if (file_put_contents($save_path, $output)) {
        $result['status'] = true;
        $result['data'] = $file_name;
    }
    return $result;
}

function enkripsiFilePDF($data_user, $pathFileAsli)
{
    $key = $data_user['verification_code'];
    $namaFile = file_get_contents(dirname(dirname(__DIR__)) . "/uploads/before-enkrip/$pathFileAsli");
    $encFile = AesCtr::encrypt($namaFile, $key, 128);
    $waktu_sekarang = str_replace(':', '-', date('Y-m-d H:i:s'));
    $file_name = $data_user['username'] . '-' . $waktu_sekarang . '-encrypt.pdf';
    $enkrip = file_put_contents(dirname(dirname(__DIR__)) . "/uploads/after-enkrip/" . $file_name, $encFile);

    $result = [
        'status' => false,
        'data' => ''
    ];
    if ($enkrip) {
        $result['status'] = true;
        $result['data'] = $file_name;
    }
    return $result;
}

function kirimHasilUser($data_user, $file_enkripsi)
{
    // OTP
    $otp = $data_user['verification_code'];
    $file_enkripsi = "../../uploads/after-enkrip/$file_enkripsi";

    $username = $data_user['username'];
    $email = $data_user['email'];
    $contactNumber = $data_user['contact_number'];

    $result = [
        'status' => false,
        'message' => 'Errorrrrrrrrr'
    ];

    if (file_exists($file_enkripsi)) {
        // Buat dan simpan PDF dari hasil tes
        $htmlContent = '<html>
                            <body>
                                <h2>HASIL TEST</h2>
                                
                                <table>
                                    <tr>
                                        <td width="30%">Name</td>
                                        <td width="2%">:</td>
                                        <td>' . $username . '</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>:</td>
                                        <td>' . $email . '</td>
                                    </tr>
                                    <tr>
                                        <td>Handphone</td>
                                        <td>:</td>
                                        <td>' . $contactNumber . '</td>
                                    </tr>
                                    <tr>
                                        <td>Password</td>
                                        <td>:</td>
                                        <td>' . $otp . '</td>
                                    </tr>
                                </table>
                            </body>
                        </html>';

        // Kirim email dengan lampiran PDF
        $mail = new PHPMailer(true);

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

            // Attachments
            $mail->addAttachment($file_enkripsi);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Hasil Tes Psikotes';
            $mail->Body    = $htmlContent;

            $mail->send();
            $result['status'] = true;
            $result['message'] = 'Message has been sent';
        } catch (Exception $e) {
            // error
        }
    }

    return $result;
}
?>