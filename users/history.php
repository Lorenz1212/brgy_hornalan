<?php
session_start();
include '../connection/connect.php';

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='user.php';</script>";
    exit();
}

$user = $_SESSION['user']; // Username ng naka-login

$query = "
    SELECT id, type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, claimed_date, cooldown_until
    FROM history 
    WHERE username = ? 
    ORDER BY claimed_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request History</title>
    <link rel="stylesheet" type="text/css" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <?php include 'sidebar.php'; ?>

    <style>
/* ✅ General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* ✅ Content Wrapper */
.content-wrapper {
    margin-left: 250px;
    padding: 90px 40px 30px;
    display: flex;
    flex-direction: column;
    transition: margin-left 0.3s ease-in-out;
}

/* ✅ Header Fix */
.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px 20px;
    border: 2px solid #1abc9c;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    margin-bottom: 20px;
}

/* ✅ Header Title */
.header-container h3 {
    color: #2c3e50;
    font-size: 20px;
    font-weight: bold;
    text-transform: uppercase;
    margin: 0;
}

/* ✅ Table Wrapper */
.table-wrapper {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
}

/* ✅ Table */
.custom-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px; /* ✅ Hindi lumiit ang font */
}

/* ✅ Table Header */
.custom-table th {
    background: #34495e;
    color: white;
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
    white-space: nowrap;
}

/* ✅ Table Data */
.custom-table td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
    word-wrap: break-word;
}

/* ✅ DataTables Wrapper */
.dataTables_wrapper {
    display: flex;
    flex-direction: column;
    align-items: stretch;
}

/* ✅ Pagination at Table Info - Magkabilang Dulo */
.dataTables_wrapper .dataTables_info {
    float: left;
    font-size: 14px;
    margin-top: 10px;
}

.dataTables_wrapper .dataTables_paginate {
    float: right;
    margin-top: 10px;
}

.dataTables_wrapper:after {
    content: "";
    display: table;
    clear: both;
}

/* ✅ Pagination Buttons */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 6px 12px;
    margin: 2px;
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

/* ✅ Responsive Fixes */
@media screen and (max-width: 1024px) {
    .content-wrapper {
        margin-left: 0;
        padding: 60px 20px;
    }

    .table-wrapper {
        padding: 15px;
    }

    .dataTables_wrapper {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        float: none;
        display: block;
        text-align: center;
        margin-bottom: 10px;
    }
}

@media screen and (max-width: 768px) {
    .content-wrapper {
        padding: 50px 15px;
    }

    .header-container {
        margin-top: 40px;
    }

    .header-container h3 {
        font-size: 18px;
    }

    .custom-table {
        font-size: 15px;
    }

    .custom-table th, .custom-table td {
        padding: 10px;
        font-size: 14px;
    }
}

@media screen and (max-width: 480px) {
    .content-wrapper {
        padding: 40px 10px;
    }

    .custom-table th, .custom-table td {
        padding: 8px;
        font-size: 13px;
    }
}

    </style>
</head>
<body>
<div class="content-wrapper">
    <div class="header-container">
        <h3>Request History</h3>
    </div>

    <div class="table-wrapper">
        <table class="custom-table" id="historyTable">
            <thead>
                <tr>
                    <th>Document Type</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Birthday</th>
                    <th>Years Stayed</th>
                    <th>Purpose</th>
                    <th>Date Requested</th>
                    <th>Amount</th>
                    <th>Contact</th>
                    <th>Claimed Date</th>
                    <th>Available On</th> <!-- ✅ Bagong column -->
                    <th>Time Left</th> <!-- Para sa live countdown -->

                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo date("F d, Y", strtotime($row['birthday'])); ?></td>
                        <td><?php echo htmlspecialchars($row['year_stay_in_brgy']); ?></td>
                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                        <td><?php echo date("F d, Y", strtotime($row['date'])); ?></td>
                        <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo date("F d, Y", strtotime($row['claimed_date'])); ?></td>
                        
                       

                        <td>
                            <?php 
                                if ($row['cooldown_until']) { 
                                    echo date("F d, Y", strtotime($row['cooldown_until'])); 
                                } else { 
                                    echo "Available"; 
                                }
                            ?>
                        </td>
                        <td class="countdown" data-time="<?php echo $row['cooldown_until']; ?>"></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#historyTable').DataTable({
        "paging": true,
        "pageLength": 10,
        "lengthChange": false,
        "ordering": true,
        "info": true,
        "searching": true,
        "dom": "tpi"
    });

});

function updateCountdowns() {
    document.querySelectorAll(".countdown").forEach(function (element) {
        let cooldownTime = new Date(element.getAttribute("data-time")).getTime();
        let now = new Date().getTime();
        let timeLeft = cooldownTime - now;

        if (timeLeft > 0) {
            let days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            let hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
            
            element.innerHTML = `<span style="color: red; font-weight: bold;">${days}d ${hours}h ${minutes}m ${seconds}s</span>`;
        } else {
            element.innerHTML = "<span style='color: green; font-weight: bold;'>You can now request a new document!</span>";
        }
    });
}


// ✅ I-update ang countdown kada 1 segundo
setInterval(updateCountdowns, 1000);
updateCountdowns(); // Tumawag agad para hindi maghintay ng 1s bago lumitaw

// Inactivity timer - logout after 5 minutes of inactivity (Hindi Binago)
let timeout;
        const logoutTime = 5 * 60 * 1000;

        function resetTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(logoutUser, logoutTime);
        }

        function logoutUser() {
            window.location.href = "logout.php"; 
        }

        window.onload = resetTimer; 
        document.onmousemove = resetTimer; 
        document.onkeypress = resetTimer; 
</script>

</body>
</html>
