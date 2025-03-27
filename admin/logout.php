<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    // Database connection
    require '../connection/connect.php';
    $username = $_SESSION['username'];

    // Update session_active to 0 for the logged-out user
    $updateQuery = "UPDATE admin SET session_active = 0 WHERE username = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("s", $username);
    $updateStmt->execute();
    $updateStmt->close();

    // Unset session variables
    unset($_SESSION['username']); // Unset admin session
    session_regenerate_id(true); // Regenerate session ID for security

    // Prevent browser from caching the previous page
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    session_unset();  // Unset all session variables
    session_destroy();  // Destroy the session
    // Redirect sa admin login page
    header("Location: admin_login.php");
    exit();
}
?>
