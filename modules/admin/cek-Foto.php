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
        <h2>HASIL FOTO</h2>
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

        <?php
            $dataset_dir = realpath(__DIR__ . '/../../dataset/' . $username);
            $dataset_url = BASE_URL . '/dataset/' . $username;


            $fotos = glob($dataset_dir . "/*.{jpg,jpeg,png}", GLOB_BRACE);

            if ($fotos) {
                echo "<h4 style='color:white;'>Foto Register:</h4>";
                echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";

                foreach ($fotos as $foto) {
                    $filename = basename($foto);
                    echo "<img src='{$dataset_url}/{$filename}' style='width: 120px; height: 120px; object-fit: cover; border: 2px solid #fff; border-radius: 8px;'>";
                }

                echo "</div><br>";
            } else {
                echo "<p style='color:white;'>Tidak ada foto ditemukan.</p>";
            }

            $dataset_dir = realpath(__DIR__ . '/../../dataset_ujian/' . $username);
            $dataset_url = BASE_URL . '/dataset_ujian/' . $username;


            $fotos = glob($dataset_dir . "/*.{jpg,jpeg,png}", GLOB_BRACE);

            if ($fotos) {
                echo "<h4 style='color:white;'>Foto Ujian:</h4>";
                echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";

                foreach ($fotos as $foto) {
                    $filename = basename($foto);
                    echo "<img src='{$dataset_url}/{$filename}' style='width: 120px; height: 120px; object-fit: cover; border: 2px solid #fff; border-radius: 8px;'>";
                }

                echo "</div><br>";
            } else {
                echo "<p style='color:white;'>Tidak ada foto ditemukan.</p>";
            }
        ?>
    </div>
</div>

<?php
require __DIR__ . '/../../includes/footer.php';
?>