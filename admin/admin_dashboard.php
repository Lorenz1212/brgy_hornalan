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
    header("Location: index.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <link rel="stylesheet" type="text/css" href="sidebar.css">
    <style>
        .container {
            margin-left: 250px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 80px;
        }

        .dashboard-box {
            width: 80%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            margin: 60px auto;
        }

        .box {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            margin: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .box:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .box i {
            font-size: 30px;
            color: #333;
            margin-bottom: 10px;
        }

        .box h2 {
            margin: 10px 0;
            font-size: 15px;
            color: #333;
        }

        .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: red;
            color: white;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 50%;
        }

        .chart-container {
    width: 80%; /* ✅ Malapad pero hindi lalampas */
    max-width: 1000px; /* ✅ Para hindi masyadong compressed */
    height: 450px; /* ✅ Mas pinahaba */
    margin: 30px auto;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: center;
    align-items: center;
    border: 2px solid #1abc9c;
}

.chart-container canvas {
    width: auto !important;
    height: auto !important;
    max-width: 100%;
    max-height: 100%;
}


    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="container">
        <div class="dashboard-box">
            <a href="clearance.php" class="box">
                <i class="fa fa-file"></i>
                <h2>Clearances</h2>
                <p>Click to view details</p>
                <span class="badge" id="approvedClearance"></span>
            </a>
            <a href="certificate.php" class="box">
                <i class="fa fa-file"></i>
                <h2>Certificates</h2>
                <p>Click to view details</p>
                <span class="badge" id="approvedCertificate"></span>
            </a>
            <a href="brgy_officials.php" class="box">
                <i class="fa fa-users"></i>
                <h2>Barangay Elected Officials</h2>
                <p>Click to view details</p>
            </a>
            <a href="walkin.php" class="box">
                <i class="fa fa-user-check"></i>
                <h2>Resident Walk-in</h2>
                <p>Click to process walk-in requests</p>
                <span class="badge" id="walkinCount"></span>
            </a>
            <a href="request.php" class="box">
                <i class="fa fa-list"></i>
                <h2>Requests</h2>
                <p>Click to view pending requests</p>
                <span class="badge" id="totalPending"></span>
            </a>
        </div>

        <!-- Chart Container -->
        <div class="chart-container">
            <canvas id="documentChart"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
    fetch("fetch_chart_data.php")
        .then(response => response.json())
        .then(data => {
            let puroks = ["Purok 1", "Purok 2", "Purok 3", "Purok 4"];
            let documentTypes = [
                "Barangay Clearance",
                "Business Clearance",
                "Indigency",
                "Barangay Certificate",
                "Residency",
                "Cedula",
                "First Time Job Seeker Certificate"
            ];

            let datasets = documentTypes.map((type, index) => {
                return {
                    label: type,
                    data: puroks.map(purok => (data[purok] && data[purok][type]) ? data[purok][type] : 0),
                    backgroundColor: generateColor(index),
                    borderWidth: 1
                };
            });

            let ctx = document.getElementById("documentChart").getContext("2d");
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: puroks,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true, // ❗ Siguraduhin naka-ON ang title
                            text: "📊 Document Distribution by Purok", // ✅ Title ng graph
                            font: {
                                size: 20, // ✅ Medyo mas malaki ang title
                                weight: "bold"
                            },
                            color: "#333",
                            padding: {
                                top: 15,
                                bottom: 20
                            }
                        },
                        legend: { position: "top" },
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    return tooltipItem.dataset.label + ": " + tooltipItem.raw;
                                }
                            }
                        },
                        datalabels: {
                            anchor: 'center',
                            align: 'center',
                            formatter: function (value) {
                                return value > 0 ? value : '';
                            },
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            color: 'black'
                        }
                    },
                    scales: {
                        x: { stacked: false },
                        y: {
                            beginAtZero: true,
                            max: 50,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        });

    function generateColor(index) {
        let colors = ["#4A90E2", "#E74C3C", "#2ECC71", "#9B59B6", "#F1C40F", "#1ABC9C", "#D35400", "#34495E", "#E67E22", "#27AE60"];
        return colors[index % colors.length];
    }
});




        // Fetch request counts via AJAX (Hindi Binago)
        function fetchRequestCounts() {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_badges.php", true);
            xhr.onload = function () {
                if (xhr.status == 200) {
                    try {
                        let data = JSON.parse(xhr.responseText);

                        if (data.error) {
                            console.error("Error: " + data.error);
                            return;
                        }

                        let clearanceBadge = document.getElementById("approvedClearance");
                        let certificateBadge = document.getElementById("approvedCertificate");
                        let pendingBadge = document.getElementById("totalPending");

                        if (data.Approved_Clearance > 0) {
                            clearanceBadge.innerText = data.Approved_Clearance + " Approved";
                            clearanceBadge.style.display = "block";
                        } else {
                            clearanceBadge.style.display = "none";
                        }

                        if (data.Approved_Certificate > 0) {
                            certificateBadge.innerText = data.Approved_Certificate + " Approved";
                            certificateBadge.style.display = "block";
                        } else {
                            certificateBadge.style.display = "none";
                        }

                        if (data.Total > 0) {
                            pendingBadge.innerText = data.Total + " Pending";
                            pendingBadge.style.display = "block";
                        } else {
                            pendingBadge.style.display = "none";
                        }

                    } catch (e) {
                        console.error("JSON parse error:", e);
                    }
                } else {
                    console.error("Error fetching data: " + xhr.status);
                }
            };
            xhr.onerror = function () {
                console.error("Request failed");
            };
            xhr.send();
        }

        fetchRequestCounts();

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
