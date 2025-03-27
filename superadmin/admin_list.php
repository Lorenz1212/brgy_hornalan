<?php
session_start();
include '../connection/connect.php'; // Database Connection


// ✅ Kunin lang ang mga may role na "admin"
$query = "SELECT username, role, session_active FROM admin WHERE role = 'admin' ORDER BY username ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin List</title>
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
            <h3>Admin List</h3>
            <div class="search-container">
                <button id="addAdminBtn" class="add-btn" onclick="window.location.href='signup.php';"><i class="fas fa-user-plus"></i> Add Admin</button>
                <input type="text" id="searchInput" placeholder="Search username...">
                <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> Search</button>
                <span id="clearBtn" class="clear-btn" onclick="clearSearch()">&#10006;</span>
            </div>
        </div>

        <div class="table-wrapper">
    <table class="custom-table" id="adminTable">
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Session Active</th>
                <th>Action</th> <!-- ✅ Nadagdag na column -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars(ucwords($row['role'])); ?></td>
                    <td><?php echo $row['session_active'] == 1 ? '<span style="color:green; font-weight:bold;">Active</span>' : '<span style="color:red;">Inactive</span>'; ?></td>
                    <td class="btn-container">

                     <!-- Toggle Session Active Button -->
                     <button id="toggleBtn-<?php echo $row['username']; ?>" class="toggle-btn <?php echo $row['session_active'] == 1 ? 'active' : 'inactive'; ?>" onclick="toggleSession('<?php echo $row['username']; ?>', <?php echo $row['session_active']; ?>)">
                    <?php echo $row['session_active'] == 1 ? 'Deactivate' : 'Activate'; ?>
                </button>

                
                <button onclick="confirmDelete('<?php echo $row['username']; ?>')" class="btn delete-btn">
                    <i class="fas fa-trash"></i> Delete
                </button>
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
    $(document).ready(function() {
        window.table = $('#adminTable').DataTable({
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

        $('#addAdminBtn').on('click', function() {
            window.location.href = 'signup.php';
        });
    });

    function updateAdminStatus() {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_admin_status.php", true);
    xhr.onload = function () {
        if (xhr.status == 200) {
            try {
                let data = JSON.parse(xhr.responseText);

                // Get all rows in the admin table
                let rows = document.querySelectorAll("#adminTable tbody tr");

                // Loop through each row and update the status
                rows.forEach(row => {
                    let username = row.cells[0].textContent.trim(); // Get the username from the first cell
                    let statusCell = row.cells[2]; // Status is in the 3rd column

                    if (data[username] == 1) {
                        // If the admin is active
                        statusCell.innerHTML = '<span style="color:green; font-weight:bold;">Active</span>';
                    } else {
                        // If the admin is inactive
                        statusCell.innerHTML = '<span style="color:red;">Inactive</span>';
                    }
                });
            } catch (e) {
                console.error("Error parsing response:", e);
            }
        } else {
            console.error("Error fetching admin status: " + xhr.status);
        }
    };
    xhr.onerror = function () {
        console.error("Error with AJAX request");
    };
    xhr.send();
}

// Call the update function every 5 seconds
setInterval(updateAdminStatus, 5000); // Update every 5 seconds

    

    function confirmDelete(username) {
    Swal.fire({
        title: "Are you sure?",
        text: "You are about to delete this admin!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            // ✅ Gumamit ng timestamp para iwas cache issue
            let url = 'delete_admin.php?username=' + encodeURIComponent(username) + '&_=' + Date.now();
            
            fetch(url, {
                method: "GET",
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json()) // ✅ Ensure JSON response
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: data.message,
                        confirmButtonColor: "#28a745"
                    }).then(() => {
                        location.reload(); // ✅ Refresh table after delete
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: data.message,
                        confirmButtonColor: "#d33"
                    });
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Request Failed!",
                    text: "Something went wrong.",
                    confirmButtonColor: "#d33"
                });
            });
        }
    });
}

