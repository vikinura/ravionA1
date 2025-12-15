<?php
$servername = "localhost";
$username = "root";  // default XAMPP
$password = "";      // biasanya kosong
$dbname = "web_login.";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}
