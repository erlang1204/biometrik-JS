<?php
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
$check_finished_test = $conn->prepare("SELECT * FROM tbl_user WHERE id = ? AND finish_test = 1 LIMIT 1");
$check_finished_test->execute([$_SESSION['user_id']]);
$check_finished_test = $check_finished_test->rowCount();
$currentUserName = $_SESSION['username'];
$currentName = $_SESSION['name'];
$currentUserId = $_SESSION['user_id'];
?>

<div class="d-flex justify-content-center align-items-center w-100" style="max-width: 800px; margin: 11px auto">
    <div class="content">
        <div class="card" style="width: 18rem; display: flex; align-items: center; background: rgba(255,255,255,0.5)">
            <img class="card-img-top" style="width: 9rem; padding-top: 2rem;" src="<?= BASE_URL; ?>/assets/images/psikotes.png" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title text-center">CFIT</h5>
                <p class="card-text" style="font-size: 13px; color: black;">Deretan kotak yang berisi gambar dengan karakteristik serupa. Tugas anda melengkapi kotak sesuai pola.</p>

                <?php if ($check_finished_test > 0): ?>
                    <div class="alert alert-success text-center" role="alert">
                        Anda telah selesai mengerjakan Test.
                    </div>
                <?php else: ?>
            </div>
            <div class="d-flex justify-content-center">
                <button id="startButton" class="btn btn-primary mb-2">Verifikasi Wajah</button>
            </div>

        <?php endif; ?>
        <div class="video-container mt-3" style="display: none;">
            <div class="video-wrapper">
                <div id="video-overlay">Menunggu verifikasi...</div>
                <!-- <video id="video" width="640" height="480" autoplay muted></video> -->
                <video id="video" width="640" height="450" autoplay></video>
                <canvas id="overlay"></canvas>
            </div>
        </div>


        </div>
    </div>
</div>

<!-- CSS untuk overlay -->
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

<!-- Script face-api -->
<script defer src="../../assets/face_logics/face-api.min.js"></script>

<script>
    let webcamStarted = false;
    const currentUserName = "<?= $currentUserName ?>";
    const currentName = "<?= $currentName ?>";
    const currentUserId = "<?= $currentUserId ?>";
    const video = document.getElementById("video");
    const overlayText = document.getElementById("video-overlay");
    const videoContainer = document.querySelector(".video-container");
    const baseURL = `<?= BASE_URL; ?>`;

    async function getLabeledFaceDescriptions() {
        const labeledDescriptors = [];

        // Ambil 5 gambar dari folder dataset user
        for (let i = 1; i <= 5; i++) {
            try {
                const img = await faceapi.fetchImage(`${baseURL}/dataset/${currentName}_${currentUserId}/${currentName}_${i}.png`);

                const detections = await faceapi
                    .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detections) {
                    labeledDescriptors.push(detections.descriptor);
                    console.log(`✅ Gambar ${i} berhasil diproses`);
                } else {
                    console.log(`⚠️ Wajah tidak ditemukan di gambar ${i}`);
                }
            } catch (error) {
                console.error(`❌ Error membaca gambar ${i}:`, error);
            }
        }

        return labeledDescriptors;
    }

    function startWebcam() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then((stream) => {
                video.srcObject = stream;
                videoContainer.style.display = "block";
            })
            .catch((err) => {
                console.error("Gagal membuka webcam:", err);
            });
    }

    // 🔁 Fungsi verifikasi 5x berturut-turut
    async function startVerificationLoop(faceMatcher, canvas, displaySize) {
        let successCount = 0;
        let failFlag = false;
        const ctx = canvas.getContext("2d");

        for (let attempt = 1; attempt <= 5; attempt++) {
            overlayText.innerText = `Verifikasi wajah ke-${attempt}...`;
            overlayText.style.backgroundColor = "rgba(0, 0, 0, 0.7)";
            console.log(`⏱️ Verifikasi ke-${attempt} dimulai`);

            const detections = await faceapi
                .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor));

            if (results.length === 0) {
                console.log(`❌ Gagal: Tidak ada wajah`);
                overlayText.innerText = `❌ Tidak ada wajah terdeteksi`;
                overlayText.style.backgroundColor = "rgba(255,0,0,0.7)";
                failFlag = true;
                break;
            }

            if (results.length > 1) {
                console.log(`❌ Gagal: Terdeteksi lebih dari 1 wajah`);
                overlayText.innerText = `❌ Hanya 1 wajah yang diperbolehkan`;
                overlayText.style.backgroundColor = "rgba(255,0,0,0.7)";
                failFlag = true;
                break;
            }

            const result = results[0];
            const box = resizedDetections[0].detection.box;
            new faceapi.draw.DrawBox(box, { label: result.label }).draw(canvas);

            if (result.label === "unknown") {
                console.log(`❌ Gagal: Wajah tidak dikenali`);
                overlayText.innerText = `❌ Wajah tidak dikenali`;
                overlayText.style.backgroundColor = "rgba(255,0,0,0.7)";
                failFlag = true;
                break;
            } else {
                console.log(`✅ Verifikasi ${attempt} sukses: ${result.label}`);
                overlayText.innerText = `✅ Verifikasi ${attempt} berhasil`;
                overlayText.style.backgroundColor = "rgba(0,128,0,0.7)";
                successCount++;
            }

            await new Promise(resolve => setTimeout(resolve, 1000)); // delay 1 detik
        }

        // Evaluasi hasil akhir
        if (!failFlag && successCount === 5) {
            console.log("🎉 Semua verifikasi berhasil. Redirect...");
            overlayText.innerText = `✅ Semua verifikasi berhasil`;
            overlayText.style.backgroundColor = "rgba(0,128,0,0.7)";
            setTimeout(() => {
                window.location.href = "<?= BASE_URL; ?>/modules/subtes/guide.php";
            }, 1500);
        } else {
            console.log("⛔ Verifikasi gagal. Ulangi dalam 3 detik...");
            overlayText.innerText = `❌ Verifikasi gagal. Ulangi...`;
            overlayText.style.backgroundColor = "rgba(255,0,0,0.7)";
            setTimeout(() => {
                startVerificationLoop(faceMatcher, canvas, displaySize); // 🔁 Ulang otomatis
            }, 3000);
        }
    }

    // Tombol Verifikasi ditekan
    document.getElementById("startButton").addEventListener("click", async () => {
        if (!webcamStarted) {
            startWebcam();
            webcamStarted = true;
        }

        await faceapi.nets.tinyFaceDetector.loadFromUri('./models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('./models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('./models');

        const labeledDescriptors = await getLabeledFaceDescriptions();
        const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);

        const canvas = faceapi.createCanvasFromMedia(video);
        canvas.id = "overlay";
        document.querySelector(".video-wrapper").appendChild(canvas);
        const displaySize = { width: video.width, height: video.height };
        faceapi.matchDimensions(canvas, displaySize);

        // Jalankan proses verifikasi 5x
        startVerificationLoop(faceMatcher, canvas, displaySize);
    });
</script>

</div>