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
        $nilai_persen = ($nilai / $total_soal) * 12;
        $nilai_bulat = round($nilai_persen);
        // Tampilkan hasil nilai

        echo '<p style="font-size: 2rem; color: white;">Nilai: ' . $nilai_bulat . '</p>';
        ?>
        <form method="POST" action="<?= BASE_URL; ?>/modules/admin/kirim-hasil.php">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <button class="btn btn-primary btn-lg btn-block" role="button" aria-pressed="true" type="submit">Kirim Ke User</button>
        </form>
    </div>
</div>

<?php
require __DIR__ . '/../../includes/footer.php';
?>