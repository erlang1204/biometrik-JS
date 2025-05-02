<?php
session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';
require __DIR__ . '/../../libs/aes.php';

if (!isset($_SESSION['user_verified'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "");
        exit();
    }
    header("Location: " . BASE_URL . "/modules/auth/verification.php");
    exit();
}

$get_id_soal = isset($_GET['id']) ? $_GET['id'] : 1;
$stmt = $conn->prepare("SELECT * FROM `tbl_soal` WHERE `id` = :id");
$stmt->bindParam(':id', $get_id_soal, PDO::PARAM_INT);
$stmt->execute();

$data_soal = $stmt->fetch(PDO::FETCH_ASSOC); // Mengambil baris sebagai array asosiatif
if (!empty($data_soal)) {
    $soal_id = $data_soal['id'];
    $soal_gambar = $data_soal['gambar'];
}

// jawaban
$stmt_jawaban = $conn->prepare("SELECT jawaban FROM `tbl_jawaban` WHERE `soal_id` = :soal_id AND `user_id` = :user_id LIMIT 1");
$stmt_jawaban->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt_jawaban->bindParam(':soal_id', $get_id_soal, PDO::PARAM_INT);
$stmt_jawaban->execute();

$data_jawaban = $stmt_jawaban->fetch(PDO::FETCH_ASSOC);
$jawaban = '';
if (!empty($data_jawaban)) {
    $jawaban = $data_jawaban['jawaban'];
}

// timer
$check_start_time = $conn->prepare("SELECT `start_time` FROM `tbl_user` WHERE `id` = :id");
$check_start_time->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
$check_start_time->execute();
$check_start_time = $check_start_time->fetch(PDO::FETCH_ASSOC);

// Set timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');
$start_time = date('Y-m-d H:i:s');

// Cek apakah user sudah memulai tes
if (empty($check_start_time['start_time'])) {

    $stmt_insert_time = $conn->prepare("UPDATE `tbl_user` SET `start_time` = :start_time WHERE `id` = :id");
    $stmt_insert_time->bindParam(':start_time', $start_time, PDO::PARAM_STR);
    $stmt_insert_time->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt_insert_time->execute();
} else {
    $start_time = $check_start_time['start_time'];
}

// timer
$time_left = 0;
$waktu_masuk = strtotime($start_time);
$current_time = time();
$time_elapsed = $current_time - $waktu_masuk;
$total_time = 180; // 3 x 60 detik
$time_left = $total_time - $time_elapsed; // sisa waktu dalam detik

if ($time_left <= 0) {
    header("Location: " . BASE_URL . "/modules/user/index.php");
    exit();
}
?>

<?php
require __DIR__ . '/../../includes/header.php';
?>

<?php
require __DIR__ . '/../../includes/navbar.php';
?>

