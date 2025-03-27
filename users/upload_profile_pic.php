<?php
session_start();
include '../connection/connect.php';

// ✅ Iwasan ang extra output bago ang JSON response
ob_start();
header("Content-Type: application/json");

// ✅ Check kung naka-login ang user
if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access!"]);
    exit();
}

// ✅ Check kung may na-receive na file
if (!isset($_FILES["profilePic"])) {
    echo json_encode(["status" => "error", "message" => "No file received!"]);
    exit();
}

$user = $_SESSION['user'];
$targetDir = "../uploads/";

// ✅ Gumawa ng `uploads/` folder kung wala pa
if (!is_dir($targetDir)) {
    // Subukan gumawa ng folder, kung hindi magtagumpay, ipakita ang error
    if (!mkdir($targetDir, 0777, true)) {
        echo json_encode(["status" => "error", "message" => "Failed to create directory."]);
        exit();
    }
}

$fileName = basename($_FILES["profilePic"]["name"]);
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedExtensions = ["jpg", "jpeg", "png", "gif"];

// ✅ Check kung valid ang file type
if (!in_array($fileExt, $allowedExtensions)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, JPEG, PNG & GIF allowed."]);
    exit();
}

// ✅ Check kung hindi lampas sa 2MB
if ($_FILES["profilePic"]["size"] > 2 * 1024 * 1024) {
    echo json_encode(["status" => "error", "message" => "File is too large. Max 2MB allowed."]);
    exit();
}

// ✅ Bumuo ng unique filename para maiwasan ang overwrite
$newFileName = "profile_" . time() . "_" . $user . "." . $fileExt;
$targetFile = $targetDir . $newFileName;

// ✅ Subukang i-move ang file sa uploads folder
if (!move_uploaded_file($_FILES["profilePic"]["tmp_name"], $targetFile)) {
    echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
    exit();
}

// ✅ I-update ang profile picture sa database gamit ang relative path
$dbFilePath = "uploads/" . $newFileName;
$updateQuery = "UPDATE users SET profile_pic = ? WHERE user = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("ss", $newFileName, $user);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "newPath" => $dbFilePath]);
} else {
    echo json_encode(["status" => "error", "message" => "Database update failed."]);
}
$stmt->close();
?>
