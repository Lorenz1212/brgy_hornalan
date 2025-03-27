<?php
include '../connection/connect.php';
session_start();

// Debugging: I-check kung naka-login ang user
if (!isset($_SESSION['user'])) {
    echo "⚠ User is not logged in.";
    exit();
} else {
    echo "✅ Logged in user: " . $_SESSION['user']; // Debugging
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = ucwords(strtolower(trim($_POST['name'])));
    $type = ucwords(strtolower(trim($_POST['docs_type'])));
    $address = ucwords(strtolower(trim($_POST['address'])));
    $birthday = date('Y-m-d', strtotime($_POST['birthday']));
    $purpose = ucwords(strtolower(trim($_POST['purpose'])));
    $yearStay = trim($_POST['year_stay']);
    $amount = isset($_POST['amount']) && $_POST['amount'] !== "" ? trim($_POST['amount']) : "0";

    $date = trim($_POST['request_date']);
    $username = $_SESSION['user'];  

    // ✅ Gumamit ng tamang column name
    $contactQuery = "SELECT contact FROM users WHERE user = ?"; // Palitan kung 'username' ang column mo
    $contactStmt = $conn->prepare($contactQuery);
    $contactStmt->bind_param("s", $username);
    $contactStmt->execute();
    $contactResult = $contactStmt->get_result();

    if ($contactResult->num_rows > 0) {
        $contactRow = $contactResult->fetch_assoc();
        $contact = $contactRow['contact'];
    } else {
        echo "⚠ User not found. Check if column name is correct!";
        exit();
    }
    $contactStmt->close();

    // Validate required fields
    if (
        empty($name) || empty($type) || empty($address) || empty($birthday) || 
        empty($purpose) || empty($yearStay) || $amount === "" || $amount === null || empty($date)
    ) {
        echo "⚠ All fields must be filled out.";
        exit();
    }


    // Validate numeric fields
    if (!is_numeric($yearStay)) {
        echo "⚠ Please enter valid numbers for Years Stayed and Amount.";
        exit();
    }

    // Determine the table and display page
    // Ensure case-insensitive comparison
    if (strtolower($type) === "barangay clearance" || strtolower($type) === "business clearance") {
        $tableName = "clearance";
        $redirectPage = "clearance_display.php";
    } else {
        $tableName = "certificate";
        $redirectPage = "cert_display.php";
    }

    // Corrected Insert query (removed the trailing comma)
    $query = "INSERT INTO $tableName (username, contact, name, type, address, birthday, purpose, year_stay_in_brgy, amount, date) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssis", $username, $contact, $name, $type, $address, $birthday, $purpose, $yearStay, $amount, $date);

    // Execute the query
    if ($stmt->execute()) {
        $certificateID = $stmt->insert_id;
        header("Location: " . $redirectPage . "?id=" . $certificateID);
        exit();
    } else {
        echo "❌ Error sa pag-submit: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
