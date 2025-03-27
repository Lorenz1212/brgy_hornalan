<?php
session_start();
include '../connection/connect.php';

// Check if superadmin is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

// Check if `id` is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "Missing ID"]);
    exit();
}

$official_id = $_GET['id'];

// Delete official from database
$sql = "DELETE FROM brgy_official WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $official_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}

$stmt->close();
$conn->close();
?>
