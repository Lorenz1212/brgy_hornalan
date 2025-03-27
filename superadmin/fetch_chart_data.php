<?php
include '../connection/connect.php'; // Database connection

$query = "SELECT 
            CASE 
                WHEN address REGEXP 'Purok [0-9]+' THEN 
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(address, 'Purok ', -1), ' ', 1))
                ELSE 'Unknown' 
            END AS purok,
            type,
            COUNT(*) AS total 
          FROM history 
          WHERE address LIKE '%Purok%'  -- Siguraduhin may Purok sa address
          GROUP BY purok, type 
          ORDER BY CAST(purok AS UNSIGNED) ASC";

$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $purok = "Purok " . $row['purok']; // Ayusin format
    $type = $row['type'];
    $count = (int) $row['total'];

    if (!isset($data[$purok])) {
        $data[$purok] = [];
    }

    $data[$purok][$type] = $count;
}

// Ibalik ang JSON data
echo json_encode($data);
?>
