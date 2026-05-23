<?php
session_start();
require "../../utils/password.php";
[$user,$pass] = credenziali();
if(check($user,$pass)){
    header("Location:../../homepage.php");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css?v=2">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <title>Accedi — Cinema Itis "Luigi di Maggio"</title>
</head>
<body>
<div class="auth-page">

    <a href="../../homepage.php" class="auth-back-home" title="Torna alla homepage">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
    </a>

    <a class="auth-brand" href="../../homepage.php">
        Cinema Itis "Luigi di Maggio"
        <span>Il tuo cinema</span>
    </a>

    <form class="auth-panel" action="accesso.php" method="post">
        
        <div class="auth-title-block">
            <h1>Accedi</h1>
        </div>
        <div class="auth-field">
            <label for="nick">Nome utente</label>
            <input type="text" id="nick" required name="nick" placeholder="Il tuo username">
        </div>
        <div class="auth-field">
            <label for="pass">Password</label>
            <div class="pass-wrapper">
                <input type="password" id="pass" required name="pass" placeholder="••••••••">
                <button type="button" class="toggle-pass" aria-label="Mostra password" data-target="pass">
                    <i class="ti ti-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        
      <?php
            if(isset($_SESSION["check"])&& $_SESSION["check"]){
                print"<center><label style='color: red'>Accesso non riuscito</label></center>";
            session_destroy();
            }
            ?>
        <div class="auth-actions">
            <input type="submit" value="Accedi">
            <a href="rec_password.php">Ho dimenticato la password</a>
            <a href="reg.php">Crea un account</a>
        </div>
    </form>

</div>
</body>

<script>
    document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const icon  = btn.querySelector('i');
        const show  = input.type === 'password';

        input.type      = show ? 'text' : 'password';
        icon.className  = show ? 'ti ti-eye-off' : 'ti ti-eye';
        btn.setAttribute('aria-label', show ? 'Nascondi password' : 'Mostra password');
    });
});
</script>
</html>
