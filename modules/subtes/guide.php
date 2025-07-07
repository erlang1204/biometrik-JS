<?php
session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../libs/aes.php';

if (!isset($_SESSION['user_verified']) || $_SESSION['user_id'] != true) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "");
        exit();
    }
    header("Location: " . BASE_URL . "/modules/auth/verification.php");
    exit();
}

?>

<?php
require __DIR__ . '/../../includes/header.php';
?>

<?php
require __DIR__ . '/../../includes/navbar.php';
?>


<?php
require __DIR__ . '/../../includes/footer.php';
?>
<?php

$check_finished_test = $conn->prepare("SELECT * FROM tbl_user WHERE id = ? AND finish_test = 1 LIMIT 1");
$check_finished_test->execute([$_SESSION['user_id']]);
$check_finished_test = $check_finished_test->rowCount();
?>

<div class="d-flex justify-content-center align-items-center w-100"
    style="max-width: 800px; margin: 0 auto; min-height: 80vh">
    <?php
    $message = "";
    $alert = "success";
    $show_alert = false;

    if (isset($_SESSION['update_profile_success'])) {
        $message = $_SESSION['update_profile_success'];
        $alert = "success";
        $show_alert = true;
        unset($_SESSION['update_profile_success']);
    }
    ?>
    <div class="content" style="margin-top:20px">
        <div class="card" style="width: 30rem; display: flex; align-items: center;background: rgba(255,255,255,0.5)">

            <div class="card-body">
                <h5 class="card-title" style="color: black; display: flex; justify-content: center; ">CFIT</h5>
                <p class="card-text" style="color: black;">"Tugas anda adalah mengisi kotak yang masih kosong sesuai
                    dengan pilihan yang tersedia. Perlu diingat bahwa setiap gambar pada kotak tersebut memiliki pola
                    tertentu. Anda perlu mengetahui pola tersebut untuk menjawab soal. Anda diwajibkan untuk memilih
                    satu jawaban" </p>
                <p class="card-text text-center" style="color: black;">Kunci: 1:C 2:E 3:E </p>
                <div>
                    <img class="card-img-top" style="margin-bottom: 10px;justify-content:center"
                        src="<?= BASE_URL; ?>/assets/images/contoh_subtes.jpeg" alt="Card image cap">
                </div>

                <?php
                if ($check_finished_test > 0) {
                    ?>
                    <div class="alert alert-success text-center" role="alert">
                        Anda telah selesai mengerjakan Test.
                    </div>
                    <?php
                } else {
                    ?>
                    <!-- <a href="<?= BASE_URL; ?>/modules/subtes/index.php" class="btn btn-secondary" style="display: flex; justify-content: center;">Lanjut</a> -->
                    <a href="<?= BASE_URL; ?>/modules/subtes/index.php" class="btn btn-secondary"
                        style="display: flex; justify-content: center;">Lanjut</a>
                    <?php
                }
                ?>

            </div>
        </div>
    </div>
</div>