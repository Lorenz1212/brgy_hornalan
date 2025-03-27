<?php
session_start();
include '../connection/connect.php';  // Ensure correct path

// Set the timezone to the correct one (example: Manila time)
date_default_timezone_set('Asia/Manila');  // Adjust timezone if necessary

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['username']);  // Use email as username
    $password = trim($_POST['password']);

    $query = "SELECT username, password, otp_code, otp_expiry FROM superadmin WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);  // Match the email
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_email, $db_password, $db_otp, $otp_expiry);
        $stmt->fetch();

        if ($password === $db_password) {
            // Set session after successful login
            $_SESSION['username'] = $db_email;  // Set session username

            // Continue OTP generation process...
            $otp_code = rand(100000, 999999);
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+60 seconds')); // OTP expires in 60 seconds

            // Save OTP and expiry in the database
            $updateQuery = "UPDATE superadmin SET otp_code = ?, otp_expiry = ? WHERE username = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("sss", $otp_code, $otp_expiry, $email);
            $updateStmt->execute();
            $updateStmt->close();

            // Send OTP via email (using PHPMailer)
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'cabugwaschritianjames01156@gmail.com';  // Use your email
                $mail->Password = 'lndb zwhp jzfo bbqi';  // Use App Password here
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your-email@gmail.com', 'System Admin');
                $mail->addAddress($db_email);  // Send OTP to user's email

                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for Login';
                $mail->Body = 'Your OTP code is: ' . $otp_code;

                $mail->send();

                // Redirect to OTP page
                header("Location: verify_otp.php");
                exit();
            } catch (Exception $e) {
                $error_message = "Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "Email not found. Please check your input.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- HTML login form (no changes here) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.9/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
<div class="indext-container fade-in">
    <div class="image-con"></div>
    <form id="loginForm" action="" method="post">
        <div class="title">
            <div class="logo-image">
                <img src="../image/logo1-removebg.png" alt="Profile Image">
            </div>
            <h1>BARANGAY INFORMATION SYSTEM</h1>
        </div>
        <h2>Login</h2>

        <!-- Error Message Display -->
        <?php if (!empty($error_message)): ?>
            <p id="error-message" class="error" style="color: red; font-weight: bold;">
                <?php echo $error_message; ?>
            </p>
        <?php endif; ?>

        <div class="input-container">
            <label for="username"><i class="fas fa-user"></i></label> 
            <input type="text" id="username" name="username" placeholder="Username" required autocomplete="off" autocorrect="off" spellcheck="false">
        </div>
        <div class="input-container">
            <label for="password"><i class="fas fa-lock"></i></label> 
            <input type="password" id="password" name="password" placeholder="Password" required autocomplete="off" autocorrect="off" spellcheck="false">
            <i class="fas fa-eye eye-icon" id="togglePassword"></i>
        </div>

        <button type="submit" name="login">Login</button>
    </form>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.9/dist/sweetalert2.all.min.js"></script>

<script>
    // Password toggle visibility (eye icon)
    var passwordField = document.getElementById("password");
    var toggleIcon = document.getElementById("togglePassword");

    toggleIcon.addEventListener("mousedown", function () {
        passwordField.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    });

    toggleIcon.addEventListener("mouseup", function () {
        passwordField.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    });

    toggleIcon.addEventListener("mouseleave", function () { 
        passwordField.type = "password"; 
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    });

    // Error display logic (para magpakita ng SweetAlert2 notifications)
    document.addEventListener("DOMContentLoaded", function () {
        <?php if (!empty($error_message)): ?>
            // Show error message as SweetAlert
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $error_message; ?>',
            });
        <?php endif; ?>
    });

    // Prevent back navigation to secured pages
    window.history.pushState(null, null, location.href);
    window.onpopstate = function () {
        window.history.pushState(null, null, location.href);
    };
</script>

<style>
    /* ✅ Fade-in effect kapag galing sa signup */
    .fade-in {
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .copyright {
    position: fixed;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    color: rgba(255, 255, 255, 0.9); /* ✅ Medyo maliwanag para readable */
    font-weight: 500;
    background: rgba(0, 0, 0, 0.3); /* ✅ Light overlay */
    padding: 5px 10px;
    border-radius: 5px;
    z-index: 1000; /* ✅ Siguradong nasa ibabaw */
}


</style>
</body>
<footer class="copyright">
    &copy; 2025 Barangay Information System. All Rights Reserved.
</footer>
</html>
