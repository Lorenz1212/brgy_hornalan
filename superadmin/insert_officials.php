<?php
include '../connection/connect.php'; // Database Connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);

    // Check kung may existing official sa parehong position
    $checkQuery = "SELECT COUNT(*) AS count FROM brgy_official WHERE position = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $position);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "An official for this position already exists!"]);
        exit();
    }

    // File upload settings
    $targetDir = "../image/"; // Dapat may trailing slash
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true); // Gumawa ng folder kung wala pa
    }

    $fileName = time() . "_" . basename($_FILES["profile"]["name"]);
    $targetFilePath = $targetDir . $fileName; // Full path kung saan i-save ang image
    $dbFilePath = "image/" . $fileName; // Relative path na isasave sa database

    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profile"]["tmp_name"], $targetFilePath)) {
            // Ipasok sa database ang tamang relative path
            $query = "INSERT INTO brgy_official (name, position, profile) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $name, $position, $dbFilePath);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Official added successfully!"]);
            } else {
                echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Error uploading file."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid file format! Allowed: JPG, JPEG, PNG, GIF."]);
    }
}
?>
