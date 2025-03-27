<?php
session_start(); // Start session

// ✅ Enable Error Reporting (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Database connection
require('../connection/connect.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve data from session (capitalize first letter of each word)
    $lastname = isset($_SESSION['lastname']) ? ucwords(strtolower($_SESSION['lastname'])) : null;
    $firstname = isset($_SESSION['firstname']) ? ucwords(strtolower($_SESSION['firstname'])) : null;
    $middlename = isset($_SESSION['middlename']) ? ucwords(strtolower($_SESSION['middlename'])) : null;
    $contact = $_SESSION['contact'] ?? null;
    
    // ✅ Capitalize address and format birthday
    $birthday = isset($_SESSION['birthday']) ? date("Y-m-d", strtotime($_SESSION['birthday'])) : null; // Ensure correct date format (YYYY-MM-DD)
    $address = isset($_SESSION['address']) ? ucwords(strtolower($_SESSION['address'])) : null; // Capitalize words

    // Retrieve data from POST
    $user = $_POST['user'] ?? null;
    $password = $_POST['password'] ?? null;
    $confirm_password = $_POST['confirm_password'] ?? null;

    // ✅ Debugging - Check kung may kulang na fields
    if (!$lastname || !$firstname || !$middlename || !$contact || !$birthday || !$address || !$user || !$password || !$confirm_password) {
        die("<script>
            alert('❌ ERROR: Missing Fields! Please complete all fields.');
            window.history.back();
        </script>");
    }

    // ✅ Check if passwords match
    if ($password !== $confirm_password) {
        die("<script>
            alert('❌ ERROR: Passwords Do Not Match!');
            window.history.back();
        </script>");
    }

    // ✅ Check kung existing na ang username
    $check_query = "SELECT userID FROM users WHERE user = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $user);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        die("<script>
            alert('❌ ERROR: Username already exists!');
            window.history.back();
        </script>");
    }
    $check_stmt->close();

    // ✅ Insert new user (plain password for now)
    $query = "INSERT INTO users (lastname, firstname, middlename, contact, birthday, address, user, password) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("<script>
            alert('❌ ERROR: Database Error! " . $conn->error . "');
            window.history.back();
        </script>");
    }

    // ✅ Bind parameters
    $stmt->bind_param('ssssssss', $lastname, $firstname, $middlename, $contact, $birthday, $address, $user, $password);

    // ✅ Execute query
    if ($stmt->execute()) {
        // ✅ Clear session after successful signup
        session_unset();
        session_destroy();

        echo "<script>
            alert('✅ Signup Successful! You can now log in.');
            window.location.href = 'user.php';
        </script>";
        exit();
    } else {
        die("<script>
            alert('❌ ERROR: Signup Failed! " . $stmt->error . "');
            window.history.back();
        </script>");
    }

    $stmt->close();
    $conn->close();
}
?>
