<?php
$host = 'localhost';
$db = 'db_e_posyandu';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

session_start();
?>