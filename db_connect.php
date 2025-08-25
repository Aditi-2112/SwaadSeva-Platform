<?php
$host = "localhost";
$username = "root";    
$password = "";          
$database = "swaadseva"; 

$conn = new mysqli($host, $username, $password, $database);

date_default_timezone_set('Asia/Kolkata');


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>