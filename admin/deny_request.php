<?php
session_start();
include '../connection/connect.php'; // Database Connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$success = false;
$message = "";

if (isset($_GET['id']) && isset($_GET['status_reason'])) {
    $id = $_GET['id'];
    $statusReason = $_GET['status_reason']; // Get the status_reason from URL
    $originalTable = '';

    // Find in certificate_request table
    $query = "SELECT * FROM certificate_request WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $originalTable = 'certificate_request';
    } else {
        // Find in clearance_request table if not in certificate_request
        $query = "SELECT * FROM clearance_request WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $originalTable = 'clearance_request';
        }
    }

    if ($row) {
        $userEmail = $row['username'];
        $userName = $row['name'];

        // Update status to "Rejected"
        $updateStatusQuery = "UPDATE $originalTable SET status = 'denied', status_reason = ? WHERE id = ?";
        $updateStatusStmt = $conn->prepare($updateStatusQuery);
        $updateStatusStmt->bind_param("si", $statusReason, $id);
        $updateStatusStmt->execute();

        // Insert into trash table
        $insertQuery = "INSERT INTO trash (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, original_table, status, status_reason) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'denied', ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssssississss", 
    $row['type'], $row['name'], $row['address'], $row['birthday'], 
    $row['year_stay_in_brgy'], $row['purpose'], $row['date'], $row['amount'], 
    $row['username'], $row['contact'], $originalTable, $statusReason
);

        if ($insertStmt->execute()) {
            // Delete from original table
            $deleteQuery = "DELETE FROM $originalTable WHERE id=?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();

            // Send email notification
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cabugwaschritianjames01156@gmail.com'; // Change to your email
                $mail->Password   = 'lndb zwhp jzfo bbqi'; // Use App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your-email@gmail.com', 'Barangay Hornalan');
                $mail->addAddress($userEmail);
                $mail->isHTML(true);
                $mail->Subject = "Your Document Request Has Been Rejected";
                $mail->Body    = "
                    <h3>Hi Mr./Mrs. {$userName},</h3>
                    <p>Your document request has been <strong>rejected</strong> due to incorrect information.</p>
                    <p><strong>Reasons for rejection:</strong></p>
                    <p>{$statusReason}</p>
                    <p>Please review your details carefully and submit a new request.</p>
                    <p>Thank you.</p>
                ";
                $mail->send();
            } catch (Exception $e) {
                error_log("Email Error: {$mail->ErrorInfo}");
            }

            // ✅ SweetAlert2 Notification
            $success = true;
            $message = "Request Rejected! Email sent to user.";
        } else {
            $message = "Error: " . $conn->error;
        }
    } else {
        $message = "Request not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejection Status</title>
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
                window.location.href = document.referrer || "clear_request.php"; // Redirect after clicking OK
            });
        });
    </script>
</body>
</html>
