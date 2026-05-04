<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Registrati — Cinema Palladino</title>
</head>
<body class="page-auth">

    <a class="auth-logo" href="../homepage.php">
        Cinema Palladino
        <span>Il tuo cinema</span>
    </a>

    <form action="registra.php" method="post">
        <div class="accedi">
            <h1>Crea account</h1>
        </div>
        <div class="campi_inserimento_credenziali">
            <label for="nick">Nome utente</label>
            <input type="text" id="nick" required name="nick" placeholder="Scegli un username">
        </div>
        <div class="campi_inserimento_credenziali">
            <label for="email">Email</label>
            <input type="email" id="email" required name="email" placeholder="tua@email.com">
        </div>
        <div class="campi_inserimento_credenziali">
            <label for="pass">Password</label>
            <input type="password" id="pass" required name="pass" placeholder="••••••••">
        </div>
        <div class="campi_inserimento_credenziali">
            <label for="conferma_pass">Conferma password</label>
            <input type="password" id="conferma_pass" required name="conferma_pass" placeholder="••••••••">
        </div>
        <div class="pulsanti">
            <input type="submit" value="Crea account">
            <a href="accesso.html">Hai già un account? Accedi</a>
            <?php
            session_start();
            if(isset($_SESSION["check"])&& $_SESSION["check"])
                print"<label>Registrazione non riuscita</label>";
            session_destroy();
            ?>
        </div>
    </form>

</body>
</html>
