<?php
include("../conn/conn.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emailPhone = $conn->real_escape_string($_POST['email_phone']);
    $password = $_POST['password'];

    $errorMsg = "";

    if (filter_var($emailPhone, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT id, full_name, email, phone, password FROM users WHERE email = ?";
    } else {
        $sql = "SELECT id, full_name, email, phone, password FROM users WHERE phone = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $emailPhone);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];

            $updateStatus = $conn->prepare("UPDATE users SET status = 'online' WHERE id = ?");
            $updateStatus->bind_param("i", $user['id']);
            $updateStatus->execute();
            $updateStatus->close();

            header("Location: ../dashboard");
            exit();
        } else {
            $errorMsg = "Invalid password.";
        }
    } else {
        $errorMsg = "No user found with given email or phone.";
    }

    $stmt->close();
    $conn->close();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Error</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: '<?= addslashes($errorMsg) ?>',
                    confirmButtonColor: '#d33'
                }).then(() => {
                    window.history.back();
                });
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>