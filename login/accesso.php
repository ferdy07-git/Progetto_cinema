<?php
require "../database/connessione.php";
require "../utils/password.php";
$nome = $_POST["nick"];
$pass = $_POST["pass"];
$password = encrypt($pass);
if(check($nome,$password)){
    session_start();
    $_SESSION["user"] = $nome;
    $_SESSION["password"] = $password;
    header("Location:../homepage.php");
}else{
}
?>


