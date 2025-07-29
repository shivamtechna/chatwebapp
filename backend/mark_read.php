<?php
session_start();
include("../conn/conn.php");
header('Content-Type: application/json');

$receiverId = $_SESSION['user_id'] ?? 0;
$senderId = $_POST['sender_id'] ?? 0;

if ($receiverId == 0 || $senderId == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

$stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
$stmt->bind_param("ii", $senderId, $receiverId);
$stmt->execute();

echo json_encode(['success' => true]);
exit;
?>