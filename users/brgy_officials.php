<?php
session_start();
include '../connection/connect.php'; // Database Connection


if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='index.php';</script>";
    exit();
}
// Cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Officials</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../users/css/sidebar.css">
    <!-- ✅ Ensure sidebar is included correctly -->
    <?php include 'sidebar.php'; ?>
    <style>
        /* ✅ GENERAL STYLES ✅ */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* ✅ MAIN CONTENT WRAPPER ✅ */
.content-wrapper {
    margin-left: 250px; /* Space for sidebar */
    padding: 80px 40px 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* ✅ Sidebar Header Styles (Responsive) ✅ */
.header-container {
    background: white;
    padding: 15px;
    width: 90%;
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
    .content-wrapper {
        margin-left: 0;
        padding: 60px 20px;
        top: 10px;
    }

    .officials-grid {
        grid-template-columns: repeat(3, 1fr); /* 3 columns for tablets */
    }

    .header-container {
        font-size: 16px; /* Reduce text size for tablets */
        padding: 10px; /* Reduced padding on tablets */
        margin-top: 20px;
    }
}

@media (max-width: 768px) {
    .officials-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 columns for mobile */
    }
    .header-container {
        font-size: 14px; /* Further reduce font size for mobile */
        padding: 8px; /* Smaller padding for mobile */
    }
}

@media (max-width: 480px) {
    .content-wrapper {
        padding: 50px 10px;
    }

    .officials-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 columns for small screens */
    }

    .official-card {
        max-width: 160px; /* Adjust width for mobile screens */
    }

    .official-card h3 {
        font-size: 12px; /* Smaller font size for mobile */
    }

    .position {
        font-size: 10px; /* Smaller font size for position */
    }
    .header-container {
        font-size: 18px; /* Smallest font size for smaller screens */
        padding: 5px; /* Minimal padding for very small screens */
        margin-top: 20px;
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
