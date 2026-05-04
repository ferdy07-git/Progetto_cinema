<?php
function encrypt($pass){
    return hash('sha256',$pass);
}
function check($user,$pass) : boolval {
    require __DIR__."/../database/connessione.php";
    $sql = "SELECT password_hash FROM utente WHERE nome = '$user'";
    $password = $conn->query($sql)->fetch_assoc()["password_hash"];
    return $password==encrypt($pass);
}
?>
