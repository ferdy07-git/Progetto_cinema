<?php
$conn = new mysqli("db", "root", "", "");
if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}
$conn->query("CREATE DATABASE IF NOT EXISTS cinema");
$conn->select_db("cinema");
?>