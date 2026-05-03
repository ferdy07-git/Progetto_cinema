<?php
require "../database/connessione.php";

$mail = $_POST["email"];
$p1 = $_POST["pas1"];
$p2 = $_POST["pas2"];

if ($p1 == $p2) {

    $sql = "SELECT * FROM utente WHERE email = '$mail'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {  
        $update = "UPDATE utente SET password_hash = '$p1' WHERE email = '$mail'";
        $conn->query($update);        
        header("Location: accesso.html");
        exit();

    } else {        
        header("Location: recuperaPassword.html?errore=utente");
        exit();
    }
} else {
    
    header("Location: recuperaPassword.html?errore=password");
    exit();
}
?>