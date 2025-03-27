<?php
session_start();
include '../connection/connect.php';

// ✅ Check kung may naipasa na ID
if (!isset($_POST['id']) || empty($_POST['id'])) {
    error_log("❌ Missing ID!");
    echo json_encode(["status" => "error", "message" => "Missing ID"]);
    exit();
}

$id = $_POST['id'];
error_log("📌 Received ID for deletion: $id");

// ✅ Kunin ang request bago i-delete para mailipat sa history
$query = "SELECT * FROM walkin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$walkin = $result->fetch_assoc();
$stmt->close();

if (!$walkin) {
    error_log("❌ Record not found sa walkin table.");
    echo json_encode(["status" => "error", "message" => "Record not found"]);
    exit();
}

error_log("📌 Walk-in Data: " . print_r($walkin, true));

// ✅ Ilipat sa `history` table bago i-delete
$query = "INSERT INTO history (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, contact, claimed_date) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssssissss", 
    $walkin['type'], 
    $walkin['name'], 
    $walkin['address'], 
    $walkin['birthday'], 
    $walkin['year_stay_in_brgy'], 
    $walkin['purpose'], 
    $walkin['date'], 
    $walkin['amount'], 
    $walkin['contact']
);

// ✅ Check SQL Execution
if (!$stmt->execute()) {
    error_log("❌ Failed to insert into history: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Failed to insert into history: " . $stmt->error]);
    exit();
}
$stmt->close();

error_log("✅ Successfully inserted into history");

// ✅ Delete ang record sa `walkin`
$query = "DELETE FROM walkin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    error_log("✅ Successfully deleted ID: $id");
    echo json_encode(["status" => "success", "redirect" => "superadmin_dashboard.php"]);
} else {
    error_log("❌ Failed to delete record: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Failed to delete record: " . $stmt->error]);
}
$stmt->close();
?>
