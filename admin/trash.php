<?php
session_start();
include '../connection/connect.php'; // Database Connection
// ✅ Check kung naka-login ang user
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='admin_login.php';</script>";
    exit();
}

// Cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$success = false;
$message = "";

if (isset($_GET['id']) && isset($_GET['type']) && isset($_SERVER['HTTP_REFERER'])) {
    $id = $_GET['id'];
    $type = $_GET['type'];
    $referer = $_SERVER['HTTP_REFERER'];

    // Table mapping
    $trashMap = [
        'certificate' => 'certificate_approved',
        'clearance' => 'clearance_approved',
        'printed' => 'printed',
        'done' => 'done'
    ];

    if (array_key_exists($type, $trashMap)) {
        $tableFrom = $trashMap[$type];
        $tableTrash = 'trash';

        $query = "SELECT * FROM $tableFrom WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            $insertQuery = "INSERT INTO $tableTrash (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, status, original_table, status_reason) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insertStmt = $conn->prepare($insertQuery);
            $status_reason = isset($_GET['status_reason']) ? $_GET['status_reason'] : "No reason provided"; // Default reason kung walang laman

            $insertStmt->bind_param("ssssissdsssss", 
            $row['type'], $row['name'], $row['address'], $row['birthday'], 
            $row['year_stay_in_brgy'], $row['purpose'], $row['date'], 
            $row['amount'], $row['username'], $row['contact'], $row['status'], 
            $tableFrom, $status_reason
        );
        

            if ($insertStmt->execute()) {
                $deleteQuery = "DELETE FROM $tableFrom WHERE id=?";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->bind_param("i", $id);
                $deleteStmt->execute();
                
                $success = true;
                $message = "Record moved to Trash successfully!";
            } else {
                $message = "Error inserting record into Trash: " . $conn->error;
            }
        } else {
            $message = "Record not found!";
        }
    } else {
        $message = "Invalid record type!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move to Trash</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                title: "<?php echo $success ? 'Success!' : 'Error!'; ?>",
                text: "<?php echo $message; ?>",
                icon: "<?php echo $success ? 'success' : 'error'; ?>",
                confirmButtonText: "OK"
            }).then(() => {
                window.location.href = "<?php echo $referer; ?>";
            });
        });
    </script>
</body>
</html>
