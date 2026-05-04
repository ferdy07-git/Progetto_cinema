<?php
    require "../database/connessione.php";
    require "../utils/password.php";

    $nome = $_POST["nick"];
    $pass = $_POST["pass"];
    $password = encrypt($pass);

    if(check($nome, $password)){
        // Recupera i dati utente dal DB
        $stmt = $conn->prepare("SELECT nome, email FROM utente WHERE nome = ?");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $result = $stmt->get_result();
        $utente = $result->fetch_assoc();

        session_start();
        $_SESSION["user"]     = $nome;
        $_SESSION["password"] = $password;
        $_SESSION["nome"]     = $utente["nome"];
        $_SESSION["email"]    = $utente["email"];

        header("Location:../homepage.php");
    }
?>


