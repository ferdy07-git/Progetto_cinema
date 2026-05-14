<?php
session_start();
if(isset($_SESSION["user"])){
    header("Location:homepage.php");
    exit();
}

require "../../database/connessione.php";
require "../../utils/password.php";

$pass = $_POST["pas1"];
$conf = $_POST["pas2"];
$mail = $_POST["email"];

// 1. Controllo coincidenza password
if ($pass !== $conf) {
    header("Location: recupera_password.html?errore=credenziali");
    exit();
}

// 2. Controllo se la mail esiste nel database
// Nota: Ho corretto la query aggiungendo "FROM utente"
$sql_check = "SELECT email FROM utente WHERE email = '$mail'";
$result = $conn->query($sql_check);

if ($result->num_rows == 0) {
    // La mail non esiste
    header("Location: recupera_password.html?errore=credenziali");
    exit();
} 

// 3. Se siamo qui, la mail esiste e le password coincidono: procediamo con l'UPDATE
$password_criptata = encrypt($pass);
$sql_update = "UPDATE utente SET password_hash = '$password_criptata' WHERE email = '$mail'";

if ($conn->query($sql_update) === TRUE) {
    // Recuperiamo il nome utente per la sessione (opzionale, ma utile)
    $res_utente = $result->fetch_assoc();
    
    // Login automatico dopo il cambio password
    $_SESSION["user"] = $mail; // O il nick se lo recuperi con una SELECT
    $_SESSION["password"] = $password_criptata;
    
    header("Location: ../homepage.php");
    exit();
}
?>
