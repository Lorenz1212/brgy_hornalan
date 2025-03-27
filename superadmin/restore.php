<?php
session_start();
include '../connection/connect.php'; // Database Connection

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
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

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // ✅ Kunin ang record mula sa trash table
    $query = "SELECT * FROM trash WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $statusReason = 'Restored from Trash';
        $status = $row['status'];
        $userEmail = $row['username'];
        $userName = $row['name'];

        // ✅ Alamin kung saang table ibabalik ang record
        $restoreTables = [
            'certificate_request' => "INSERT INTO certificate_request (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, status_reason) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'clearance_request' => "INSERT INTO clearance_request (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, status_reason) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'certificate_approved' => "INSERT INTO certificate_approved (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, status, status_reason) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'clearance_approved' => "INSERT INTO clearance_approved (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, status, status_reason) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'printed' => "INSERT INTO printed (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'done' => "INSERT INTO done (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        ];

        if (isset($restoreTables[$row['original_table']])) {
            $insertQuery = $restoreTables[$row['original_table']];
            $insertStmt = $conn->prepare($insertQuery);

            if ($row['original_table'] === 'certificate_request' || $row['original_table'] === 'clearance_request') {
                $insertStmt->bind_param("ssssississs",
                    $row['type'], $row['name'], $row['address'], $row['birthday'],
                    $row['year_stay_in_brgy'], $row['purpose'], $row['date'],
                    $row['amount'], $row['username'], $row['contact'], $statusReason
                );
            } elseif ($row['original_table'] === 'certificate_approved' || $row['original_table'] === 'clearance_approved') {
                $insertStmt->bind_param("ssssississss",
                    $row['type'], $row['name'], $row['address'], $row['birthday'],
                    $row['year_stay_in_brgy'], $row['purpose'], $row['date'],
                    $row['amount'], $row['username'], $row['contact'], $status, $statusReason
                );
            } else {
                $insertStmt->bind_param("ssssissdss",
                    $row['type'], $row['name'], $row['address'], $row['birthday'],
                    $row['year_stay_in_brgy'], $row['purpose'], $row['date'],
                    $row['amount'], $row['username'], $row['contact']
                );
            }

            if ($insertStmt->execute()) {
                // ✅ Burahin ang record sa Trash Table
                deleteFromTrash($conn, $id);

                // ✅ Magpadala ng email notification kung Certificate o Clearance
                if ($row['original_table'] === 'certificate_request' || $row['original_table'] === 'clearance_request') {
                    sendEmailNotification($userEmail, $userName, $row['type']);
                }

                $success = true;
                $message = $row['type'] . " Request Restored!";
            } else {
                $message = "Error: " . $conn->error;
            }
        } else {
            $message = "Invalid original table!";
        }
    } else {
        $message = "Request not found!";
    }
}

mysqli_close($conn);

// ✅ Function para burahin mula sa trash table
function deleteFromTrash($conn, $id) {
    $deleteQuery = "DELETE FROM trash WHERE id=?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $id);
    $deleteStmt->execute();
}

// ✅ Function para sa email notification
function sendEmailNotification($userEmail, $userName, $documentType) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cabugwaschritianjames01156@gmail.com'; 
        $mail->Password   = 'lndb zwhp jzfo bbqi'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('your-email@gmail.com', 'Barangay Hornalan');
        $mail->addAddress($userEmail);

        $mail->isHTML(true);
        $mail->Subject = "Restoration of Your $documentType Request";
        $mail->Body    = " 
            <h3>Hi Mr./Mrs. {$userName},</h3>
            <p>We apologize for the accidental denial of your <strong>$documentType</strong> request. It has now been restored.</p>
            <p>Thank you for your understanding.</p>
            <br>
            <p>Best regards,</p>
            <p><strong>Barangay Hornalan</strong></p>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Status</title>
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
                window.location.href = "recycle.php";
            });
        });
    </script>
</body>
</html>
