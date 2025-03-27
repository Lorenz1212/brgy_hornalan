<?php
session_start();
include '../connection/connect.php';

// Initialize an empty array to store session statuses
$status = [];

// Query to get the username and session_active status of all admins
$query = "SELECT username, session_active FROM admin WHERE role = 'admin'";
$result = mysqli_query($conn, $query);

// Loop through the result and add the session status to the $status array
while ($row = mysqli_fetch_assoc($result)) {
    $status[$row['username']] = $row['session_active'];
}

// Return the status array as a JSON response
echo json_encode($status);

// Close the connection
mysqli_close($conn);
?>
