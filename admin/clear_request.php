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

$query = "SELECT * FROM clearance_request WHERE status='Pending' ORDER BY time ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Clearance Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php include 'sidebar.php'; ?>
</head>
<body>
    <div class="content-wrapper">
        <!-- Header -->
        <div class="header-container">
            <h3>Pending Clearance Requests</h3>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search for names...">
                <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button>
                <span id="clearBtn" class="clear-btn" onclick="clearSearch()">&#10006;</span>
            </div>
        </div>

        <!-- Table -->
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
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['address']; ?></td>
                            <td><?php echo date("F d, Y", strtotime($row['birthday'])); ?></td>
                            <td><?php echo $row['year_stay_in_brgy']; ?></td>
                            <td><?php echo $row['purpose']; ?></td>
                            <td><?php echo date("F d, Y", strtotime($row['date'])); ?></td><!-- Para maging format "March 20, 2025" -->
                            <td>₱<?php echo $row['amount']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['contact']; ?></td>

                            <td >
                            <div class="btn-container">
                            <a href="javascript:void(0);" onclick="approveRequest('<?php echo $row['id']; ?>')" class="btn approve-btn">✔ Approve</a>
                            
                            <a href="javascript:void(0);" 
                                    onclick="confirmDeny('<?php echo $row['id']; ?>', 
                                            '<?php echo $row['name']; ?>',
                                            '<?php echo $row['address']; ?>',
                                            '<?php echo $row['birthday']; ?>',
                                            '<?php echo $row['year_stay_in_brgy']; ?>',
                                            '<?php echo $row['contact']; ?>')"
                                    class="btn deny-btn">✖ Deny
                                </a>
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

    function confirmDeny(id, name, address, birthday, yearsStayed, contact) {
    let invalidFields = []; // Store invalid fields as messages
    let statusReason = ""; // Store the status reason message

    Swal.fire({
        title: 'Select Incorrect Fields',
        html: `
            <p><strong>Click the incorrect fields:</strong></p>
            <div id="fieldContainer">
                <p class="selectable-field" data-key="name">Name: <span>${name}</span></p>
                <p class="selectable-field" data-key="address">Address: <span>${address}</span></p>
                <p class="selectable-field" data-key="birthday">Birthday: <span>${birthday}</span></p>
                <p class="selectable-field" data-key="yearsStayed">Years Stayed: <span>${yearsStayed}</span></p>
                <p class="selectable-field" data-key="contact">Contact: <span>${contact}</span></p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirm Denial',
        cancelButtonText: 'Cancel',
        didOpen: () => {
            document.querySelectorAll('.selectable-field').forEach(field => {
                field.addEventListener('click', function () {
                    let key = this.getAttribute('data-key');
                    if (invalidFields.includes(key)) {
                        invalidFields = invalidFields.filter(f => f !== key); // Remove from invalid fields
                        this.style.color = ''; // Revert color
                    } else {
                        invalidFields.push(key); // Mark as invalid
                        this.style.color = 'red'; // Highlight in red
                    }
                });
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (invalidFields.length === 0) {
                Swal.fire('Error', 'Please select at least one incorrect field.', 'error');
                return;
            }

            // Prepare status reason message
            statusReason = invalidFields.map(field => {
                switch (field) {
                    case 'name': return 'Name is incorrect.';
                    case 'address': return 'Address is incorrect.';
                    case 'birthday': return 'Birthday is incorrect.';
                    case 'yearsStayed': return 'Years Stayed is incorrect.';
                    case 'contact': return 'Contact is incorrect.';
                    default: return '';
                }
            }).join(' ');

            // Confirm the action before proceeding
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to deny this request?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, deny it!',
                cancelButtonText: 'No, cancel',
            }).then((confirmation) => {
                if (confirmation.isConfirmed) {
                    // Redirect with status_reason to proceed with rejection
                    window.location.href = 'deny_request.php?id=' + id + '&status_reason=' + encodeURIComponent(statusReason);
                }
            });
        }
    });
}


    // Function to show loading spinner
function showLoadingSpinner() {
    Swal.fire({
        title: 'Please wait...',
        html: 'Sending email...',
        allowOutsideClick: false, // Disable clicking outside to close the spinner
        didOpen: () => {
            Swal.showLoading() // Show the loading spinner
        }
    });
}

function approveRequest(id) {
    // Show loading spinner before email processing
    showLoadingSpinner();

    // Trigger PHP processing for email sending
    window.location.href = 'approved_clear.php?id=' + id;
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


<!-- CSS -->
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
        /*Table Style */
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

/* Approve Button */
.approve-btn {
    background: #27ae60;
    color: white;
}

.approve-btn:hover {
    opacity: 0.9;
    transform: scale(1.05);
}

/* Deny Button */
.deny-btn {
    background: #c0392b;
    color: white;
}

.deny-btn:hover {
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

/* Custom Styles for SweetAlert2 Popup */
.swal-container {
    font-family: 'Arial', sans-serif;
    color: #333;
}

.swal-popup {
    border-radius: 10px;
    padding: 20px;
    background: #f9f9f9;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    width: 400px;
}

.swal-title {
    font-size: 20px;
    font-weight: bold;
    color: #34495e;
    text-align: center;
    margin-bottom: 15px;
}

.swal-content {
    font-size: 16px;
    color: #555;
}

.selectable-field {
    font-size: 14px;
    margin: 10px 0;
    padding: 8px;
    border-radius: 5px;
    cursor: pointer;
    background-color: #eaf1f1;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.selectable-field:hover {
    background-color: #d5e6e6;
}

.swal-confirm-btn {
    background-color: #27ae60;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    text-transform: uppercase;
}

.swal-confirm-btn:hover {
    background-color: #2ecc71;
}

.swal-cancel-btn {
    background-color: #e74c3c;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    text-transform: uppercase;
}

.swal-cancel-btn:hover {
    background-color: #c0392b;
}

/* Highlight incorrect fields in red */
.selectable-field.red {
    color: red;
    font-weight: bold;
}

/* Adjust the input text in the popup */
.selectable-field span {
    font-weight: normal;
    color: #555;
}

</style>
