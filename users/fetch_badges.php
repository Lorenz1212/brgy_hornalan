<?php
session_start();
include '../connection/connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$user = $_SESSION['user'];

// Query for pending requests
$query = "
    SELECT COUNT(*) as count FROM (
        SELECT id FROM clearance_request WHERE username = ? AND status = 'pending'
        UNION ALL
        SELECT id FROM certificate_request WHERE username = ? AND status = 'pending'
    ) AS pending_requests";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $user, $user);
$stmt->execute();
$result = $stmt->get_result();
$pendingCount = $result->fetch_assoc()['count'];

// Query for approved requests
$query = "
    SELECT COUNT(*) as count FROM (
        SELECT id FROM clearance_approved WHERE username = ? AND status = 'approved'
        UNION ALL
        SELECT id FROM certificate_approved WHERE username = ? AND status = 'approved'
    ) AS approved_requests";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $user, $user);
$stmt->execute();
$result = $stmt->get_result();
$approvedCount = $result->fetch_assoc()['count'];

// Query for pickup requests
$query = "SELECT COUNT(*) as count FROM done WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$pickupCount = $result->fetch_assoc()['count'];

// Query for rejected requests
$query = "SELECT COUNT(*) as count FROM trash WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$rejectedCount = $result->fetch_assoc()['count'];

$stmt->close();
$conn->close();

echo json_encode([
    "pending" => $pendingCount,
    "approved" => $approvedCount,
    "pickup" => $pickupCount,
    "rejected" => $rejectedCount
]);
?>