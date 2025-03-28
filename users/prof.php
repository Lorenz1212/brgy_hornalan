<?php
session_start();
require '../connection/connect.php';

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='index.php';</script>";
    exit();
}
// Cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar.css">
</head>
<body>

    <!-- ✅ Ensure sidebar is included correctly -->
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <div class="dashboard-box">
            <a href="clearance.php" class="box">
                <i class="fa fa-file"></i>
                <h2>Clearances</h2>
                <p>Click to view details</p> 
            </a>
            <a href="certificate.php" class="box">
                <i class="fa fa-file"></i>
                <h2>Certificates</h2>
                <p>Click to view details</p> 
            </a>
            <a href="brgy_officials.php" class="box">
                <i class="fa fa-users"></i>
                <h2>Barangay Elected Officials</h2>
                <p>Click to view details</p>
            </a>
        </div>
    </div>
<script>

    // Inactivity timer - logout after 5 minutes of inactivity
let timeout;
        const logoutTime = 5 * 60 * 1000; // 5 minutes

        function resetTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(logoutUser, logoutTime);
        }

        function logoutUser() {
            // Redirect to logout page after 5 minutes of inactivity
            window.location.href = "logout.php"; 
        }

        // Detect user activity
        window.onload = resetTimer; // Reset timer on page load
        document.onmousemove = resetTimer; // Reset timer on mouse movement
        document.onkeypress = resetTimer; // Reset timer on keyboard input

        
</script>
</body>
</html>
<style>
   /* ✅ GENERAL STYLES ✅ */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
}

/* ✅ CONTAINER FOR DASHBOARD ✅ */
.container {
    margin-left: 250px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    padding: 120px;
    transition: margin-left 0.3s ease-in-out;
}

/* ✅ DASHBOARD BOX ✅ */
.dashboard-box {
    width: 80%;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin: auto;
}

/* ✅ BOX STYLE ✅ */
.box {
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 300px;
    height: 150px;
    margin: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
}

/* ✅ HOVER EFFECT ✅ */
.box:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* ✅ ICON STYLE ✅ */
.box i {
    font-size: 30px;
    color: #333;
    margin-bottom: 10px;
}

/* ✅ TEXT STYLE ✅ */
.box h2 {
    margin: 10px 0;
    font-size: 18px;
    color: #333;
    font-weight: bold;
}

.box p {
    color: #666;
    font-size: 15px;
}

/* ✅ RESPONSIVE DESIGN ✅ */
@media screen and (max-width: 1024px) {
    .container {
        margin-left: 0;
        padding: 60px 20px;
        justify-content: center; /* ✅ Center ang content */
    }

    .sidebar {
        transform: translateX(-100%);
        position: fixed;
    }

    .sidebar.open {
        transform: translateX(0);
    }
}

@media screen and (max-width: 768px) {
    .container {
        display: flex;
        flex-direction: column;
        align-items: center; /* ✅ Center ang laman */
        padding: 60px 10px;
    }

    .dashboard-box {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .box {
        width: 90%;
        max-width: 320px; /* ✅ Para hindi masyadong malapad */
    }
}

@media screen and (max-width: 480px) {
    .container {
        padding: 80px 10px;
    }

    .dashboard-box {
        width: 100%;
    }

    .box {
        width: 100%;
        max-width: 300px;
        padding: 15px;
        text-align: center;
    }

    .box i {
        font-size: 25px;
    }

    .box h2 {
        font-size: 14px;
    }

    .box p {
        font-size: 12px;
    }
}

    </style>