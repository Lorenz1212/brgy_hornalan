<?php
session_start();
include '../connection/connect.php'; // Database Connection


// Check kung may session username na
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='superadmin_login.php';</script>";
    exit();
}

// Wala na ang role check, diretso na lang sa dashboard
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$query = "SELECT * FROM trash ORDER BY deleted_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trash Requests</title>
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
            <h3>Rejected and Trashed Requests</h3>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search for names...">
                <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button>
                <span id="clearBtn" class="clear-btn" onclick="clearSearch()">&#10006;</span>
            </div>
        </div>

        <div class="table-wrapper">
        <button id="deleteSelectedBtn" class="delete-btn">Delete Selected</button>

            <table class="custom-table" id="recycleTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Birthday</th>
                        <th>Years Stayed</th>
                        <th>Purpose</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><input type="checkbox" class="selectItem" value="<?php echo $row['id']; ?>"></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo date("F d, Y", strtotime($row['birthday'])); ?></td>
                            <td><?php echo htmlspecialchars($row['year_stay_in_brgy']); ?></td>
                            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td><?php echo date("F d, Y", strtotime($row['date'])); ?></td>
                            <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td>
                            <div class="btn-container">
                                <a href="javascript:void(0);" onclick="approveRequest('<?php echo $row['id']; ?>')" class="btn approve-btn">✔ Restore</a>
                                </div>
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
    window.location.href = 'restore.php?id=' + id;
}

</script>
<?php
mysqli_close($conn);
?>

<!-- Script For Search, Clear Button -->
<script>
    $(document).ready(function() {
    window.table = $('#recycleTable').DataTable({
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

    $('#selectAll').on('click', function() {
        $('.selectItem').prop('checked', this.checked);
    });

    $('#deleteSelectedBtn').on('click', function() {
        let selectedIds = [];
        $('.selectItem:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No items selected!',
                text: 'Please select at least one item to delete.',
                confirmButtonColor: '#d33'
            });
            return;
        }

        Swal.fire({
            title: "Are you sure?",
            text: "You are about to delete the selected requests!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete them!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'document_delete.php',
                    type: 'POST',
                    data: { ids: selectedIds },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted Successfully!',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            }
        });
    });
});
    </script>

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

/* ✅ Table Style */
.table-wrapper {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
    position: relative;
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

/* ✅ Delete Selected Button */
#deleteSelectedBtn {
    background: #e74c3c;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: 0.3s;
    display: block;
    margin-bottom: 15px;
    align-self: flex-start;
}

#deleteSelectedBtn:hover {
    background: #c0392b;
    transform: scale(1.05);
}

#deleteSelectedBtn:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
}

/* ✅ Action Button Container */
.btn-container {
    display: flex;
    justify-content: center;
    gap: 5px;
}

/* ✅ Restore Button */
.approve-btn {
    display: inline-block;
    background: #27ae60;
    color: white;
    padding: 6px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    border: none;
    cursor: pointer;
  
}

.approve-btn:hover {
    opacity: 0.9;
    transform: scale(1.05);
}

/* ✅ Search Bar */
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

/* ✅ Clear Button (X) */
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
