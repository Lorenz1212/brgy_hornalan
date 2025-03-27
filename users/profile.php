<?php
session_start();
include '../connection/connect.php';

// ✅ Check kung naka-login ang user
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Unauthorized access! Please log in first.'); window.location.href='user.php';</script>";
    exit();
}

// ✅ Kunin ang user data mula sa database
$user = $_SESSION['user'];
$query = "SELECT lastname, firstname, middlename, contact, user, password, address, birthday, profile_pic FROM users WHERE user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($lastname, $firstname, $middlename, $contact, $username, $password, $address, $birthday, $profile_pic);
$stmt->fetch();
$stmt->close();

// ✅ Default profile picture kung walang naka-upload
$profilePic = !empty($profile_pic) ? "../uploads/" . $profile_pic : "uploads/default.png";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Lightbox CSS -->
<link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">

<!-- Lightbox JS -->
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>

    <!-- ✅ Sidebar -->
    <?php include 'sidebar.php'; ?>

    <style>
        body {
            background: #f4f4f4;
            margin: 0;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding-left: 260px;
            padding-right: 20px;
        }

        .profile-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }

       /* ✅ Profile Picture Styles */
.profile-pic-container {
    position: relative;
    width: 120px; /* Increased the width */
    height: 120px; /* Increased the height */
    margin: 0 auto 12px; /* Adjusted margin for spacing */
}

.profile-pic-container img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #16a085; /* Slightly thicker border */
    cursor: pointer;
}

.profile-pic-container input {
    display: none;
}

.profile-pic-container label {
    position: absolute;
    bottom: 5px; /* Adjusted position to make it a bit higher */
    right: 5px; /* Adjusted position to make it a bit higher */
    background: #16a085;
    color: white;
    padding: 8px; /* Increased padding for a larger button */
    border-radius: 50%;
    cursor: pointer;
    font-size: 14px; /* Increased font size for better visibility */
}

.profile-pic-container label i {
    font-size: 14px; /* Increased camera icon size */
}




        .profile-info {
            text-align: left;
            margin-bottom: -5px;
        }

        .profile-info label {
            font-weight: bold;
            display: block;
            color: #555;
            font-size: 16px;
        }

        .profile-info p {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            text-transform: capitalize;
        }

        .btn-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn {
            padding: 10px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        .edit-btn {
            background: #3498db;
            color: white;
        }

        .edit-btn:hover {
            background: #2980b9;
        }

        /* ✅ Close Button */
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

        .close-btn:hover {
            color: #c0392b;
        }

                /* ✅ Media Queries for Responsiveness */
                @media (max-width: 1024px) {
            .container {
                padding-left: 0; /* ✅ Remove space for sidebar on tablet and smaller devices */
                max-width: 80%;
                margin-left: 35px;
            }

            .profile-container {
                max-width: 80%;
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                max-width: 80%;
                padding: 20px;
            }

            .btn-container {
                flex-direction: column;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding-left: 0;
                padding-right: 10px;
                max-width: 80%;
                margin-left: 35px;
                
            }

            .profile-container {
                max-width: 100%;
                margin-left: 0;
                margin-bottom: 60px;
            }

            .profile-info p {
                font-size: 14px; /* ✅ Smaller text on mobile */
            }

            .btn {
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>



<div class="container">
    <div class="profile-container">
        <button class="close-btn" onclick="window.location.href='prof.php'"><i class="fas fa-times"></i></button>
        <h2>User Profile</h2>

        <!-- ✅ Profile Picture -->
        <div class="profile-pic-container">
            <!-- Use Lightbox for profile picture -->
            <a href="<?php echo $profilePic; ?>" data-lightbox="profile" data-title="Profile Picture">
                <img id="profilePic" src="<?php echo $profilePic; ?>" alt="Profile Picture">
            </a>
            <input type="file" id="profilePicInput" accept="image/*">
            <label for="profilePicInput"><i class="fa fa-camera"></i></label>
        </div>



        <div class="profile-info">
            <label>Last Name:</label>
            <p><?php echo htmlspecialchars($lastname); ?></p>
        </div>

        <div class="profile-info">
            <label>First Name:</label>
            <p><?php echo htmlspecialchars($firstname); ?></p>
        </div>

        <div class="profile-info">
            <label>Middle Name:</label>
            <p><?php echo htmlspecialchars($middlename); ?></p>
        </div>

        <div class="profile-info">
            <label>Address:</label>
            <p><?php echo htmlspecialchars($address); ?></p>
        </div>

        <div class="profile-info">
            <label>Birthday:</label>
            <p><?php echo date("F d, Y", strtotime($birthday)); ?></p>
        </div>

        <div class="profile-info">
            <label>Contact:</label>
            <p><?php echo htmlspecialchars($contact); ?></p>
        </div>

        <div class="btn-container">
            <button class="btn edit-btn" onclick="window.location.href='edit_profile.php'"><i class="fa fa-edit"></i> Edit</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
    $("#profilePicInput").change(function () {
        var formData = new FormData();
        formData.append("profilePic", $("#profilePicInput")[0].files[0]);

        $.ajax({
            url: "upload_profile_pic.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                console.log("Response from server:", response);

                if (response.status === "success") {
                    $("#profilePic").attr("src", response.newPath);
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated!',
                        text: 'Your profile picture has been updated successfully.',
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed!',
                        text: response.message,
                        timer: 3000
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log("AJAX Error:", xhr.responseText);
            }
        });
    });
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

</body>
</html>
