<?php
session_start();
require "../../database/connessione.php";
require "../../utils/password.php";

$pass = $_POST["pass"];
$conf = $_POST["conferma_pass"];
$mail = $_POST["email"];

// 1. Controllo coincidenza password
if ($pass !== $conf) {
    header("Location: recupera_password.html?errore=credenziali");
    exit();
}

// 2. Controlla se la mail esiste e recupera i dati utente
$result = $conn->query("SELECT * FROM utente WHERE email = '$mail'");

if ($result->num_rows === 0) {
    header("Location: recupera_password.html?errore=credenziali");
    exit();
}

$utente = $result->fetch_assoc();

// 3. Aggiorna la password
$password_criptata = encrypt($pass);
$conn->query("UPDATE utente SET password_hash = '$password_criptata' WHERE email = '$mail'");

// 4. Popola la sessione e vai in homepage
$_SESSION["user"]     = $utente["email"];
$_SESSION["nick"]     = $utente["nick"];
$_SESSION["nome"]     = $utente["nome"];
$_SESSION["password"] = $password_criptata;

header("Location: ../../homepage.php");
exit();
?>