function toggleSession(username, currentStatus) {
    let newStatus = currentStatus == 1 ? 0 : 1; // If active, make it inactive, otherwise active

    // Confirmation prompt before toggling
    Swal.fire({
        title: currentStatus == 1 ? 'Deactivate Session?' : 'Activate Session?',
        text: currentStatus == 1 ? 'This will log the admin out.' : 'This will activate the admin session.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: currentStatus == 1 ? 'Deactivate' : 'Activate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Make AJAX request to update session_active
            let xhr = new XMLHttpRequest();
            xhr.open('GET', 'update_session_status.php?username=' + username + '&status=' + newStatus, true);
            xhr.onload = function() {
                // Debug logs
                console.log(xhr.status);  // Tignan ang status code ng response
                console.log(xhr.responseText);  // Tignan ang response ng server
                
                if (xhr.status == 200) {
                    let data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        Swal.fire('Success!', data.message, 'success').then(() => {
                            // Pag-update ng button nang direkta, walang reload
                            let button = document.getElementById('toggleBtn-' + username);

                            // I-update ang button na may tamang class at text
                            if (newStatus == 1) {
                                button.classList.remove('inactive');
                                button.classList.add('active');
                                button.innerText = 'Deactivate'; // Change button text
                            } else {
                                button.classList.remove('active');
                                button.classList.add('inactive');
                                button.innerText = 'Activate'; // Change button text
                            }
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                } else {
                    Swal.fire('Error!', 'Request failed. Please try again.', 'error');
                }
            };
            xhr.send();
        }
    });
}




</script>

<style>
    /* ✅ Table Container */
.content-wrapper {
    margin-left: 250px;
    padding: 90px 40px 30px;
    display: flex;
    flex-direction: column;
}

/* ✅ Header Container */
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
    background: #34495e;
    color: white;
    height: 50px;
}

/* ✅ Add Admin Button */
.add-btn {
    padding: 6px 12px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-right: 10px;
    font-size: 14px;
    font-weight: bold;
}

.add-btn i {
    margin-right: 5px;
}

.add-btn:hover {
    background: #2980b9;
}

/* Search Bar */
.search-container {
    display: flex;
    gap: 5px;
    position: relative;
    align-items: center;
}

#searchInput {
    padding: 8px;
    width: 300px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.search-btn {
    padding: 6px 12px;
    background: #16a085;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.search-btn:hover {
    background: #1abc9c;
}

/* Clear Button (X) */
.clear-btn {
    position: absolute;
    right: 100px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    cursor: pointer;
    display: none;
}

/* ✅ Action Button Container */
.btn-container {
    display: flex;
    justify-content: center;
    gap: 5px;
}

/* ✅ Edit Button */
.edit-btn {
    background: #f39c12;
    color: white;
    padding: 6px 10px;
    border-radius: 5px;
    text-decoration: none;
}

.edit-btn:hover {
    opacity: 0.9;
    transform: scale(1.05);
}

/* ✅ Delete Button */
.delete-btn {
    background: #e74c3c;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
}

.delete-btn:hover {
    opacity: 0.9;
    transform: scale(1.05);
}

/* **Media Queries for Responsiveness** */

/* For tablets and smaller devices */
@media (max-width: 1024px) {
    .content-wrapper {
        margin-left: 0; /* Remove left margin */
        padding: 50px 20px; /* Adjust padding */
    }

    .header-container {
        flex-direction: column; /* Stack items vertically */
        text-align: center;
    }

    .table-wrapper {
        padding: 15px; /* Adjust table wrapper padding */
    }

    .custom-table th, .custom-table td {
        font-size: 14px; /* Adjust text size */
    }

    #searchInput {
        width: 100%; /* Make search input full width */
        margin-bottom: 10px; /* Add margin */
    }

    .add-btn {
        width: 100%; /* Make button full width */
    }
}

/* For mobile screens */
@media (max-width: 768px) {
    .header-container h3 {
        font-size: 16px; /* Smaller font size */
    }

    .custom-table th, .custom-table td {
        font-size: 12px; /* Smaller font size */
    }

    .search-container {
        flex-direction: column;
        align-items: flex-start; /* Align items to the left */
    }

    .search-btn {
        margin-top: 10px; /* Add margin to search button */
    }

    .content-wrapper {
        padding: 20px; /* Adjust padding for smaller screens */
    }

    .add-btn {
        width: 100%; /* Full width button */
        margin-top: 10px;
    }
}

/* For very small mobile screens */
@media (max-width: 480px) {
    .header-container h3 {
        font-size: 14px; /* Even smaller font size */
    }

    .custom-table th, .custom-table td {
        font-size: 10px; /* Very small text size */
    }

    .search-container {
        width: 100%; /* Full width search container */
    }

    .custom-table {
        font-size: 10px; /* Smaller font size for table */
    }

    .btn-container {
        flex-direction: column; /* Stack buttons vertically */
        gap: 10px;
    }

    .add-btn {
        width: 100%; /* Full width button */
    }
}

/* ✅ Toggle Button (Activate/Deactivate) */
.toggle-btn {
    padding: 8px 15px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    text-align: center;
    border: none;
    transition: background-color 0.3s ease, transform 0.3s ease;
    color: white;
    display: inline-block;
    margin: 5px;
}

.toggle-btn.active {
    background-color: #27ae60; /* Green for active */
}

.toggle-btn.inactive {
    background-color: #e74c3c; /* Red for inactive */
}

.toggle-btn:hover {
    transform: scale(1.05);
}

.toggle-btn:focus {
    outline: none;
}
</style>

<?php
mysqli_close($conn);
?>
