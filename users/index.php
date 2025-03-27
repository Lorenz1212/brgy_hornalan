<?php
require '../connection/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['user']);
    $password = trim($_POST['password']);

    $query = "SELECT user, password FROM users WHERE user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_user, $db_password);
        $stmt->fetch();

        if ($password === $db_password) {
            $_SESSION['user'] = $db_user; // Set session
            echo json_encode(["success" => true, "redirect" => "prof.php"]);
            exit();
        } else {
            echo json_encode(["success" => false, "message" => "Invalid password. Please try again."]);
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found. Please sign up first."]);
        exit();
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
    <title>Login Form</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .input-container {
            position: relative;
            width: 100%;
        }

        /* ✅ Ayusin ang Eye Icon (Ilalagay sa Kanan) */
        .eye-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: gray;
            font-size: 18px;
        }
        /* Smaller SweetAlert font sizes */
.small-popup {
    font-size: 14px !important; /* Reduce the font size of the entire popup */
}

.small-title {
    font-size: 16px !important; /* Reduce the font size of the title */
}

.small-content {
    font-size: 12px !important; /* Reduce the font size of the content */
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

        /* ✅ Responsive Design */
        @media (max-width: 768px) {
            .eye-icon {
                font-size: 16px;
                right: 10px;
            }
        }

        @media (max-width: 480px) {
            .eye-icon {
                font-size: 14px;
                right: 8px;
            }
        }

        /* ✅ SweetAlert Custom Spinner */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #1abc9c; /* ✅ Green spinner */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 10px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ✅ Custom Font Sizes for SweetAlert */
        .small-popup {
            font-size: 14px !important;
        }

        .small-title {
            font-size: 16px !important;
        }

        .small-content {
            font-size: 12px !important;
        }


    </style>
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
        <div class="input-container">
            <label for="user"><i class="fas fa-user"></i></label> 
            <input type="text" id="user" name="user" placeholder="Username" required autocomplete="off">
        </div>
        <div class="input-container">
            <label for="password"><i class="fas fa-lock"></i></label> 
            <input type="password" id="password" name="password" placeholder="Password" required autocomplete="off">
            <i class="fas fa-eye eye-icon" id="togglePassword"></i>
        </div>
        <div class="text">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </div>    
        <button type="submit" name="login">Login</button>
    </form>
</div>

<script>

document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let formData = new FormData(this);

    // ✅ Disable login button & change text
    let loginButton = document.querySelector("button[name='login']");
    loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
    loginButton.disabled = true;

    fetch("", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success', // ✅ Check icon animation
                title: 'Login Successful!',
                text: 'Redirecting...',
                showConfirmButton: false,
                timer: 1500, // ✅ Wait for check animation
                willClose: () => {
                    Swal.fire({
                        title: "Redirecting...",
                        html: '<div class="spinner"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });

                    setTimeout(() => {
                        window.location.href = data.redirect; // 🔥 Redirect after spinner
                    }, 2000);
                }
            });

        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message,
                customClass: {
                    popup: 'small-popup',
                    title: 'small-title',
                    content: 'small-content'
                }
            });

            // ✅ Ibalik ang normal login button kung may error
            loginButton.innerHTML = 'Login';
            loginButton.disabled = false;
        }
    })
    .catch(error => {
        console.error("Error:", error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong. Please try again.'
        });

        // ✅ Ibalik ang normal login button kung may error
        loginButton.innerHTML = 'Login';
        loginButton.disabled = false;
    });
});

    document.addEventListener("DOMContentLoaded", function () {
        let toggleIcon = document.getElementById("togglePassword");
        let passwordField = document.getElementById("password");

        function showPassword() {
            passwordField.type = "text";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        }

        function hidePassword() {
            passwordField.type = "password";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        }

        // ✅ Para sa desktop
        toggleIcon.addEventListener("mousedown", showPassword);
        toggleIcon.addEventListener("mouseup", hidePassword);
        toggleIcon.addEventListener("mouseleave", hidePassword);

        // ✅ Para sa mobile
        toggleIcon.addEventListener("touchstart", showPassword);
        toggleIcon.addEventListener("touchend", hidePassword);

        // Show SweetAlert error if session has error message
        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: '<?php echo $_SESSION['error']; ?>',
                confirmButtonColor: '#d33',
                customClass: {
                    popup: 'small-popup',  // Smaller font size for the entire popup
                    title: 'small-title',   // Smaller font size for the title
                    content: 'small-content' // Smaller font size for the content
                }
            });
            <?php unset($_SESSION['error']); ?> // Clear session error after displaying
        <?php endif; ?>

    });
</script>

</body>

<footer class="copyright">
    &copy; 2025 Barangay Information System. All Rights Reserved.
</footer>

<style>
.copyright {
    position: absolute;
    bottom: 10px;
    width: auto;
    text-align: center;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8); /* ✅ Medyo light ang kulay para hindi distracting */
    font-weight: 500;
    background: rgba(0, 0, 0, 0.2); /* ✅ Light overlay */
    padding: 5px;
    border-radius: 5px;
}


</style>
</html>
