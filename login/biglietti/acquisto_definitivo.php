<?php
session_start();
require("../../database/connessione.php");
$posti = $_SESSION['posti_selezionati'];
$id_spettacolo = $_SESSION['id_spettacolo'];
$importo = 9.50;
$user = $_SESSION["user"];
$utente = $conn->query("SELECT id_utente FROM utente WHERE nome = '$user'")->fetch_assoc()["id_utente"];
foreach($posti as $posto){
    $sql = "INSERT INTO biglietto VALUES(NULL,$importo,$posto,$id_spettacolo,$utente)";
    $conn->query($sql);
}
header("../../homepage.php");
?>