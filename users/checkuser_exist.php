<?php
require('../connection/connect.php');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user'])) {
    $user = trim($_POST['user']);
    
    $query = "SELECT user FROM users WHERE user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "exists";
    } else {
        echo "available";
    }

    $stmt->close();
    $conn->close();
}
?>