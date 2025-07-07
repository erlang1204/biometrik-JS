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

$currentUserName = $_SESSION['username'];


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
$total_time = 108000; // 3 x 60 detik
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

<div class="d-flex justify-content-center align-items-center w-100" style="margin: 4px auto; height: 100%">
    <div class="row" style="width: 100%; display:flex; justify-content:center;" id="renderSoal">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Timer : <span id="timer"></span></h5>
                </div>
                <div id="renderPilihanSoal">
                    <div class="card" id="pilihanForm">
                    </div>
                </div>
                
                
            </div>
            <div class="video-container mt-3" style="display: none;">
                        <div class="video-wrapper">
                            <div id="video-overlay">Menunggu verifikasi...</div>
                            <!-- <video id="video" width="640" height="480" autoplay muted></video> -->
                               <video id="video" width="640" height="480" autoplay></video>
                <canvas id="overlay"></canvas>
                        </div>
                    </div>
        </div>
        <div class="col-md-8" style="padding: 0">
        <div class="card" id="dynamicForm">
            <!-- Soal pertama akan dimuat secara default oleh JS -->
        </div>
     

    </div>
</div>


<?php
require __DIR__ . '/../../includes/footer.php';
?>

<style>
.fixed-webcam {
    position: fixed;
    bottom: 10px;
    left: 10px;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.2);
    padding: 5px;
    border-radius: 10px;
}

.video-wrapper {
    position: relative;
    width: 300px;
    height: 225px;
}

#video {
    position: absolute;
    top: -9px;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Tambahkan style ini untuk canvas hasil face-api */
.video-wrapper canvas {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
    width: 100% !important;
    height: 100% !important;
}
#video-overlay {
    position: absolute;
    top: -5px;
    left: 10px;
    z-index: 2;
    color: white;
    padding: 5px 10px;
    background-color: rgba(0, 0, 0, 0.5);
    font-weight: bold;
    border-radius: 5px;
}
</style>

<script defer src="../../assets/face_logics/face-api.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<!-- Bootstrap 5 Bundle (sudah termasuk Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


     
<script>
let currentSoal = 1;
const totalSoal = 12;
let selectedAnswerId = null;

function renderPilihanSoal(no){
    const pilihanSoalHtml = `
    <div class="card-body">
        ${[1,2,3,4,5,6,7,8,9,10,11,12].reduce((html, n, i) => {
            if (i % 3 === 0) html += `<div class="d-flex justify-content-center mb-3" style="gap: 9px;">`;
            html += `<button class="btn btn-primary nomor" data-soal="${n}" style="width: 4rem;height: 49px;">${n}</button>`;
            if (i % 3 === 2) html += `</div>`;
            return html;
        }, '')}
    </div>`;
    
    document.getElementById('pilihanForm').innerHTML = pilihanSoalHtml;

    // Tambahkan listener setelah render
    document.querySelectorAll('.nomor').forEach(btn => {
        btn.addEventListener('click', () => {
            const nomor = parseInt(btn.getAttribute('data-soal'));
            currentSoal = nomor;
            renderSoal(nomor);
            renderPilihanSoal(nomor); // render ulang supaya highlight aktif bisa diatur
        });
    });
}

    document.querySelectorAll('.nomor').forEach(btn => {
        const nomor = parseInt(btn.getAttribute('data-soal'));
        if (nomor === no) {
            btn.classList.add('active');
            btn.style.backgroundColor = "#0d6efd";
            btn.style.color = "#fff";
        } else {
            btn.classList.remove('active');
            btn.style.backgroundColor = "#cfe4ff";
            btn.style.color = "#000";
        }
    });


function renderSoal(no) {
    const baseUrl = "<?= BASE_URL ?>";
    const soalHtml = `
    
        <div class="card-body" style="display:flex; flex-direction: column;">
            <p class="card-title" style="margin-left: 3rem;">${no}. Tugas anda adalah mengisi kotak yang masih kosong sesuai dengan pilihan yang tersedia. Perlu diingat bahwa setiap gambar pada kotak tersebut memiliki pola tertentu. Anda perlu mengetahui pola tersebut untuk menjawab soal. Pilih jawaban yang sesuai dengan pola.</p>
            <img class="card-img-top" style="width: 32rem; padding-top: 0rem; padding-bottom: 1rem; margin-left: 14rem;" src="${baseUrl}/assets/images/no${no}.png" alt="Soal Gambar ${no}">
            
            <div class="d-flex justify-content-center mb-3" style="gap: 78px;">
                <a id="a" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">A</a>
                <a id="b" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">B</a>
                <a id="c" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">C</a>
            </div>
            <div class="d-flex justify-content-center mb-3" style="gap: 78px;">
                <a id="d" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">D</a>
                <a id="e" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">E</a>
                <a id="f" class="btn answer-btn" style="width: 6rem;height: 4rem; display: flex;justify-content: center;align-items: center; background-color: #cfe4ff;">F</a>
            </div>

            <div class="d-flex justify-content-between" style="padding-bottom: 0rem;">
                <div class="content" style="padding-left: 1rem">
                    ${no > 1 ? `<button id="prevBtn" class="btn btn-danger" style="width: 8rem; height: 3rem;">Sebelumnya</button>` : ''}
                </div>
                <div style="padding-right: 1rem">
                    ${no < totalSoal ? 
                        `<button id="nextBtn" class="btn btn-primary" style="width: 8rem; height: 3rem;">Selanjutnya</button>` : `` 
                    }
                    ${no == totalSoal ?  
                        `<button id="finishBtn" class="btn btn-success" data-toggle="modal" data-target="#confirmationModal" style="width: 8rem; height: 3rem;">Selesai</button>` : ``
                    }
                </div>
            </div>
        </div>
    `;

    document.getElementById('dynamicForm').innerHTML = soalHtml;
    

    attachAnswerListeners();
    attachNavListeners();
}

