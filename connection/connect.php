<?php
// $host = "localhost";
// $username = "root";
// $password = ""; 
// $database = "brgy_info"; 

$host = "localhost";
$username = "u856582098_hornalan";
$password = "O?8/D046q|"; 
$database = "u856582098_hornalan"; 

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
