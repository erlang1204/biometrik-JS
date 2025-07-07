<?php
$check_finished_test = $conn->prepare("SELECT * FROM tbl_user WHERE id = ? AND finish_test = 1 LIMIT 1");
$check_finished_test->execute([$_SESSION['user_id']]);
$check_finished_test = $check_finished_test->rowCount();
$currentUserName = $_SESSION['username'];
?>

<div class="d-flex justify-content-center align-items-center w-100" style="max-width: 800px; margin: 0 auto; min-height: 80vh">
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

                    <!-- <button id="startButton" class="btn btn-primary mb-2">Verifikasi Wajah</button>

                    <div class="video-container mt-3" style="display: none;">
                        <div class="video-wrapper">
                            <div id="video-overlay">Menunggu verifikasi...</div> -->
                            <!-- <video id="video" width="640" height="480" autoplay muted></video> -->
                               <video id="video" width="600" height="450" autoplay></video>
                <canvas id="overlay"></canvas>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>

            
            </div>
        </div>
    </div>

    <!-- CSS untuk overlay -->
<style>
.video-wrapper {
    position: relative;
    width: 640px;
    height: 480px;
}

#video {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 0;
}

canvas {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
}

#video-overlay {
    position: absolute;
    top: 10px;
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

            await faceapi.nets.ssdMobilenetv1.loadFromUri('./models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('./models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('./models');

            const labeledFaceDescriptors = await getLabeledFaceDescriptions();
            const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);

            const canvas = faceapi.createCanvasFromMedia(video);
            document.querySelector(".video-wrapper").appendChild(canvas);
            console.log("Menambahkan canvas ke video-wrapper");

            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);
            console.log('sampe sini ga sih');
            

                const interval = setInterval(async () => {
                    const detections = await faceapi
                        .detectAllFaces(video)
                        .withFaceLandmarks()
                        .withFaceDescriptors();

                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

                    const results = resizedDetections.map((d) => faceMatcher.findBestMatch(d.descriptor));
                    
                    console.log(faceMatcher);
                    results.forEach((result, i) => {
                        const box = resizedDetections[i].detection.box;
                        const drawBox = new faceapi.draw.DrawBox(box, { label: result.label });
                        drawBox.draw(canvas);
                        console.log('ada ga');
                        

                if (
                    result.label === 'person 1' ||
                    result.label === 'person 2' ||
                    result.label === 'person 3' ||
                    result.label === 'person 4' ||
                    result.label === 'person 5'
                ) {
                    overlayText.innerText = "✅ Halo, " + currentUserName;
                    overlayText.style.backgroundColor = "rgba(0, 128, 0, 0.7)";
                    console.log('Ada');
                    }
                else {
                    overlayText.innerText = "❌ Tidak dikenali";
                    overlayText.style.backgroundColor = "rgba(255, 0, 0, 0.7)";
                    console.log('gaada');
                            
                    }
                    });

                    if (results.length === 0) {
                        overlayText.innerText = "Tidak ada wajah terdeteksi";
                        overlayText.style.backgroundColor = "rgba(0, 0, 0, 0.7)";
                        console.log('nah ini gaadaa yang terdetaksi');
                        
                    }
                }, 500);
            // });
        });
    </script>
 
</div>
