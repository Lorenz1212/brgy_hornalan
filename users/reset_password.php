<?php
session_start();
require '../connection/connect.php'; // Connection sa database

// Check if userID is set
if (isset($_GET['userID'])) {
    $userID = $_GET['userID'];

    // Process new password
    if (isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if passwords match
        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match. Please try again.";
        } else {
            // Update password sa database (walang hashing)
            $query = "UPDATE users SET password = ? WHERE userID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $new_password, $userID);
            $stmt->execute();

            // Set success message
            $_SESSION['success'] = "Password updated successfully.";

            // Redirect para maiwasan ang resubmission
            header("Location: reset_password.php?userID=" . $userID);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* SweetAlert2 font size adjustment */
        .swal2-popup {
            font-size: 14px !important; /* Adjust font size here */
        }

        * {
            font-family: "Poppins", sans-serif;
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

        /* ✅ Dark Overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); /* Darkens the background (adjust the opacity to make it darker or lighter) */
            z-index: -1; /* Keeps the overlay behind the content */
        }

        .reset-box {
            background-color: rgba(255, 255, 255, 0.7);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            backdrop-filter: blur(5px);
            position: relative;
            z-index: 1;
        }

        .reset-box h2 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #333;
        }

        /* Input Container */
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            margin-bottom: 15px;
        }

        /* Input Field */
        .input-container input {
            width: 100%;
            padding: 10px 35px 10px 35px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        .input-container input:focus {
            border-color: #16a085;
        }

        /* Lock Icon */
        .input-container .lock-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: gray;
            font-size: 18px;
        }

        /* Eye Icon */
        .input-container .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: gray;
            font-size: 18px;
        }

        /* Submit Button */
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #16a085;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #1abc9c;
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

        /* Media Queries for responsiveness */
        @media (max-width: 480px) {
            .reset-box {
                padding: 20px;
                max-width: 90%;
            }

            .reset-box h2 {
                font-size: 24px;
            }

            .input-container input {
                font-size: 14px;
            }

            button[type="submit"] {
                font-size: 14px;
            }
        }

    </style>
</head>
<body>

<div class="reset-box fade-in">
    <h2>Reset Your Password</h2>

    <!-- Reset Password Form -->
    <form action="reset_password.php?userID=<?php echo htmlspecialchars($userID); ?>" method="POST">
        <div class="input-container">
            <i class="fas fa-lock lock-icon"></i>
            <input type="password" id="new_password" name="new_password" placeholder="Enter New Password" required>
            <i class="fas fa-eye eye-icon" id="toggleNewPassword"></i>
        </div>

        <div class="input-container">
            <i class="fas fa-lock lock-icon"></i>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
            <i class="fas fa-eye eye-icon" id="toggleConfirmPassword"></i>
        </div>

        <button type="submit" name="reset_password">Reset Password</button>
    </form>
</div>

<script>
    // Function to toggle password visibility
    function togglePassword(inputId, iconId) {
        var passwordField = document.getElementById(inputId);
        var toggleIcon = document.getElementById(iconId);

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
    }

    // Apply sa password fields
    togglePassword("new_password", "toggleNewPassword");
    togglePassword("confirm_password", "toggleConfirmPassword");

    // Show SweetAlert for password mismatch
    document.querySelector("form").addEventListener("submit", function (event) {
        var newPassword = document.getElementById("new_password").value;
        var confirmPassword = document.getElementById("confirm_password").value;

        if (newPassword !== confirmPassword) {
            event.preventDefault();
            Swal.fire({
                title: '❌ Passwords do not match.',
                text: 'Please try again.',
                icon: 'error',
                confirmButtonColor: '#e74c3c'
            });
        }
    });

    // Show SweetAlert messages if there are session messages
    document.addEventListener("DOMContentLoaded", function () {
        var messages = [
            "<?php echo isset($_SESSION['good']) ? $_SESSION['good'] : ''; ?>",
            "<?php echo isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>",
            "<?php echo isset($_SESSION['error']) ? $_SESSION['error'] : ''; ?>"
        ].filter(msg => msg.trim() !== '');

        if (messages.length > 0) {
            var finalMessage = messages.join("<br>");
            var redirect = messages.includes("Password updated successfully.");

            Swal.fire({
                title: finalMessage,
                icon: redirect ? 'success' : 'error',
                confirmButtonColor: '#16a085'
            }).then((result) => {
                if (result.isConfirmed && redirect) {
                    window.location.href = "user.php"; // Redirect after success
                }
            });

            <?php unset($_SESSION['success'], $_SESSION['error']); ?>
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
    var successMessage = "<?php echo isset($_SESSION['good']) ? $_SESSION['good'] : ''; ?>";

    if (successMessage) {
        Swal.fire({
            title: successMessage,
            icon: 'info',
            confirmButtonColor: '#16a085'
        });
        <?php unset($_SESSION['good']); ?> // Clear the session message after displaying it
    }
});

</script>

</body>
</html>
