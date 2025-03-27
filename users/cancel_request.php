<?php
session_start();
include '../connection/connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && isset($_POST['type'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];

    // Tukuyin kung saan ide-delete
    if ($type === "Certificate Request") {
        $query = "DELETE FROM certificate_request WHERE id = ?";
    } elseif ($type === "Clearance Request") {
        $query = "DELETE FROM clearance_request WHERE id = ?";
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request type"]);
        exit();
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to cancel request"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
