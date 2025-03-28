<?php
session_start();
require '../connection/connect.php';

if (!isset($_SESSION['admin_login_attempts'])) {
    $_SESSION['admin_login_attempts'] = 0; // Init admin attempt counter
}

if (!isset($_SESSION['admin_lockout_time'])) {
    $_SESSION['admin_lockout_time'] = 0; // Init admin lockout timer
}

$cooldown = 60; // 60-second cooldown

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Check if admin is currently locked out
    if ($_SESSION['admin_login_attempts'] >= 5 && time() < $_SESSION['admin_lockout_time']) {
        echo json_encode(["success" => false, "message" => "Too many failed attempts. Try again in " . ($_SESSION['admin_lockout_time'] - time()) . " seconds."]);
        exit();
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT username, password, session_active FROM admin WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_user, $db_password, $session_active);
        $stmt->fetch();

        if ($session_active == 1) {
            echo json_encode(["success" => false, "message" => "This account is already logged in."]);
            exit();
        }

        // ✅ Check if password is correct
        if ($password === $db_password) {
            $_SESSION['username'] = $db_user;
            $_SESSION['admin_login_attempts'] = 0; // Reset failed attempts

            $updateQuery = "UPDATE admin SET session_active = 1 WHERE username = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("s", $username);
            $updateStmt->execute();
            $updateStmt->close();

            echo json_encode(["success" => true, "redirect" => "admin/admin_dashboard.php"]);
            exit();
        } else {
            // ✅ Increase attempt counter
            $_SESSION['admin_login_attempts']++;

            // ✅ Lockout on exactly 5th failed attempt
            if ($_SESSION['admin_login_attempts'] === 5) {
                $_SESSION['admin_lockout_time'] = time() + $cooldown;
                echo json_encode(["success" => false, "message" => "Too many failed attempts. Please wait 60 seconds before trying again."]);
            } else {
                echo json_encode(["success" => false, "message" => "Incorrect password. Attempts left: " . (5 - $_SESSION['admin_login_attempts'])]);
            }
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "Username not found."]);
        exit();
    }

    $stmt->close();
}
?>



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

document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let formData = new FormData(this);
    let loginButton = document.querySelector("button[name='login']");
    
    loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
    loginButton.disabled = true;

    fetch("admin_login.php", {
        method: "POST",
        body: new URLSearchParams(formData),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Login Successful!',
                text: 'Redirecting...',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = data.redirect;
            });

        } else {
            if (data.message.includes("Try again in")) {
                let secondsLeft = parseInt(data.message.match(/\d+/)[0]);
                startCooldown(secondsLeft);
            }

            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message
            });

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

        loginButton.innerHTML = 'Login';
        loginButton.disabled = false;
    });
});

// ✅ Function para sa cooldown button
function startCooldown(seconds) {
    let loginButton = document.querySelector("button[name='login']");
    loginButton.disabled = true;
    let countdown = setInterval(() => {
        if (seconds <= 0) {
            clearInterval(countdown);
            loginButton.innerHTML = "Login";
            loginButton.disabled = false;
        } else {
            loginButton.innerHTML = "Try again in " + seconds + "s";
            seconds--;
        }
    }, 1000);
}



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

    // Show error message as SweetAlert
    document.addEventListener("DOMContentLoaded", function () {
        <?php if (!empty($error_message)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $error_message; ?>',
                showConfirmButton: true,  // Ensure a button is visible
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
    /* ✅ Custom Spinner sa SweetAlert */
.spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: #1abc9c; /* ✅ Green spinner */
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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
</body>

<footer class="copyright">
    &copy; 2025 Barangay Information System. All Rights Reserved.
</footer>

</html>
