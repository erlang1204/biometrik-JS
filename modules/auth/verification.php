<?php
session_start();

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/app.php';
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $verification_code = $_POST['verification_code'];

    if (!empty($verification_code)) {
        $stmt = $conn->prepare("SELECT `id`, `name`, `email` FROM `tbl_user` WHERE `username` = :username and `verification_code` = :verification_code");
        $stmt->execute(['username' => $username, 'verification_code' => $verification_code]);
    
        $check_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($check_user)) {
            $verification_code = rand(100000, 999999);
            $stmt = $conn->prepare("UPDATE `tbl_user` SET `verification_code` = $verification_code WHERE `username` = :username");
            $stmt->execute(['username' => $username]);
    
            $_SESSION['user_verified'] = true;
            header("Location: " . BASE_URL . "/modules/user/index.php");
            exit();
        } else {
            $_SESSION['verification_error'] = "Invalid verification code";
            header("Location: " . BASE_URL . "/modules/auth/verification.php");
            exit();
        }
    } else {
        $_SESSION['verification_error'] = "Please enter verification code";
        header("Location: " . BASE_URL . "/modules/auth/verification.php");
        exit();
    }
}

if (isset($_SESSION['user_verified']) && $_SESSION['user_verified'] == true) {
    header("Location: " . BASE_URL . "");
    exit();
}

?>

<?php
require __DIR__ . '/../../includes/header.php';
?>
<div class="main">
    <div class="d-flex justify-content-center align-items-center w-100 " style="height: 100vh">
        <div class="verification-form" id="loginForm">
            <?php
            $message = "";
            $alert = "success";
            $show_alert = false;
            if (isset($_SESSION['verification_error'])) {
                $message = $_SESSION['verification_error'];
                $alert = "danger";
                $show_alert = true;
                unset($_SESSION['verification_error']);
            }
            ?>
            <?php if ($show_alert) { ?>
                <div class="alert alert-<?= $alert; ?> mb-3" role="alert">
                    <?= $message; ?>
                </div>
            <?php } ?>
            <h2 class="text-center">Verification OTP</h2>
            <p class="text-center">Please ask the admin for a verification code.</p>
            <form action="<?= BASE_URL; ?>/modules/auth/verification.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= $_SESSION['username']; ?>">
                </div>
                <div class="form-group">
                    <label for="verification_code">Verification Code:</label>
                    <input type="number" class="form-control" id="verification_code" name="verification_code">
                </div>
                <button type="submit" class="btn btn-secondary login-btn form-control mt-4" name="verify">Verify</button>
                <a href="<?= BASE_URL; ?>/modules/auth/logout.php" type="button" class="btn btn-danger login-btn form-control mt-2" name="logout">Logout</a>
            </form>
        </div>

    </div>

</div>
<?php
require __DIR__ . '/../../includes/footer.php';
?>