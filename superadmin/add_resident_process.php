<?php
session_start();
include '../connection/connect.php'; // Database connection

// ✅ Check kung connected sa database
if ($conn->connect_error) {
    die("❌ Connection Failed: " . $conn->connect_error);
}

// ✅ Check kung may POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kunin ang values at siguraduhin hindi null
    $complete_address = ucwords(trim($_POST['complete_address'] ?? ''));
    $name_of_family_member = ucwords(trim($_POST['name_of_family_member'] ?? ''));
    $position_in_the_family = ucwords(trim($_POST['position_in_the_family'] ?? ''));
    $age = trim($_POST['age'] ?? '');
    $civil_status = ucwords(trim($_POST['civil_status'] ?? ''));
    $occupation_source_of_income = ucwords(trim($_POST['occupation_source_of_income'] ?? ''));
    $contact_number = trim($_POST['contact_number'] ?? '');

    // ✅ Required Fields Check
    if (
        empty($complete_address) || empty($name_of_family_member) || empty($position_in_the_family) || 
        empty($age) || empty($civil_status) || empty($occupation_source_of_income)
    ) {
        $_SESSION['error'] = "❌ Please fill in all required fields.";
        header("Location: add_resident.php");
        exit();
    }

    // ✅ Convert Age to Integer (Kung VARCHAR sa DB, huwag i-intval)
    if (!ctype_digit($age)) {
        $_SESSION['error'] = "❌ Age must be a valid number.";
        header("Location: add_resident.php");
        exit();
    }

    // ✅ SQL Query to Insert Data
    $query = "INSERT INTO resident_list 
              (complete_address, name_of_family_member, position_in_the_family, age, civil_status, occupation_source_of_income, contact_number) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    // ✅ Check kung successful ang query preparation
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $_SESSION['error'] = "❌ Prepare Failed: " . $conn->error;
        header("Location: add_resident.php");
        exit();
    }

    // ✅ Bind parameters at i-execute ang query
    $stmt->bind_param("sssssss", $complete_address, $name_of_family_member, $position_in_the_family, $age, $civil_status, $occupation_source_of_income, $contact_number);

    // ✅ I-check kung successful ang execution
    if (!$stmt->execute()) {
        $_SESSION['error'] = "❌ Query Failed: " . $stmt->error;
        header("Location: add_resident.php");
        exit();
    }

    // ✅ SUCCESS MESSAGE AT REDIRECT SA `resident_list.php`
    $_SESSION['success'] = "✅ Resident added successfully!";
    header("Location: resident_list.php");
    exit();
}

$conn->close();
?>
