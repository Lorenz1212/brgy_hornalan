<?php
session_start();
include '../connection/connect.php';

// Debugging: Check if session is set properly
if (!isset($_SESSION['username'])) {
    error_log("Session not set. Username is missing.");
    echo json_encode(["status" => "error", "message" => "Session expired, please log in again."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = trim($_POST['otp_code']);

    // Debugging: Log session username and OTP
    error_log("Session username: " . $_SESSION['username']);
    
    $query = "SELECT otp_code, otp_expiry FROM superadmin WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_SESSION['username']);  // Use session username
    $stmt->execute();
    $stmt->bind_result($db_otp, $otp_expiry);
    $stmt->fetch();
    $stmt->close();

    // Log OTP and expiry from the database
    error_log("DB OTP: " . $db_otp);
    error_log("DB OTP Expiry: " . $otp_expiry);

    if (!$db_otp) {
        echo json_encode(["status" => "error", "message" => "No OTP found. Please request again."]);
    } elseif ($entered_otp !== $db_otp) {
        echo json_encode(["status" => "error", "message" => "Invalid OTP!"]);
    } elseif (strtotime($otp_expiry) < time()) {
        echo json_encode(["status" => "error", "message" => "Your OTP has expired."]);
    } else {
        $clear_otp = "UPDATE superadmin SET otp_code = NULL, otp_expiry = NULL WHERE username = ?";
        $stmt = $conn->prepare($clear_otp);
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["status" => "success", "message" => "OTP Verified!", "redirect" => "superadmin_dashboard.php"]);
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        verifyOTP(); // Automatically run SweetAlert when the page loads
    });

    function verifyOTP() {
        Swal.fire({
            title: 'Enter OTP',
            input: 'text',
            inputPlaceholder: 'Enter your OTP code',
            inputAttributes: {
                autocomplete: 'off' // Disable autocomplete
            },
            showCancelButton: true,
            showCloseButton: true, // X Button
            allowOutsideClick: false,
            confirmButtonText: 'Verify',
            cancelButtonText: 'Cancel',
            width: '400px',
            didClose: () => { 
                window.location.href = 'index.php'; // Redirect when closed
            },
            preConfirm: (otp) => {
                // Send OTP code to verify_otp.php using POST
                return fetch('verify_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'otp_code=' + encodeURIComponent(otp) // Sending the OTP
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "error") {
                        // Show error if OTP is incorrect or expired
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message,
                            confirmButtonColor: '#d33',
                            showCloseButton: true,
                            didClose: () => { verifyOTP(); } // Retry OTP modal when closed
                        });
                    } else if (data.status === "success") {
                        // Show success and redirect to the dashboard
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            confirmButtonColor: '#3085d6',
                            showCloseButton: true,
                        }).then(() => {
                            window.location.href = data.redirect; // Redirect to dashboard
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Show generic error message if the request fails
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed!',
                        text: 'Something went wrong. Please try again.',
                        confirmButtonColor: '#d33',
                        showCloseButton: true,
                        didClose: () => { verifyOTP(); } // Retry OTP modal when closed
                    });
                });
            }
        });
    }
</script>

</body>
</html>
