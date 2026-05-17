<?php

session_start();
include("../../database/connessione.php");
include("../../utils/password.php");
if(login()){
    header("Location:../auth/form_accesso.php")
}
[$user,$pass] = credenziali();
$sql = "SELECT id_biglietto,posto,spettacolo FROM biglietto WHERE utente = '$user'";
$dati = $conn->query($sql);
$passati = [];
$disponibili = [];
while($r = $dati->fetch_assoc()){
    $spec = $r["spettacolo"];
    $sql = "SELECT data_spettacolo FROM spettacolo WHERE id_spettacolo = $spec";
}

?>
