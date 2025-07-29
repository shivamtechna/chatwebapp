<?php
session_start();
include("../conn/conn.php");
date_default_timezone_set('Asia/Kolkata');

$senderId = $_SESSION['user_id'] ?? 0;
$receiverId = $_GET['receiver_id'] ?? 0;

if ($senderId == 0 || $receiverId == 0) {
    echo json_encode([]);
    exit;
}

// Sirf messages fetch karo, no update here
$stmt = $conn->prepare("SELECT * FROM messages 
                        WHERE (sender_id = ? AND receiver_id = ?) 
                           OR (sender_id = ? AND receiver_id = ?) 
                        ORDER BY created_at ASC");
$stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>