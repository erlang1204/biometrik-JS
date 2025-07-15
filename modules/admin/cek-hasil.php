<?php
session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';

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
                $id = $user['id'];
                $name = $user['name'];
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
                            <span class="badge bg-primary" style="font-size: 2rem; padding: 20px 30px;">' . $nilai_bulat . '</span>
                        </div>
                        <div>
                            <span class="badge bg-' . $badge_color . '" style="font-size: 1.2rem; padding: 10px 20px;">
                                ' . $status_lulus . '
                            </span>
                        </div>
                        <p class="mt-2">Jawaban Benar: ' . $nilai . ' dari ' . $total_soal . ' soal</p>
                    </div>
                </div>';

        echo "<div class='mt-4'>";

        // --- FOTO REGISTER ---
        $name = $user['name'];
        $folderName = $name.'_'.$id;
        $dataset_dir_register = realpath(__DIR__ . '/../../dataset/' . $folderName);
        $dataset_url_base = 'dataset/' . $folderName; // Pastikan folder ini bisa diakses lewat web

        if ($dataset_dir_register && is_dir($dataset_dir_register)) {
            $fotos_register = glob($dataset_dir_register . "/*.{jpg,jpeg,png}", GLOB_BRACE);

            if (!empty($fotos_register)) {
                echo "<h4 style='color:white;'>Foto Register:</h4>";
                echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";

                foreach ($fotos_register as $index => $foto_path) {
                    $filename = basename($foto_path);
                    $imgUrl = BASE_URL . '/' . $dataset_url_base . '/' . rawurlencode($filename); // Encode untuk URL aman

                    echo "<img 
                        src='{$imgUrl}' 
                        alt='{$filename}' 
                        title='{$filename}' 
                        style='width: 120px; height: 120px; object-fit: cover; border: 2px solid #fff; border-radius: 8px; cursor: pointer;' 
                        onclick='showImage(\"{$imgUrl}\")'>";
                }

                echo "</div><br>";
            } else {
                echo "<p style='color:white;'>Tidak ada foto register ditemukan.</p>";
            }
        } else {
            echo "<p style='color:white;'>Tidak ada foto register ditemukan.</p>";
        }



        // --- FOTO UJIAN ---
        $name = $user['name'];
        $folderName = $name.'_'.$id;
        $dataset_dir_ujian = realpath(__DIR__ . '/../../dataset_ujian/' . $folderName);
        var_dump($dataset_dir_ujian);
        die();
        $dataset_url_base = 'dataset_ujian/' . $folderName; // Pastikan folder ini bisa diakses lewat web

        if ($dataset_dir_ujian && is_dir($dataset_dir_ujian)) {
            $fotos_ujian = glob($dataset_dir_ujian . "/*.{jpg,jpeg,png}", GLOB_BRACE);

            if (!empty($fotos_ujian)) {
                echo "<h4 style='color:white;'>Foto Ujian:</h4>";
                echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";

                foreach ($fotos_ujian as $index => $foto_path) {
                    $filename = basename($foto_path);
                    $imgUrl = BASE_URL . '/' . $dataset_url_base . '/' . rawurlencode($filename); // Encode untuk URL aman

                    echo "<img 
                        src='{$imgUrl}' 
                        alt='{$filename}' 
                        title='{$filename}' 
                        style='width: 120px; height: 120px; object-fit: cover; border: 2px solid #fff; border-radius: 8px; cursor: pointer;' 
                        onclick='showImage(\"{$imgUrl}\")'>";
                }

                echo "</div><br>";
            } else {
                echo "<p style='color:white;'>Tidak ada foto ujian ditemukan.</p>";
            }
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