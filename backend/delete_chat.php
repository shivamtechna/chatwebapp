<?php
session_start();
include("../conn/conn.php");

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit();
}

$currentUserId = $_SESSION['user_id'];
$receiverId = $_POST['receiver_id'] ?? null;

if (!$receiverId) {
    echo json_encode(['success' => false, 'error' => 'Missing receiver_id']);
    exit();
}

// Delete all messages between these users
$sql = "DELETE FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $currentUserId, $receiverId, $receiverId, $currentUserId);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
