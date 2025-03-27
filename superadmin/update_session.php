<?php
session_start();
include '../connection/connect.php';

// Check if the status parameter is provided in the URL
if (isset($_GET['status'])) {
    // Get the session status from the URL parameter
    $status = $_GET['status'];
    
    // Ensure that the user is logged in before updating the session_active
    if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
        $username = $_SESSION['username'];
        
        // Update the session_active to 0 (inactive) when the user leaves or closes the tab
        $update = "UPDATE admin SET session_active = ? WHERE username = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "is", $status, $username);
        mysqli_stmt_execute($stmt);
    }
}

mysqli_close($conn);
?>
