<?php
include '../connection/connect.php';


$current_page = basename($_SERVER['PHP_SELF']);

// Fetch request counts
$requestCounts = [
    'Approved_Clearance' => 0, 'Approved_Certificate' => 0,
    'printed.php' => 0, 'done.php' => 0
];

// Check kung existing ang mga table bago mag-query
$approvedClearanceCheck = mysqli_query($conn, "SHOW TABLES LIKE 'clearance_approved'");
$approvedCertificateCheck = mysqli_query($conn, "SHOW TABLES LIKE 'certificate_approved'");
$printed = mysqli_query($conn, "SHOW TABLES LIKE 'printed'");
$claiming = mysqli_query($conn, "SHOW TABLES LIKE 'done'");


if (mysqli_num_rows($printed) > 0) {
    $printedQuery = "SELECT COUNT(*) as count FROM printed WHERE id";
    $printedResult = mysqli_query($conn, $printedQuery);
    $printedRow = mysqli_fetch_assoc($printedResult);
    $requestCounts['printed'] = $printedRow['count'] > 0 ? $printedRow['count'] : 0;
}

if (mysqli_num_rows($claiming) > 0) {
    $claimingQuery = "SELECT COUNT(*) as count FROM done WHERE id";
    $claimingResult = mysqli_query($conn, $claimingQuery);
    $claimingRow = mysqli_fetch_assoc($claimingResult);
    $requestCounts['done'] = $claimingRow['count'] > 0 ? $claimingRow['count'] : 0;
}

// Count approved clearance requests
$approvedClearanceQuery = "SELECT COUNT(*) as count FROM clearance_approved where status = 'approved'";
$approvedClearanceResult = mysqli_query($conn, $approvedClearanceQuery);
$approvedClearanceRow = mysqli_fetch_assoc($approvedClearanceResult);
$requestCounts['Approved_Clearance'] = $approvedClearanceRow['count'] > 0 ? $approvedClearanceRow['count'] : 0;

// Count approved certificate requests
$approvedCertificateQuery = "SELECT COUNT(*) as count FROM certificate_approved where status = 'approved'";
$approvedCertificateResult = mysqli_query($conn, $approvedCertificateQuery);
$approvedCertificateRow = mysqli_fetch_assoc($approvedCertificateResult);
$requestCounts['Approved_Certificate'] = $approvedCertificateRow['count'] > 0 ? $approvedCertificateRow['count'] : 0;

?>

<link rel="stylesheet" type="text/css" href="sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Navbar -->
<div class="navbar">
    <div class="user-info">
        <!-- Notification Bell Icon -->
        <div class="notif-container">
    <a href="#" id="requestToggle" class="notif-icon">
        <i class="fa fa-bell"></i>
        <span class="badge" style="display:none;"><?php echo $requestCounts['Total']; ?></span>
    </a>
    <div class="dropdown-content notif-dropdown" id="requestDropdown">
        <a href="clear_request.php">
            <i class="fa fa-file"></i> Clearance 
            <span class="badge" style="display:none;"><?php echo ($requestCounts['Clearance'] > 0) ? $requestCounts['Clearance'] : ''; ?></span>
        </a>
        <a href="cert_request.php">
            <i class="fa fa-file"></i> Certificate 
            <span class="badge" style="display:none;"><?php echo ($requestCounts['Certificate'] > 0) ? $requestCounts['Certificate'] : ''; ?></span>
        </a>
    </div>
</div>

        </div>

        <!-- User Profile Dropdown -->
        <button class="dropbtn" id="userProfileBtn">
            <i class="fas fa-user"></i>
            <i class="fas fa-caret-down"></i> SuperAdmin
        </button>
        <div class="dropdown-content" id="userDropdown">
            <a href="#" id="logoutBtn">Logout</a>
        </div>

    </div>
</div>



