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

$query = "SELECT * FROM certificate_approved where status = 'approved' ORDER BY YEAR(date) ASC, MONTH(date) ASC, DAY(date) ASC";// Pag kakasunod sunod ng araw
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Clearances</title>
    <link rel="stylesheet" type="text/css" href="print.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'sidebar.php'; ?>
</head>
<body>
    <div class="content-wrapper">
        <div class="header-container">
            <h3>Approved Certificate Requests</h3>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search for names...">
                <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button>
                <span id="clearBtn" class="clear-btn" onclick="clearSearch()">&#10006;</span>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="custom-table" id="printedTable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Birthday</th>
                        <th>Years Stayed</th>
                        <th>Purpose</th>
                        <th>Scheduled Date</th>
                        <th>Payment</th>
                        <th>Username</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['type']; ?></td>
                            <td><?php echo ucwords(strtolower(htmlspecialchars($row['name']))); ?></td>
                            <td><?php echo ucwords(strtolower(htmlspecialchars($row['address']))); ?></td>
                            <td><?php echo date("F d, Y", strtotime($row['birthday'])); ?></td>
                            <td><?php echo $row['year_stay_in_brgy']; ?></td>
                            <td><?php echo $row['purpose']; ?></td>
                            <td><?php echo date("F d, Y", strtotime($row['date'])); ?></td><!-- Para maging format "March 20, 2025" -->
                            <td>₱<?php echo $row['amount']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['contact']; ?></td>
                            <td>
                                <div class="btn-container">
                                    <button class="btn print-btn" onclick="openPrintModal('<?php echo $row['name']; ?>', '<?php echo $row['address']; ?>', '<?php echo $row['birthday']; ?>', '<?php echo $row['year_stay_in_brgy']; ?>', '<?php echo $row['purpose']; ?>', '<?php echo $row['type']; ?>', '<?php echo $row['id']; ?>')">🖨 Print</button>
                                    <a href="javascript:void(0);" class="btn trash-btn" onclick="confirmTrash('<?php echo $row['id']; ?>')">🗑 Trash</a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script For Search, Clear Button -->
    <script>
    $(document).ready(function() {
        window.table = $('#printedTable').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "info": true,
            "searching": true,
            "dom": "tpi"
        });

        $('#searchBtn').on('click', function() {
            let searchValue = $('#searchInput').val();
            table.search(searchValue).draw();
        });

        $('#clearBtn').on('click', function() {
            $('#searchInput').val("");
            table.search("").draw();
            $('#clearBtn').hide();
        });

        $('#searchInput').on('input', function() {
            $('#clearBtn').toggle($(this).val().length > 0);
        });
    });
    </script>
    <!-- Modal for Print -->
    <div id="printModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePrintModal()">&times;</span>
            <div class="modal-header">
                <img src="../image/logo1-removebg.png" alt="Logo" class="logo">
                <div class="head">
                    <p>Republic of the Philippines</p>
                    <p>Province of Laguna</p>
                    <p>Municipality of Calamba</p>
                    <h3>Barangay Hornalan</h3>
                </div>
                <img src="../image/logo.png" alt="Logo" class="logo">
            </div>
            <div class="underline"></div>
            <div class="contentbrgy">
                <h1 id="modalTitle">CLEARANCE</h1>
            </div>
            <div class="contents">
                <h2>To Whom It May Concern:</h2>
                <p>This is to certify that: <span id="details"></span>
                    is a resident of <span id="residentAddress"></span>, born on <span id="residentBirthday"></span>, and has been staying in Barangay Hornalan for <span id="yearsStayed"></span> years. This certification is issued for the purpose of <span id="certificatePurpose"></span>.
                    Issued this <span id="Date"></span> at Barangay Hornalan, Calamba Laguna.</p>
                <div class="punongbrgy">
                    <p>Hon. June Oña</p>
                    <p>Punong Barangay</p>
                </div>
            </div>
            <button id="printBtn" onclick="printCertificate()">Print</button>
        </div>
    </div>

    <script>
        function openPrintModal(name, address, birthday, years, purpose, type, id) {
    // ✅ Convert birthday to "Month Day, Year" format
    let formattedBirthday = new Date(birthday).toLocaleDateString('en-US', {
        month: 'long', day: 'numeric', year: 'numeric'
    });

    // ✅ Update modal content
    document.getElementById("modalTitle").innerText = type;
    document.getElementById("details").innerText = name;
    document.getElementById("residentAddress").innerText = address;
    document.getElementById("residentBirthday").innerText = formattedBirthday; // ✅ Updated
    document.getElementById("yearsStayed").innerText = years;
    document.getElementById("clearancePurpose").innerText = purpose;
    document.getElementById("clearanceDate").innerText = new Date().toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    // ✅ Update print button action
    let printButton = document.querySelector("#printModal button");
    printButton.setAttribute("onclick", "printCertificate(" + id + ")");

    document.getElementById("printModal").style.display = "block";
}


        function printCertificate(id) {
    if (!id) {
        console.error("Walang ID na ipinasa!");
        return;
    }

    // Trigger the print dialog
    window.print();

    // Use the onafterprint event to check if the user has finished printing
    window.onafterprint = function () {
        // Wait for 3 seconds after the print dialog closes before sending the update
        setTimeout(function () {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "printed.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log("✅ Natapos na ang pag-print. In-update na ang record.");
                    window.location.href = "printed.php"; // Redirect after updating the record
                }
            };
            xhr.send("id=" + id);
        }, 3000); // Delay to ensure print dialog is fully closed
    };
}




