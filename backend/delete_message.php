<?php
// delete_message.php
session_start();
include '../conn/conn.php';

if (!isset($_SESSION['user_id'])) {
    echo "unauthorized";
    exit();
}

$userId = $_SESSION['user_id'];
$messageId = intval($_POST['id'] ?? 0);

if ($messageId > 0) {
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $stmt->bind_param("ii", $messageId, $userId);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "fail";
    }
    $stmt->close();
} else {
    echo "invalid";
}
?>