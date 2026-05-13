<?php
    require "../database/connessione.php";
    require "../utils/password.php";
 session_start();
    $nome = $_POST["nick"];
    $pass = $_POST["pass"];
    $password = encrypt($pass);

    if(check($nome, $password)){
        // Recupera i dati utente dal DB
        $sql = "SELECT email,tipo FROM utente WHERE nome = '$nome'";
        $result = $conn->query($sql);
        $utente = $result->fetch_assoc();
        $tipo = $utente["tipo"];
       
        $_SESSION["user"]     = $nome;
        $_SESSION["password"] = $password;
        $_SESSION["email"]    = $utente["email"];

        switch($tipo){
            case 1:
                header("Location:../homepage.php");
                break;
            case 2:
                header("Location:../#.php");
                break;
            case 3:
                header("Location:./admin/modifica.php");
                break;
        }     
    }else{
        $_SESSION["check"] = TRUE;
        header("Location:form_accesso.php");
        
        
    }
?>
