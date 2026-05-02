<?php
$conn = new mysqli("localhost", "root", "", "cinema");

if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}
?>
