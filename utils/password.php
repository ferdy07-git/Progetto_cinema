<?php
function encrypt($pass){
    return hash('sha256',$pass);
}
function check($user,$pass){
    require __DIR__."/../database/connessione.php";
    if($user !=NULL){
    $sql = "SELECT password_hash FROM utente WHERE nome = '$user'";
    $password = $conn->query($sql)->fetch_assoc()["password_hash"];
    return $password==$pass;
    }
    return false;
}
function credenziali(){
    if(isset($_SESSION["user"]) && isset($_SESSION["password"])){
    return [$_SESSION["user"],$_SESSION["password"]];
    }
    return [NULL,NULL];
}
function login(){
    [$user,$pass] = credenziali();
    $path = substr(__DIR__,strpos(__DIR__,"htdocs")+6)."/../login/auth/form_accesso.php";
    if(is_null($user) || is_null($pass) || !(check($user,$pass))){
        header("Location:$path");
    }
}
function check_log($type){
    login();
    [$user,$pass] = credenziali();
    require __DIR__."/../database/connessione.php";
    $sql = "SELECT tipo FROM utente WHERE nome = '$user'";
    $tipo = $conn->query($sql)->fetch_assoc()["tipo"];
    if($tipo != $type){
        $path = substr(__DIR__,strpos(__DIR__,"htdocs")+6)."/../login/auth/form_accesso.php";
        if(is_null($user) || is_null($pass) || !(check($user,$pass))){
        header("Location:$path");
        }
    }
}
?>
