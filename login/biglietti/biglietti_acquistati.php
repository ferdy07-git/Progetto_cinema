<?php
session_start();
include("../../database/connessione.php");
include("../../utils/password.php");
login();

$nome     = htmlspecialchars($_SESSION['user']  ?? 'Utente');
$email    = htmlspecialchars($_SESSION['email'] ?? '');
$iniziali = strtoupper(substr($_SESSION['user'] ?? 'U', 0, 1));

// Recupera biglietti dell'utente
$result = $conn->query("
    SELECT 
        b.id_biglietto,
        b.posto,
        b.importo,
        f.titolo,
        f.locandina,
        s.data_spettacolo,
        s.ora_inizio,
        sa.nome AS sala_nome
    FROM biglietto b
    JOIN spettacolo s  ON b.spettacolo = s.id_spettacolo
    JOIN film f        ON s.film       = f.id_film
    JOIN sala sa       ON s.sala       = sa.id_sala
    JOIN utente u      ON b.utente     = u.id_utente
    WHERE u.nome = '" . $conn->real_escape_string($nome) . "'
    ORDER BY s.data_spettacolo DESC, s.ora_inizio DESC
");

$biglietti = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Biglietti – Cinema Itis</title>
    <link rel="stylesheet" href="../style/style.css?v=2">
</head>
<body class="page-home">

<!-- ── TOPBAR ─────────────────────────────── -->
 
<div class="hero-strip">
    <div class="hero-topbar">
        
        <h1 class="hero-site-title">Itis "Luigi di Maggio"</h1>

        <div class="profile-menu">
            <label for="toggle-menu" class="avatar-btn"><?php echo $iniziali; ?></label>
            <input type="checkbox" id="toggle-menu">
            <div class="dropdown">
                <div class="user-info">
                    <span class="name"><?php echo $nome; ?></span>
                    <span class="email"><?php echo $email; ?></span>
                </div>
                <a href='../auth/rec_password.php' class='menu-link'><span>🔑</span> Modifica password</a>
                <a href='../auth/elimina_account.php' class='menu-link'><span>❌</span> Elimina account</a>
                <a href="../auth/logout.php" class="menu-link logout"><span>👋</span> Esci</a>
            </div>
        </div>
        
    </div>
    <a href="../../homepage.php" class="auth-back-home" title="Torna alla homepage">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
    </a>

    <h2 class="hero-title">I Miei <span>Biglietti</span></h2>
    <p><?php echo count($biglietti); ?> bigliett<?php echo count($biglietti) === 1 ? 'o acquistato' : 'i acquistati'; ?></p>
</div>

<!-- ── CONTENT ───────────────────────────── -->
<div class="page-layout" style="max-width:860px; margin:0 auto;">
    <main style="flex:1; min-width:0;">

        <?php if (empty($biglietti)): ?>
        <div class="ticket-empty">
            <center> 
            <div class="icon" >🎟</div>
            <br>
            <p>Nessun biglietto acquistato.</p><br>
            <span>Esplora i film disponibili e acquista il tuo primo biglietto!</span><br>
            <a href="../../homepage.php" class="btn-acquista" style="margin-top:1.5rem; display:inline-block; text-decoration:none; width:fit-content;">
                Sfoglia i film
            </a></center>
        </div>

        <?php else: ?>
        <div class="container" style="grid-template-columns:1fr; gap:1.25rem;">

            <?php foreach ($biglietti as $b):
                $data_fmt   = date('d/m/Y', strtotime($b['data_spettacolo']));
                $ora_fmt    = substr($b['ora_inizio'], 0, 5);
                $importo_fmt = number_format($b['importo'], 2, ',', '.') . ' €';
            ?>
            <div class="ticket-card">

                <img src="../../img/<?php echo htmlspecialchars($b['locandina'] ?? 'default-film.webp'); ?>"
                     alt="Locandina <?php echo htmlspecialchars($b['titolo']); ?>"
                     onerror="this.src='img/default-film.webp'">

                <div class="ticket-body">
                    <h2><?php echo htmlspecialchars($b['titolo']); ?></h2>

                    <div class="ticket-meta">
                        <div class="ticket-meta-item">
                            <span class="label">📅 Data</span>
                            <span class="value"><?php echo $data_fmt; ?></span>
                        </div>
                        <div class="ticket-meta-item">
                            <span class="label">🕐 Ora inizio</span>
                            <span class="value"><?php echo $ora_fmt; ?></span>
                        </div>
                        <div class="ticket-meta-item">
                            <span class="label">🏛️ Sala</span>
                            <span class="value"><?php echo htmlspecialchars($b['sala_nome']); ?></span>
                        </div>
                        <div class="ticket-meta-item">
                            <span class="label">💺 Posto</span>
                            <span class="value">
                                <span class="ticket-badge-posto"><?php echo htmlspecialchars($b['posto']); ?></span>
                            </span>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:0.25rem;">
                        <span class="ticket-importo"><?php echo $importo_fmt; ?></span>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>

        </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
