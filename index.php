<?php
session_start();
require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] == "admin") {
    header("Location: " . BASE_URL . "/modules/admin/index.php");
    exit();
  }
  header("Location: " . BASE_URL . "/modules/user/index.php");
  exit();
}

?>

<?php
require __DIR__ . '/includes/header.php';
?>
<main class="d-flex justify-content-center align-items-center w-100" style="margin: 0 auto; min-height: 100vh">

  <!-- Login Area -->

  <div class="login-container">

    <?php
    $message = "";
    $alert = "success";
    $show_alert = false;
    if (isset($_SESSION['login_error'])) {
      $message = $_SESSION['login_error'];
      $alert = "danger";
      $show_alert = true;
      unset($_SESSION['login_error']);
    } else if (isset($_SESSION['register_user_success'])) {
      $message = $_SESSION['register_user_success'];
      $alert = "success";
      $show_alert = true;
      unset($_SESSION['register_user_success']);
    } else if (isset($_SESSION['register_user_exists'])) {
      $message = $_SESSION['register_user_exists'];
      $alert = "danger";
      $show_alert = true;
      unset($_SESSION['register_user_exists']);
    } else if (isset($_SESSION['register_errors'])) {
      $message = $_SESSION['register_errors'];
      $alert = "danger";
      $show_alert = true;
      unset($_SESSION['register_errors']);
    }
    ?>
    <div class="login-form" id="loginForm" style="display: flex; flex-direction: column; align-items: center;">
      <?php if ($show_alert) { ?>
        <div class="alert alert-<?= $alert; ?> mb-3" role="alert">
          <?= $message; ?>
        </div>
      <?php } ?>
      <img src="<?= BASE_URL; ?>/assets/images/logo.png" alt="Logo" height="100">
      <h2 class="text-center">Log In Your Account</h2>
      <p class="text-center">Welcome! Please Enter Your Details.</p>
      <form action="<?= BASE_URL; ?>/modules/auth/login.php" method="POST">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" class="form-control" id="username" name="username">
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" class="form-control" id="password" name="password">
          <input hidden type="text" name="status"></input>
        </div>
        <button type="submit" class="btn btn-secondary login-btn form-control">Login</button>
        <br />
        <p id="register">No Account?<span style="color:#ffb300; text-decoration:none;" class="switch-form-link-register" onclick="showRegistrationForm()"> Register Here.</span></p>
        <p>Open exam results <a href="<?= BASE_URL; ?>/decrypt-form.php" style="color:#ffb300;text-decoration:none;">Here.</a></p>

      </form>
    </div>

  </div>

  <!-- TESTING GIT AE BROO -->

  <!-- Registration Area -->
  <div class="registration-form" id="registrationForm">
    <h2 class="text-center">Registration Form</h2>
    <p class="text-center">Fill in your personal details.</p>
    <form action="<?= BASE_URL; ?>/modules/auth/register.php" method="POST" enctype="multipart/form-data">
      <!-- Data Diri -->
      <div class="form-group registration row">
        <div class="col-12">
          <label for="name">Name:</label>
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      <div class="form-group registration row">
        <div class="col-5">
          <label for="contactNumber">Contact Number:</label>
          <input type="number" class="form-control" id="contactNumber" name="contact_number" maxlength="11">
        </div>
        <div class="col-7">
          <label for="email">Email:</label>
          <input type="text" class="form-control" id="email" name="email">
        </div>
      </div>
      <div class="form-group registration">
        <label for="registerUsername">Username:</label>
        <input type="text" class="form-control" id="registerUsername" name="username">
      </div>
      <div class="form-group registration">
        <label for="registerPassword">Password:</label>
        <input type="password" class="form-control" id="registerPassword" name="password">
      </div>
      <input type="hidden" name="key" value="tes">

      <!-- Webcam + Gambar Wajah -->
      <div class="form-group">
        <label class="fw-bold">Ambil Gambar Wajah (5x)</label>
        <div class="text-center mb-2">
          <video id="webcam" autoplay muted playsinline width="320" height="240" style="border:1px solid #ccc; display:none;"></video>
          <div id="countdown" class="alert alert-warning fw-bold text-center" style="display: none;"></div>
          <button type="button" class="btn btn-outline-primary" onclick="takeMultipleImages()">📷 Ambil Gambar Wajah</button>
          <p class="mt-1 mb-0" style="font-size: 0.9rem; color: #ffff">Klik tombol untuk mulai ambil 5 foto wajah</p>
        </div>
        <div id="multiple-images" class="d-flex flex-wrap gap-3 justify-content-center mt-3"></div>
      </div>

      <!-- Hidden Input Base64 -->
      <div class="d-none">
        <input type="file" name="capturedImage1" id="img1">
        <input type="file" name="capturedImage2" id="img2">
        <input type="file" name="capturedImage3" id="img3">
        <input type="file" name="capturedImage4" id="img4">
        <input type="file" name="capturedImage5" id="img5">
      </div>

      <!-- Submit -->
      <p>Already have an account? Login <span style="color:black;" class="switch-form-link" onclick="showLoginForm()">Here.</span></p>
      <button type="submit" class="btn btn-dark login-register form-control" name="register">Register</button>
    </form>
  </div>

  <!-- FaceAPI -->
  <script src="./models/face-api.min.js"></script>
  <script>
    const loginForm = document.getElementById('loginForm');
    const registrationForm = document.getElementById('registrationForm');
    registrationForm.style.display = "none";

    function showRegistrationForm() {
      registrationForm.style.display = "";
      loginForm.style.display = "none";
    }

    function showLoginForm() {
      registrationForm.style.display = "none";
      loginForm.style.display = "flex";
    }

    let currentImage = 1;
    let videoStream = null;

    async function loadModels() {
      await faceapi.nets.tinyFaceDetector.loadFromUri('./models');
    }
    loadModels();
    async function takeMultipleImages() {
      const webcam = document.getElementById("webcam");

      webcam.style.display = "block";
      if (!videoStream) {
        videoStream = await navigator.mediaDevices.getUserMedia({
          video: true
        });
        webcam.srcObject = videoStream;
      }

      for (let i = currentImage; i <= 5; i++) {
        await delayCountdown(i);

        const file = await captureValidatedImage(webcam, i); // <--- file langsung, bukan base64
        if (!file) {
          alert("Wajah tidak terdeteksi. Ulangi.");
          i--;
          continue;
        }

        // ✅ Masukkan file ke input file
        const inputFile = document.getElementById("img" + i);
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        inputFile.files = dataTransfer.files;

        // ✅ Tampilkan preview
        const url = URL.createObjectURL(file);
        renderPreview(url, i);

        currentImage++;
      }
    }


    function delayCountdown(i) {
      return new Promise(resolve => {
        const el = document.getElementById("countdown");
        el.style.display = "block";
        let t = 3;
        const timer = setInterval(() => {
          el.innerHTML = `📸 <strong>Foto ke-${i}</strong> dalam <span class="text-danger">${t}</span> detik...`;
          if (t-- < 0) {
            clearInterval(timer);
            el.style.display = "none";
            resolve();
          }
        }, 100);
      });
    }

    async function captureValidatedImage(video, index) {
      const canvas = document.createElement("canvas");
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(video, 0, 0);
      const detection = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions({
        inputSize: 160
      }));
      if (!detection) return null;
      return await new Promise((resolve) => {
        canvas.toBlob((blob) => {
          if (!blob) return resolve(null);
          const file = new File([blob], `image_${index}.jpg`, {
            type: "image/jpeg"
          });
          resolve(file);
        }, "image/jpeg", 0.95);
      });
    }

    function renderPreview(base64, index) {
      const container = document.getElementById("multiple-images");
      const div = document.createElement("div");
      div.classList.add("text-center");
      div.id = "preview-" + index;
      div.innerHTML = `
      <img src="${base64}" width="100" height="100" class="rounded border border-secondary shadow-sm" alt="Wajah ${index}">
      <p class="text-muted mt-1 mb-1" style="font-size: 0.8rem;">Wajah ${index}</p>
      <button type="button" class="btn btn-sm btn-danger" onclick="retakeImage(${index})">Ulang Foto</button>
    `;
      if (document.getElementById("preview-" + index)) {
        document.getElementById("preview-" + index).replaceWith(div);
      } else {
        container.appendChild(div);
      }
    }

    async function retakeImage(index) {
      const webcam = document.getElementById("webcam");
      webcam.style.display = "block";
      await delayCountdown(index);
      const base64 = await captureValidatedImage(webcam, index);
      if (!base64) {
        alert("Wajah tidak terdeteksi. Ulangi lagi.");
        return;
      }
      document.getElementById("img" + index).value = base64;
      renderPreview(base64, index);
    }
  </script>


  <?php
  require __DIR__ . '/includes/footer.php';
  ?>