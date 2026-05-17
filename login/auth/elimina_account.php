<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: ../../homepage.php");
    exit();
}

require "../../database/connessione.php";

$nome = $_SESSION["user"];

// Recupera l'id dell'utente
$sql = "SELECT id_utente FROM utente WHERE nome = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nome);
$stmt->execute();
$id_utente = $stmt->get_result()->fetch_assoc()["id_utente"];

// Elimina prima i biglietti collegati
$sql = "DELETE FROM biglietto WHERE utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();

// Poi elimina l'utente
$sql = "DELETE FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utente);
$stmt->execute();

session_destroy();
header("Location: ../../homepage.php");
exit();
?>