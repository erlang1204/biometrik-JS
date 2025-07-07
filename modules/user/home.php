<?php
session_start();
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
$check_finished_test = $conn->prepare("SELECT * FROM tbl_user WHERE id = ? AND finish_test = 1 LIMIT 1");
$check_finished_test->execute([$_SESSION['user_id']]);
$check_finished_test = $check_finished_test->rowCount();
$currentUserName = $_SESSION['username'];
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
        const video = document.getElementById("video");
        const overlayText = document.getElementById("video-overlay");
  const videoContainer = document.querySelector(".video-container");

        async function getLabeledFaceDescriptions() {
            const labeledDescriptors = [];

            // ambil 5 gambar dataset sesuai username
            for (let i = 1; i <= 5; i++) {
                try {
                const img = await faceapi.fetchImage(
                    `/dataset/${currentUserName}/${currentUserName}_${i}.png`
                );

                // deteksi wajah pada masing-masing 5 gambar. 
                // faceapi.detectAllFaces() digunakan untuk mendeteksi wajah pada gambar.
                // Jika ditemukan wajah, faceapi akan menambahkan:
                // Landmarks: Titik-titik penting seperti mata, hidung, mulut
                // Descriptor: Vektor wajah 128-dimensi

                const detections = await faceapi
                    .detectSingleFace(img)
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                // detectSingleFace = deteksi wajah pada gambar
                // withFaceLandmarks = deteksi titik pada wajah
                // withFaceDescriptor = menghasilkan vektor wajah / mengkodekan ke bentuk numerik, gunanya untuk memberikan nilai numerik masing-masing wajah dan untuk perbandingan dengan wajah lain
                // semakin kecil nilai numerik, semakin mirip
                // membandingkan wajah dengan euclideanDistance

                
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

        // tampilkan webcam
        function startWebcam() {
            navigator.mediaDevices.getUserMedia({ video: true }).then((stream) => {
                video.srcObject = stream;
                document.querySelector(".video-container").style.display = "block";
            }).catch((err) => {
                console.error("Gagal mengakses webcam", err);
            });
        }

        // klik tombol Verifikasi Wajah
        document.getElementById("startButton").addEventListener("click", async () => {
            if (!webcamStarted) {
                startWebcam();
                webcamStarted = true;
            }


            // ssdMobilenetv1: Model deteksi wajah cepat & akurat.
            // faceLandmark68Net: Deteksi 68 titik landmark wajah.
            // faceRecognitionNet: Ekstraksi face descriptor (128 angka).
            // load semua fungsi di bawah ke browser agar bisa pakai

            await faceapi.nets.ssdMobilenetv1.loadFromUri('./models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('./models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('./models');


            // simpan hasil array getLabeledFaceDescriptions (vektor wajah gambar) ke variabel baru
            const labeledFaceDescriptors = await getLabeledFaceDescriptions();

            // mencocokan hasil scan wajah pada video dengan gambar
            // numerik atau kode wajah dan kode video dicocokan
            // kalo > 0.6 berarti ga mirip
            const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);

            // Buat kanvas untuk menggambar kotak wajah dan label nama.
            const canvas = faceapi.createCanvasFromMedia(video);
            document.querySelector(".video-wrapper").appendChild(canvas);
            console.log("Menambahkan canvas ke video-wrapper");

            // custom ukuran canvas aja
            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);
            console.log('sampe sini ga sih');
            

                const interval = setInterval(async () => {
                    const detections = await faceapi
                        .detectAllFaces(video)
                        .withFaceLandmarks()
                        .withFaceDescriptors();

                    // deteksi wajah pada masing-masing video. 
                    // faceapi.detectAllFaces() digunakan untuk mendeteksi wajah pada video.
                    // Jika ditemukan wajah, faceapi akan menambahkan:
                    // Landmarks: Titik-titik penting seperti mata, hidung, mulut dari video
                    // Descriptor: Vektor wajah 128-dimensi dri video

                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

                    // Setiap wajah dalam frame dibandingkan dengan data yang sudah dikenali
                    // hasil berupa nama
                    // Wajah terdeteksi dari webcam.
                    // Descriptor-nya dihitung.
                    // Dicari descriptor mana yang paling mirip dari database.
                    // Jika jarak < 0.6, wajah dianggap cocok → label dikembalikan.
                    // Jika tidak, hasilnya 'unknown'.

                    const results = resizedDetections.map((d) => faceMatcher.findBestMatch(d.descriptor));
                    
                    console.log(faceMatcher);

                    // result: hasil pencocokan wajah ke-i.
                    // resizedDetections[i]: deteksi wajah ke-i yang sudah di-resize agar sesuai dengan ukuran video.
                    // resizedDetections[i].detection.box: koordinat posisi wajah (bounding box).
                    // faceapi.draw.DrawBox(): menggambar kotak di wajah, dengan label (nama).
                    // drawBox.draw(canvas): menggambar kotak pada elemen canvas.
                    // Jadi, ini adalah proses visualisasi wajah yang dikenali pada layar webcam.

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
                    console.log('Wajah dikenali dan cocok.');

                    // Redirect setelah 2 detik (opsional, bisa langsung jika mau)
                    setTimeout(() => {
                    // url masih belom update untuk lempar ke soal
                    window.location.href = "<?= BASE_URL; ?>/modules/subtes/guide.php";
                    }, 2000);
                    
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
