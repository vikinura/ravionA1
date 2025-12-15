<?php
$servername = "localhost";
<<<<<<< HEAD
$username = "root";
$password = "";
=======
$username = "root";  
$password = "";      
>>>>>>> 9723a675da2df4d870deb6a4f8fc710840e9cecb
$dbname = "web_login";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}
