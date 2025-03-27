<?php
session_start(); // Start the session
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>

    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: Arial, sans-serif;
            background: url("../image/bg.png") no-repeat center center fixed;
            background-size: cover; /* Fullscreen background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Ginawang flexible */
            padding: 20px; /* Para may space sa gilid */
        }
        /* ✅ Dark Overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); /* Darkens the background (adjust the opacity to make it darker or lighter) */
            z-index: -1; /* Keeps the overlay behind the content */
        }

        /* Signup Form Container */
        form {
            background-color: rgba(255, 255, 255, 0.9); /* Light transparency */
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Label at Input Fields */
        label {
            align-self: flex-start;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
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

        button:hover {
            background-color: #16a085;
        }

        /* Login Link */
        .login-link {
            text-align: center;
            font-size: 15px;
            margin-top: 15px;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .login-link a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        /* ✅ Full Responsive Design */
        @media (max-width: 1024px) {
            body {
                padding: 10px;
                background-size: cover;
            }
            form {
                width: 90%;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 5px;
                background-size: cover;
                justify-content: flex-start; /* Para di masyadong dikit */
            }
            form {
                width: 95%;
                padding: 18px;
            }
        }

        @media (max-width: 480px) {
            body {
                flex-direction: column;
                justify-content: center;
                padding: 10px;
            }
            form {
                width: 100%;
                padding: 15px;
                max-width: 400px;
            }
            input {
                font-size: 14px;
                padding: 10px;
            }
            button {
                font-size: 14px;
                padding: 10px;
            }
        }

        /* ✅ Fade-in effect para sa signup form */
        .fade-in {
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>
</head>
<body>
        <form action="signup_2.php" method="post" class="fade-in">

        <label for="lastname">Last Name:</label>
        <input type="text" id="lastname" name="lastname" required autocomplete="off" autocorrect="off" spellcheck="false">

        <label for="firstname">First Name:</label>
        <input type="text" id="firstname" name="firstname" required autocomplete="off" autocorrect="off" spellcheck="false">

        <label for="middlename">Middle Name:</label>
        <input type="text" id="middlename" name="middlename" required autocomplete="off" autocorrect="off" spellcheck="false">

        <label for="contact">Contact Number:</label>
        <input type="tel" id="contact" name="contact" pattern="[0-9]{11}" placeholder="09XXXXXXXXX" required autocomplete="off" autocorrect="off" spellcheck="false">
        
        <div class="login-link">
            <p>Already have an account? <a href="user.php">Log in here</a></p>
        </div>

        <button type="submit">Next</button>
    </form>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelector("form").classList.add("fade-in");

        // ✅ Kunin ang mga input fields
        let contactInput = document.getElementById("contact");

        // ✅ Regular expressions para sa validation
        let contactPattern = /^09\d{9}$/; // ✅ 11 digits, must start with 09

        // ✅ Function para sa contact validation gamit ang SweetAlert2
        function validateContact() {
            let value = contactInput.value.trim();
            if (!contactPattern.test(value)) {
                Swal.fire({
                    icon: "error",
                    title: "Invalid Contact Number!",
                    text: "The contact number must be **11 digits** and start with **'09'**.",
                    confirmButtonColor: "#d33"
                });
                contactInput.value = "";
            }
        }

        // ✅ Event listener para sa contact validation
        contactInput.addEventListener("blur", validateContact);
    });
</script>

