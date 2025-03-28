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
    header("Location: index.php");
    exit();
}

// Function to fetch barangay officials
function getBrgyOfficials($conn) {
    $sql = "SELECT * FROM brgy_official ORDER BY FIELD(position, 
        'Barangay Chairman', 
        'Committee of Peace and Order', 
        'Committee on Education', 
        'Committee on Public Works and Hi-ways', 
        'Committee on Appropriation', 
        'Committee on Womens and Family',  
        'Committee on Agriculture',       
        'Committee on Health',   
        'SK Chairman Committee on Sports and Youth Development',                                  
        'Barangay Secretary',            
        'Barangay Treasurer'
    )";
    $result = mysqli_query($conn, $sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

$barangayOfficials = getBrgyOfficials($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'sidebar.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Officials</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* ✅ MAIN CONTENT WRAPPER ✅ */
        .content-wrapper {
            margin-left: 250px;
            padding: 90px 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ✅ HEADER DESIGN ✅ */
        .header-container {
            background: white;
            padding: 15px;
            width: 90% ;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            border: 2px solid #1abc9c;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* ✅ OFFICIALS CONTAINER (SCROLLBAR SA LOOB, CENTERED) ✅ */
        .officials-container {
            width: 90%; /* ✅ Mas maliit para centered */
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            max-height: 75vh; /* ✅ Para may vertical scroll */
            overflow-y: auto; /* ✅ Scrollbar lang sa taas-baba */
        }

        /* ✅ GRID LAYOUT (MAAYOS ANG PAGKA-CENTER) ✅ */
        .officials-grid {
            display: flex;
            flex-wrap: wrap; /* ✅ Para hindi mag-overflow */
            justify-content: center; /* ✅ Centered na lahat */
            gap: 50px;
            padding: 20px;
        }

        /* ✅ CHAIRMAN ROW ✅ */
        .chairman-row {
            display: flex;
            justify-content: center;
            width: 100%; /* ✅ Para hindi lumagpas */
            margin-bottom: 20px;
        }

        /* ✅ FIRST 5 OFFICIALS ✅ */
        .upper-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* ✅ Centered */
            
            margin-bottom: 20px;
        }

        /* ✅ LAST 5 OFFICIALS ✅ */
        .lower-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* ✅ Centered */
            gap: 50px;
        }

        /* ✅ OFFICIAL CARD DESIGN ✅ */
        .official-card {
            width: 200px;
            background: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out;
        }

        .official-card:hover {
            transform: scale(1.05);
        }

        /* ✅ IMAGE STYLE ✅ */
        .official-card img {
            width: 100%;
            height: 170px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #34495e;
            margin-bottom: 15px;
        }

        /* ✅ TEXT STYLES ✅ */
        .official-card h3 {
            margin: 10px 0 5px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .position {
            font-size: 12px;
            color: #555;
        }

        /* ✅ RESPONSIVE DESIGN ✅ */
        @media (max-width: 1024px) {
            .officials-container {
                    width: 95%;
                }
                .upper-row, .lower-row {
                    gap: 15px;
                }

            .upper-row, .lower-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .upper-row, .lower-row {
            flex-direction: column;
            align-items: center;
        }
            .upper-row, .lower-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .upper-row, .lower-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>


    <div class="content-wrapper">
        <div class="header-container">
        BARANGAY ELECTED OFFICIALS
        </div>

        <div class="officials-container">
            <?php 
            if (!empty($barangayOfficials)) :
                echo '<div class="chairman-row">';
                foreach ($barangayOfficials as $official) :
                    if ($official['position'] === 'Barangay Chairman') {
                        echo '<div class="official-card">
                                <img src="' . '../' . htmlspecialchars($official['profile']) . '" 
                                    alt="Profile Picture" onerror="this.onerror=null; this.src=\'../image/default.png\';">
                                <h3>' . htmlspecialchars($official['name']) . '</h3>
                                <p class="position">' . htmlspecialchars($official['position']) . '</p>
                            </div>';
                        break;
                    }
                endforeach;
                echo '</div>'; // Close chairman row
            endif;
            ?>

            <!-- ✅ FIRST 5 OFFICIALS -->
            <div class="officials-grid upper-row">
                <?php
                $count = 0;
                $otherOfficials = []; // Array para ma-track ang natitirang officials

                foreach ($barangayOfficials as $official) :
                    if ($official['position'] !== 'Barangay Chairman') {
                        if ($count < 5) {
                            echo '<div class="official-card">
                                    <img src="' . '../' . htmlspecialchars($official['profile']) . '" 
                                        alt="Profile Picture" onerror="this.onerror=null; this.src=\'../image/default.png\';">
                                    <h3>' . htmlspecialchars($official['name']) . '</h3>
                                    <p class="position">' . htmlspecialchars($official['position']) . '</p>
                                </div>';
                        } else {
                            $otherOfficials[] = $official; // I-save ang natitirang officials para sa lower row
                        }
                        $count++;
                    }
                endforeach;
                ?>
            </div>

            <!-- ✅ LAST 5 OFFICIALS -->
            <div class="officials-grid lower-row">
                <?php
                foreach ($otherOfficials as $official) :
                    echo '<div class="official-card">
                            <img src="' . '../' . htmlspecialchars($official['profile']) . '" 
                                alt="Profile Picture" onerror="this.onerror=null; this.src=\'../image/default.png\';">
                            <h3>' . htmlspecialchars($official['name']) . '</h3>
                            <p class="position">' . htmlspecialchars($official['position']) . '</p>
                        </div>';
                endforeach;
                ?>
            </div>
        </div>

    </div>
</body>
</html>

<script>
    let isSessionExpired = false;  // Variable to track session expiration

        // Function to handle user activity (click, keypress, etc.)
        function updateLastActivity() {
            sessionStorage.setItem('last_activity', Date.now());  // Update the last activity timestamp
        }

        // Event listeners for common user activity
        document.addEventListener('click', updateLastActivity);
        document.addEventListener('keypress', updateLastActivity);
        document.addEventListener('mousemove', updateLastActivity);

        // Function to check session timeout
        function checkSession() {
            let lastActivity = sessionStorage.getItem('last_activity');
            const timeoutDuration = 600000;  // 10 minutes in milliseconds

            // Check if the last activity is older than the timeout duration
            if (lastActivity && (Date.now() - lastActivity) > timeoutDuration) {
                // Mark the session as expired and logout
                isSessionExpired = true;
                sessionStorage.setItem('last_activity', Date.now());  // Update the last activity time even if expired
                window.location.href = 'logout.php';  // Redirect to logout page
            }
        }

        // Check session every 10 seconds (or adjust as needed)
        setInterval(checkSession, 10000);  // Check every 10 seconds

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