<?php
session_start();
if(isset($_SESSION["user"])){
    header("Location:homepage.php");
}
require "../utils/password.php";
require "../database/connessione.php";
$user = $_POST["nick"];
$pass = $_POST["pass"];
$conf = $_POST["conferma_pass"];
$mail = $_POST["email"];
if(!($pass == $conf)){
    $_SESSION["check"] = TRUE;
    header("Location:reg.php");
}else{
    $password = encrypt($pass);
    $sql = "INSERT INTO utente VALUES(NULL,'$user','$password','$mail',1)";
    if($conn->query($sql)==true){
        $_SESSION["user"] = $user;
        $_SESSION["password"] = $password;
    }else{
        $_SESSION["check"] = TRUE;
        header("Location:reg.php");
    }
}
?>