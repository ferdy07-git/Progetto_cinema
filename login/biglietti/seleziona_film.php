<?php
    require "../../database/connessione.php";
    session_start();
    include("../../utils/password.php");
    if(login()){
        header("Location:../auth/form_accesso.php");
    }else{
    $_SESSION['id_spettacolo']=$_GET['id_spettacolo'];
    print($_SESSION['id_spettacolo']);
    header("Location:./acquista_biglietto.php");
    }
?>