<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
    <img src="../image/logo1-removebg.png" alt="Barangay Logo" class="logo">
    </div>
    <div class="sidebar-nav">
        <p>Hello! SuperAdmin</p>
        <ul>
        <li><a href="superadmin_dashboard.php" class="nav-link <?php echo ($current_page == 'superadmin_dashboard.php') ? 'active' : ''; ?>">
            <i class="fa fa-tachometer-alt"></i> Dashboard
        </a></li>
        <li><a href="brgy_officials.php" class="nav-link <?php echo ($current_page == 'brgy_officials.php') ? 'active' : ''; ?>">
            <i class="fa fa-users"></i> Barangay Officials
        </a></li>
        <li><a href="resident_list.php" class="nav-link <?php echo ($current_page == 'resident_list.php') ? 'active' : ''; ?>">
            <i class="fa fa-list"></i> Resident List</a></li>
        <li><a href="clearance.php" class="nav-link <?php echo ($current_page == 'clearance.php') ? 'active' : ''; ?>">
            <i class="fa fa-file"></i> Clearance
            <?php if ($requestCounts['Approved_Clearance'] > 0): ?>
                <span class="badge"><?php echo $requestCounts['Approved_Clearance']; ?></span>
            <?php endif; ?>
        </a></li>
        <li><a href="certificate.php" class="nav-link <?php echo ($current_page == 'certificate.php') ? 'active' : ''; ?>">
            <i class="fa fa-file"></i> Certificate
            <?php if ($requestCounts['Approved_Certificate'] > 0): ?>
                <span class="badge"><?php echo $requestCounts['Approved_Certificate']; ?></span>
            <?php endif; ?>
        </a></li>
        <li><a href="printed.php" class="nav-link <?php echo ($current_page == 'printed.php') ? 'active' : ''; ?>">
            <i class="fa fa-file"></i> Printed
            <?php if ($requestCounts['printed'] > 0): ?>
                <span class="badge"><?php echo $requestCounts['printed']; ?></span>
            <?php endif; ?>
        </a></li>
        <li><a href="done.php" class="nav-link <?php echo ($current_page == 'done.php') ? 'active' : ''; ?>">
            <i class="fa fa-file"></i> Claiming
            <?php if ($requestCounts['done'] > 0): ?>
                <span class="badge"><?php echo $requestCounts['done']; ?></span>
            <?php endif; ?>
        </a></li>
        <li><a href="history.php" class="nav-link <?php echo ($current_page == 'history.php') ? 'active' : ''; ?>">
            <i class="fa fa-history"></i> History</a></li>
        <li><a href="recycle.php" class="nav-link <?php echo ($current_page == 'recycle.php') ? 'active' : ''; ?>">
    <i class="fa fa-trash"></i> Trash</a></li>

        </ul>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    // Notification Toggle
    const notifToggle = document.querySelector("#requestToggle");
    const notifDropdown = document.querySelector("#requestDropdown");

    if (notifToggle) {
        notifToggle.addEventListener("click", function (event) {
            event.preventDefault();
            notifDropdown.classList.toggle("show");
        });
    }

    // User Dropdown Toggle
    const userProfileBtn = document.querySelector("#userProfileBtn");
    const userDropdown = document.querySelector("#userDropdown");

    if (userProfileBtn) {
        userProfileBtn.addEventListener("click", function () {
            userDropdown.classList.toggle("show");
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener("click", function (event) {
        if (!notifToggle.contains(event.target) && !notifDropdown.contains(event.target)) {
            notifDropdown.classList.remove("show");
        }
        if (!userProfileBtn.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove("show");
        }
    });
    });
function updateBadges() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_badges.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var data = JSON.parse(xhr.responseText);
            
            // Update Total Badge
            var totalBadge = document.querySelector(".notif-icon .badge");
            if (totalBadge) {
                if (data.Total > 0) {
                    totalBadge.textContent = data.Total;
                    totalBadge.style.display = "inline-block";
                } else {
                    totalBadge.style.display = "none";
                }
            }

            // Update Clearance Badge
            var clearanceBadge = document.querySelector("#requestDropdown a[href='clear_request.php'] .badge");
            if (clearanceBadge) {
                if (data.Clearance > 0) {
                    clearanceBadge.textContent = data.Clearance;
                    clearanceBadge.style.display = "inline-block";
                } else {
                    clearanceBadge.style.display = "none";
                }
            }

            // Update Certificate Badge
            var certBadge = document.querySelector("#requestDropdown a[href='cert_request.php'] .badge");
            if (certBadge) {
                if (data.Certificate > 0) {
                    certBadge.textContent = data.Certificate;
                    certBadge.style.display = "inline-block";
                } else {
                    certBadge.style.display = "none";
                }
            }
        }
    };
    xhr.send();
}

// Tawagin ang function kada 5 segundo (5000 milliseconds)
setInterval(updateBadges, 5000);

// Tawagin agad ang function pag-load ng page
updateBadges();

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


</script>

<style>
.sidebar-nav a {
    position: relative;
    padding: 20px;
    display: block;
    color: white;
    text-decoration: none;
    transition: background 0.3s;
}

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

.sidebar-nav .nav-link.active {
    background-color: #1abc9c;
}

</style>
