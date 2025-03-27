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
        $role = $_SESSION['role']; // Get the role of the user
        
        // Only update session_active for "admin" role
        if ($role === 'admin') {
            $update = "UPDATE admin SET session_active = ? WHERE username = ?";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "is", $status, $username);
            mysqli_stmt_execute($stmt);
        }
    }
}

// Close database connection
mysqli_close($conn);
?>
