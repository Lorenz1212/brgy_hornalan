<?php
session_start(); // Store Step 1 data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['lastname'] = $_POST['lastname'];
    $_SESSION['firstname'] = $_POST['firstname'];
    $_SESSION['middlename'] = $_POST['middlename'];
    $_SESSION['contact'] = $_POST['contact'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Step 2</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- ✅ SweetAlert2 -->
    <style>
        /* General Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* Body Styling */
        body {
            font-family: Arial, sans-serif;
            background: url("../image/bg.png") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Dark Overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        /* ✅ Ensure form-container has relative positioning */
        .form-container {
            position: relative; /* Parent element for absolute positioning */
            background-color: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ✅ Proper Close Button (X) Position - No Border, No Background */
        .close-btn {
            position: absolute;
            top: -20px;
            left: 45%;
            background: none;   /* ❌ No background */
            border: none;       /* ❌ No border */
            outline: none;      /* ❌ No focus outline */
            box-shadow: none;   /* ❌ No box shadow */
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            color: #e74c3c;
            transition: 0.2s ease-in-out;
        }

        /* ✅ Hover Effect - Only Color Change */
        .close-btn:hover {
            color: #c0392b;
            transform: scale(1.1);
        }

        /* ✅ Remove focus outline when clicked */
        .close-btn:focus {
            outline: none;
            box-shadow: none;
        }



        /* Labels and Input Fields */
        label {
            align-self: flex-start;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
            text-transform: capitalize;
        }

        /* Button */
        button {
            width: 100%;
            padding: 12px;
            background-color: #34495e;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s ease;
            margin-top: 15px;
        }
        /* ✅ Fix for Button Hover Effect */
        button:hover {
            transform: scale(1.05);  /* ✅ Subtle size increase */
            opacity: 0.8;            /* ✅ Light fade effect */
            transition: transform 0.2s ease-in-out, opacity 0.2s ease-in-out;
        }


        /* Responsive Design */
        @media (max-width: 480px) {
            .form-container {
                width: 100%;
                padding: 15px;
                max-width: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container fade-in">
    <!-- ✅ X Button - Now Fully Fixed -->
    <button class="close-btn" onclick="window.location.href='user.php'" onfocus="this.blur()" tabindex="-1">✖</button>

    <form id="signupForm" action="authentication.php" method="post">
        <label for="birthday">Birthday:</label>
        <input type="date" name="birthday" id="birthday" required>

        <label for="address">Address:</label>
        <input type="text" name="address" id="address" placeholder="e.g., Blk 12 Lot 5, Purok 1, Greenfield Subd." required autocomplete="off"> 
        <small style="color: red;">
            Note: Please enter only **Block, Lot, Street, Purok, and Subdivision (if applicable).**  
            *Do not include Barangay, City, or Province.*
        </small>

        <button type="submit">Next</button>
    </form>
</div>


    <script>
document.addEventListener("DOMContentLoaded", function () {
    let addressInput = document.getElementById("address");

    // ✅ Updated Regex - Hindi na mahigpit sa commas
    let validAddressPattern = /^(Blk|Block)\s\d+(?:,\s?| )?(Lot\s\d+(?:,\s?| )?)?(Purok\s\d+(?:,\s?| )?)?[A-Za-z0-9\s]+$/i;

    // ✅ Restricted words na bawal sa address
    let restrictedWords = ["barangay", "brgy", "calamba", "laguna"];

    addressInput.addEventListener("input", function () {
        let address = addressInput.value.trim().toLowerCase();

        // ✅ Bawal isama ang barangay, calamba, laguna
        restrictedWords.forEach(word => {
            if (address.includes(word)) {
                Swal.fire({
                    icon: "warning",
                    title: "Invalid Format",
                    text: "Do not include '" + word.charAt(0).toUpperCase() + word.slice(1) + "' in the address.",
                    confirmButtonColor: "#d33"
                }).then(() => {
                    addressInput.value = address.replace(word, "").trim(); // Alisin ang restricted word
                });
            }
        });
    });

    // ✅ Final validation bago mag-submit ng form
    document.getElementById("signupForm").addEventListener("submit", function (event) {
        let address = addressInput.value.trim();

        if (!validAddressPattern.test(address)) {
            event.preventDefault(); // ❌ Prevent form submission
            Swal.fire({
                icon: "error",
                title: "Invalid Address Format",
                text: "Address must follow the correct format. Example: 'Blk 12, Lot 5, Purok 1, Greenfield Subd.'",
                confirmButtonColor: "#d33"
            });
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let birthdayInput = document.getElementById("birthday");
    let signupForm = document.getElementById("signupForm");

    // ✅ Kuhanin ang kasalukuyang petsa (TODAY) at tanggalin ang oras
    let today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // ✅ I-set ang max date para hindi makapag-input ng future date
    let maxDate = new Date(today);
    maxDate.setDate(today.getDate() - 1); // Kahapon ang max limit
    birthdayInput.setAttribute("max", maxDate.toISOString().split("T")[0]);

    // ✅ Final validation kapag mag-submit na ng form
    signupForm.addEventListener("submit", function (event) {
        let selectedDate = new Date(birthdayInput.value);
        let selectedYear = parseInt(birthdayInput.value.split("-")[0]); // Kunin ang year bilang number

        // ✅ Bawal ang future date at current date
        if (selectedDate >= today) {
            event.preventDefault(); // ❌ Prevent form submission
            Swal.fire({
                icon: "error",
                title: "Invalid Date",
                text: "Birthday must be a past date (not today or future).",
                confirmButtonColor: "#d33"
            });
            return;
        }

        // ✅ Siguraduhin na ang year ay may 4 digits at hindi bababa sa 1900
        if (selectedYear < 1900 || selectedYear > today.getFullYear()) {
            event.preventDefault(); // ❌ Prevent form submission
            Swal.fire({
                icon: "error",
                title: "Invalid Year Format",
                text: "Year must be between 1900 and " + today.getFullYear() + ".",
                confirmButtonColor: "#d33"
            });
        }
    });
});


    </script>
</body>

<footer class="copyright">
    &copy; 2025 Barangay Information System. All Rights Reserved.
</footer>

<style>
.copyright {
    position: absolute;
    bottom: 10px;
    width: auto;
    text-align: center;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8); /* ✅ Medyo light ang kulay para hindi distracting */
    font-weight: 500;
    background: rgba(0, 0, 0, 0.2); /* ✅ Light overlay */
    padding: 5px;
    border-radius: 5px;
}


</style>
</html>
