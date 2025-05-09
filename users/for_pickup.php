<?php
session_start();
include '../connection/connect.php';

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='user.php';</script>";
    exit();
}

$user = $_SESSION['user']; // Username ng naka-login

$query = "
    SELECT id, type, name, address, birthday, year_stay_in_brgy, purpose, date, amount, username, contact, status
    FROM done 
    WHERE username = ?
    ORDER BY date DESC
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
    <title>For Pickup Requests</title>
    <link rel="stylesheet" type="text/css" href="css/sidebar.css">
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
        <h3>For Pickup Requests</h3>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search for names..." oninput="toggleClearButton()">
            <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button>
            <span id="clearBtn" class="clear-btn" onclick="clearSearch()" style="display:none;">&#10006;</span>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="custom-table" id="pickupRequestsTable">
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
                    <th>Status</th>
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
                        <td style="color: #f39c12; font-weight: bold;"><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    let table = $('#pickupRequestsTable').DataTable({
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

        .dataTables_info {
            position: absolute;
            bottom: 15px;
            left: 20px;
            font-size: 18px;
        }

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
            padding-right: 30px;
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
</style>

</body>
</html>
