<?php
include("../conn/conn.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = isset($_POST['fullName']) ? $conn->real_escape_string($_POST['fullName']) : '';
    $email    = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $phone    = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $about    = ''; // default value for about
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
        die("All fields are required.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $status = "offline";
                $imagePath = 'uploads/' . $newFileName;

                // ✅ Ensure the number of columns and values match
                $sql = "INSERT INTO users (full_name, email, phone, about, image, status, password, image_path, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssss", $fullName, $email, $phone, $about, $newFileName, $status, $hashedPassword, $imagePath);

                if ($stmt->execute()) {
                    header("Location: ../index");
                    exit();
                } else {
                    echo "Database error: " . $stmt->error;
                }
            } else {
                echo "Error uploading the file.";
            }
        } else {
            echo "Only JPG, JPEG, or PNG files are allowed.";
        }
    } else {
        echo "Please upload a profile image.";
    }
}

$conn->close();
?>
