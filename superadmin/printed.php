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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $table = "";

    // Hanapin sa clearance_approved
    $query = "SELECT * FROM clearance_approved WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $table = "clearance_approved";
    } else {
        // Kung wala sa clearance_approved, hanapin sa certificate_approved
        $query = "SELECT * FROM certificate_approved WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $table = "certificate_approved";
        }
    }

    if ($row) {
        $userEmail = $row['username']; // Email ng user
        $userName = $row['name']; // Pangalan ng user

        // Ipasok sa printed table
        $insertQuery = "INSERT INTO printed (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssssissdss", 
            $row['type'], $row['name'], $row['address'], $row['birthday'], 
            $row['year_stay_in_brgy'], $row['purpose'], $row['date'], $row['amount'], $row['username'], $row['contact']
        );

        if ($insertStmt->execute()) {
            // Burahin mula sa clearance_approved o certificate_approved
            $deleteQuery = "DELETE FROM $table WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();

            // Magpadala ng email notification
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cabugwaschritianjames01156@gmail.com'; // Palitan ng tamang email
                $mail->Password   = 'lndb zwhp jzfo bbqi'; // Gumamit ng App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your-email@gmail.com', 'Barangay Hornalan');
                $mail->addAddress($userEmail);

                $mail->isHTML(true);
                $mail->Subject = "Your Document has been Printed";
                $mail->Body    = "
                    <h3>Hi Mr./Mrs. {$userName},</h3>
                    <p>Your document has been successfully printed.</p>
                    <p>Please wait for further instructions on when you can claim your document.</p>
                    <p>If you have any questions, feel free to contact the barangay office.</p>
                    <br>
                    <p>Best regards,</p>
                    <p><strong>Barangay Hornalan</strong></p>
                ";

                $mail->send();
                echo "Success"; // Ibalik sa frontend na successful ang process
            } catch (Exception $e) {
                echo "Email Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Error: No record found.";
    }

    $stmt->close();
    $insertStmt->close();
    $deleteStmt->close();
    $conn->close();
}
?>

<?php
// Kunin ang lahat ng printed records
$query = "SELECT * FROM printed ORDER BY YEAR(date) ASC, MONTH(date) ASC, DAY(date) ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printed Clearances</title>
    <link rel="stylesheet" href="print.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'sidebar.php'; ?>
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


/* ✅ Ayusin ang 'Action' Column */
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

/* ✅ Done Button */
.btn-done {
    background: #27ae60;
    color: white;
}

.btn-done:hover {
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

/* ✅ Ayusin ang 'Action' Column */
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

/* ✅ Done Button */
.btn-done {
    background: #27ae60;
    color: white;
}

.btn-done:hover {
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
            <h3>Printed Clearance Records</h3>
            <div class="search-container">
    <input type="text" id="searchInput" placeholder="Search for names..." oninput="toggleClearButton()">
    <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button> <!-- ✅ May ID na -->
    <span id="clearBtn" class="clear-btn" onclick="clearSearch()" style="display:none;">&#10006;</span> <!-- Itago muna -->
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
                        <th>Email</th>
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
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>

                            <td>    <!-- ✅ Action Buttons sa loob ng isang <td> -->
                                <div class="btn-container">
                                <a href="javascript:void(0);" onclick="approveRequest('<?php echo $row['id']; ?>')"class="btn btn-done">✔ Done</a>
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
                    window.location.href = `trash.php?id=${id}&type=printed&status_reason=${encodeURIComponent(statusReason)}`;

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
    window.location.href = 'done.php?id=' + id;
}
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
