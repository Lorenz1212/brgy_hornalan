<?php
session_start();
require '../connection/connect.php'; // Connection sa database
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Ensure that user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "You need to log in first.";
    header("Location: login.php");
    exit();
}

// Get the user's verification code and expiration time
$user = $_SESSION['user'];
$query = "SELECT verification_code, code_expiry FROM users WHERE user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($verification_code, $code_expiry);
$stmt->fetch();

$current_time = time();
$expiry_time = strtotime($code_expiry);
$remaining_time = max(0, $expiry_time - $current_time);

// Initialize countdown when confirmed in the previous page
if (isset($_SESSION['countdown_started']) && $_SESSION['countdown_started'] === true) {
    $_SESSION['countdown_started'] = false;  // Reset the flag so it doesn't repeat
    $remaining_time = 60;  // Countdown starts here

    $new_expiry_time = date('Y-m-d H:i:s', strtotime('+60 seconds'));
    $update_query = "UPDATE users SET code_expiry = ? WHERE user = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ss", $new_expiry_time, $user);
    $update_stmt->execute();
} else {
    $remaining_time = max(0, strtotime($code_expiry) - time());
}

// Handle Resend Code
if (isset($_POST['resend_code'])) {
    $new_verification_code = rand(100000, 999999);
    $new_expiry_time = date('Y-m-d H:i:s', strtotime('+60 seconds'));

    $update_query = "UPDATE users SET verification_code = ?, code_expiry = ? WHERE user = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sss", $new_verification_code, $new_expiry_time, $user);
    $update_stmt->execute();

    // Send the new verification code via email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'cabugwaschritianjames01156@gmail.com';  // Replace with your email
        $mail->Password = 'lndb zwhp jzfo bbqi'; // Replace with your password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'Admin');
        $mail->addAddress($user);
        $mail->isHTML(true);
        $mail->Subject = 'New Verification Code';
        $mail->Body = "Your new verification code is: <b>$new_verification_code</b>. This code will expire in 60 seconds.";
        $mail->send();

        // Return a success message to trigger SweetAlert
        echo json_encode(["success" => true, "message" => "A new verification code has been sent!"]);
    } catch (Exception $e) {
        echo json_encode(["error" => true, "message" => "Mailer Error: {$mail->ErrorInfo}"]);
    }
    exit();
}

// Handle verification code submission
if (isset($_POST['verify_code'])) {
    $entered_code = $_POST['code'];

    if ($entered_code == $verification_code && $remaining_time > 0) {
        $_SESSION['good'] = "Verification successful. You can now reset your password.";
        header("Location: reset_password.php?userID=" . $_SESSION['userID']);
        exit();
    } else {
        $_SESSION['error'] = "Invalid or expired verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: url("../image/bg.png") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .container {
            position: relative;  
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        h2 {
            font-size: 26px;
            color: #333;
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #34495e;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        button:hover {
            background-color: #16a085;
        }

        #timer {
            margin-top: 15px;
            font-size: 14px;
            color: #555;
        }

        #resendForm {
            width: 100%;
        }
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

    </style>
</head>
<body>

<div class="container fade-in">
    <h2>Enter Verification Code</h2>

    <!-- Verification Code Form -->
    <form action="verify_code.php" method="POST" id="verifyCodeForm">
        <input type="text" name="code" placeholder="Enter Verification Code" required autocomplete="off">
        <button type="submit" name="verify_code">Verify Code</button>
    </form>

    <!-- Countdown Timer -->
    <p id="timer" style="display: none;">
        Resend in <span id="countdown"><?php echo $remaining_time; ?></span> seconds
    </p>
    <form action="verify_code.php" method="POST" id="resendForm" style="display: none;">
        <button type="submit" name="resend_code">Resend Code</button>
    </form>

</div>

<script>
   let countdown = <?php echo $remaining_time; ?>;

if (countdown > 0) {
    document.getElementById("timer").style.display = "block";
    let timer = setInterval(() => {
        countdown--;
        document.getElementById("countdown").innerText = countdown;
        if (countdown <= 0) {
            clearInterval(timer);
            document.getElementById("timer").style.display = "none";
            document.getElementById("resendForm").style.display = "block";  // Resend button will appear after timer finishes
        }
    }, 1000);
} else {
    document.getElementById("resendForm").style.display = "block";  // Resend button will appear if no timer
}

// Handle Resend Code with SweetAlert Loading
document.getElementById('resendForm').onsubmit = function(event) {
    event.preventDefault();  // Prevent default form submission

    // Hide Resend Code button while the process is ongoing
    document.getElementById("resendForm").style.display = "none";  // Hide the button
    Swal.fire({
        title: 'Resending Code...',
        text: 'Please wait while we send a new verification code.',
        icon: 'info',
        showConfirmButton: false
    });

    Swal.showLoading();  // Show the SweetAlert spinner

    // AJAX request to resend the code
    var formData = new FormData();
    formData.append('resend_code', true);  // Indicate the form submission action

    fetch('verify_code.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'New Code Sent!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#16a085'
            }).then(() => {
                // Start countdown only after clicking "OK"
                startCountdown();
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#e74c3c'
            }).then(() => {
                document.getElementById("resendForm").style.display = "block";  // Show button again if there is an error
            });
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: 'There was an issue sending the verification code. Please try again.',
            icon: 'error',
            confirmButtonColor: '#e74c3c'
        }).then(() => {
            document.getElementById("resendForm").style.display = "block";  // Show button again if there is an error
        });
    });
}

// Countdown function
function startCountdown() {
    let countdown = 60;
    document.getElementById("timer").style.display = "block";

    let timer = setInterval(() => {
        countdown--;
        document.getElementById("countdown").innerText = countdown;
        if (countdown <= 0) {
            clearInterval(timer);
            document.getElementById("timer").style.display = "none";
            document.getElementById("resendForm").style.display = "block";
        }
    }, 1000);
}

//Animation
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".reset-box").classList.add("fade-in");
    });
</script>

</body>
</html>
