<?php
session_start();
include '../connection/connect.php'; // Database connection

// ✅ Check kung may form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $complete_address = trim($_POST['complete_address']);
    $name_of_family_member = trim($_POST['name_of_family_member']);
    $position_in_the_family = trim($_POST['position_in_the_family']);
    $age = intval($_POST['age']);
    $civil_status = trim($_POST['civil_status']);
    $occupation_source_of_income = trim($_POST['occupation_source_of_income']);
    $contact_number = trim($_POST['contact_number']);

    // ✅ Check kung may laman ang required fields
    if (empty($complete_address) || empty($name_of_family_member) || empty($position_in_the_family) || empty($age) || empty($civil_status) || empty($occupation_source_of_income)) {
        echo "<script>
            alert('Please fill in all required fields.');
            window.location.href = 'add_resident.php';
        </script>";
        exit();
    }

    // ✅ SQL Query to Insert Data
    $query = "INSERT INTO resident_list 
              (complete_address, name_of_family_member, position_in_the_family, age, civil_status, occupation_source_of_income, contact_number) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssss", $complete_address, $name_of_family_member, $position_in_the_family, $age, $civil_status, $occupation_source_of_income, $contact_number);

    if ($stmt->execute()) {
        echo "<script>
            alert('Resident added successfully!');
            window.location.href = 'resident_list.php';
        </script>";
    } else {
        echo "<script>
            alert('Error! Unable to add resident.');
            window.location.href = 'add_resident.php';
        </script>";
    }
    $stmt->close();
}

$conn->close();
?>
