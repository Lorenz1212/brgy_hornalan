<?php
session_start();
include '../connection/connect.php'; // Database Connection


// Check kung may session username na
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='superadmin_login.php';</script>";
    exit();
}

// Wala na ang role check, diretso na lang sa dashboard
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// ✅ Check kung may selected items
if (isset($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = $_POST['ids'];
    
    // Convert IDs to a comma-separated string para sa SQL query
    $idList = implode(",", array_map('intval', $ids));
    
    // ✅ Delete records mula sa trash table
    $deleteQuery = "DELETE FROM trash WHERE id IN ($idList)";
    if (mysqli_query($conn, $deleteQuery)) {
        echo json_encode(["status" => "success", "message" => "Selected records deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete records!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No records selected!"]);
}

mysqli_close($conn);
