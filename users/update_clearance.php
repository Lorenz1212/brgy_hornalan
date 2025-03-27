<?php
include '../connection/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kunin ang values mula sa form
    // Apply ucwords to ensure proper capitalization
    $name = ucwords(strtolower(trim($_POST['name'])));  // Capitalize first letter of each word
    $address = ucwords(strtolower(trim($_POST['address'])));  // Capitalize first letter of each word
    $birthday = $_POST['birthday'];
    $purpose = ucwords(strtolower(trim($_POST['purpose'])));  // Capitalize first letter of each word
    $type = $_POST['type'];
    $yearStay = $_POST['year_stay']; 
    $date = $_POST['date'];
    $amount = $_POST['amount']; 

    // Siguraduhing nakuha ang clearanceID mula sa URL
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $clearanceID = $_GET['id'];
    } else {
        echo "⚠ Invalid na ID.";
        exit();
    }

    // Update query (including type, amount, year stay)
    $query = "UPDATE clearance SET name = ?, address = ?, birthday = ?, purpose = ?, type = ?, year_stay_in_brgy = ?, date = ?, amount = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssii", $name, $address, $birthday, $purpose, $type, $yearStay, $date, $amount, $clearanceID);  // Added fields

    // I-execute ang query
    if ($stmt->execute()) {
        // Matagumpay na na-update ang record
        header("Location: clearance_display.php?id=" . $clearanceID); // Redirect to clearance_display.php with the updated record ID
        exit(); // Make sure no further code executes after the redirect
    } else {
        echo "Error sa pag-update: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
