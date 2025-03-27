<?php
session_start();
include '../connection/connect.php';

// Kunin ang session username
$username = $_SESSION['username'];

// Query para kunin ang `session_active`
$query = "SELECT session_active FROM admin WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($session_active);
$stmt->fetch();
$stmt->close();

// Kung inactive ang session, mag-log out
if ($session_active == 0) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="sidebar.css">
    <style>
        .container {
            margin-left: 250px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 80px;
        }

        .dashboard-box {
            width: 80%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            margin: 60px auto;
        }

        .box {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            margin: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .box:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .box i {
            font-size: 30px;
            color: #333;
            margin-bottom: 10px;
        }

        .box h2 {
            margin: 10px 0;
            font-size: 15px;
            color: #333;
        }

        .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: red;
            color: white;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="container">
        <div class="dashboard-box">
            <a href="clear_request.php" class="box">
                <i class="fa fa-file"></i>
                <h2>Clearance Requests</h2>
                <p>Click to view pending clearances</p>
                <span class="badge" id="clearancePending">Loading...</span>
            </a>
            <a href="cert_request.php" class="box">
                <i class="fa fa-file"></i>
                <h2>Certificate Requests</h2>
                <p>Click to view pending certificates</p>
                <span class="badge" id="certificatePending">Loading...</span>
            </a>
        </div>
    </div>

    <script>
        // Fetch request counts via AJAX
        function fetchRequestCounts() {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_badges.php", true);
            xhr.onload = function () {
                if (xhr.status == 200) {
                    let data = JSON.parse(xhr.responseText);
                    document.getElementById("clearancePending").innerText = data.Clearance + " Pending";
                    document.getElementById("certificatePending").innerText = data.Certificate + " Pending";
                }
            };
            xhr.send();
        }

        fetchRequestCounts();
        setInterval(fetchRequestCounts, 5000); // 10 seconds

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
