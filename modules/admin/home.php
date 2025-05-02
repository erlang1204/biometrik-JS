<?php

?>
<div class="d-flex justify-content-center align-items-center w-100" style="margin: 40px auto; min-height: 80vh;">
    <div class="content p-4">
        <?php
            $message = "";
            $alert = "success";
            $show_alert = false;
            if (isset($_SESSION['delete_user_success'])) {
                $message = $_SESSION['delete_user_success'];
                $alert = "danger";
                $show_alert = true;
                unset($_SESSION['delete_user_success']);
            } else if (isset($_SESSION['update_profile_success'])) {
                $message = $_SESSION['update_profile_success'];
                $alert = "success";
                $show_alert = true;
                unset($_SESSION['update_profile_success']);
            }
        ?>
        <h4 class="my-3">List of users</h4>
        <hr>
        <table class="table table-hover table-collapse overflow-auto w-100">
            <thead>
                <tr>
                    <th scope="col">User ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Contact Number</th>
                    <th scope="col">Email</th>
                    <th scope="col">Username</th>
                    <th scope="col">Password</th>
                    <th scope="col">Code OTP</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>

                <?php
                    $stmt = $conn->prepare("SELECT * FROM `tbl_user`");
                    $stmt->execute();

                    $result = $stmt->fetchAll();

                    foreach ($result as $row) {
                        $userID = $row['id'];
                        $name = $row['name'];
                        $contactNumber = $row['contact_number'];
                        $email = $row['email'];
                        $username = $row['username'];
                        $password = $row['password'];
                        $verification_code = $row['verification_code'];
                ?>
                    <tr>
                        <td id="userID-<?= $userID ?>"><?php echo $userID ?></td>
                        <td id="name-<?= $userID ?>"><?php echo $name ?></td>
                        <td id="contactNumber-<?= $userID ?>"><?php echo $contactNumber ?></td>
                        <td id="email-<?= $userID ?>"><?php echo $email ?></td>
                        <td id="username-<?= $userID ?>"><?php echo $username ?></td>
                        <td id="password-<?= $userID ?>"><?php echo $password ?></td>
                        <td id="verification_code-<?= $userID ?>"><?php echo $verification_code ?></td>
                        <td>
                            <div class="d-flex justify-content-center align-items-center" style="gap: 4px;">
                                <button id="sendbtn" style="width: 40px; height: 40px; font-size: 22px; line-height: 22px; padding: 2px;" class="btn bg-info text-white" onclick="send_otp('<?php echo $email; ?>', <?php echo $userID; ?>)" title="Send OTP">&#9993;</button>
                                <button id="deleteBtn" style="width: 40px; height: 40px; font-size: 22px; line-height: 22px; padding: 2px;" class="btn bg-danger text-white" onclick="delete_user(<?php echo $userID ?>)" title="Delete User">&#128465;</button>
                                <a title="Cek Hasil" style="width: 40px; height: 40px; font-size: 22px; line-height: 32px; padding: 2px;" class="btn bg-white" href="<?= BASE_URL; ?>/modules/admin/cek-hasil.php?id=<?= $userID ?>">&#128065;</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    // Send OTP Via Email
    function send_otp(email, id) {
        if (confirm("Do you want to send OTP to this email?")) {
            window.location.href = "<?= BASE_URL; ?>/modules/admin/send-otp.php?user_id=" + id;
        }
    }
    // Delete user
    function delete_user(id) {
        if (confirm("Do you want to delete this user?")) {
            var id = id;

            // Delete User
            $.ajax({
                url: "<?= BASE_URL; ?>/modules/admin/delete-user.php",
                type: "POST",
                data: {
                    id: id
                },
                success: function(data) {
                    console.log(data);

                    if (data == "success") {
                        alert("User deleted successfully");
                        window.location.href = "<?= BASE_URL; ?>/modules/admin/index.php";
                    }
                }
            });
        }
    }
</script>