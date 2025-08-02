<?php
session_start();
include '../conn/conn.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: index');
    exit();
}

$userId = $_SESSION['user_id'];

$full_name = trim($_POST['full_name'] ?? '');
$about = trim($_POST['about'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Validate required fields
if (empty($full_name) || empty($email)) {
    $_SESSION['error'] = "Name and Email are required.";
    header("Location: ../dashboard");
    exit();
}

// Handle image upload if any
$image_name = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $fileSize = $_FILES['image']['size'];
    $fileType = $_FILES['image']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Allowed extensions
    $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Generate new filename
        $newFileName = $userId . '_' . time() . '.' . $fileExtension;
        $uploadFileDir = '../uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $image_name = $newFileName;
        } else {
            $_SESSION['error'] = "There was an error uploading the image.";
            header("Location: ../dashboard");
            exit();
        }
    } else {
        $_SESSION['error'] = "Upload failed. Allowed file types: " . implode(',', $allowedfileExtensions);
        header("Location: ../dashboard");
        exit();
    }
}
if ($image_name) {
    $sql = "UPDATE users SET full_name = ?, about = ?, email = ?, phone = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $full_name, $about, $email, $phone, $image_name, $userId);
} else {
    $sql = "UPDATE users SET full_name = ?, about = ?, email = ?, phone = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $full_name, $about, $email, $phone, $userId);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Profile updated successfully!";
} else {
    $_SESSION['error'] = "Failed to update profile.";
}

$stmt->close();
header("Location: ../dashboard");
exit();