function attachAnswerListeners() {
    const buttons = document.querySelectorAll('.answer-btn');
    buttons.forEach(button => {
        button.style.backgroundColor = (selectedAnswerId === button.id) ? 'blue' : '#cfe4ff';
        button.addEventListener('click', function () {
            buttons.forEach(btn => btn.style.backgroundColor = '#cfe4ff');
            this.style.backgroundColor = 'blue';
            selectedAnswerId = this.id;
        });
    });
}

function saveAnswer(callback) {
    if (!selectedAnswerId) {
        alert('Silakan pilih jawaban terlebih dahulu!');
        return;
    }

    $.ajax({
        url: "<?= BASE_URL ?>/modules/subtes/save_answer.php",
        type: 'POST',
        data: {
            answerId: selectedAnswerId,
            soalId: currentSoal,
            
        },
        success: function (response) {
            console.log("Jawaban tersimpan:", response);
            if (callback) callback();
        },
        error: function (xhr) {
            console.error(xhr);
            alert('Gagal menyimpan jawaban.');
        }
    });
}

function attachNavListeners() {
    const nextBtn = document.getElementById("nextBtn");
    const listSoal = document.getElementById("nomorSoal")
    const prevBtn = document.getElementById("prevBtn");
    const finishBtn = document.getElementById("finishBtn");
    const submitBtn = document.getElementById("submitBtn");

    if(listSoal){
        listSoal.addEventListener("click", () => {
            console.log('test aja broo');
            
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", () => {
            saveAnswer(() => {
                if (currentSoal < totalSoal) {
                    currentSoal++;
                    selectedAnswerId = null;
                    renderSoal(currentSoal);
                }
            });
        });
        nextBtn.addEventListener("click", async () => {
            console.log("bisa klik next nih");
            console.log('soal now :',currentSoal);
            
            overlayText.innerText = "✅ Halo, " + currentUserName;
            overlayText.style.backgroundColor = "rgba(0, 128, 0, 0.7)";
            overlayText.style.display = "block";

            // const canvasSnapshot = document.createElement("canvas");
            // canvasSnapshot.width = video.videoWidth;
            // canvasSnapshot.height = video.videoHeight;

            // const ctx = canvasSnapshot.getContext("2d");
            // ctx.drawImage(video, 0, 0, canvasSnapshot.width, canvasSnapshot.height);

            // const imageBase64 = canvasSnapshot.toDataURL("image/png");

            const videoContainer = document.querySelector(".video-container");
            // Kirim gambar ke backend menggunakan fetch
            // try {
            //     const response = await fetch("save_snapshot.php", {
            //         method: "POST",
            //         headers: {
            //             "Content-Type": "application/json"
            //         },
            //         body: JSON.stringify({
            //             image: imageBase64,
            //             username: currentUserName
            //         })
            //     });

            //     const result = await response.json();
            //     console.log(result);
            //     if (result.success) {
            //         console.log("✅ Gambar berhasil disimpan.");
            //     } else {
            //         console.error("❌ Gagal menyimpan gambar.");
            //     }
            // } catch (error) {
            //     console.error("Terjadi kesalahan saat mengirim gambar:", error);
            // }

            try {
        const canvasSnapshot = await html2canvas(videoContainer, {
            backgroundColor: null, // agar transparan
            useCORS: true // jika ada elemen gambar dari domain lain
        });

        const imageBase64 = canvasSnapshot.toDataURL("image/png");

        // Kirim ke backend
        const response = await fetch("save_snapshot.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                image: imageBase64,
                username: currentUserName
            })
        });

        const result = await response.json();
        console.log(result);
        if (result.success) {
            console.log("✅ Gambar berhasil disimpan.");
        } else {
            console.error("❌ Gagal menyimpan gambar.");
        }
    } catch (error) {
        console.error("Terjadi kesalahan saat mengambil snapshot:", error);
    }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", () => {
            if (currentSoal > 1) {
                currentSoal--;
                selectedAnswerId = null;
                renderSoal(currentSoal);
            }
        });
    }

    if (finishBtn) {
        finishBtn.addEventListener("click", () => {
            saveAnswer(() => {
                $('#confirmationModal').modal('show');
            });
        });
        cancelBtn.addEventListener("click", () => {
            saveAnswer(() => {
                $('#confirmationModal').modal('close');
            });
        });
        // Tombol di dalam modal konfirmasi
    submitBtn.addEventListener('click', function () {
        $.ajax({
            url: "<?= BASE_URL ?>/modules/subtes/finish.php",
            type: "POST",
            success: function (response) {
                console.log("Tes selesai:", response);
                window.location.href = "<?= BASE_URL ?>/modules/user/home.php";
            },
            error: function (xhr) {
                console.error(xhr);
                alert('Gagal menyelesaikan tes.');
            }
        });
    });
    }

    
}

