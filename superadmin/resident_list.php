<?php
session_start();
include '../connection/connect.php'; // Database Connection
// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='index.php';</script>";
    exit();
}
// Cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$query = "SELECT * FROM resident_list ORDER BY id ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
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

        /* Search Bar */
         /* Search Bar & Search Button */
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
            left: 45%;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            cursor: pointer;
            display: none;
        }


        #residentTable_filter input {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding-right: 30px;
            margin-bottom: 10px;
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

/* Custom Button Styles */
.add-btn {
    padding: 10px 20px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 8px; /* Space between the icon and text */
    transition: background-color 0.3s ease;
    margin-left: 10px; /* Add some space between the buttons */
}

.add-btn i {
    font-size: 14px; /* Icon size */
}

.add-btn:hover {
    background-color: #2980b9; /* Darker green on hover */
}

.add-btn:focus {
    outline: none;
    box-shadow: 0 0 5px rgba(26, 188, 156, 0.8);
}

/* Additional Button Styling for Print Button */
#printBtn {
    background-color: #e74c3c; /* Blue color for Print button */
}

#printBtn:hover {
    background-color: #c0392b; /* Darker blue on hover */
}

/* ✅ Delete Button */
.delete-btn {
    background: #e74c3c;
    color: white;
    padding: 6px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
}

.delete-btn:hover {
    opacity: 0.9;
    transform: scale(1.05);
}
    </style>
</head>
<body>
<div class="content-wrapper">
    <div class="header-container">
        <h3>Resident List</h3>
        <div class="search-container">
    <input type="text" id="searchInput" placeholder="Search for names..." oninput="toggleClearButton()">
    <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button> <!-- ✅ May ID na -->
    <span id="clearBtn" class="clear-btn" onclick="clearSearch()" style="display:none;">&#10006;</span> <!-- Itago muna -->

     <!-- ✅ ADD BUTTON -->
     <button id="addResidentBtn" class="add-btn">
        <i class="fas fa-user-plus"></i> Add Resident
    </button>

        <button id="printBtn" class="add-btn" onclick="printTable()">
            <i class="fas fa-print"></i> Print
        </button>
</div>

    </div>

    <div class="table-wrapper">
        <table id="residentTable" class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name of Family Member</th>
                    <th>Complete Address</th>
                    <th>Position in the Family</th>
                    <th>Age</th>
                    <th>Civil Status</th>
                    <th>Occupation</th>
                    <th>Contact Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name_of_family_member']); ?></td>
                        <td><?php echo htmlspecialchars($row['complete_address']); ?></td>
                        <td><?php echo htmlspecialchars($row['position_in_the_family']); ?></td>
                        <td><?php echo htmlspecialchars($row['age']); ?></td>
                        <td><?php echo htmlspecialchars($row['civil_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['occupation_source_of_income']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                        <td>
                            <div class="btn-container">
                                <a href="javascript:void(0);" onclick="deleteResident('<?php echo $row['id']; ?>')" class="btn delete-btn">🗑 Delete</a>
                            </div>
                            </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // ✅ Initialize DataTable with manual search
    window.table = $('#residentTable').DataTable({
        "paging": true,
        "pageLength": 15,
        "lengthChange": false, // ❌ Hide "Show Entries" dropdown
        "ordering": true,
        "info": true,
        "searching": true, // ✅ Enable DataTables search
        "dom": "tpi", // ❌ Hide default search box
        "columnDefs": [
            { targets: 0, searchable: false }, // ❌ Hindi searchable ang ID
            { targets: [1, 2, 3, 4, 5, 6, 7], searchable: true } // ✅ Searchable lahat ng ibang columns
        ]
    });

    // ✅ Search Button Click Event (Manual Search)
    $('#searchBtn').on('click', function() {
        let searchValue = $('#searchInput').val().trim();
        table.search(searchValue, false, false).draw();
    });

    // ✅ "X" Button (Clear Search)
    $('#clearBtn').on('click', function() {
        $('#searchInput').val(""); // I-clear ang input
        table.search("").draw(); // I-reset ang search
        $('#clearBtn').hide(); // Itago ang clear button
    });

    // ✅ Ipakita lang ang "X" button kapag may laman ang search input
    $('#searchInput').on('input', function() {
        $('#clearBtn').toggle($(this).val().length > 0);
    });
});

// ✅ Delete Single Resident
function deleteResident(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "You are about to delete this resident!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'delete_resident.php',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Resident Deleted!',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                }
            });
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

        document.getElementById("addResidentBtn").addEventListener("click", function() {
    window.location.href = "add_resident.php"; // ✅ Redirect sa form
});

function printTable() {
    let table = $('#residentTable').DataTable();
    let filteredData = table.rows({ search: 'applied' }).nodes(); // ✅ Kunin lang ang filtered rows

    if (filteredData.length === 0) {
        alert("❌ Walang data na naka-filter para ma-print.");
        return;
    }

    let printContent = `
        <html>
        <head>
            <title>Resident List</title>
            <style>
            body { 
                font-family: Arial, sans-serif; 
                font-size: 14px; 
                text-align: center; 
                background: none !important; /* ✅ Tatanggalin ang background */
            }
            .table-wrapper {
                margin: 20px;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                table-layout: fixed;
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            }
            th, td { 
                padding: 8px; 
                text-align: center; 
                border: 2px solid black; 
                word-wrap: break-word;
                min-width: 100px;
            }
            /* ✅ Ayusin ang ID column */
            td:first-child { 
                font-size: 14px;
                line-height: 1.6;
                min-width: 70px;
                max-width: 90px;
                white-space: nowrap;
                overflow: hidden;
                padding: 10px 5px;
            }
            th { 
                background-color: #2c3e50 !important; 
                color: white !important;
                font-weight: bold;
                text-transform: uppercase;
                white-space: nowrap;
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important;
                font-size: 15px;
            }
            tbody tr:nth-child(even) { background-color: #f2f2f2; }
            tbody tr:nth-child(odd) { background-color: white; }
            h2 {
                font-size: 20px;
                text-align: center;
                margin-bottom: 20px;
            }
            /* ✅ Page break every 15 rows */
            tr:nth-child(15n) { page-break-after: always; }
            .dataTables_length, 
            .dataTables_filter, 
            .dataTables_info, 
            .dataTables_paginate,
            .search-container, 
            .add-btn {
                display: none !important;
            }
            @media print {
                body { background: none !important; } /* ✅ Alisin ang background sa print */
            }
        </style>
        </head>
        <body>
            <h2>Resident List</h2>
            <div class="table-wrapper">
                <table class="custom-table">
                    ${document.querySelector("#residentTable thead").outerHTML}
                    <tbody>
    `;

    $(filteredData).each(function () {
        printContent += this.outerHTML;
    });

    printContent += `</tbody></table></div></body></html>`;

    // ✅ Gumawa ng hidden iframe para sa print
    let iframe = document.createElement("iframe");
    iframe.style.position = "absolute";
    iframe.style.width = "0px";
    iframe.style.height = "0px";
    iframe.style.border = "none";

    document.body.appendChild(iframe);
    let doc = iframe.contentWindow.document;

    doc.open();
    doc.write(printContent);
    doc.close();

    // ✅ Hintayin mag-load bago i-trigger ang print
    setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        document.body.removeChild(iframe);
    }, 500);
}

</script>


</body>
</html>

<?php mysqli_close($conn); ?>
