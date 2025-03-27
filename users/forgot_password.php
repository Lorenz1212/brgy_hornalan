<?php
session_start();
require '../connection/connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);
$show_popup = false;
$popup_message = '';
$popup_icon = '';

if (isset($_POST['request_reset']) && isset($_POST['user'])) {
    $user_email = trim($_POST['user']);
    $_SESSION['user'] = $user_email; // ✅ Save email sa session

    // ✅ Hanapin ang user sa database
    $query = "SELECT userID FROM users WHERE user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userID);
        $stmt->fetch();
        $_SESSION['userID'] = $userID; // ✅ Save userID sa session

        // ✅ Debugging logs
        error_log("✅ DEBUG: userID sa forgot_password.php = " . $_SESSION['userID']);

        // ✅ Generate verification code
        $verification_code = rand(100000, 999999);
        $expiry_time = date('Y-m-d H:i:s', strtotime('+60 seconds'));

        // ✅ Store verification code sa database
        $update_query = "UPDATE users SET verification_code = ?, code_expiry = ? WHERE userID = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $verification_code, $expiry_time, $userID);
        $update_stmt->execute();

        // ✅ Send verification code via email
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'cabugwaschritianjames01156@gmail.com';
            $mail->Password   = 'lndb zwhp jzfo bbqi';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your_email@gmail.com', 'Admin');
            $mail->addAddress($user_email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code';
            $mail->Body    = "Your verification code is: <b>$verification_code</b>. This code will expire in 60 seconds.";

            $mail->send();
            $_SESSION['countdown_started'] = true;
            header("Location: verify_code.php?userID=" . $_SESSION['userID']); // ✅ Redirect with userID
            exit();
        } catch (Exception $e) {
            $popup_message = "Mailer Error: {$mail->ErrorInfo}";
            $popup_icon = "error";
            $show_popup = true;
        }
    } else {
        $popup_message = "No user found with that email.";
        $popup_icon = "error";
        $show_popup = true;
    }

    $stmt->close();
    $conn->close();
}


// ✅ Redirect to verification page
if ($show_popup && !empty($redirect_url)) {
    header("Location: $redirect_url");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: 'Poppins', sans-serif;
            background: url("../image/bg.png") no-repeat center center fixed;
            background-size: cover; /* Fullscreen background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-attachment: fixed;
        }

        /* Dark Overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); /* Darkens the background */
            z-index: -1;
        }

        /* Forgot Password Form */
        .forgot-box {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .forgot-box h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            align-self: flex-start;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }

        input {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
            color: #333;
            transition: 0.3s ease;
        }

        input:focus {
            border-color: #16a085;
            outline: none;
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #34495e;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        button:hover {
            background-color: #16a085;
        }

        /* Close (X) Button */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            color: #333;
            cursor: pointer;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #16a085;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            margin-top: 20px;
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


        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            form {
                width: 90%;
                padding: 30px;
            }

            input, button {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            form {
                width: 100%;
                padding: 25px;
            }

            input, button {
                font-size: 14px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>

<div class="forgot-box fade-in">
    <span class="close-btn" onclick="window.location.href='user.php'">X</span>
    <h2>Forgot Password</h2>

    <!-- Forgot Password Form -->
    <form action="forgot_password.php" method="POST" id="forgotPasswordForm">
        <input type="text" name="user" placeholder="Enter Username or Email" required autocomplete="off">
        <button type="submit" name="request_reset">Send Verification Code</button>
    </form>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner"></div>
</div>

<script>
    // Show SweetAlert popup
    function showPopup() {
        Swal.fire({
            title: 'Notification',
            text: '<?php echo $popup_message; ?>',
            icon: '<?php echo $popup_icon; ?>',
            confirmButtonColor: '#16a085',
            confirmButtonText: 'Continue'
        }).then((result) => {
            if (result.isConfirmed) {
                <?php $_SESSION['countdown_started'] = true; ?>
                window.location.href = '<?php echo $redirect_url; ?>'; // Redirect to verification page
            }
        });
    }

    // Show the loading spinner when the form is being submitted
    document.getElementById('forgotPasswordForm').onsubmit = function() {
        document.getElementById('loadingSpinner').style.display = 'block';
    }


    // Show SweetAlert popup if the message is set
    <?php if ($show_popup): ?>
        showPopup();
    <?php endif; ?>

    //Animation
    document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("fade-in");
    });
</script>

</body>
</html>
