<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "");
}

$stmt = $conn->prepare("SELECT `id`, `name`, `email`, `contact_number`, `username` FROM `tbl_user` WHERE `id` = :id LIMIT 1");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();

$data_user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary" style="width: 100%;">
    <div class="container">
        <a class="navbar-brand mr-auto" href="<?= BASE_URL; ?>">
            <img src="<?= BASE_URL; ?>/assets/images/logo.png" alt="Logo" height="30" class="d-inline-block align-top mr-2">
            PT.Sinar Metrindo Perkasa
        </a>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle rounded" type="button" data-toggle="dropdown" aria-expanded="false">Hello, <?= $_SESSION['name']; ?></button>
            <div class="dropdown-menu dropdown-menu-right p-3">
                <a type="button" class="dropdown-item mb-2" data-toggle="modal" data-target="#updateUserModal">Update Profile</a>
                <a type="button" href="<?= BASE_URL; ?>/modules/auth/logout.php" id="logoutButton" class="btn btn-danger d-block">Log Out</a>
            </div>
        </div>
    </div>
</nav>

<!-- Update Modal -->
<div class="modal fade mt-5" id="updateUserModal" tabindex="-1" aria-labelledby="updateUser" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateUserModal">Update User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?= BASE_URL; ?>/modules/user/update.php" method="POST">
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="name">Name:</label>
                            <input value="<?= $data_user['name']; ?>" type="text" class="form-control" id="name" name="name">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-5">
                            <label for="updateContactNumber">Contact Number:</label>
                            <input value="<?= $data_user['contact_number']; ?>" type="number" class="form-control" id="updateContactNumber" name="contact_number" maxlength="11">
                        </div>
                        <div class="col-7">
                            <label for="updateEmail">Email:</label>
                            <input value="<?= $data_user['email']; ?>" type="text" class="form-control" id="updateEmail" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="updateUsername">Username:</label>
                        <input value="<?= $data_user['username']; ?>" type="text" class="form-control" id="updateUsername" name="username">
                    </div>
                    <div class="form-group">
                        <label for="updatePassword">Password:</label>
                        <input type="text" class="form-control" id="updatePassword" name="password">
                    </div>
                    <button type="submit" class="btn btn-dark login-register form-control">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>