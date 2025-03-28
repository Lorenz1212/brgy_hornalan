<?php
session_start();
include '../connection/connect.php'; // Database Connection


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

$success = false;
$message = "";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Kunin ang data mula sa done table
    $query = "SELECT * FROM done WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userEmail = $row['username']; // Email mula sa database
        $userName = $row['name']; // Pangalan mula sa database

        // Ipasok sa history table kasama ang claimed_date
        $historyQuery = "INSERT INTO history (type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, claimed_date) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $historyStmt = $conn->prepare($historyQuery);
        $historyStmt->bind_param("ssssissdss", 
            $row['type'], $row['name'], $row['address'], $row['birthday'], 
            $row['year_stay_in_brgy'], $row['purpose'], $row['date'], $row['amount'], $row['username'], $row['contact']
        );

        if ($historyStmt->execute()) {
            // Burahin mula sa done table
            $deleteQuery = "DELETE FROM done WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();

            // Magpadala ng email notification sa user
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cabugwaschritianjames01156@gmail.com'; // Palitan ng email mo
                $mail->Password   = 'lndb zwhp jzfo bbqi'; // Gumamit ng App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your-email@gmail.com', 'Barangay Hornalan');
                $mail->addAddress($userEmail); // Gamitin ang email mula sa database

                $mail->isHTML(true);
                $mail->Subject = "Your Document has been Claimed";
                $mail->Body    = "
                    <h3>Hi Mr./Mrs. $userName,</h3>
                    <p>This is to confirm that your requested document has been successfully claimed.</p>
                    <p>If you did not claim this document, please contact our office immediately.</p>
                    <br>
                    <p>Best regards,</p>
                    <p><strong>Barangay Hornalan</strong></p>
                ";

                $mail->send();
                $success = true;
                $message = "Successfully moved to History! Email notification sent.";
            } catch (Exception $e) {
                $success = true;
                $message = "Successfully moved to History! But email failed: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Error: " . $conn->error;
        }
    } else {
        $message = "Record not found!";
    }

    $stmt->close();
    $historyStmt->close();
    $deleteStmt->close();
}

// Kunin ang lahat ng history records
$query = "SELECT * FROM history ORDER BY YEAR(date) ASC, MONTH(date) ASC, DAY(date) ASC";
$result = mysqli_query($conn, $query);
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

    <?php if (!empty($message)) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                title: "<?php echo $success ? 'Success!' : 'Error!'; ?>",
                text: "<?php echo $message; ?>",
                icon: "<?php echo $success ? 'success' : 'error'; ?>",
                confirmButtonText: "OK"
            }).then(() => {
                window.location.href = "done.php";
            });
        });
    </script>
<?php endif; ?>



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
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="header-container">
            <h3>Clearance History</h3>
            <div class="search-container">
    <input type="text" id="searchInput" placeholder="Search for names..." oninput="toggleClearButton()">
    <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button> <!-- ✅ May ID na -->
    <span id="clearBtn" class="clear-btn" onclick="clearSearch()" style="display:none;">&#10006;</span> <!-- Itago muna -->
</div>
        </div>

        <div class="table-wrapper">
            <table class="custom-table" id="historyTable">
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
        <th>Claimed Date</th>
    </tr>
</thead>
<tbody>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
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
            <td><?php echo date("F d, Y", strtotime($row['claimed_date'])); ?></td>
        </tr>
    <?php } ?>
</tbody>

            </table>
        </div>
    </div>
    <script>
  $(document).ready(function() {
    // Initialize DataTable without the default search box
    window.table = $('#historyTable').DataTable({
        "paging": true,
        "pageLength": 10,
        "lengthChange": false, // ❌ Hide "Show Entries" dropdown
        "ordering": true,
        "info": true,
        "searching": true, // ✅ Enable DataTables search
        "dom": "tpi" // ❌ Hide default search box
    });

    // Search button functionality
    $('#searchBtn').on('click', function() {
        let searchValue = $('#searchInput').val();
        table.search(searchValue).draw(); // ✅ Gagana lang kapag pinindot
    });

    // Clear button functionality
    $('#clearBtn').on('click', function() {
        $('#searchInput').val(""); // I-clear ang input
        table.search("").draw(); // I-reset ang search
        $('#clearBtn').hide(); // Itago ulit ang clear button
    });

    // Toggle "X" button kapag may laman ang search
    $('#searchInput').on('input', function() {
        $('#clearBtn').toggle($(this).val().length > 0);
    });
});

</script>
</body>
</html>

<?php mysqli_close($conn); ?>
