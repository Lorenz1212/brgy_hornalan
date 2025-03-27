<?php
session_start();
include '../connection/connect.php';

// ✅ Check kung naka-login ang user
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='user.php';</script>";
    exit();
}

// ✅ Kunin ang kasalukuyang user data
$user = $_SESSION['user'];
$query = "SELECT lastname, firstname, middlename, contact, address, birthday FROM users WHERE user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($lastname, $firstname, $middlename, $contact, $address, $birthday);
$stmt->fetch();
$stmt->close();

// ✅ Kung may form submission (update profile)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_lastname = ucwords(strtolower(trim($_POST['lastname'])));
    $new_firstname = ucwords(strtolower(trim($_POST['firstname'])));
    $new_middlename = ucwords(strtolower(trim($_POST['middlename'])));
    $new_contact = trim($_POST['contact']);
    $new_address = ucwords(strtolower(trim($_POST['address'])));
    $new_birthday = date('Y-m-d', strtotime($_POST['birthday']));

    // ✅ Regular expression para sa validation ng mga special characters, allowing "N/A" as a valid input
        $pattern = "/^[a-zA-Z0-9\s,.]+$/"; // ✅ Tanging letters, numbers, space, comma, at period lang ang pwede
        $na_pattern = "/^N\/A$/i"; // ✅ Tumatanggap ng "N/A" na may case-insensitive matching

        if (!preg_match($pattern, $new_lastname) || 
            !preg_match($pattern, $new_firstname) || 
            (!preg_match($pattern, $new_middlename) && !preg_match($na_pattern, $new_middlename))
        ) {
            echo "<script>
                alert('❌ Invalid characters detected! Please remove special characters or use N/A for middle name.');
                window.history.back();
            </script>";
            exit();
        }

    // ✅ I-update ang profile sa database
    $update_query = "UPDATE users SET lastname = ?, firstname = ?, middlename = ?, contact = ?, address = ?, birthday = ? WHERE user = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssss", $new_lastname, $new_firstname, $new_middlename, $new_contact, $new_address, $new_birthday, $user);

    if ($stmt->execute()) {
        echo "<script>
            alert('Profile updated successfully!');
            window.location.href = 'profile.php';
        </script>";
    } else {
        echo "<script>alert('❌ Error updating profile!');</script>";
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar.css">
    


    <style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    overflow: hidden;
}

.edit-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding-left: 260px; /* Space for sidebar */
    padding-right: 20px; /* Added padding for smaller screens */
}

.edit-profile {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 400px;
    text-align: center;
    position: relative;
    margin-left: -10%;
}

.input-group {
    text-align: left;
    margin-bottom: 15px;
}

input[type="text"], input[type="date"] {
    width: 95%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    margin-top: 5px;
    background-color: white;
    color: #333;
    appearance: none; /* Para mawala ang default styles sa ibang browsers */
    text-transform: capitalize;
}

input[type="date"]:focus {
    border-color: #16a085;
    outline: none;
}


label {
    font-weight: bold;
    color: #555;
}

/* Button Container */
.btn-container {
    display: flex;
    justify-content: center; /* Center the buttons */
    gap: 15px; /* Adjust the gap to a smaller value */
    margin-top: 20px;
}

/* Button Styles */
.btn {
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    text-align: center;
    width: auto; /* Buttons take their natural width */
}

/* Save Button */
.save-btn {
    background-color: #16a085;
    color: white;
}

.save-btn:hover {
    background-color: #1abc9c;
}

/* Cancel Button */
.cancel-btn {
    background-color: #e74c3c;
    color: white;
}

.cancel-btn:hover {
    background-color: #c0392b;
}


.close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #e74c3c;
}

/* Media Queries for Responsiveness */
@media (max-width: 1024px) {
    .edit-container {
        padding-left: 0; /* Remove space for sidebar on tablet and smaller devices */
        max-width: 80%;
        margin-left: 35px;
    }

    .edit-profile {
        max-width: 80%;
        margin-left: 0;
    }

    .btn-container {
        flex-direction: column;
        gap: 5px;
    }
}

@media (max-width: 768px) {
    .edit-profile {
        padding: 20px;
    }

    input[type="text"] {
        font-size: 14px;
        padding: 8px;
    }

    .btn {
        font-size: 14px;
        padding: 12px;
    }

    .btn-container {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .edit-container {
        padding: 10px;
        max-width: 100%;
        margin-left: 0;
    }

    .edit-profile {
        max-width: 100%;
    }

    input[type="text"] {
        font-size: 14px;
        padding: 8px;
    }

    .btn {
        font-size: 14px;
        padding: 12px;
    }

    .close-btn {
        font-size: 20px;
        top: 15px;
        right: 15px;
    }
}

    </style>
</head>
<body>

<!-- ✅ Sidebar -->
<?php include 'sidebar.php'; ?>

<div class="edit-container">
    <div class="edit-profile">
    <button class="close-btn" onclick="window.location.href='profile.php'">
        <i class="fas fa-times"></i>
    </button>

    <h2>Edit Profile</h2>
    <form method="POST">
        <div class="input-group">
            <label>Last Name:</label>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required autocomplete="off">
        </div>
        <div class="input-group">
            <label>First Name:</label>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required autocomplete="off">
        </div>
        <div class="input-group">
            <label>Middle Name:</label>
            <input type="text" name="middlename" value="<?php echo htmlspecialchars($middlename); ?>" required autocomplete="off">
        </div>
        <div class="input-group">
            <label for="address">Address:</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" required autocomplete="off">
                <small style="color: red;">
                    Note: Please enter only **Block, Lot, Street, Purok, and Subdivision (if applicable).**  
                    *Do not include Barangay, City, or Province.*
                </small>
        </div>
        <div class="input-group">
            <label>Birthday:</label>
            <input type="date" name="birthday" value="<?php echo htmlspecialchars($birthday); ?>" required autocomplete="off">
        </div>
        <div class="input-group">
            <label>Contact:</label>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($contact); ?>" required autocomplete="off">
        </div>
        <div class="btn-container">
            <button type="submit" class="btn save-btn"><i class="fa fa-save"></i> Save</button>
            <button type="button" class="btn cancel-btn" onclick="window.location.href='profile.php'"><i class="fa fa-times"></i> Cancel</button>
        </div>
    </form>

    </div>
    
</div>

<script>

document.addEventListener("DOMContentLoaded", function () {
        // ✅ Restrict future dates in birthday field
        let birthdayField = document.querySelector("input[name='birthday']");
        if (birthdayField) {
            let today = new Date().toISOString().split("T")[0];
            birthdayField.setAttribute("max", today);
        }

        // ✅ Address validation
        let addressInput = document.querySelector("input[name='address']");
        if (addressInput) {
            let restrictedWords = ["barangay", "brgy", "calamba", "laguna"];
            let alertShown = {};

            addressInput.addEventListener("input", function () {
                let address = addressInput.value.toLowerCase();

                restrictedWords.forEach(word => {
                    if (address.includes(word) && !alertShown[word]) {
                        alertShown[word] = true; // Mark as shown
                        Swal.fire({
                            icon: "warning",
                            title: "Invalid Format",
                            text: "Do not include '" + word.charAt(0).toUpperCase() + word.slice(1) + "'.",
                            confirmButtonColor: "#d33"
                        }).then(() => {
                            addressInput.value = address.replace(word, "").trim(); // Remove restricted word
                            alertShown[word] = false; // Reset alert flag
                        });
                    }
                });
            });
        }
    });

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

        
</script>
</body>
</html>
