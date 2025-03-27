<?php
// update_session_status.php
session_start();
include '../connection/connect.php';

if (isset($_GET['username']) && isset($_GET['status'])) {
    $username = $_GET['username'];
    $status = $_GET['status'];

    // Sanitize input
    $username = mysqli_real_escape_string($conn, $username);

    // Query to update session_active
    $query = "UPDATE admin SET session_active = ? WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $status, $username);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Session status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update session status']);
    }
}
?>
