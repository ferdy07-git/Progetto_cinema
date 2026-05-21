<?php
session_start();
require "../../utils/password.php";
[$user,$pass] = credenziali();
if(check($user,$pass)){
    header("Location:../../homepage.php");
    exit(); 
}
require "../../database/connessione.php";

$nome = $_POST["nick"];
$pass = $_POST["pass"];
$conf = $_POST["conferma_pass"];
$mail = $_POST["email"];



if (!(
    strlen($pass) >= 8 &&
    preg_match('/[A-Z]/', $pass) &&
    preg_match('/[0-9]/', $pass) &&
    preg_match('/[\W_]/', $pass)
)) {
 $_SESSION["check"] = TRUE;
    header("Location:reg.php");
    exit();
    }


$sql = "SELECT nome FROM utente WHERE nome = '$nome'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $_SESSION["check"] = TRUE;
    header("Location:reg.php");
    exit();
} 

$sql = "SELECT email FROM utente WHERE email = '$mail'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $_SESSION["check"] = TRUE;
    header("Location:reg.php");
    exit();
}else{

 $password = encrypt($pass);
    $sql = "INSERT INTO utente VALUES(NULL,'$nome','$password','$mail',1)";
    if($conn->query($sql) == true){
    $_SESSION["user"]  = $nome;
    $_SESSION["email"] = $mail;
    $_SESSION["tipo"]  = 1;
    $_SESSION["password"] = $password;
    header("Location: ../../homepage.php");
}
}
?>
