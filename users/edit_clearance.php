<?php
session_start();
include '../connection/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='index.php';</script>";
    exit();
}


$username = $_SESSION['user']; // Get logged-in user

// ✅ Kunin ang user details mula sa `users` table
$query = "SELECT lastname, firstname, middlename, address, birthday FROM users WHERE user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($lastname, $firstname, $middlename, $address, $birthday);
$stmt->fetch();
$stmt->close();

// ✅ Format Full Name: "Lastname, Firstname Middlename"
$full_name = trim("$lastname, $firstname $middlename");

// ✅ Ensure clearance ID exists in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid ID.'); window.location.href='prof.php';</script>";
    exit();
}

$clearanceID = $_GET['id'];

// ✅ Fetch clearance data from database
$query = "SELECT * FROM clearance WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $clearanceID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "<script>alert('Record not found.'); window.location.href='prof.php';</script>";
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Clearance Request</title>
    <link rel="stylesheet" href="../users/css/sidebar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include '../users/sidebar.php'; ?>

<div class="clearance-form">
    <h1>Edit Clearance Request</h1>
    <form action="update_clearance.php?id=<?= urlencode($clearanceID) ?>" method="POST">
        
        <!-- ✅ Clearance Type -->
        <label for="type">Clearance Type:</label>
        <select id="clearance_type" name="type" required>
            <option value="">Select Clearance Type</option>
            <option value="Barangay Clearance" <?= ($row['type'] == 'Barangay Clearance') ? 'selected' : ''; ?>>Barangay Clearance</option>
            <option value="Business Clearance" <?= ($row['type'] == 'Business Clearance') ? 'selected' : ''; ?>>Business Clearance</option>
        </select>

        <!-- ✅ Auto-filled Full Name (Read-Only) -->
        <label for="name">Full Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($full_name) ?>" readonly>

        <!-- ✅ Auto-filled Address (Read-Only) -->
        <label for="address">Address:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" readonly>

        <!-- ✅ Auto-filled Birthday (Read-Only) -->
        <label for="birthday">Birthday:</label>
        <input type="date" name="birthday" value="<?= $birthday ?>" readonly>

        <!-- ✅ Years Stayed in Barangay -->
        <label for="year_stay">Years Stayed in Barangay:</label>
        <input type="number" name="year_stay" value="<?= htmlspecialchars($row['year_stay_in_brgy']) ?>" placeholder="Enter Years" required>

        <!-- ✅ Purpose (Auto-fetch) -->
        <label for="purpose">Purpose:</label>
        <select id="purpose" name="purpose" required>
            <option value="">Select Purpose</option>
        </select>
        <input type="hidden" id="selectedPurpose" value="<?= htmlspecialchars($row['purpose']) ?>">

        <!-- ✅ Amount (Auto-fetch) -->
        <label for="amount">Amount:</label>
        <input type="number" name="amount" id="amount" value="<?= htmlspecialchars($row['amount']) ?>" readonly>

        <!-- ✅ Date of Request -->
        <label for="date">Date:</label>
        <input type="date" name="date" value="<?= date('Y-m-d', strtotime($row['date'])) ?>" required>

        <div class="button-container">
            <a href="javascript:history.back()" class="btn">Back</a>
            <button type="submit" class="update-btn">Update</button>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let docsType = document.getElementById('clearance_type').value;
    let selectedPurpose = document.getElementById('selectedPurpose').value; // ✅ Kunin ang dating purpose

    console.log("🔍 Docs Type:", docsType);
    console.log("🔍 Selected Purpose Before Fetch:", selectedPurpose);

    if (docsType) {
        fetch('get_purpose.php?docs_type=' + docsType)
            .then(response => response.text())
            .then(data => {
                let purposeDropdown = document.getElementById('purpose');
                purposeDropdown.innerHTML = data;
                console.log("📌 Purpose Options Loaded:", data); // ✅ Debugging

                // ✅ Hanapin agad ang selectedPurpose
                let matchedOption = Array.from(purposeDropdown.options).find(option => 
                    option.value.trim().toLowerCase() === selectedPurpose.trim().toLowerCase()
                );

                if (matchedOption) {
                    matchedOption.selected = true;
                    console.log("✅ Purpose Set Instantly:", selectedPurpose);
                }
            })
            .catch(error => console.error("❌ Error fetching purpose:", error));
    }
});


    document.getElementById('clearance_type').addEventListener('change', function() {
        var docsType = this.value;
        document.getElementById('amount').value = '';
        document.getElementById('purpose').innerHTML = '<option value="">Select Purpose</option>';

        if (docsType) {
            var xhrPurpose = new XMLHttpRequest();
            xhrPurpose.open('GET', 'get_purpose.php?docs_type=' + docsType, true);
            xhrPurpose.onreadystatechange = function() {
                if (xhrPurpose.readyState == 4 && xhrPurpose.status == 200) {
                    document.getElementById('purpose').innerHTML = xhrPurpose.responseText;
                }
            };
            xhrPurpose.send();

            var xhrFee = new XMLHttpRequest();
            xhrFee.open('GET', 'get_fee.php?docs_type=' + docsType, true);
            xhrFee.onreadystatechange = function() {
                if (xhrFee.readyState == 4 && xhrFee.status == 200) {
                    document.getElementById('amount').value = xhrFee.responseText;
                }
            };
            xhrFee.send();
        }
    });