// Tampilkan soal pertama saat halaman dimuat
document.addEventListener("DOMContentLoaded", () => {
    renderSoal(currentSoal);
    renderPilihanSoal(currentSoal);
});
</script>



    <script>
        let webcamStarted = false;
        const currentUserName = "<?= $currentUserName ?>";
        const video = document.getElementById("video");
        const overlayText = document.getElementById("video-overlay");
  const videoContainer = document.querySelector(".video-container");

        async function getLabeledFaceDescriptions() {
            const labeledDescriptors = [];
            for (let i = 1; i <= 5; i++) {
        try {
          const img = await faceapi.fetchImage(
             `/test/dataset/${currentUserName}/${currentUserName}_${i}.png`
          );
          const detections = await faceapi
            .detectSingleFace(img)
            .withFaceLandmarks()
            .withFaceDescriptor();

          if (detections) {
              console.log('ada');
            labeledDescriptors.push(detections.descriptor);
            
          } else {
            console.log(`No face detected in/${i}.png`);
          }
        } catch (error) {
          console.error(`Error processing ${i}.png:`, error);
        }
    }
    return labeledDescriptors;
          
        }

        function startWebcam() {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then((stream) => {
                video.srcObject = stream;
                document.querySelector(".video-container").style.display = "block";
            })
            .catch((err) => {
                console.error("Gagal mengakses webcam", err);
            });
    } else {
        alert("Browser Anda tidak mendukung akses webcam. Silakan gunakan browser terbaru seperti Chrome atau Firefox.");
        console.error("navigator.mediaDevices.getUserMedia tidak tersedia");
    }
}



        document.addEventListener('DOMContentLoaded', async function() {
    if (!webcamStarted) {
        startWebcam();
        webcamStarted = true;
    }

    const modelPath = "https://192.168.1.153:4434/test/models/";
    await faceapi.nets.tinyFaceDetector.loadFromUri(modelPath);
    await faceapi.nets.faceLandmark68Net.loadFromUri(modelPath);
    await faceapi.nets.faceRecognitionNet.loadFromUri(modelPath);

    async function getLabeledFaceDescriptions() {
        const labeledDescriptors = [];
        for (let i = 1; i <= 5; i++) {
            try {
                const img = await faceapi.fetchImage(`/test/dataset/${currentUserName}/${currentUserName}_${i}.png`);
                const detection = await faceapi
                    .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detection) {
                    labeledDescriptors.push(detection.descriptor);
                } else {
                    console.log(`No face detected in ${i}.png`);
                }
            } catch (error) {
                console.error(`Error processing ${i}.png:`, error);
            }
        }
        return labeledDescriptors;
    }

    const labeledFaceDescriptors = await getLabeledFaceDescriptions();
    const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);

    const canvas = faceapi.createCanvasFromMedia(video);
    canvas.setAttribute("id", "overlay");
    document.querySelector(".video-wrapper").appendChild(canvas);

    const displaySize = {
        width: video.offsetWidth,
        height: video.offsetHeight
    };
    faceapi.matchDimensions(canvas, displaySize);

    let hasAlerted = false;

    setInterval(async () => {
    const detections = await faceapi
        .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptors();

    const resizedDetections = faceapi.resizeResults(detections, displaySize);
    canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

    const results = resizedDetections.map((d) => faceMatcher.findBestMatch(d.descriptor));

    results.forEach((result, i) => {
        const box = resizedDetections[i].detection.box;
        
        // Hitung persentase kecocokan
        const distance = result.distance;
        const similarity = Math.max(0, 1 - distance);
        const percentage = Math.round(similarity * 100);

        // Tampilkan label dengan persen
        const labelWithPercent = `${result.label} (${percentage}%)`;
        const drawBox = new faceapi.draw.DrawBox(box, { label: labelWithPercent });
        drawBox.draw(canvas);

        if (result.label.includes("person") || result.label === currentUserName) {
            overlayText.innerText = `✅ Halo, ${currentUserName} (${percentage}%)`;
            overlayText.style.backgroundColor = "rgba(0, 128, 0, 0.7)";
            overlayText.style.display = "block";
        } else {
            overlayText.innerText = `❌ Tidak dikenali (${percentage}%)`;
            overlayText.style.backgroundColor = "rgba(255, 0, 0, 0.7)";
        }
    });

    if (results.length === 0) {
        overlayText.innerText = "Tidak ada wajah terdeteksi";
        overlayText.style.backgroundColor = "rgba(0, 0, 0, 0.7)";
    }
}, 1000);

});

        

    </script>
    


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
    

    
</script>