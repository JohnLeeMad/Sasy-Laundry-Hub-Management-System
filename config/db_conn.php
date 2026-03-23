<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "laundry_v2";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($conn, "SET time_zone = '+08:00'");