</script>

<style>
/* ✅ CLEARANCE FORM - Responsive and Centered */
.clearance-form {
    width: 30%;
    max-width: 500px;
    padding: 20px;
    border-radius: 8px;
    background-color: #f4f4f4;
    box-shadow: 2px 2px 15px 10px rgba(0, 0, 0, 0.1);
    border: 2px solid #1abc9c;

    /* ✅ Center the form */
    position: absolute;
    top: 50%;
    left: 55%;
    transform: translate(-50%, -50%);
}

/* ✅ Title */
h1 {
    color: #333;
    font-size: 25px;
    margin-bottom: 8px;
    text-align: center;
}

/* ✅ Label Styling */
.clearance-form label {
    font-weight: bold;
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
}

/* ✅ Input Fields */
.clearance-form input, 
.clearance-form select {
    width: 100%; /* Parehong lapad */
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-bottom: 10px;
    text-transform: capitalize;
    box-sizing: border-box;
}

/* ✅ BUTTON CONTAINER */
.button-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 20px;
}

/* ✅ BUTTON STYLES */
.button-container a,
.button-container button {
    flex: 1;
    max-width: 120px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    border-radius: 6px;
    transition: background-color 0.3s, transform 0.2s;
}

/* ✅ Back Button */
.btn {
    background-color: #34495e;
    color: white;
    text-decoration: none;
    border: none;
}

.btn:hover {
    background-color: #1abc9c;
    transform: scale(1.05);
}

/* ✅ Update Button */
.update-btn {
    background-color: #1abc9c;
    color: white;
    border: none;
    cursor: pointer;
}

.update-btn:hover {
    background-color: #16a085;
    transform: scale(1.05);
}
/* ✅ Read-Only Fields */
input[readonly] {
    background-color: #e9ecef;
    cursor: not-allowed;
}
/* ✅ RESPONSIVE DESIGN ✅ */
@media (max-width: 1024px) {
    .clearance-form {
        width: 50%;
    }

    h1 {
        font-size: 20px;
    }
}

@media (max-width: 768px) {
    .clearance-form {
        width: 80%;
        padding: 18px;
        position: static;
        transform: none;
        margin: 20px auto;
    }

    h1 {
        font-size: 22px;
    }

    /* ✅ Buttons in one row */
    .button-container {
        flex-direction: row;
        gap: 10px;
    }
}

@media (max-width: 480px) {
    .clearance-form {
        width: 95%;
        max-width: 360px;
        padding: 15px;
        margin-top: 20%;
    }

    h1 {
        font-size: 20px;
    }

    /* ✅ Button layout sa mobile */
    .button-container {
        flex-direction: column;
        gap: 8px;
    }

    .button-container a,
    .button-container button {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
    }
}
</style>

</body>
</html>
