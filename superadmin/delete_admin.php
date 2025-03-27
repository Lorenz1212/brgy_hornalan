<?php
session_start();
include '../connection/connect.php'; // Database Connection

header('Content-Type: application/json'); // ✅ Siguraduhin na JSON ang response


// Check kung may session username na
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='superadmin_login.php';</script>";
    exit();
}

// Wala na ang role check, diretso na lang sa dashboard
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // ✅ Check kung may existing admin account
    $checkQuery = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Admin account not found!"]);
        exit();
    }
    $stmt->close();

    // ✅ Delete admin account
    $deleteQuery = "DELETE FROM admin WHERE username = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Admin successfully deleted!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete admin!"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "No username provided!"]);
}
?>
