
<?php
include '../connection/connect.php';

// Siguraduhing naka-login ang user
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];

    // Query para kunin ang profile picture at ibang user info
    $query = "SELECT firstname, profile_pic FROM users WHERE user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->bind_result($firstname, $profile_pic);
    $stmt->fetch();
    $stmt->close();

    // Default profile picture kung walang naka-upload
    $profilePic = !empty($profile_pic) ? "../uploads/" . $profile_pic : "../uploads/default.png";
} else {
    $profilePic = "../uploads/default.png"; // Default picture kung hindi naka-login
}
?>
<link rel="stylesheet" type="text/css" href="../css/sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<!-- Navbar at the top right -->
<div class="navbar">
    <div class="user-info">
        <!-- Sidebar Toggle Button (Burger Menu) -->
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <button class="dropbtn">
            <!-- Display Profile Picture or Default Image -->
            <img src="<?php echo $profilePic; ?>" alt="User Profile" class="user-profile-pic">
            <i class="fas fa-caret-down"></i> User
        </button>
        <div class="dropdown-content" id="userDropdown">
            <a href="profile.php">Profile</a>
            <a href="#" id="logoutBtn">Logout</a>
        </div>
        <script>
            // Your existing script for sidebar toggle and dropdown
        </script>
    </div>
</div>

    <style>
        /* Profile Picture Style in Navbar */
    .user-profile-pic {
        width: 50px; /* Adjust size as needed */
        height: 50px;
        border-radius: 50%; /* Make it circular */
        object-fit: cover; /* Ensures the image fits inside the circle */
        margin-right: 8px; /* Adds space between the image and the name */
        vertical-align: middle; /* Vertically align the image */
    }

    </style>

    <script>

                // ✅ Sidebar Toggle sa Mobile
    const sidebar = document.querySelector(".sidebar");
    const toggleButton = document.createElement("button");
    toggleButton.className = "menu-toggle";
    toggleButton.innerHTML = "&#9776;";
    document.body.appendChild(toggleButton);

    toggleButton.addEventListener("click", function () {
        sidebar.classList.toggle("open");
    });

    // ✅ Dropdown Toggle
    const dropdownButton = document.querySelector(".dropbtn");
    const dropdownContent = document.querySelector(".dropdown-content");

    dropdownButton.addEventListener("click", function () {
        dropdownContent.classList.toggle("show");
    });

    // ✅ Close dropdown kapag nag-click sa labas
    window.addEventListener("click", function (event) {
        if (!dropdownButton.contains(event.target) && !dropdownContent.contains(event.target)) {
            dropdownContent.classList.remove("show");
        }
    });

    function toggleSidebar() {
        var sidebar = document.querySelector(".sidebar");
        sidebar.classList.toggle("open"); // ✅ I-toggle ang class para lumabas o mawala
    }



    document.addEventListener("DOMContentLoaded", function () {
    // ✅ Logout Confirmation using SweetAlert2
    const logoutLink = document.querySelector("#logoutBtn");

    if (logoutLink) {
        logoutLink.addEventListener("click", function (event) {
            event.preventDefault(); // ✅ Pigilan ang default action

            Swal.fire({
                title: "Are you sure you want to logout?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes",
                cancelButtonText: "No"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "logout.php"; // ✅ Redirect to logout
                }
            });
        });
    }
});

$(document).ready(function() {
    function fetchRequestCounts() {
        $.ajax({
            url: 'fetch_badges.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                updateBadge('#pendingBadge', response.pending);
                updateBadge('#approvedBadge', response.approved);
                updateBadge('#pickupBadge', response.pickup);
                updateBadge('#rejectedBadge', response.rejected);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching counts:', error);
            }
        });
    }

    function updateBadge(selector, count) {
        let badge = $(selector);
        if (count > 0) {
            badge.text(count).show(); // ✅ Ipakita lang kung may laman
        } else {
            badge.hide(); // ✅ Itago kung wala pang laman
        }
    }

    fetchRequestCounts();
    setInterval(fetchRequestCounts, 10000); // Update every 10 seconds
});


        </script>

    </div>
</div>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <img src="../image/logo1-removebg.png" alt="Barangay Logo" class="logo">
    </div>

    <div class="sidebar-nav">
        <p>Hello, <strong><?php echo $firstname ?: 'User'; ?></strong> </p>

        <ul>
            <li><a href="../users/prof.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'prof.php' ? 'active' : ''; ?>"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>

            <li><a href="brgy_officials.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'brgy_officials.php' ? 'active' : ''; ?>"><i class="fa fa-users"></i> Barangay Officials</a></li>

            <li><a href="pending_requests.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'pending_requests.php' ? 'active' : ''; ?>">
                <i class="fa fa-clock"></i> Pending Requests <span class="badge" id="pendingBadge"></span></a></li>

            <li><a href="approved_requests.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'approved_requests.php' ? 'active' : ''; ?>">
                <i class="fa fa-check-circle"></i> Approved Requests <span class="badge" id="approvedBadge"></span></a></li>

            <li><a href="for_pickup.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'for_pickup.php' ? 'active' : ''; ?>">
                <i class="fa fa-box"></i> For Pickup <span class="badge" id="pickupBadge"></span></a></li>

            <li><a href="rejected_requests.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'rejected_requests.php' ? 'active' : ''; ?>">
                <i class="fa fa-times-circle"></i> Rejected Requests <span class="badge" id="rejectedBadge"></span></a></li>

            <li><a href="history.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'history.php' ? 'active' : ''; ?>"><i class="fa fa-history"></i> History</a></li>
        </ul>
    </div>
</div>

<style>

.sidebar-nav a .badge {
    position: absolute;
    top: 8px;
    right: 10px;
    background: red;
    color: white;
    padding: 3px 7px;
    border-radius: 50%;
    font-size: 12px;
    font-weight: bold;
    animation: notifBlink 1s infinite;
}

.sidebar-nav a {
    position: relative;
    display: flex;
    align-items: center;
    padding: 20px;
    text-decoration: none;
    color: #ecf0f1;
    font-size: 18px;
    transition: background-color 0.3s ease;
}

</style>