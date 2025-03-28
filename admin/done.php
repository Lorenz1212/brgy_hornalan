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


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Iwasan ang pagsasara ng connection bago matapos ang queries
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Kunin ang data mula sa printed table
    $query = "SELECT * FROM printed WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userEmail = $row['username'];
        $userName = $row['name'];

        // Ipasok sa done table na may status na "For Pickup"
            $insertQuery = "INSERT INTO done (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insertStmt = $conn->prepare($insertQuery);
            $status = "For Pickup"; // ✅ Idagdag ang status

            $insertStmt->bind_param("ssssissdsss", 
            $row['type'], $row['name'], $row['address'], $row['birthday'], 
            $row['year_stay_in_brgy'], $row['purpose'], $row['date'], $row['amount'], 
            $row['username'], $row['contact'], $status // ✅ Isama ang status
            );


        if ($insertStmt->execute()) {
            // Burahin mula sa printed table
            $deleteQuery = "DELETE FROM printed WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();

            // Magpadala ng email notification sa user
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cabugwaschritianjames01156@gmail.com';
                $mail->Password   = 'lndb zwhp jzfo bbqi';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your-email@gmail.com', 'Barangay Hornalan');
                $mail->addAddress($userEmail);

                $mail->isHTML(true);
                $mail->Subject = "Your Document is Ready for Pickup";
                $mail->Body    = "
                    <h3>Hi Mr./Mrs. $userName,</h3>
                    <p>Your document is now completed and ready for pickup.</p>
                    <p>You can claim it today until 5:00 PM or on the scheduled day you selected.</p>
                    <p>Kindly bring a valid ID for verification. Thank you!</p>
                    <br>
                    <p>Best regards,</p>
                    <p><strong>Barangay Hornalan</strong></p>
                ";

                $mail->send();
                $success = true;
                $message = "Successfully moved to Done! Email notification sent.";
            } catch (Exception $e) {
                $success = true;
                $message = "Successfully moved to Done! But email failed: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Error: " . $conn->error;
        }
    } else {
        $message = "Record not found!";
    }

    $stmt->close();
    $insertStmt->close();
    $deleteStmt->close();
}

// **Huwag isara ang connection bago gamitin ulit**
// Kunin ang lahat ng "done" records
$query = "SELECT * FROM done ORDER BY YEAR(date) ASC, MONTH(date) ASC, DAY(date) ASC";
$result = mysqli_query($conn, $query);

// **Isara lang ang connection sa dulo ng script**
mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Clearances</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'sidebar.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                title: "<?php echo $success ? 'Success!' : 'Error!'; ?>",
                text: "<?php echo $message; ?>",
                icon: "<?php echo $success ? 'success' : 'error'; ?>",
                confirmButtonText: "OK"
            }).then(() => {
                window.location.href = "printed.php";
            });
        });

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
    <style>
        

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
            padding-right: 30px; /* Add space for the 'X' button */
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

        .clear-btn {
            position: absolute;
            right: 115px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            cursor: pointer;
            display: none;
        }



        /* Buttons */
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .btn {
            font-size: 14px;
            padding: 6px 10px;
            background: #16a085;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-claimed {
            background: #27ae60;
        }

        .btn-claimed:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

        .trash-btn {
            background: #e74c3c;
        }

        .trash-btn:hover {
            opacity: 0.9;
            transform: scale(1.05);
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
</head>
<body>
    <div class="content-wrapper">
        <div class="header-container">
            <h3>Completed Clearance Records</h3>
            <div class="search-container">
    <input type="text" id="searchInput" placeholder="Search for names..." oninput="toggleClearButton()">
    <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button> <!-- ✅ May ID na -->
    <span id="clearBtn" class="clear-btn" onclick="clearSearch()" style="display:none;">&#10006;</span> <!-- Itago muna -->
</div>
        </div>

        <div class="table-wrapper">
            <table class="custom-table" id="doneTable">
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
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr id="row-<?php echo $row['id']; ?>">
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo date("F d, Y", strtotime($row['birthday'])); ?></td>
                            <td><?php echo htmlspecialchars($row['year_stay_in_brgy']); ?></td>
                            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td><?php echo date("F d, Y", strtotime($row['date'])); ?></td><!-- Para maging format "March 20, 2025" -->
                            <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td class="btn-container">
                            <a href="javascript:void(0);" onclick="approveRequest('<?php echo $row['id']; ?>')"class="btn btn-claimed">✔ Claimed</a>
                            <a href="javascript:void(0);" 
                                    class="btn trash-btn" 
                                    onclick="confirmTrash(
                                        '<?php echo $row['id']; ?>', 
                                        '<?php echo addslashes($row['name']); ?>', 
                                        '<?php echo addslashes($row['address']); ?>', 
                                        '<?php echo $row['birthday']; ?>', 
                                        '<?php echo $row['year_stay_in_brgy']; ?>', 
                                        '<?php echo addslashes($row['contact']); ?>'
                                    )">
                                    🗑 Trash
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<script>
function confirmTrash(id, name, address, birthday, yearsStayed, contact) {
    let invalidFields = []; // Store incorrect fields
    let statusReason = ""; // Store reason for trash

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
        confirmButtonText: 'Confirm Move to Trash',
        cancelButtonText: 'Cancel',
        didOpen: () => {
            document.querySelectorAll('.selectable-field').forEach(field => {
                field.addEventListener('click', function () {
                    let key = this.getAttribute('data-key');
                    if (invalidFields.includes(key)) {
                        invalidFields = invalidFields.filter(f => f !== key);
                        this.style.color = ''; // Revert color
                    } else {
                        invalidFields.push(key);
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

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to move this record to trash?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, move it!',
                cancelButtonText: 'Cancel'
            }).then((confirmation) => {
                if (confirmation.isConfirmed) {
                    window.location.href = `trash.php?id=${id}&type=done&status_reason=${encodeURIComponent(statusReason)}`;

                }
            });
        }
    });
}

    //Function to show loading spinner
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
    window.location.href = 'history.php?id=' + id;
}


</script>

<script>
    
$(document).ready(function() {
    // ✅ Siguraduhin na tamang ID ang ginagamit
    window.table = $('#doneTable').DataTable({
        "paging": true,
        "pageLength": 10,
        "lengthChange": false,  // ❌ Hide "Show Entries"
        "ordering": true,
        "info": true,
        "searching": true,  // ✅ Enable search functionality
        "dom": "tpi"  // ❌ Hide default DataTables search box
    });

    // ✅ Custom Search Button
    $('#searchBtn').on('click', function() {
        let searchValue = $('#searchInput').val();
        table.search(searchValue).draw();  // ✅ Apply custom search
    });

    // ✅ Clear Search Button
    $('#clearBtn').on('click', function() {
        $('#searchInput').val("");  // I-clear ang input
        table.search("").draw();  // I-reset ang search
        $('#clearBtn').hide();  // Itago ang "X" button
    });

    // ✅ Show/Hide Clear Button (X) kapag may laman ang search
    $('#searchInput').on('input', function() {
        $('#clearBtn').toggle($(this).val().length > 0);
    });
});

</script>

<?php mysqli_close($conn); ?>
