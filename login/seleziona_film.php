<?php
    require "../database/connessione.php";
    session_start();
    $_SESSION['id_spettacolo']=$_GET['id_spettacolo'];
    print($_SESSION['id_spettacolo']);
    header("Location:./acquista_biglietto.php");
?>