function confirmTrash(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to move this record to trash?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, move it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                html: 'Moving to trash...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Mag-redirect sa trash.php pagkatapos ng 1.5 seconds
            setTimeout(() => {
                window.location.href = 'trash.php?id=' + id + '&type=certificate';
            }, 1500);
        }
    });
}

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

<?php mysqli_close($conn); ?>

<style>

/* ✅ Table Container */
body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .content-wrapper {
            margin-left: 250px;
            padding: 90px 40px 30px;
            display: flex;
            flex-direction: column;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 10px 20px;
            border: 2px solid #1abc9c;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .header-container h3 {
            color: #2c3e50;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .table-wrapper {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th, .custom-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .custom-table thead {
            position: sticky;
            top: 0;
            background: #34495e;
            color: white;
            height: 50px;
        }

        /* Style ng pagination buttons */
        .dataTables_wrapper .dataTables_paginate {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 8px 15px;
            margin: 0 5px;
            background: #16a085;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #1abc9c;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #34495e;
            pointer-events: none;
        }
        /* Itago ang unang column (ID) */
        #residentTable th:nth-child(1),
        #residentTable td:nth-child(1) {
            display: none;
        }

        /* Ayusin ang position ng 'Showing X to X of X entries' */
        .dataTables_info {
            position: absolute;
            bottom: 15px; /* Itaas ang text */
            left: 20px; /* Ilayo ng konti sa gilid */
            font-size: 18px; /* Mas maliit na font para malinis tignan */
        }

/* ✅ Action Button Container */
.btn-container {
    display: flex;
    justify-content: center;
    gap: 5px;
}

/* ✅ Button Styles */
.btn {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    text-align: center;
    white-space: nowrap;
    border: none;
}

/* ✅ Print Button */
.print-btn {
    background: #2980b9;
    color: white;
}

.print-btn:hover {
    opacity: 0.9;
    transform: scale(1.05);
}

/* ✅ Trash Button */
.trash-btn {
    background: #e74c3c;
    color: white;
}

.trash-btn:hover {
    opacity: 0.9;
    transform: scale(1.05);
}

/* Search Bar */
.search-container {
    display: flex;
    gap: 5px;
    position: relative;
}

#searchInput {
    padding: 8px;
    width: 300px;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding-right: 30px; /* Space for the clear button */
}

.search-btn {
    padding: 6px 12px;
    background: #16a085;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
}

.search-btn i {
    margin-right: 5px;
}

.search-btn:hover {
    background: #1abc9c;
}

/* Clear Button (X) */
.clear-btn {
    position: absolute;
    right: 115px; /* Align to the right side of the input field */
    top: 50%;
    transform: translateY(-50%); /* Vertically center the button */
    font-size: 18px;
    cursor: pointer;
    display: none; /* Hidden by default */
    
} 
/*Hide sidebar & NavBar */
@media print {
    .navbar, .sidebar {
        display: none;
    }
}

</style>
