<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

include("../conn/conn.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $senderId = $_SESSION['user_id'] ?? 0;
    $receiverId = $_POST['receiver_id'] ?? 0;
    $message = trim($_POST['message'] ?? '');

    if ($senderId && $receiverId && $message !== '') {
        $now = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $senderId, $receiverId, $message, $now);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Invalid input"]);
    }
}
?>