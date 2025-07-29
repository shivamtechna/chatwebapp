<?php
include '../conn/conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$current_user = $conn->real_escape_string($_SESSION['user_id']);

// Query to get users who chatted with current user
$sql = "
    SELECT u.id, u.full_name, u.image, u.status,
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = u.id AND receiver_id = '$current_user' AND is_read = 0) AS unread_count
    FROM users u
    WHERE u.id IN (
        SELECT sender_id FROM messages WHERE receiver_id = '$current_user'
        UNION
        SELECT receiver_id FROM messages WHERE sender_id = '$current_user'
    )
    AND u.id != '$current_user'
    GROUP BY u.id
    ORDER BY (
        SELECT MAX(created_at) FROM messages 
        WHERE (sender_id = u.id AND receiver_id = '$current_user') 
           OR (sender_id = '$current_user' AND receiver_id = u.id)
    ) DESC
";

$result = $conn->query($sql);
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>