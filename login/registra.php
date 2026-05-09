<?php
session_start();
if(isset($_SESSION["user"])){
    header("Location:homepage.php");
    exit(); 
}

require "../utils/password.php";
require "../database/connessione.php";

$nome = $_POST["nick"];
$pass = $_POST["pass"];
$conf = $_POST["conferma_pass"];
$mail = $_POST["email"];


if($pass !== $conf){
    $_SESSION["check"] = TRUE;
    header("Location:reg.php");
    exit();
}


$sql = "SELECT nome FROM utente WHERE nome = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nome); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION["check"] = TRUE;
    header("Location:reg.php");
    exit();
} 

$sql = "SELECT email FROM utente WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mail); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION["check"] = TRUE;
    header("Location:reg.php");
    exit();
}else{

 $password = encrypt($pass);
    $sql = "INSERT INTO utente VALUES(NULL,'$user','$password','$mail',1)";
    if($conn->query($sql)==true){
        $_SESSION["user"] = $user;
        $_SESSION["password"] = $password;
        header("Location: accesso.html");
    }else{
        $_SESSION["check"] = TRUE;
        header("Location:reg.php");
    }
}
?>
