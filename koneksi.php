<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "chipok_db";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>
