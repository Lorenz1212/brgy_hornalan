<?php
session_start();
include '../connection/connect.php'; // Database Connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user'])) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Unauthorized Access!',
            text: 'Please log in first.',
            confirmButtonColor: '#d33'
        }).then(() => {
            window.location.href='index.php';
        });
    </script>";
    exit();
}

// Cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$success = false;
$message = "";
$emailSent = false;

if (isset($_GET['id'])) {
    $clearanceID = $_GET['id'];

    // ✅ Kunin ang record mula sa certificate table
    $query = "SELECT * FROM certificate WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $clearanceID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // ✅ Ipasok sa certificate_request table
        $insertQuery = "INSERT INTO certificate_request (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, contact, username) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssssisssss", 
            $row['type'], 
            $row['name'], 
            $row['address'], 
            $row['birthday'], 
            $row['year_stay_in_brgy'], 
            $row['purpose'], 
            $row['date'], 
            $row['amount'], 
            $row['contact'], 
            $row['username']
        );

        if ($insertStmt->execute()) {
            // ✅ Burahin ang record mula sa certificate table
            $deleteQuery = "DELETE FROM certificate WHERE id=?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $clearanceID);

            if ($deleteStmt->execute()) {
                // ✅ Magpadala ng email notification
                $userEmail = $row['username']; // User's email
                $userName = $row['name']; // User's name

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'cabugwaschritianjames01156@gmail.com'; // Replace with your email
                    $mail->Password   = 'lndb zwhp jzfo bbqi'; // Use App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('cabugwaschritianjames01156@gmail.com', 'Barangay Hornalan');
                    $mail->addAddress($userEmail);

                    $mail->isHTML(true);
                    $mail->Subject = "Your Document Request is Sent and Awaiting Approval";
                    $mail->Body    = "
                        <h3>Hi Mr./Mrs. {$userName},</h3>
                        <p>Your document request has been sent and is now awaiting approval.</p>
                        <p>Please wait while your request is processed. We will notify you once your document is ready for pickup.</p>
                        <p>Thank you for your patience!</p>
                    ";

                    $mail->send();
                    $emailSent = true;
                } catch (Exception $e) {
                    error_log("Email Error: {$mail->ErrorInfo}");
                    $emailSent = false;
                }

                // ✅ Set success message
                $success = true;
                $message = "Request Sent! " . ($emailSent ? "Please check your email or spam folder for confirmation." : "However, email notification failed to send.");
            } else {
                $message = "Failed to delete the record.";
            }
            $deleteStmt->close();
        } else {
            $message = "Failed to transfer record.";
        }

        $insertStmt->close();
    } else {
        $message = "The requested document does not exist.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Status</title>
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
                window.location.href = "prof.php";
            });
        });
    </script>
</body>
</html>
