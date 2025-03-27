<?php
session_start();
include '../connection/connect.php'; // Database connection

// ✅ Check kung may form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Apply ucwords() to capitalize each word for address, name, position, etc.
    $complete_address = ucwords(trim($_POST['complete_address']));
    $name_of_family_member = ucwords(trim($_POST['name_of_family_member']));
    $position_in_the_family = ucwords(trim($_POST['position_in_the_family']));
    $age = intval($_POST['age']);
    $civil_status = ucwords(trim($_POST['civil_status']));
    $occupation_source_of_income = ucwords(trim($_POST['occupation_source_of_income']));
    $contact_number = trim($_POST['contact_number']);

    // ✅ Check kung may laman ang required fields
    if (empty($complete_address) || empty($name_of_family_member) || empty($position_in_the_family) || empty($age) || empty($civil_status) || empty($occupation_source_of_income)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Incomplete Fields',
                text: 'Please fill in all required fields.',
                confirmButtonColor: '#d33'
            }).then(() => {
                window.location.href = 'add_resident.php';
            });
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
            Swal.fire({
                icon: 'success',
                title: 'Resident Added!',
                text: 'Resident added successfully.',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = 'resident_list.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to add resident. Please try again.',
                confirmButtonColor: '#d33'
            }).then(() => {
                window.location.href = 'add_resident.php';
            });
        </script>";
    }
    $stmt->close();
}

$conn->close();
?>
