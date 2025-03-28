<?php
include '../connection/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];

    $query = "DELETE FROM resident_list WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
}

$conn->close();
?>
