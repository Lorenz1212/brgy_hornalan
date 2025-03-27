<?php
session_start();
include '../connection/connect.php';

// Kunin ang latest status ng mga admin
$query = "SELECT username, session_active FROM admin ORDER BY role DESC";
$result = mysqli_query($conn, $query);

$admin_status = [];
while ($row = mysqli_fetch_assoc($result)) {
    $admin_status[$row['username']] = $row['session_active'];
}

echo json_encode($admin_status);
?>
