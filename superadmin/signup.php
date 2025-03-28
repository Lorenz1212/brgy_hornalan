<?php
session_start();
include '../connection/connect.php';


// Check kung may session username na
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='index.php';</script>";
    exit();
}

// Wala na ang role check, diretso na lang sa dashboard
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // ✅ Check kung may existing username
    $checkQuery = "SELECT accountID FROM admin WHERE username = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username already exists!";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
    } else {
        // ✅ Insert Admin account
        $insertQuery = "INSERT INTO admin (username, password, role) VALUES (?, ?, 'admin')";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $_SESSION['success'] = "New admin successfully created!";
            header("Location: signup.php");
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong!";
        }
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
    <title>Register New Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        /* ✅ BODY & FULLSCREEN CENTER */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .signup-container {
            width: 100%;
            max-width: 400px;
            padding: 25px;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            text-align: center;
            position: relative;
        }

        /* ✅ Close Button (X) */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 18px;
            color: #888;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #333;
        }

        .input-container {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 8px;
            background: white;
            margin-bottom: 15px;
        }

        .input-container label {
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-size: 16px;
        }

        .input-container input {
            border: none;
            outline: none;
            padding: 10px;
            flex: 1;
            font-size: 14px;
            background: white;
        }

        .eye-icon {
            cursor: pointer;
            color: #666;
            margin-left: auto;
            padding-right: 10px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #16a085;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn:hover {
            background: #1abc9c;
        }

        /* ✅ RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .signup-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="signup-container">
        <span class="close-btn" onclick="window.location.href='admin_list.php'">&times;</span>
        <h2>Register New Admin</h2>

        <!-- SweetAlert Notifications -->
        <?php if (isset($_SESSION['error'])): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: '<?php echo $_SESSION['error']; ?>',
                    confirmButtonColor: '#d33'
                });
                <?php unset($_SESSION['error']); ?>
            </script>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: '<?php echo $_SESSION['success']; ?>',
                    confirmButtonColor: '#28a745'
                });
                <?php unset($_SESSION['success']); ?>
            </script>
        <?php endif; ?>

        <form id="signupForm" action="signup.php" method="post">
            <div class="input-container">
                <label for="username"><i class="fas fa-user"></i></label> 
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>

            <div class="input-container">
                <label for="password"><i class="fas fa-lock"></i></label> 
                <input type="password" id="password" name="password" placeholder="Password" required>
                <i class="fas fa-eye eye-icon" id="togglePassword"></i>
            </div>

            <div class="input-container">
                <label for="confirm_password"><i class="fas fa-lock"></i></label> 
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <i class="fas fa-eye eye-icon" id="toggleConfirmPassword"></i>
            </div>

            <button type="submit" class="btn">Sign Up</button>
        </form>
    </div>

    <script>
        // ✅ Password Visibility Toggle
        function togglePasswordVisibility(inputId, iconId) {
            var passwordField = document.getElementById(inputId);
            var toggleIcon = document.getElementById(iconId);

            toggleIcon.addEventListener("mousedown", function () {
                passwordField.type = "text";
                toggleIcon.classList.add("fa-eye-slash");
                toggleIcon.classList.remove("fa-eye");
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

        togglePasswordVisibility("password", "togglePassword");
        togglePasswordVisibility("confirm_password", "toggleConfirmPassword");
    </script>
</body>
</html>
