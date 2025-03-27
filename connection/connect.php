<?php
// $host = "localhost"; // Change this if your database is hosted on a different server
// $username = "root"; // Your MySQL username
// $password = ""; // Your MySQL password
// $database = "brgy_info"; // Name of your 

$host = "localhost"; // Change this if your database is hosted on a different server
$username = "u856582098_hornalan"; // Your MySQL username
$password = "JZdtYY7Ih3*"; // Your MySQL password
$database = "u856582098_hornalan"; // Name of your database

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
