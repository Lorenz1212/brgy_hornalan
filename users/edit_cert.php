<?php
session_start();
include '../connection/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='users.php';</script>";
    exit();
}

$username = $_SESSION['user'];

// Fetch user details
$query = "SELECT lastname, firstname, middlename, address, birthday FROM users WHERE user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($lastname, $firstname, $middlename, $address, $birthday);
$stmt->fetch();
$stmt->close();

// Format Full Name
$full_name = trim("$lastname, $firstname $middlename");

// ✅ Siguraduhin na may valid na 'id' sa URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid ID.'); window.location.href='prof.php';</script>";
    exit();
}

$certificateID = $_GET['id'];

// ✅ Fetch the record from the certificate table
$query = "SELECT * FROM certificate WHERE id = ? AND username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $certificateID, $username);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Siguraduhin na may nakuha na record
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
    <title>Certificate Request Form</title>
    <link rel="stylesheet" href="../users/css/sidebar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include '../users/sidebar.php'; ?>

<div class="Clearance-form">
    <h1>Certificate Request Form</h1>
    <p><strong>Note:</strong> Please fill out the form below accurately to avoid delays.</p>

    <form action="update_cert.php?id=<?= urlencode($certificateID); ?>" method="POST">
    <!-- Certificate Type Field -->
    <label for="certificate_type">Certificate Type:</label>
    <select id="certificate_type" name="type" required>

        <option value="Indigency" <?= ($row['type'] == 'Indigency') ? 'selected' : ''; ?>>Indigency</option>
        <option value="Barangay Certificate" <?= ($row['type'] == 'Barangay Certificate') ? 'selected' : ''; ?>>Barangay Certificate</option>
        <option value="Residency" <?= ($row['type'] == 'Residency') ? 'selected' : ''; ?>>Residency</option>
        <option value="Cedula" <?= ($row['type'] == 'Cedula') ? 'selected' : ''; ?>>Cedula</option>
        <option value="First Time Job Seeker Certificate" <?= ($row['type'] == 'First Time Job Seeker Certificate') ? 'selected' : ''; ?>>First Time Job Seeker Certificate</option>
    </select>

    <!-- Full Name Field -->
    <label for="name">Full Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($row['name']); ?>" readonly>

    <!-- Address Field -->
    <label for="address">Address:</label>
    <input type="text" name="address" value="<?= htmlspecialchars($row['address']); ?>" readonly>

    <!-- Birthday Field -->
    <label for="birthday">Birthday:</label>
    <input type="date" name="birthday" value="<?= date('Y-m-d', strtotime($row['birthday'])); ?>" readonly>

    <!-- Year Stay in Barangay Field -->
    <label for="year_stay">Years Stayed in Barangay:</label>
    <input type="number" name="year_stay" id="year_stay" value="<?= htmlspecialchars($row['year_stay_in_brgy']); ?>" required>

    <!-- Purpose Field -->
    <label for="purpose">Purpose:</label>
    <select id="purpose" name="purpose" required>
        <option value="">Select Purpose</option>
    </select>
    <input type="hidden" id="selectedPurpose" value="<?= htmlspecialchars($row['purpose']); ?>">

    <!-- Amount Field -->
    <label for="amount">Amount:</label>
    <input type="number" name="amount" id="amount" value="<?= htmlspecialchars($row['amount']); ?>" readonly>

    <!-- Date Field -->
    <label for="date">Date of Request:</label>
    <input type="date" name="date" id="date" value="<?= date('Y-m-d', strtotime($row['date'])); ?>" required>

    <div class="button-container">
        <a href="javascript:history.back()" class="btn">Back</a>
        <button type="submit" class="update-btn">Update</button>
    </div>
</form>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let docsType = document.getElementById('certificate_type').value;
    let selectedPurpose = document.getElementById('selectedPurpose').value;

    // ✅ Populate "Purpose" dropdown on load
    if (docsType) {
        fetch('get_purpose.php?docs_type=' + docsType)
            .then(response => response.text())
            .then(data => {
                document.getElementById('purpose').innerHTML = data;

                // ✅ Set the selected purpose
                let matchedOption = Array.from(document.getElementById('purpose').options).find(option => 
                    option.value.trim().toLowerCase() === selectedPurpose.trim().toLowerCase()
                );

                if (matchedOption) {
                    matchedOption.selected = true;
                }
            });
    }

    // ✅ Populate "Amount" on load
    fetch('get_fee.php?docs_type=' + docsType)
        .then(response => response.text())
        .then(data => {
            document.getElementById('amount').value = data;
        });

    // ✅ Ensure Year Stayed is correctly populated
    document.getElementById('year_stay').value = "<?= htmlspecialchars($row['year_stay_in_brgy']); ?>";
});

// ✅ Auto-fetch Purpose & Fee on change
document.getElementById('certificate_type').addEventListener('change', function() {
    let docsType = this.value;
    document.getElementById('amount').value = '';
    document.getElementById('purpose').innerHTML = '<option value="">Select Purpose</option>';

    if (docsType) {
        fetch('get_purpose.php?docs_type=' + docsType)
            .then(response => response.text())
            .then(data => document.getElementById('purpose').innerHTML = data);

        fetch('get_fee.php?docs_type=' + docsType)
            .then(response => response.text())
            .then(data => document.getElementById('amount').value = data);
    }
});



// Inactivity Timer - Logout after 5 minutes of inactivity
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


<style>
/* ✅ FORM CONTAINER - Fully Responsive */
.Clearance-form {
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

/* ✅ Input Fields */
.Clearance-form label {
    font-weight: bold;
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
}

.Clearance-form input, 
.Clearance-form select {
    width: 100%; /* Parehong lapad */
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-bottom: 10px;
    text-transform: capitalize;
    box-sizing: border-box;
}

/* ✅ Button Container */
.button-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    gap: 10px;
}

/* ✅ Buttons */
.button-container a,
.button-container button {
    flex: 1;
    max-width: 110px;
    height: 38px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    font-size: 13px;
    font-weight: bold;
    border-radius: 5px;
    transition: background-color 0.3s, transform 0.2s;
}

.btn {
    background-color: #34495e;
    color: white;
    text-decoration: none;
}

.btn:hover {
    background-color: #1abc9c;
    transform: scale(1.05);
}

.update-btn {
    background-color: #1abc9c;
    color: white;
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
    .Clearance-form {
        width: 50%;
    }

    h1 {
        font-size: 20px;
    }
}

@media (max-width: 768px) {
    .Clearance-form {
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
    .Clearance-form {
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
