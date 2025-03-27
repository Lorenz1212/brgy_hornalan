<?php
session_start();
include '../connection/connect.php';

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='user.php';</script>";
    exit();
}

// ✅ Kunin ang user details mula sa `users` table
$user = $_SESSION['user'];
$query = "SELECT lastname, firstname, middlename, address, birthday FROM users WHERE user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($lastname, $firstname, $middlename, $address, $birthday);
$stmt->fetch();
$stmt->close();

// ✅ Format Full Name: "Lastname, Firstname Middlename"
$full_name = trim("$lastname, $firstname $middlename");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Request Form</title>
    <link rel="stylesheet" type="text/css" href="../users/css/sidebar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include '../users/sidebar.php'; ?>
<div class="form-container">
    <!-- X BUTTON -->
    <button id="closeForm" class="close-btn">&times;</button>
    <h1>Certificate Request Form</h1>
    <p><strong>Note:</strong> Please fill out the form below. Ensure that all details are accurate to avoid processing delays.</p>

    <form id="clearanceForm" action="submit.php" method="POST">
        <div class="form-grid">
            
            <div>
                <label for="docs_type">Certificate Type:</label>
                <select id="clearance_type" name="docs_type" required>
                    <option value="">Select Clearance Type</option>
                    <option value="Indigency">Indigency</option>
                    <option value="Barangay Certificate">Barangay Certificate</option>
                    <option value="Residency">Residency</option>
                    <option value="Cedula ">Cedula </option>
                    <option value="First Time Job Seeker Certificate">First Time job seeker certificate</option>
                </select>
            </div>
            
            <div>
                <label for="year_stay">Years Stayed in Barangay:</label>
                <input type="number" name="year_stay" placeholder="Enter Years" required min="1">
                <small style="color: red; font-style: italic;">* If less than 1 year, enter 1.</small>
            </div>

            <!-- ✅ Auto-filled Full Name (Read-Only) -->
            <div>
                <label for="name">Full Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($full_name) ?>" readonly>
            </div>

            <div>
                <label for="purpose">Purpose:</label>
                <select id="purpose" name="purpose" required>
                    <option value="">Select Purpose</option>
                </select>
            </div>

            <!-- ✅ Auto-filled Address (Read-Only) -->
            <div>
                <label for="address">Address:</label>
                <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" readonly>
            </div>

            <div>
                <label for="request_date">Date of Request:</label>
                <input type="date" name="request_date" required>
            </div>

            <!-- ✅ Auto-filled Birthday (Read-Only) -->
            <div>
                <label for="birthday">Birthday:</label>
                <input type="date" name="birthday" value="<?= $birthday ?>" readonly>
            </div>

            <div>
                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" placeholder="Enter Amount" required readonly>
            </div>
        </div>

        <button type="submit" id="submitBtn">Submit Request</button>
    </form>
</div>

<script>
    // ✅ Restrict request date (No past dates)
    document.addEventListener("DOMContentLoaded", function () {
        let today = new Date().toISOString().split("T")[0];
        document.querySelector('input[name="request_date"]').setAttribute("min", today);
    });

    // ✅ Auto-fetch Purpose & Fee based on Clearance Type
    document.getElementById('clearance_type').addEventListener('change', function() {
        var docsType = this.value;
        document.getElementById('amount').value = '';
        document.getElementById('purpose').innerHTML = '<option value="">Select Purpose</option>';

        if (docsType) {
            // ✅ Fetch Purposes
            fetch('get_purpose.php?docs_type=' + docsType)
                .then(response => response.text())
                .then(data => document.getElementById('purpose').innerHTML = data);

            // ✅ Fetch Fee
            fetch('get_fee.php?docs_type=' + docsType)
                .then(response => response.text())
                .then(data => document.getElementById('amount').value = data);
        }
    });

    //To Detect Duplicate Form
    document.getElementById('clearanceForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Iwasan muna mag-submit

    var documentType = document.getElementById('clearance_type').value;

    if (!documentType) {
        Swal.fire("Error", "Please select a document type.", "error");
        return;
    }

    // AJAX para i-check ang pending request
    fetch('check_pending.php?type=' + documentType)
        .then(response => response.json())
        .then(data => {
            if (data.status === "error") {
                Swal.fire("Duplicate Request", data.message, "warning");
            } else {
                this.submit(); // Kung walang duplicate, proceed sa submission
            }
        })
        .catch(error => {
            Swal.fire("Error", "Something went wrong. Please try again.", "error");
        });
});

// ✅ Function para i-redirect sa prof.php kapag pinindot ang X button
document.getElementById("closeForm").addEventListener("click", function() {
    window.location.href = "prof.php"; // Redirect sa prof.php
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
/* ✅ Responsive Form Styling */
.form-container {
    width: 50%;
    padding: 20px;
    border-radius: 8px;
    background-color: #f4f4f4;
    box-shadow: 2px 2px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    border: 2px solid #1abc9c;
    position: fixed;
    top: 50%;
    left: 55%;
    transform: translate(-50%, -50%);
}
.form-container p {
    font-size: 15px;
    color: #666;
    margin-bottom: 30px;
}

/* ✅ Grid Layout */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px 30px;
    text-align: left;
    margin-bottom: 30px;
}

.form-grid div {
    display: flex;
    flex-direction: column;
}

/* ✅ Input Fields */
input, select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    margin-bottom: 8px;
}

/* ✅ Read-Only Fields */
input[readonly] {
    background-color: #e9ecef;
    cursor: not-allowed;
}

/* ✅ Submit Button */
#submitBtn {
    width: 200px;
    padding: 10px;
    background-color: #34495e;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}

#submitBtn:hover {
    transform: scale(1.05);
    background-color: #16a085;
}


/* ✅ X Button Styling */
.close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    background: none;
    border: none;
    font-size: 24px;
    color: #e74c3c;
    font-weight: bold;
    cursor: pointer;
}

.close-btn:hover {
    color: #c0392b;
}

/* ✅ RESPONSIVE DESIGN ✅ */
@media (max-width: 1024px) {
    .form-container {
        width: 65%; /* Mas maliit sa tablet */
    }

    h1 {
        font-size: 24px;
    }

    .form-container p {
        font-size: 14px;
    }
}

@media (max-width: 768px) {
    .form-container {
        width: 80%;
        padding: 15px;
        position: static;
        transform: none;
        margin: 20px auto;
    }

    h1 {
        font-size: 22px;
    }

    .form-container p {
        font-size: 14px;
    }

    /* ✅ Isang column sa tablet at mobile */
    .form-grid {
        grid-template-columns: 1fr;
    }

    input, select {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .form-container {
        width: 90%;
        padding: 10px;
        margin-top: 18%;
    }

    h1 {
        font-size: 20px;
    }

    .form-container p {
        font-size: 13px;
        margin-bottom: 20px;
    }

    /* Mas compact ang inputs */
    input, select {
        font-size: 13px;
        padding: 6px;
    }

    #submitBtn {
        width: 100%;
        font-size: 14px;
        padding: 8px;
    }
}

</style>

</body>
</html>
