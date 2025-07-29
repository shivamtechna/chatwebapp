<?php
$conn = new mysqli("db", "root", "shivam8433", "webchatapp");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>