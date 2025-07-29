<?php
session_start();
include("../conn/conn.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$currentUserId = $_SESSION['user_id'];
$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode([]);
    exit();
}

$sql = "SELECT id, full_name, image, status, phone 
        FROM users 
        WHERE id != ? AND (full_name LIKE ? OR phone LIKE ?)
        ORDER BY full_name 
        LIMIT 20";

$stmt = $conn->prepare($sql);
$likeQuery = "%$q%";
$stmt->bind_param("iss", $currentUserId, $likeQuery, $likeQuery);
$stmt->execute();

$result = $stmt->get_result();
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
header('Content-Type: application/json');
echo json_encode($users);
?>