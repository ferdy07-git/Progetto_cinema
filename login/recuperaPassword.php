<?php
require "../database/connessione.php";
require "../utils/password.php";

$mail = $_POST["email"];
$p1 = $_POST["pas1"];
$p2 = $_POST["pas2"];

if ($p1 == $p2) {

    $sql = "SELECT * FROM Utente WHERE email = '$mail'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {  
        $password = encrypt($p1);
        $update = "UPDATE utente SET password_hash = '$password' WHERE email = '$mail'";
        $conn->query($update);        
        header("Location: accesso.html");
        exit();

    } else {        
        header("Location: recupera_password.html?errore=credenziali");

        exit();
    }
} else {
    
    header("Location: recupera_password.html?errore=credenziali");
    exit();
}
?>
