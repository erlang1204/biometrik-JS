<?php
$check_finished_test = $conn->prepare("SELECT * FROM tbl_user WHERE id = ? AND finish_test = 1 LIMIT 1");
$check_finished_test->execute([$_SESSION['user_id']]);
$check_finished_test = $check_finished_test->rowCount();
?>

<div class="d-flex justify-content-center align-items-center w-100" style="max-width: 800px; margin: 0 auto; min-height: 80vh">
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
    <div class="content">
        <div class="card" style="width: 18rem; display: flex; align-items: center; background: rgba(255,255,255,0.5)">
            <img class="card-img-top" style="width: 18rem; padding-top: 2rem;" src="<?= BASE_URL; ?>/assets/images/psikotes.png" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title text-center">CFIT</h5>
                <p class="card-text" style="color: black;">Deretan kotak yang berisi gambar dengan karakteristik serupa. Tugas anda melengkapi kotak sesuai pola.</p>

                <?php if ($check_finished_test > 0): ?>
                    <div class="alert alert-success text-center" role="alert">
                        Anda telah selesai mengerjakan Test.
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL; ?>/modules/subtes/index.php" class="btn btn-secondary mb-2">Lanjut</a>

                    <button id="startButton" class="btn btn-primary mb-2">Verifikasi Wajah</button>

                    <div class="video-container mt-3" style="display: none;">
                        <div class="video-wrapper">
                            <div id="video-overlay">Menunggu verifikasi...</div>
                            <video id="video" width="640" height="480" autoplay muted></video>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- CSS untuk overlay -->
    <style>
        #video-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 10;
        }
        .video-wrapper {
            position: relative;
            display: inline-block;
        }
    </style>

    <!-- Script face-api -->
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js"></script>

    <script>
        let webcamStarted = false;
        const currentUserName = "<?= $_SESSION['username']; ?>";
        const video = document.getElementById("video");
        const overlayText = document.getElementById("video-overlay");

        async function getLabeledFaceDescriptions() {
            const labeledDescriptors = [];
            try {
                const img = await faceapi.fetchImage(`/test/dataset/${currentUserName}/${currentUserName}_1.png`);
                const detection = await faceapi
                    .detectSingleFace(img)
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detection) {
                    labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(currentUserName, [detection.descriptor]));
                }
            } catch (error) {
                console.error(`Gagal memuat data wajah untuk ${currentUserName}:`, error);
            }
            return labeledDescriptors;
        }

        function startWebcam() {
            navigator.mediaDevices.getUserMedia({ video: true }).then((stream) => {
                video.srcObject = stream;
                document.querySelector(".video-container").style.display = "block";
            }).catch((err) => {
                console.error("Gagal mengakses webcam", err);
            });
        }

        document.getElementById("startButton").addEventListener("click", async () => {
            if (!webcamStarted) {
                startWebcam();
                webcamStarted = true;
            }

            await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

            const labeledFaceDescriptors = await getLabeledFaceDescriptions();
            const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);

            const canvas = faceapi.createCanvasFromMedia(video);
            document.querySelector(".video-wrapper").appendChild(canvas);

            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);

            video.addEventListener("play", () => {
                const interval = setInterval(async () => {
                    const detections = await faceapi
                        .detectAllFaces(video)
                        .withFaceLandmarks()
                        .withFaceDescriptors();

                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

                    const results = resizedDetections.map((d) => faceMatcher.findBestMatch(d.descriptor));

                    results.forEach((result, i) => {
                        const box = resizedDetections[i].detection.box;
                        const drawBox = new faceapi.draw.DrawBox(box, { label: result.label });
                        drawBox.draw(canvas);

                        if (result.label === currentUserName) {
                            overlayText.innerText = "✅ Halo, " + currentUserName;
                            overlayText.style.backgroundColor = "rgba(0, 128, 0, 0.7)";
                            clearInterval(interval); // Stop loop saat cocok
                        } else {
                            overlayText.innerText = "❌ Tidak dikenali";
                            overlayText.style.backgroundColor = "rgba(255, 0, 0, 0.7)";
                        }
                    });

                    if (results.length === 0) {
                        overlayText.innerText = "Tidak ada wajah terdeteksi";
                        overlayText.style.backgroundColor = "rgba(0, 0, 0, 0.7)";
                    }
                }, 500);
            });
        });
    </script>
</div>