<div class="d-flex justify-content-center align-items-center w-100" style="margin: 40px auto; height: 100%">
    <div class="row" style="width: 100%; display:flex; justify-content:center;">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Timer : <span id="timer"></span></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center mb-3" style="gap: 10px;">
                        <a id="1" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=1" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">1</a>
                        <a id="2" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=2" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center;background-color: #cfe4ff;">2</a>
                        <a id="3" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=3" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">3</a>
                    </div>
                    <div class="d-flex justify-content-center mb-3" style="gap: 10px;">
                        <a id="4" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=4" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">4</a>
                        <a id="5" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=5" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">5</a>
                        <a id="6" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=6" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">6</a>
                    </div>
                    <div class="d-flex justify-content-center mb-3" style="gap: 10px;">
                        <a id="7" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=7" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">7</a>
                        <a id="8" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=8" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">8</a>
                        <a id="9" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=9" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">9</a>
                    </div>
                    <div class="d-flex justify-content-center mb-3" style="gap: 10px;">
                        <a id="10" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=10" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">10</a>
                        <a id="11" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=11" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">11</a>
                        <a id="12" class="btn btn-primary nomor" href="<?= BASE_URL ?>/modules/subtes/index.php?id=12" style="width: 10rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">12</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8" style="padding: 0">

            <div class="card">
                <div class="card-body" style="display:flex; flex-direction: column;">
                    <p class="card-title" style="margin-left: 3rem;"><?= $soal_id ?>. Tugas anda adalah mengisi
                        kotak
                        yang masih kosong sesuai dengan pilihan yang tersedia. Perlu diingat bahwa setiap gambar pada
                        kotak tersebut memiliki pola tertentu. Anda perlu mengetahui pola tersebut untuk menjawab soal.
                        Pilih jawaban yang sesuai dengan pola.</p>
                    <img class="card-img-top" style="width: 32rem; padding-top: 0rem; padding-bottom: 1rem; margin-left: 14rem;" src="<?= BASE_URL . '/assets/images/' . $soal_gambar ?>" alt="Card image cap">
                    <div class="d-flex justify-content-center mb-3" style="gap: 78px;">
                        <a id="a" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">A</a>
                        <a id="b" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center;  background-color: #cfe4ff;">B</a>
                        <a id="c" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center;  background-color: #cfe4ff;">C</a>
                    </div>
                    <div class="d-flex justify-content-center mb-3" style="gap: 78px;">
                        <a id="d" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center;   background-color: #cfe4ff;">D</a>
                        <a id="e" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center;  background-color: #cfe4ff;">E</a>
                        <a id="f" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center;  background-color: #cfe4ff;">F</a>
                    </div>
                </div>

                <!-- Modal Selesai -->
                <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Konfirmasi</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                Apakah Anda yakin sudah selesai?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary" id="confirmFinish">Ya, Selesai</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if ($soal_id == 1) {
                ?>
                    <div class="d-flex justify-content-between" style="padding-bottom: 1rem">
                        <div class="content" style="padding-left: 1rem">
                            <a style="width: 8rem;height: 3rem; display: flex;justify-content: center;align-items: center;"></a>
                        </div>
                        <div style="padding-right: 1rem">
                            <a id="next" class="btn btn-primary" style="width: 8rem;height: 3rem; display: flex;justify-content: center;align-items: center; background-color: #80bdff" data-url="<?= BASE_URL ?>/modules/subtes/index.php?id=<?= $soal_id + 1 ?>">Selanjutnya</a>
                        </div>
                    </div>
                <?php
                } elseif ($soal_id > 1 && $soal_id < 12) {
                ?>
                    <div class="d-flex justify-content-between" style="padding-bottom: 1rem">
                        <div class="content" style="padding-left: 1rem">
                            <a class="btn btn-danger" style="width: 8rem;height: 3rem; display: flex;justify-content: center;align-items: center;" href="<?= BASE_URL ?>/modules/subtes/index.php?id=<?= $soal_id - 1 ?>">sebelumnya</a>
                        </div>
                        <div style="padding-right: 1rem">
                            <a id="next" class="btn btn-primary" style="width: 8rem;height: 3rem; display: flex;justify-content: center;align-items: center; background-color: #80bdff" data-url="<?= BASE_URL ?>/modules/subtes/index.php?id=<?= $soal_id + 1 ?>">Selanjutnya</a>
                        </div>
                    </div>
                <?php
                } elseif ($soal_id == 12) {
                ?>
                    <div class="d-flex justify-content-between" style="padding-bottom: 1rem">
                        <div class="content" style="padding-left: 1rem">
                            <a class="btn btn-danger" style="width: 8rem;height: 3rem; display: flex;justify-content: center;align-items: center;" href="<?= BASE_URL ?>/modules/subtes/index.php?id=<?= $soal_id - 1 ?>">sebelumnya</a>
                        </div>
                        <div style="padding-right: 1rem">
                            <a id="finish" class="btn btn-primary" style="width: 8rem; height: 3rem; display: flex; justify-content: center; align-items: center; background-color: #80bdff" data-toggle="modal" data-target="#confirmationModal">Selesai</a>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>


<?php
require __DIR__ . '/../../includes/footer.php';
?>

<script type="text/javascript">
    var timeLeft = <?= $time_left; ?>;

    function updateTimer() {
        var minutes = Math.floor(timeLeft / 60);
        var seconds = timeLeft % 60;

        var formattedMinutes = ('0' + minutes).slice(-2);
        var formattedSeconds = ('0' + seconds).slice(-2);

        document.getElementById('timer').innerText = formattedMinutes + ":" + formattedSeconds;

        if (timeLeft <= 0) {
            $.ajax({
                url: "<?= BASE_URL ?>/modules/subtes/finish.php",
                type: "POST",
                success: function(response) {
                    alert('Waktu habis!');
                    console.log(response);
                    window.location.href = "<?= BASE_URL ?>/modules/user/index.php";
                }
            })
        } else {
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateTimer();
    });

    document.getElementById('confirmFinish').addEventListener('click', function() {
        $.ajax({
            url: "<?= BASE_URL ?>/modules/subtes/finish.php",
            type: "POST",
            success: function(response) {
                console.log(response);
            }
        })
    });
    $(document).ready(function() {
        const buttons = document.querySelectorAll('.answer-btn');
        var selectedAnswerId = '<?= $jawaban; ?>';
        var userId = <?= $_SESSION['user_id']; ?>;
        var soalId = <?= $soal_id; ?>;

        buttons.forEach(button => {
            if (selectedAnswerId == button.id) {
                button.style.backgroundColor = 'blue';
            }
            button.addEventListener('click', function() {
                // Reset background color for all buttons
                buttons.forEach(btn => btn.style.backgroundColor = '#cfe4ff');
                // Set background color for the clicked button
                this.style.backgroundColor = 'blue'; // Or use a CSS class like 'bg-success'

                // Store the clicked ID in a variable
                selectedAnswerId = this.id;
            });
        });

        $('#next').on('click', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

            if (selectedAnswerId) {
                $.ajax({
                    url: "<?= BASE_URL ?>/modules/subtes/save_answer.php",
                    type: 'POST',
                    data: {
                        answerId: selectedAnswerId,
                        soalId: soalId
                    },
                    success: function(response) {
                        console.log(response);
                        window.location.href = url;
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr);
                        alert('Failed to save answer');
                    }
                });
            } else {
                alert('Please select an answer before proceeding.');
            }
        });

        $('#finish').on('click', function(e) {
            e.preventDefault();

            if (selectedAnswerId) {
                $.ajax({
                    url: "<?= BASE_URL ?>/modules/subtes/save_answer.php",
                    type: 'POST',
                    data: {
                        answerId: selectedAnswerId,
                        soalId: soalId
                    },
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr);
                        alert('Failed to save answer');
                    }
                });
            } else {
                alert('Please select an answer before finishing brooo.');
            }
        });

    });
</script>