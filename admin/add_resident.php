<?php
session_start();
include '../connection/connect.php';

// Kunin ang session username
$username = $_SESSION['username'];

// Query para kunin ang `session_active`
$query = "SELECT session_active FROM admin WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($session_active);
$stmt->fetch();
$stmt->close();

// Kung inactive ang session, mag-log out
if ($session_active == 0) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resident</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <?php include 'sidebar.php'; ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-container {
            width: 100%;
            max-width: 500px;
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
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: left;
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            text-transform: capitalize;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-save {
            background: #1abc9c;
            color: white;
        }
        .btn-save:hover {
            background: #16a085;
        }
        .btn-cancel {
            background: #e74c3c;
            color: white;
        }
        .btn-cancel:hover {
            background: #c0392b;
        }
        /* Close Button (X) */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            color: red;
            cursor: pointer;
        }
        .close-btn:hover {
            color: darkred;
        }
    </style>
</head>
<body>

<div class="content-wrapper">
    <div class="form-container">
        <span class="close-btn" onclick="window.location.href='resident_list.php'">&times;</span>
        <h2>Add Resident</h2>
        <form action="add_resident_process.php" method="POST">
            <div class="input-group">
                <label>Complete Address</label>
                <input type="text" name="complete_address" required>
            </div>
            <div class="input-group">
                <label>Name of Family Member</label>
                <input type="text" name="name_of_family_member" required>
            </div>
            <div class="input-group">
                <label>Position in the Family</label>
                <select name="position_in_the_family" required>
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Son">Son</option>
                    <option value="Daughter">Daughter</option>
                    <option value="Grand Daughter">Grand Daughter</option>
                    <option value="Grand Son">Grand Son</option>
                    <option value="Daughter in-law">Daughter in-law</option>
                    <option value="Son in-law">Son in-law</option>
                </select>
            </div>
            <div class="input-group">
                <label>Age</label>
                <input type="number" name="age" required>
            </div>
            <div class="input-group">
                <label>Civil Status</label>
                <select name="civil_status" required>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Widowed">Widowed</option>
                </select>
            </div>
            <div class="input-group">
                <label>Occupation/Source of Income</label>
                <input type="text" name="occupation_source_of_income" required>
            </div>
            <div class="input-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number">
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-save">Save Resident</button>
                <button type="button" class="btn btn-cancel" onclick="window.location.href='resident_list.php'">Cancel</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
