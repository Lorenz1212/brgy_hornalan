<?php
session_start(); // Start session
include '../connection/connect.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$requestCounts = [
    'Clearance' => 0, 
    'Certificate' => 0, 
    'Approved_Clearance' => 0, 
    'Approved_Certificate' => 0, 
    'Total' => 0
];

// I-check kung may error sa connection
if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Count pending clearance requests
$clearanceQuery = "SELECT COUNT(*) as count FROM clearance_request WHERE status = 'Pending'";
$clearanceResult = mysqli_query($conn, $clearanceQuery);
if ($clearanceResult) {
    $clearanceRow = mysqli_fetch_assoc($clearanceResult);
    $requestCounts['Clearance'] = $clearanceRow['count'];
}

// Count pending certificate requests
$certificateQuery = "SELECT COUNT(*) as count FROM certificate_request WHERE status = 'Pending'";
$certificateResult = mysqli_query($conn, $certificateQuery);
if ($certificateResult) {
    $certificateRow = mysqli_fetch_assoc($certificateResult);
    $requestCounts['Certificate'] = $certificateRow['count'];
}

// Count approved clearance requests
$approvedClearanceQuery = "SELECT COUNT(*) as count FROM clearance_approved where status = 'approved'";
$approvedClearanceResult = mysqli_query($conn, $approvedClearanceQuery);
if ($approvedClearanceResult) {
    $approvedClearanceRow = mysqli_fetch_assoc($approvedClearanceResult);
    $requestCounts['Approved_Clearance'] = $approvedClearanceRow['count'];
}

// Count approved certificate requests
$approvedCertificateQuery = "SELECT COUNT(*) as count FROM certificate_approved where status = 'approved'";
$approvedCertificateResult = mysqli_query($conn, $approvedCertificateQuery);
if ($approvedCertificateResult) {
    $approvedCertificateRow = mysqli_fetch_assoc($approvedCertificateResult);
    $requestCounts['Approved_Certificate'] = $approvedCertificateRow['count'];
}

// Compute total pending requests
$requestCounts['Total'] = $requestCounts['Clearance'] + $requestCounts['Certificate'];

header('Content-Type: application/json');
echo json_encode($requestCounts);
?>
