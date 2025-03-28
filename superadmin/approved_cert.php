<?php
session_start(); // Start session
include '../connection/connect.php'; // Database Connection


// Check kung may session username na
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='index.php';</script>";
    exit();
}

// Wala na ang role check, diretso na lang sa dashboard
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];  // Get the request ID from the URL

    // Step 1: Get the record from the clearance_request table
    $query = "SELECT * FROM certificate_request WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Get the user's email and name
        $userEmail = $row['username'];  // User's email (using 'username' field)
        $userName = $row['name'];       // User's name

        // Step 2: Insert the record into the clearance_approved table with status 'approved'
        $insertQuery = "INSERT INTO certificate_approved (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $status = 'approved';  // Set status to 'approved'
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssssississs", 
            $row['type'], $row['name'], $row['address'], $row['birthday'], 
            $row['year_stay_in_brgy'], $row['purpose'], $row['date'], $row['amount'], $row['username'], $row['contact'], $status
        );

        if ($insertStmt->execute()) {
            // Step 3: Delete the record from the clearance_request table after approval
            $deleteQuery = "DELETE FROM certificate_request WHERE id=?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();

            // Step 4: Send an email notification to the user
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cabugwaschritianjames01156@gmail.com';  // Replace with your email
                $mail->Password   = 'lndb zwhp jzfo bbqi';  // Use App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your-email@gmail.com', 'Barangay Hornalan');
                $mail->addAddress($userEmail); // Use user's email from the database

                $mail->isHTML(true);
                $mail->Subject = "Your Certificate Request is Approved";
                $mail->Body    = "
                    <h3>Hi Mr./Mrs. {$userName},</h3>
                    <p>Your certficate request has been <strong>approved</strong> and is now being processed.</p>
                    <p>Please wait while we print and complete your document.</p>
                    <p>We will notify you once it is ready for pickup.</p>
                    <p>Thank you.</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                error_log("Email Error: {$mail->ErrorInfo}");
            }

            // ✅ Gamitin ang JavaScript para sa SweetAlert
            $success = true;
            $message = "Request Approved! Email sent to user.";
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
    <title>Approval Status</title>
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
                window.location.href = "cert_request.php"; // Redirect after clicking OK
            });
        });
    </script>
</body>
</html>