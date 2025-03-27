<?php
include '../connection/connect.php';

if (isset($_GET['docs_type'])) {
    $docsType = $_GET['docs_type'];

    // Kunin ang fee mula sa database
    $query = "SELECT fee FROM document WHERE docs_type = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $docsType);
    $stmt->execute();
    $stmt->bind_result($fee);
    $stmt->fetch();

    // Kung NULL ang fee, gawin itong 0
    echo ($fee === null) ? "0" : $fee;
} else {
    echo "No docs_type provided";
}

?>
