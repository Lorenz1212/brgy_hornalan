<?php
session_start();
include '../connection/connect.php';

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='users.php';</script>";
    exit();
}

// Cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Get clearance ID from URL
$clearanceID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$clearanceID || !is_numeric($clearanceID)) {
    die("<p style='color:red;'>Invalid request. Missing or invalid clearance ID.</p>");
}

// Kunin ang clearance details
$query = "SELECT * FROM clearance WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $clearanceID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $formatted_birthday = date('m/d/Y', strtotime($row['birthday']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Request Details</title>
    <link rel="stylesheet" type="text/css" href="../users/css/sidebar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include '../users/sidebar.php'; ?>

<div class="form-container">
    <!-- X BUTTON -->
    <button id="closeForm" class="close-btn">&times;</button>
    <h1>Clearance Request Details</h1>
    <p class="note"><strong>Note:</strong> Please check your information carefully before clicking "Done."</p> <!-- ✅ Added Note -->
    <div class="record">
        <p><strong>Name:</strong> 
            <?php echo isset($row['name']) ? htmlspecialchars(ucwords(strtolower(trim($row['name'])))) : ''; ?>
        </p>
        <p><strong>Clearance Type:</strong> <?php echo htmlspecialchars($row['type']); ?></p>
        <p><strong>Address:</strong> 
            <?php echo isset($row['address']) ? htmlspecialchars(ucwords(strtolower(trim($row['address'])))) : ''; ?>
        </p>
        <p><strong>Birthday:</strong> <?php echo htmlspecialchars($formatted_birthday); ?></p>
        <p><strong>Years Stayed:</strong> <?php echo htmlspecialchars($row['year_stay_in_brgy']); ?></p>
        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($row['purpose']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars(date('m/d/Y', strtotime($row['date']))); ?></p>
        <p><strong>Amount:</strong> ₱<?php echo number_format($row['amount'], 2); ?></p>
    </div>

    <div class="button-container">
        <a href="../users/edit_clearance.php?id=<?php echo $row['id']; ?>" class="btn">Edit</a>
        <button class="btn done-btn" onclick="showPaymentNotice()">Done</button>
    </div>
</div>

<!-- ✅ Loading Spinner -->
<div id="loadingSpinner" class="spinner-container">
    <div class="spinner"></div>
    <p>Please wait...</p>
</div>

<script>
$(document).ready(function () {
    $("#loadingSpinner").hide(); // ✅ Siguraduhing nakatago ang spinner sa simula
});

function showPaymentNotice() {
    Swal.fire({
        title: 'Payment Notice',
        text: "Kindly pay ₱<?php echo number_format($row['amount'], 2); ?> at the barangay.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Proceed',
        cancelButtonText: 'Cancel',
        backdrop: 'none',
        customClass: {
            popup: 'custom-swal-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showLoadingSpinner();
        }
    });
}

function showLoadingSpinner() {
    $("#loadingSpinner").fadeIn(300); // ✅ Lalabas lang kapag pinindot ang "Proceed"
    setTimeout(() => {
        window.location.href = "../users/done.php?id=<?php echo $clearanceID; ?>";
    }, 2000);
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

               // ✅ Function para i-redirect sa prof.php kapag pinindot ang X button
document.getElementById("closeForm").addEventListener("click", function() {
    window.location.href = "prof.php"; // Redirect sa prof.php
});
</script>

</body>
</html>


!-- ✅ CSS Styles -->
<style>
/* ✅ FORM CONTAINER - Centered & Fully Responsive */
.form-container {
    width: 30%;
    max-width: 500px;
    padding: 20px;
    border-radius: 10px;
    background-color: #f4f4f4;
    box-shadow: 2px 2px 15px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    border: 2px solid #1abc9c;
    position: absolute;
    top: 50%;
    left: 55%;
    transform: translate(-50%, -50%);
}

/* ✅ Title */
h1 {
    color: #333;
    font-size: 25px;
    margin-bottom: 20px;
}

/* ✅ Record Box */
.record {
    background: #ffffff;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-align: left;
    text-transform: capitalize;
}

/* ✅ Text */
.record p {
    font-size: 16px;
    color: #333;
    margin: 8px 0;
}
.record p strong {
    color: #16a085;
}

/* ✅ Button Container */
.button-container {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

/* ✅ Buttons */
.btn {
    padding: 8px 16px;
    background-color: #34495e;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: background-color 0.3s, transform 0.2s;
}
.btn:hover {
    background-color: #16a085;
    transform: scale(1.05);
}
.done-btn {
    background-color: #16a085;
}
.done-btn:hover {
    background-color: #1abc9c;
}

/* ✅ Style for the note */
.note {
    color: #e74c3c;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
    text-align: center;
}

/* ✅ Centered Loading Spinner (Hidden sa Simula) */
.spinner-container {
    display: none; /* ✅ Siguraduhin na hindi lalabas agad */
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.8);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    color: white;
    z-index: 9999;

    /* 🔹 Gumamit ng flexbox para perfect center */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 150px;
    height: 150px;
}

/* ✅ Spinner Animation */
.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid white;
    border-top: 4px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ✅ Loading Text */
.spinner-container p {
    margin: 0;
    font-size: 16px;
    font-weight: bold;
}


/* ✅ Ibalik ang border ng SweetAlert2 modal */
.custom-swal-popup {
    border: 2px solid #1abc9c; /* ✅ Green border */
    border-radius: 10px; /* ✅ Para rounded */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* ✅ Para may shadow effect */
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

/* ✅ Responsive */
@media (max-width: 768px) {
    .form-container {
        width: 50%;
        max-width: 500px;
        top: 55%;
        left: 50%;
    }
    h1 { font-size: 22px; }
    .record p { font-size: 14px; }
    .button-container { flex-direction: row; gap: 15px; }
}
@media (max-width: 480px) {
    .form-container {
        width: 90%;
        max-width: 350px;
    }
    h1 { font-size: 18px; }
    .button-container { flex-direction: row; gap: 10px; }
    .btn, .done-btn { font-size: 14px; max-width: 100px; }
}
</style>
<?php
} else {
    echo "<p>No clearance found for the provided ID.</p>";
}

$stmt->close();
$conn->close();
?>