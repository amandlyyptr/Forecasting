<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "arima_app";

$db = mysqli_connect($host, $user, $pass, $db);

if (!$db) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
