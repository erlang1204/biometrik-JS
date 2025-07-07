<?php
session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../libs/aes.php';

if (!isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] != "admin") {
        header("Location: " . BASE_URL . "/modules/user/index.php");
        exit();
    }
    header("Location: " . BASE_URL . "");
    exit();
}

?>

<?php
require __DIR__ . '/../../includes/header.php';
?>
<?php
require __DIR__ . '/../../includes/navbar.php';
?>

<div class="d-flex justify-content-center align-items-center w-100" style="margin: 40px auto; min-height: 80vh;">
    <div class="content p-4">
        <h2>HASIL PEMERIKSAAN PSIKOLOGIS CFIT</h2>
        <table class="table table-borderless">
            <?php
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM `tbl_user` WHERE `id` = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {

                $username = $user['username'];
                $email = $user['email'];
                $contactNumber = $user['contact_number'];
            ?>
                <tr>
                    <td>Name:</td>
                    <td><?= $username; ?></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td><?= $email; ?></td>
                </tr>
                <tr>
                    <td>Handphone:</td>
                    <td><?= $contactNumber; ?></td>
                </tr>
            <?php
            } else {
                echo "<tr><td colspan='2'>No user data found.</td></tr>";
            }
            ?>
        </table>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">Soal</th>
                    <th scope="col">Jawaban</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM `tbl_jawaban` WHERE `user_id` = :id ORDER BY CAST(soal_id AS UNSIGNED)");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll();

                foreach ($result as $row) {
                    $soal = $row['soal_id'];
                    $jawaban = $row['jawaban'];
                ?>
                    <tr>

                        <td><?= $soal; ?></td>
                        <td><?= $jawaban; ?></td>
                    </tr>
                    
                <?php
                }
                ?>
            </tbody>
        </table>

        <?php
        // Kode untuk penilaian nilai
        $stmt_kunci = $conn->prepare("SELECT id, jawaban FROM `tbl_soal` ORDER BY CAST(id AS UNSIGNED)");
        $stmt_kunci->execute();
        $jawaban = $stmt_kunci->fetchAll(PDO::FETCH_ASSOC);

        $nilai = 0;
        $total_soal = count($jawaban);

        foreach ($jawaban as $kunci) {
            $soal_id = $kunci['id'];
            $jawaban_benar = $kunci['jawaban'];

            foreach ($result as $jawaban_user) {
                if ($jawaban_user['soal_id'] == $soal_id) {
                    $jawaban_user = $jawaban_user['jawaban'];

                    // Bandingkan jawaban user dengan kunci jawaban (dapat disesuaikan dengan logika penilaian yang Anda inginkan)
                    if (strtolower($jawaban_user) == strtolower($jawaban_benar)) {
                        $nilai++;
                    }
                }
            }
        }

        // Hitung nilai akhir dalam persentase
$nilai_persen = ($nilai / $total_soal) * 100;
$nilai_bulat = round($nilai_persen);

// Tentukan status kelulusan
$status_lulus = $nilai >= 6 ? "LULUS" : "TIDAK LULUS";
$badge_color = $nilai >= 6 ? "success" : "danger";

// Tampilkan hasil nilai & status
echo '
<div class="mt-4 mb-4" style="color: #fff">
    <div class="card-body text-center">
        <h4 class="mb-3">Nilai Akhir Anda</h4>
        <div class="mb-3">
            <span class="badge bg-primary" style="font-size: 2rem; padding: 20px 30px;">' . $nilai_bulat .'</span>
        </div>
        <div>
            <span class="badge bg-' . $badge_color . '" style="font-size: 1.2rem; padding: 10px 20px;">
                ' . $status_lulus . '
            </span>
        </div>
        <p class="mt-2">Jawaban Benar: ' . $nilai . ' dari ' . $total_soal . ' soal</p>
    </div>
</div>';

        // Fungsi kompres dan encode base64
        function compressToBase64($path, $width = 200, $quality = 60) {
            $info = getimagesize($path);
            $mime = $info['mime'];

            switch ($mime) {
                case 'image/jpeg':
                    $src = imagecreatefromjpeg($path);
                    break;
                case 'image/png':
                    $src = imagecreatefrompng($path);
                    break;
                default:
                    return '';
            }

            $old_width = imagesx($src);
            $old_height = imagesy($src);
            $ratio = $old_height / $old_width;
            $new_height = (int) round($width * $ratio);

            $dst = imagecreatetruecolor($width, $new_height);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $new_height, $old_width, $old_height);

            ob_start();
            imagejpeg($dst, null, $quality);
            $data = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            return 'data:image/jpeg;base64,' . base64_encode($data);
        }

        echo "<div class='mt-4'>";

        // --- FOTO REGISTER ---
        $dataset_dir_register = realpath(__DIR__ . '/../../dataset/' . $username);
        $fotos_register = glob($dataset_dir_register . "/*.{jpg,jpeg,png}", GLOB_BRACE);

        if ($fotos_register) {
            echo "<h4 style='color:white;'>Foto Register:</h4>";
            echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
            foreach ($fotos_register as $foto) {
                $imgBase64 = compressToBase64($foto, 200, 60);
                echo "<img src='{$imgBase64}' style='width: 120px; height: 120px; object-fit: cover; border: 2px solid #fff; border-radius: 8px; cursor: pointer;' onclick='showImage(\"{$imgBase64}\")'>";
            }
            echo "</div><br>";
        } else {
            echo "<p style='color:white;'>Tidak ada foto register ditemukan.</p>";
        }

        // --- FOTO UJIAN ---
        $dataset_dir_ujian = realpath(__DIR__ . '/../../dataset_ujian/' . $username);
        $fotos_ujian = glob($dataset_dir_ujian . "/*.{jpg,jpeg,png}", GLOB_BRACE);

        if ($fotos_ujian) {
            echo "<h4 style='color:white;'>Foto Ujian:</h4>";
            echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
           foreach ($fotos_ujian as $foto) {
    $imgBase64 = compressToBase64($foto, 200, 60);
    echo "<img src='{$imgBase64}' style='width: 120px; height: 120px; object-fit: cover; border: 2px solid #fff; border-radius: 8px; cursor: pointer;' onclick='showImage(\"{$imgBase64}\")'>";
}
echo "</div><br>";
} else {
    echo "<p style='color:white;'>Tidak ada foto ujian ditemukan.</p>";
}
echo "</div>";

        ?>
        <form method="POST" action="<?= BASE_URL; ?>/modules/admin/kirim-hasil.php">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <button class="btn btn-primary btn-lg btn-block" role="button" aria-pressed="true" type="submit">Kirim Ke User</button>
        </form>
    </div>
</div>
<!-- Modal Preview Gambar -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark">
      <div class="modal-body text-center">
        <img id="modalImage" src="" class="img-fluid rounded" alt="Preview" style="max-height: 80vh;">
      </div>
    </div>
  </div>
</div>

<script>
  function showImage(src) {
    const modalImg = document.getElementById("modalImage");
    modalImg.src = src;
    new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
  }
</script>

<?php
require __DIR__ . '/../../includes/footer.php';
?>