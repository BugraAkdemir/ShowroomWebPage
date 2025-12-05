<?php
$servername = "localhost";
$username = "root";
$password = "b";
$dbname = "vendotek_magaza"; // kendi DB adını yaz

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}
?>
