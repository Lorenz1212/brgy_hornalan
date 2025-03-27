<?php
session_start();
include '../connection/connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access!"]);
    exit();
}

$user = $_SESSION['user'];
$document_type = $_GET['type'] ?? '';

if (empty($document_type)) {
    echo json_encode(["status" => "error", "message" => "No document type provided!"]);
    exit();
}

// ✅ Listahan ng documents na may cooldown (7 days)
$documents_with_cooldown = ["Barangay Clearance", "Business Clearance", "Barangay Certificate", "Residency", "Cedula"];

// ✅ QUERY 1: Check kung may existing request sa ibang tables
$request_query = "
    SELECT id FROM clearance_request WHERE username = ? AND type = ? AND status = 'pending'
    UNION ALL
    SELECT id FROM certificate_request WHERE username = ? AND type = ? AND status = 'pending'
    UNION ALL
    SELECT id FROM clearance_approved WHERE username = ? AND type = ? AND status = 'approved'
    UNION ALL
    SELECT id FROM certificate_approved WHERE username = ? AND type = ? AND status = 'approved'
    UNION ALL
    SELECT id FROM printed WHERE username = ? AND type = ?
    UNION ALL
    SELECT id FROM done WHERE username = ? AND type = ?
";

$request_stmt = $conn->prepare($request_query);
$request_stmt->bind_param("ssssssssssss", 
    $user, $document_type, 
    $user, $document_type, 
    $user, $document_type, 
    $user, $document_type, 
    $user, $document_type, 
    $user, $document_type
);
$request_stmt->execute();
$request_stmt->store_result();
$has_request = ($request_stmt->num_rows > 0);
$request_stmt->close();

// ✅ QUERY 2: Check kung may active cooldown sa history table
$cooldown_end = "";
$has_cooldown = false;

if (in_array($document_type, $documents_with_cooldown)) {
    $cooldown_query = "SELECT cooldown_until FROM history WHERE username = ? AND type = ? AND cooldown_until > NOW()";
    $cooldown_stmt = $conn->prepare($cooldown_query);
    $cooldown_stmt->bind_param("ss", $user, $document_type);
    $cooldown_stmt->execute();
    $cooldown_stmt->bind_result($cooldown_end);
    $cooldown_stmt->fetch();
    $has_cooldown = !empty($cooldown_end);
    $cooldown_stmt->close();
}

// ✅ Display the correct message
if ($has_request && $has_cooldown) {
    echo json_encode(["status" => "error", "message" => "You already have a request and have recently claimed this document. Please wait until the cooldown period ends."]);
} elseif ($has_request) {
    echo json_encode(["status" => "error", "message" => "You already have a request for this document."]);
} elseif ($has_cooldown) {
    echo json_encode(["status" => "error", "message" => "You have recently claimed this document. Please wait until the cooldown period ends."]);
} else {
    echo json_encode(["status" => "success", "message" => "You can proceed with the request."]);
}

$conn->close();
?>
