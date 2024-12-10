<?php
// db.php
$servername = "localhost";
$username = "root"; // aapka MySQL username
$password = ""; // aapka MySQL password
$dbname = "rozee";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
