<?php
$conn = new mysqli("localhost", "root", "", "");
$sql = "CREATE DATABASE IF NOT EXISTS cinema";
$conn->query($sql);
$sql = "USE cinema";
$conn->query($sql);
if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}
?>
