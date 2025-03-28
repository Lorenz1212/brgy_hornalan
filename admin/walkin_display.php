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

// ✅ INSERT WALK-IN REQUEST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = strtoupper($_POST['type']); // Uppercase lahat ng letters
    $name = ucwords(strtolower($_POST['name'])); // Capitalize bawat word
    $address = strtoupper($_POST['address']); // Uppercase lahat ng letters
    $birthday = $_POST['birthday'];
    $year_stay = $_POST['year_stay_in_brgy'];
    $purpose = ucwords(strtolower($_POST['purpose'])); // Capitalize bawat word
    $amount = $_POST['amount'];
    $contact = $_POST['contact']; // ✅ Contact number

    // ✅ Validation ng Contact Number (dapat 11 digits)
    if (!preg_match("/^09[0-9]{9}$/", $contact)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid Contact Number!',
                text: 'Please enter a valid 11-digit phone number starting with 09.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'walkin.php';
            });
        </script>";
        exit();
    }

    // ✅ Insert sa database
    $query = "INSERT INTO walkin (type, name, address, birthday, year_stay_in_brgy, purpose, amount, contact, date) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssisss", $type, $name, $address, $birthday, $year_stay, $purpose, $amount, $contact);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Database Error!',
                text: 'Failed to insert walk-in request. Please try again.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'walkin.php';
            });
        </script>";
        exit();
    }
    $stmt->close();
}

// ✅ Kunin ang latest inserted data
$query = "SELECT * FROM walkin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $last_id);
$stmt->execute();
$result = $stmt->get_result();
$walkin = $result->fetch_assoc();
$stmt->close();


// ✅ Check kung may nakuha bago gamitin
if (!$walkin) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'No Walk-in Request Found!',
            text: 'No data available to print.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'walkin.php';
        });
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-in Document Print</title>
    <link rel="stylesheet" type="text/css" href="print.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Modal Background */
        .modal {
            display: block;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Modal Content */
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fefefe;
            padding: 20px;
            border: 1px solid #888;
            width: 80vw;
            height: 70vh;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Close Button */
        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #aaa;
            font-weight: bold;
        }

        .close:hover {
            color: red;
        }

        /* Header Layout */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            margin-top: 20px;
        }

        .modal-header img {
            height: 100px;
            margin: 0 25px;
        }

        .head {
            text-align: center;
        }

        .head p {
            margin: 2px 0;
            font-size: 12px;
        }

        .head h3 {
            font-size: 18px;
            font-weight: bold;
        }

        /* Underline Divider */
        .underline {
            width: 100%;
            border-bottom: 2px solid black;
            margin: 15px 0;
        }

        /* Clearance Title */
        .contentbrgy {
            text-align: center;
            margin-bottom: 15px;
        }

        .contentbrgy h1 {
            font-size: 20px;
            font-weight: bold;
        }

        /* Content Body */
        .contents {
            text-align: left;
            padding: 30px;
            font-size: 14px;
            line-height: 1.7;
        }

        /* Footer Section */
        .punongbrgy {
            margin-top: 40px;
            text-align: left;
        }

        .punongbrgy p {
            margin: 0;
            padding: 5px 0;
            font-weight: bold;
        }

        .punongbrgy p:first-child {
            display: inline-block;
            width: 120px;
            border-bottom: 1px solid black;
            padding-top: 5px;
        }

        /* Print Button */
        #printBtn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: block;
            margin: 4% auto;
            width: 20%;
        }

        #printBtn:hover {
            background-color: #45a049;
        }

        /* Print View Adjustments */
/* Print View Adjustments */
@media print {
    .modal-content button,
    .close {
        display: none;

        
    }

    .modal {
        margin-top: 20px;
    }

    .modal-content {
        border: none;
        width: 100%;
        margin: 0;
        height: 100%;
    }
     /* ✅ Itago ang print button at close button */
     #printBtn, .close {
        display: none !important;
    }
}


@page {
    size: auto;
    margin: 0;
}


    </style>
</head>
<body>

<!-- Modal for Print -->
<div id="printModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closePrintModal()">&times;</span>
        <div class="modal-header">
            <img src="../image/logo1-removebg.png" alt="Logo" class="logo">
            <div class="head">
                <p>Republic of the Philippines</p>
                <p>Province of Laguna</p>
                <p>Municipality of Calamba</p>
                <h3>Barangay Hornalan</h3>
            </div>
            <img src="../image/logo.png" alt="Logo" class="logo">
        </div>
        <div class="underline"></div>
        <div class="contentbrgy">
            <h1><?php echo strtoupper($walkin['type']); ?></h1>
        </div>
        <div class="contents">
            <h2>To Whom It May Concern:</h2>
            <p>This is to certify that <b><?php echo ucwords(strtolower($walkin['name'])); ?></b>,
                a resident of <b><?php echo $walkin['address']; ?></b>, born on <b><?php echo date("F d, Y", strtotime($walkin['birthday'])); ?></b>,
                and has been staying in Barangay Hornalan for <b><?php echo $walkin['year_stay_in_brgy']; ?> years</b>.
                This certification is issued for the purpose of <b><?php echo $walkin['purpose']; ?></b>.
                Issued this <b><?php echo date("F d, Y"); ?></b> at Barangay Hornalan, Calamba Laguna.</p>
            <div class="punongbrgy">
                <p>Hon. June Oña</p>
                <p>Punong Barangay</p>
            </div>
        </div>
        <button id="printBtn" onclick="printCertificate(<?php echo $walkin['id']; ?>)">🖨 Print</button>
    </div>
</div>

<script>
function closePrintModal() {
    window.location.href = "walkin.php"; 
}

function printCertificate(id) {
    if (!id) {
        console.error("❌ Walang ID na ipinasa!");
        return;
    }

    console.log("📄 Piniprint ang ID:", id); // ✅ I-log ang ID bago mag-print

    // ✅ Buksan ang Print Dialog
    window.print();

    // ✅ Force close ang modal pagkatapos ng print
    setTimeout(() => {
        console.log("✅ Auto-closing modal...");
        document.getElementById("printModal").style.display = "none"; // Isara ang modal

        // ⏳ Maghintay ng 2 seconds bago mag-request sa `printed_walkin.php`
        setTimeout(() => {
            fetch("printed_walkin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + id
            })
            .then(response => response.json()) // ✅ I-convert ang response sa JSON
            .then(data => {
                console.log("📩 Server Response:", data);

                if (data.status === "success") {
                    console.log("✅ Walk-in request successfully printed and deleted.");
                    window.location.href = "admin_dashboard.php"; // ⬅️ Redirect sa Admin Dashboard
                } else {
                    console.error("❌ Error:", data.message);
                    Swal.fire({
                        icon: "error",
                        title: "Failed!",
                        text: data.message
                    });
                }
            })
            .catch(error => console.error("❌ Fetch Error:", error));
        }, 2000); // ⏳ 2-second delay bago mag-request
    }, 500); // ⏳ 0.5-second delay para isara ang modal
}

</script>

</body>
</html>
