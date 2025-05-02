<?php
require __DIR__ . '/libs/aes.php';
require __DIR__ . '/libs/aesctr.php';
require __DIR__ . '/includes/functions.php';

$key = $_POST['kunci'];
$namaFile = file_get_contents($_FILES['doc']['tmp_name']);
$decFile = AesCtr::decrypt($namaFile, $key, 128);

// Check if the decrypted content is valid
if ($decFile === false || empty($decFile)) {
    echo "<script>
            alert('Key Tidak Valid atau File Tidak Bisa Didekripsi');
            window.history.back(); // Go back to the previous page
          </script>";
    exit;
}

// Save the decrypted file temporarily
$waktu_sekarang = str_replace(':', '-', date('Y-m-d H:i:s'));
$file_name = 'Hasil' . '-' . $waktu_sekarang . '-decrypt.pdf';
$file_path =  __DIR__ . "/uploads/hasil-dekrip/" . $file_name;
file_put_contents($file_path, $decFile);

// Trigger the download
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);

// Delete the temporary file after download
unlink($file_path);

// Redirect back to the previous page
echo "<script>
        alert('File Berhasil Didekripsi');
        window.history.back(); // Go back to the previous page
    </script>";
exit;
