<?php
require "../database/connessione.php";


$nome = $_POST["nick"];
$pass = $_POST["pass"];

$sql = "SELECT * FROM Utente WHERE nome = '$nome' AND password_hash = '$pass'";

$result = $conn->query($sql);


if ($result->num_rows > 0) {
    header("Location: ../homepage.php");
    exit();
} else {
    header("Location: accesso.html?errore=credenziali_sbagliate");
}


?>


