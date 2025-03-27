<?php
session_start();
include '../connection/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='superadmin_login.php';</script>";
    exit();
}

// Fetch Barangay Officials
function getBrgyOfficials($conn) {
    $sql = "SELECT * FROM brgy_official ORDER BY FIELD(position, 
        'Barangay Chairman', 
        'Committee of Peace and Order', 
        'Committee on Education', 
        'Committee on Public Works and Hi-ways', 
        'Committee on Appropriation', 
        'Committee on Womens and Family',  
        'Committee on Agriculture',       
        'Committee on Health',   
        'SK Chairman Committee on Sports and Youth Development',                                  
        'Barangay Secretary',            
        'Barangay Treasurer'
    )";
    $result = mysqli_query($conn, $sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

$barangayOfficials = getBrgyOfficials($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Officials</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        /* ✅ MAIN CONTENT WRAPPER ✅ */
        .content-wrapper {
            margin-left: 250px;
            padding: 90px 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ✅ HEADER DESIGN ✅ */
        .header-container {
            background: white;
            padding: 15px;
            width: 90%;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            border: 2px solid #1abc9c;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* ✅ BUTTON WRAPPER (Para ilagay sa kanan) ✅ */
        .add-btn-container {
            width: 90%;
            display: flex;
            justify-content: flex-end; /* Ilalagay ang button sa kanan */
            margin-top: 10px;
        }

            /* ✅ ADD BUTTON STYLES ✅ */
            .add-btn {
                padding: 10px 20px;
                background-color: #1abc9c;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                margin: 20px 0;
            }

        .add-btn:hover {
            background-color: #16a085;
        }

        /* ✅ DELETE BUTTON STYLES ✅ */
        .delete-btn {
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        /* ✅ OFFICIALS CONTAINER (SCROLLBAR SA LOOB, CENTERED) ✅ */
        .officials-container {
            width: 90%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            max-height: 65vh;
            overflow-y: auto;
        }

        /* ✅ GRID LAYOUT ✅ */
        .officials-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 50px;
            padding: 20px;
        }

        /* ✅ OFFICIAL CARD DESIGN ✅ */
        .official-card {
            width: 200px;
            background: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out;
        }

        .official-card:hover {
            transform: scale(1.05);
        }

        /* ✅ IMAGE STYLE ✅ */
        .official-card img {
            width: 100%;
            height: 170px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #34495e;
            margin-bottom: 15px;
        }

        /* ✅ TEXT STYLES ✅ */
        .official-card h3 {
            margin: 10px 0 5px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .position {
            font-size: 12px;
            color: #555;
        }

/* ✅ MODAL STYLES (ADD OFFICIAL FORM) ✅ */
.modal {
    display: none;  /* Ensure modal is hidden initially */
    position: fixed;
    z-index: 10;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}


.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    width: 90%;
    max-width: 450px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    border: 2px solid #1abc9c;
    display: flex;
    flex-direction: column;
    gap: 15px; /* Mas maluwag na pagitan */
}

/* ✅ Close Button (X) */
.close {
    color: #aaa;
    float: right;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}

/* ✅ FORM INPUT STYLING (Pantay at Responsive) */
.modal-content label {
    font-size: 14px;
    font-weight: bold;
    color: #333;
}

.modal-content input, 
.modal-content select {
    width: 100%;
    padding: 12px; /* Mas malaking padding */
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
    margin-bottom: 15px;
}

/* ✅ FORM BUTTON CONTAINER (Para Gitna ang Button) */
.button-container {
    display: flex;
    justify-content: center; /* Gitnang-gitna horizontally */
    margin-top: 15px; /* Dagdagan ng spacing sa taas */
}

/* ✅ FORM BUTTON */
.modal-content button {
    padding: 12px 25px;
    background-color: #1abc9c;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}


.modal-content button:hover {
    background-color: #16a085;
}
h2{
    text-align: center;
}

    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="header-container">
            BARANGAY OFFICIALS
        </div>

        <div class="add-btn-container">
        <button class="add-btn" id="addOfficialBtn">Add New Official</button>
        </div>

        <div class="officials-container">
            <?php 
            if (!empty($barangayOfficials)) :
                echo '<div class="chairman-row">';
                foreach ($barangayOfficials as $official) :
                    if ($official['position'] === 'Barangay Chairman') {
                        echo '<div class="official-card">
                                <img src="' . '../' . htmlspecialchars($official['profile']) . '" 
                                    alt="Profile Picture" onerror="this.onerror=null; this.src=\'../image/default.png\';">
                                <h3>' . htmlspecialchars($official['name']) . '</h3>
                                <p class="position">' . htmlspecialchars($official['position']) . '</p>
                                <button class="delete-btn" onclick="deleteOfficial(' . $official['id'] . ')">Delete</button>
                            </div>';
                        break;
                    }
                endforeach;
                echo '</div>'; // Close chairman row
            endif;
            ?>

            <div class="officials-grid upper-row">
                <?php
                $count = 0;
                $otherOfficials = []; 

                foreach ($barangayOfficials as $official) :
                    if ($official['position'] !== 'Barangay Chairman') {
                        if ($count < 5) {
                            echo '<div class="official-card">
                                    <img src="' . '../' . htmlspecialchars($official['profile']) . '" 
                                        alt="Profile Picture" onerror="this.onerror=null; this.src=\'../image/default.png\';">
                                    <h3>' . htmlspecialchars($official['name']) . '</h3>
                                    <p class="position">' . htmlspecialchars($official['position']) . '</p>
                                    <button class="delete-btn" onclick="deleteOfficial(' . $official['id'] . ')">Delete</button>
                                </div>';
                        } else {
                            $otherOfficials[] = $official; 
                        }
                        $count++;
                    }
                endforeach;
                ?>
            </div>

            <div class="officials-grid lower-row">
                <?php
                foreach ($otherOfficials as $official) :
                    echo '<div class="official-card">
                            <img src="' . '../' . htmlspecialchars($official['profile']) . '" 
                                alt="Profile Picture" onerror="this.onerror=null; this.src=\'../image/default.png\';">
                            <h3>' . htmlspecialchars($official['name']) . '</h3>
                            <p class="position">' . htmlspecialchars($official['position']) . '</p>
                            <button class="delete-btn" onclick="deleteOfficial(' . $official['id'] . ')">Delete</button>
                        </div>';
                endforeach;
                ?>
            </div>
        </div>
    </div>


        <!-- Positions -->
    <div id="addOfficialModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New Official</h2>
        <form id="addOfficialForm" enctype="multipart/form-data">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="position">Position:</label>
            <select id="position" name="position" required>
                <option value="Barangay Chairman">Barangay Chairman</option>
                <option value="Committee of Peace and Order">Committee of Peace and Order</option>
                <option value="Committee on Education">Committee on Education</option>
                <option value="Committee on Public Works and Hi-ways">Committee on Public Works and Hi-ways</option>
                <option value="Committee on Appropriation">Committee on Appropriation</option>
                <option value="Committee on Womens and Family">Committee on Womens and Family</option>
                <option value="Committee on Agriculture">Committee on Agriculture</option>
                <option value="Committee on Health">Committee on Health</option>
                <option value="SK Chairman Committee on Sports and Youth Development">SK Chairman</option>
                <option value="Barangay Secretary">Barangay Secretary</option>
                <option value="Barangay Treasurer">Barangay Treasurer</option>
            </select>

            <label for="profile">Upload Profile Picture:</label>
            <input type="file" id="profile" name="profile" accept="image/*" required>

            <div class="button-container">
                <button type="submit">Upload</button>
            </div>

        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure the modal is hidden when the page loads
    document.getElementById("addOfficialModal").style.display = "none";
});

document.getElementById("addOfficialBtn").onclick = function() {
    // Show the modal when the "Add New Official" button is clicked
    document.getElementById("addOfficialModal").style.display = "flex";
};

document.querySelector(".close").onclick = function() {
    // Hide the modal when the close button (X) is clicked
    document.getElementById("addOfficialModal").style.display = "none";
};

// Prevent the modal from appearing immediately by using display: none in CSS
// The following ensures that clicking outside the modal does not close it unless explicitly done
window.onclick = function(event) {
    const modal = document.getElementById("addOfficialModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
};

        function deleteOfficial(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("delete_official.php?id=" + id, { method: "GET" })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.success ? "success" : "error",
                                title: data.success ? "Deleted!" : "Error!",
                                text: data.message,
                                confirmButtonColor: "#3085d6"
                            }).then(() => location.reload());
                        })
                        .catch(error => Swal.fire("Error!", "Request failed: " + error, "error"));
                }
            });
        }
        document.getElementById("addOfficialForm").onsubmit = function(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            Swal.fire({
                title: "Uploading...",
                text: "Please wait while the data is being processed.",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            fetch("insert_officials.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.success ? "success" : "error",
                    title: data.success ? "Success!" : "Error!",
                    text: data.message,
                    confirmButtonColor: "#3085d6"
                }).then(() => {
                    if (data.success) {
                        document.getElementById("addOfficialModal").style.display = "none";
                        location.reload();
                    }
                });
            })
            .catch(error => Swal.fire("Error!", "Request failed: " + error, "error"));
        };
    </script>
</body>
</html>
