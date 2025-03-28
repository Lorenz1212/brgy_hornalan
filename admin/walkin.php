<?php
session_start();
include '../connection/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='index.php';</script>";
    exit();
}

$username = $_SESSION['username'];

// Check if session is active
$query = "SELECT session_active FROM admin WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($session_active);
$stmt->fetch();
$stmt->close();

if ($session_active == 0) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-in Request</title>
    <link rel="stylesheet" href="sidebar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'sidebar.php'; ?>
</head>
<body>

<div class="form-container">
    <button id="closeForm" class="close-btn">&times;</button>
    <h1>Walk-in Request Form</h1>
    <form id="walkinForm" action="walkin_display.php" method="POST">
    <div class="form-grid">
        <div>
            <label for="docs_type">Document Type:</label>
            <select id="docs_type" name="type" required>
                <option value="">Select Document</option>
                <option value="Barangay Clearance">Barangay Clearance</option>
                <option value="Business Clearance">Business Clearance</option>
                <option value="Indigency">Indigency</option>
                <option value="Barangay Certificate">Barangay Certificate</option>
                <option value="Residency">Residency</option>
                <option value="Cedula">Cedula</option>
                <option value="First Time Job Seeker Certificate">First Time Job Seeker Certificate</option>
            </select>
        </div>
        <div>
            <label for="name">Full Name:</label>
            <input type="text" name="name" placeholder="Juan Dela Cruz" required>
        </div>
        <div>
            <label for="address">Address:</label>
            <input type="text" name="address" placeholder="Block, Lot, Street, Purok, Subdivision" required>
            <small style="color: red;">
                Note: Please enter only **Block, Lot, Street, Purok, and Subdivision (if applicable).**  
                *Do not include Barangay, City, or Province.*
            </small>
        </div>
        <div>
            <label for="birthday">Birthday:</label>
            <input type="date" name="birthday" required>
        </div>
        <div>
            <label for="year_stay_in_brgy">Years Stayed in Barangay:</label>
            <input type="number" name="year_stay_in_brgy" min="1" placeholder="e.g. 5" required>
            <small style="color: red;">* If less than 1 year, enter 1.</small>
        </div>
        <div>
            <label for="contact">Contact Number:</label>
            <input type="text" name="contact" id="contact" maxlength="11" placeholder="09XXXXXXXXX" required>
        </div>
        <div>
            <label for="purpose">Purpose:</label>
            <select id="purpose" name="purpose" required>
                <option value="">Select Purpose</option>
            </select>
        </div>
        <div>
            <label for="amount">Amount:</label>
            <input type="number" name="amount" id="amount" readonly>
        </div>
    </div>
    <button type="submit" id="submitBtn">Proceed to Print</button>
</form>

</div>

<script>
    document.getElementById('docs_type').addEventListener('change', function() {
        var docsType = this.value;
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
    document.addEventListener("DOMContentLoaded", function () {
        let today = new Date().toISOString().split("T")[0]; 
        let birthdayInput = document.querySelector("input[name='birthday']");
        birthdayInput.setAttribute("max", today);
    });

    document.getElementById("contact").addEventListener("input", function() {
        let contact = this.value.replace(/\D/g, '');
        this.value = contact;
        if (contact.length > 11) {
            this.value = contact.substring(0, 11);
        }
    });

    document.getElementById("walkinForm").addEventListener("submit", function(event) {
        let contact = document.getElementById("contact").value;
        if (contact.length < 11) {
            event.preventDefault();
            Swal.fire({
                icon: "warning",
                title: "Invalid Contact Number",
                text: "Contact number must be exactly 11 digits.",
                confirmButtonColor: "#d33"
            });
        }
    });
</script>

<style>
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
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    .form-grid div {
        display: flex;
        flex-direction: column;
    }
    input, select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-top: 10px;
        text-transform: capitalize;
    }
    #submitBtn {
        width: 200px;
        padding: 10px;
        background-color: #34495e;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 15px;
    }

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
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    .form-grid div {
        display: flex;
        flex-direction: column;
    }
    input, select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-top: 10px;
        text-transform: capitalize;
    }
    #submitBtn {
        width: 200px;
        padding: 10px;
        background-color: #34495e;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 15px;
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
 div label{
    text-align: left;
    font-weight: bold;
 }
 
 small{
    text-align: left;
 }
</style>