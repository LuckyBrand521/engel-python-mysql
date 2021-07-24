<?php
$servername = '50f3194.online-server.cloud';
$username = 'engel';
$password = 'F8*zYt#agM';
$db = 'kwon';
$conn = new mysqli($servername, $username, $password, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 