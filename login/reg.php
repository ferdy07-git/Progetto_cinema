<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Registrati — Cinema Palladino</title>
</head>
<body class="page-auth">

    <a href="../homepage.php" class="home-btn" title="Torna alla homepage">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
    </a>

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
            <?php
            session_start();
            if(isset($_SESSION["check"])&& $_SESSION["check"])
                print"<center><label style='color: red'>Registrazione non riuscita</label></center>";
            session_destroy();
            ?>
        <div class="pulsanti">
            <input type="submit" value="Crea account">
            <a href="accesso.html">Hai già un account? Accedi</a>
        </div>
    </form>

</body>
</html>
