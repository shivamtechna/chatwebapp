<?php
session_start();
include("../conn/conn.php");

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Status ko 'offline' update karo
    $update = $conn->prepare("UPDATE users SET status = 'offline' WHERE id = ?");
    $update->bind_param("i", $userId);
    $update->execute();
    $update->close();

    // Session destroy karo
    session_unset();
    session_destroy();
}

// Redirect to login page
header("Location: ../index");
exit();
?>
