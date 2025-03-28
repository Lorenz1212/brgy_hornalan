<?php
session_start(); // Store Step 2 data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['birthday'] = $_POST['birthday'];
    $_SESSION['address'] = $_POST['address'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Step 2</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <form id="signupForm" action="signup_process.php" method="post" class="fade-in">
        <!-- Close Button -->
<button class="close-btn" onclick="window.location.href='index.php'">✖</button>

    
    <label for="user">Username:</label>
        <input type="email" id="user" name="user" placeholder="example@gmail.com" required autocomplete="off" onkeyup="checkUsername()">
        <span id="usernameMessage" style="color: red; font-size: 14px;"></span><br>

        <label for="password">Password:</label>
        <div class="password-container">
            <input type="password" id="password" name="password"  required>
            <i class="fa-solid fa-eye toggle-password"
            onmousedown="holdPassword('password', this)" 
            onmouseup="releasePassword('password', this)" 
            onmouseleave="releasePassword('password', this)"></i>
        </div>

        <label for="confirm_password">Confirm Password:</label>
        <div class="password-container">
            <input type="password" id="confirm_password" name="confirm_password" required>
            <i class="fa-solid fa-eye toggle-password"
            onmousedown="holdPassword('confirm_password', this)" 
            onmouseup="releasePassword('confirm_password', this)" 
            onmouseleave="releasePassword('confirm_password', this)"></i>
        </div>

        <button type="submit" id="submitBtn">Sign Up</button>
    </form>

    <script>
        function holdPassword(fieldId, icon) {
            let field = document.getElementById(fieldId);
            field.type = "text"; // Gawing text habang hawak
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        }

        function releasePassword(fieldId, icon) {
            let field = document.getElementById(fieldId);
            field.type = "password"; // Babalik sa password mode kapag binitiwan
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }

        function checkUsername() {
            let username = document.getElementById("user").value;
            let messageSpan = document.getElementById("usernameMessage");
            let submitBtn = document.getElementById("submitBtn");

            if (username.length === 0) {
                messageSpan.innerText = "";
                submitBtn.disabled = true;
                return;
            }

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "checkuser_exist.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.responseText.trim() === "exists") {
                        messageSpan.innerText = "⚠ Username already exists. Please choose another.";
                        messageSpan.style.color = "red";
                        submitBtn.disabled = true;
                    } else {
                        messageSpan.innerText = "";
                        submitBtn.disabled = false;
                    }
                }
            };
            xhr.send("user=" + encodeURIComponent(username));
        }
        
        document.addEventListener("DOMContentLoaded", function () {
        let urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('signup_success')) {
            document.querySelector(".indext-container").classList.add("fade-in");
        }
    });


    document.getElementById("signupForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Pigilan ang default form submission

    let email = document.getElementById("user").value.trim();
    let emailPattern = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirm_password").value;
    let form = this;
    let formData = new FormData(form);

    // ✅ Password Regex: 8+ characters, may uppercase, lowercase, number, at special character
    let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!emailPattern.test(email)) {
        Swal.fire({
            icon: "error",
            title: "Invalid Email",
            text: "Use a valid Gmail address.",
            iconHtml: '<i class="fa fa-envelope"></i>',
            customClass: {
                popup: 'small-popup',
                title: 'small-title',
                content: 'small-content'
            }
        });
        return;
    }

    if (!passwordPattern.test(password)) {
        Swal.fire({
            icon: "error",
            title: "Weak Password!",
            text: "Password must be at least 8 characters long, and include uppercase, lowercase, number, and special character.",
            confirmButtonColor: "#d33",
            iconHtml: '<i class="fa fa-lock"></i>',
            customClass: {
                popup: 'small-popup',
                title: 'small-title',
                content: 'small-content'
            }
        });
        return;
    }

    if (password !== confirmPassword) {
        Swal.fire({
            icon: "error",
            title: "Passwords do not match!",
            confirmButtonColor: "#d33",
            iconHtml: '<i class="fa fa-lock"></i>',
            customClass: {
                popup: 'small-popup',
                title: 'small-title',
                content: 'small-content'
            }
        });
        return;
    }

    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.text())
    .then(responseText => {
        if (responseText.includes("Signup Successful")) {
            Swal.fire({
                title: 'Success!',
                text: 'Your account has been created successfully!',
                icon: 'success',
                confirmButtonColor: '#16a085',
                width: "300px",
                padding: "10px",
                customClass: {
                    popup: 'small-popup',  
                    title: 'small-title',
                    content: 'small-content'
                }
            }).then(() => {
                window.location.href = "user.php";
            });

        } else if (responseText.includes("Username already exists")) {
            Swal.fire({
                icon: "error",
                title: "Username Taken!",
                text: "Please choose another username.",
                confirmButtonColor: "#d33",
                width: "300px",
                padding: "10px",
                customClass: {
                    popup: 'small-popup',
                    title: 'small-title',
                    content: 'small-content'
                }
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Signup Failed!",
                text: responseText,
                confirmButtonColor: "#d33",
                width: "300px",
                padding: "10px",
                customClass: {
                    popup: 'small-popup',
                    title: 'small-title',
                    content: 'small-content'
                }
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Something went wrong. Please try again.",
            confirmButtonColor: "#d33",
            width: "300px",
            padding: "10px",
            customClass: {
                popup: 'small-popup',
                title: 'small-title',
                content: 'small-content'
            }
        });
    });
});

</script>

</body>
<footer class="copyright">
    &copy; 2025 Barangay Information System. All Rights Reserved.
</footer>



</html>

<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: url("../image/bg.png") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        form {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
        }

        label {
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
            background: #f9f9f9;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #555;
        }

        /*Button */
        .btn-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 15px;
        }

        button {
            flex: 1;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button[type="button"] {
            background: #e74c3c;
            color: white;
            
        }

        button[type="submit"] {
            background: #34495e;
            color: white;
            margin-top: 10px;
        }
        
        /* ✅ Fade-in effect para sa signup form */
        .fade-in {
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeIn 0.5s ease-out forwards;
        }

        /* ✅ Properly Position Close Button */
        .close-btn {
            position: absolute;
            top: -5px;
            right: 5px;
            background: none;
            border: none;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            color: #e74c3c;
            transition: transform 0.2s ease-in-out, color 0.2s ease-in-out;
        }

        /* ✅ Smooth Hover Effect */
        .close-btn:hover {
            color: #c0392b;
            transform: scale(1.1);
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

        /*Popup Message */
        .small-popup {
            font-size: 14px !important; /* ✅ Mas maliit na font */
        }

        .small-title {
            font-size: 16px !important; /* ✅ Mas maliit na title */
            
        }

        .small-content {
            font-size: 12px !important; /* ✅ Mas compact na text */
            
        }
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
