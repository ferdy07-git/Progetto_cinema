<?php
session_start();
require "../../database/connessione.php";
require "../../utils/password.php";
check_log(2);

if (!isset($_SESSION["csrf_venditore"])) {
    $_SESSION["csrf_venditore"] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION["csrf_venditore"];

$flash = $_SESSION["flash_venditore"] ?? ["type" => "", "text" => ""];
unset($_SESSION["flash_venditore"]);

function set_flash(string $type, string $text): void {
    $_SESSION["flash_venditore"] = ["type" => $type, "text" => $text];
}

function redirect_to_list(): void {
    if (!headers_sent()) {
        header("Location: ./venditore.php");
    } else {
        echo "<script>window.location.href='./venditore.php';</script>";
    }
    exit;
}

// Carica film
$films = [];
$rFilm = $conn->query("SELECT id_film, titolo FROM film ORDER BY titolo");
if ($rFilm) {
    while ($f = $rFilm->fetch_assoc()) $films[] = $f;
}

// Carica sale
$sale = [];
$rSala = $conn->query("SELECT id_sala, nome, posti FROM sala ORDER BY nome");
if ($rSala) {
    while ($s = $rSala->fetch_assoc()) $sale[] = $s;
}

// Carica spettacoli
$query = "
    SELECT
        s.id_spettacolo,
        s.data_spettacolo,
        s.ora_inizio,
        f.id_film,
        f.titolo AS titolo_film,
        f.locandina,
        f.durata,
        sa.id_sala,
        sa.nome AS nome_sala,
        sa.posti,
        (SELECT COUNT(*) FROM biglietto b WHERE b.spettacolo = s.id_spettacolo) AS biglietti_venduti
    FROM spettacolo s
    INNER JOIN film f ON s.film = f.id_film
    INNER JOIN sala sa ON s.sala = sa.id_sala
    ORDER BY s.data_spettacolo DESC, s.ora_inizio ASC
";
$result = $conn->query($query);
if ($result === false) die("Errore: " . htmlspecialchars($conn->error));

$spettacoli = [];
while ($row = $result->fetch_assoc()) $spettacoli[] = $row;

$nomeUtente  = htmlspecialchars($_SESSION['user']  ?? 'Venditore');
$emailUtente = htmlspecialchars($_SESSION['email'] ?? '');
$iniziali    = strtoupper(substr($_SESSION['user'] ?? 'V', 0, 1));

$oggi = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venditore - Gestione Spettacoli</title>
    <link rel="stylesheet" href="../style/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-home">

    <div class="hero-strip">
        <div class="hero-topbar">
            <h1 class="hero-site-title">Itis "Luigi di Maggio"</h1>
            <div class="profile-menu">
                <label for="toggle-menu" class="avatar-btn"><?php echo $iniziali; ?></label>
                <input type="checkbox" id="toggle-menu">
                <div class="dropdown">
                    <div class="user-info">
                        <span class="name"><?php echo $nomeUtente; ?></span>
                        <span class="email"><?php echo $emailUtente; ?></span>
                    </div>
                    <a href="../../homepage.php" class="menu-link"><span>🏠</span> Vai alla home</a>
                    <a href="../auth/logout.php" class="menu-link logout"><span>👋</span> Esci</a>
                </div>
            </div>
        </div>

        <h2 class="hero-title">Pannello <span>Venditore</span></h2>
        <p>Vendi biglietti per gli spettacoli in programmazione</p>

        <div class="search-bar-wrapper" style="margin-top:14px;">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-input" class="search-bar" placeholder="Cerca per titolo, sala, data..." autocomplete="off">
            <button class="search-clear" id="search-clear" title="Cancella" style="display:none;">✕</button>
        </div>
    </div>

    <div class="page-layout">
        <aside class="sidebar-generi">
            <h3 class="sidebar-title">Sale</h3>
            <div class="genere-list-wrapper">
                <ul class="genere-list">
                    <li>
                        <button class="genere-btn active" data-sala="tutte">
                            <span class="genere-dot"></span> Tutte le sale
                        </button>
                    </li>
                    <?php foreach ($sale as $s): ?>
                    <li>
                        <button class="genere-btn" data-sala="<?php echo htmlspecialchars($s['nome'], ENT_QUOTES); ?>">
                            <span class="genere-dot"></span>
                            <?php echo htmlspecialchars($s['nome']); ?>
                            <small style="font-size:.65rem; color:var(--text-dim); margin-left:auto;"><?php echo (int)$s['posti']; ?>p</small>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <main style="width:100%">
            <?php
                $totSpett = count($spettacoli);
                $totBiglietti = array_sum(array_column($spettacoli, 'biglietti_venduti'));
                $spettOggi = count(array_filter($spettacoli, fn($sp) => $sp['data_spettacolo'] === $oggi));
            ?>
            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-label">Spettacoli totali</span>
                    <span class="stat-value"><?php echo $totSpett; ?></span>
                    <span class="stat-sub">in archivio</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Biglietti venduti</span>
                    <span class="stat-value"><?php echo $totBiglietti; ?></span>
                    <span class="stat-sub">su tutti gli spettacoli</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Oggi in programma</span>
                    <span class="stat-value"><?php echo $spettOggi; ?></span>
                    <span class="stat-sub"><?php echo date('d/m/Y'); ?></span>
                </div>
            </div>

            <div class="container" id="spett-container">

                <?php if (($flash["type"] ?? "") === "error" && $flash["text"]): ?>
                    <div class="no-results" style="display:flex; margin:0 0 16px; grid-column:1/-1;">
                        <p>⚠️ <?php echo htmlspecialchars($flash["text"]); ?></p>
                    </div>
                <?php endif; ?>

                <!-- LISTA SPETTACOLI -->
                <?php foreach ($spettacoli as $sp):
                    $pct = $sp['posti'] > 0 ? round(($sp['biglietti_venduti'] / $sp['posti']) * 100) : 0;
                    $barColor  = $pct >= 90 ? '#e05c5c' : ($pct >= 60 ? '#f2994a' : '#6fcf97');
                    $dataFmt   = date('d/m/Y', strtotime($sp['data_spettacolo']));
                    $isPassato = $sp['data_spettacolo'] < $oggi;
                    $isEsaurito = ((int)$sp['posti'] - (int)$sp['biglietti_venduti']) <= 0;
                ?>
                    <div class="spett-card"
                        data-titolo="<?php echo strtolower(htmlspecialchars($sp['titolo_film'], ENT_QUOTES)); ?>"
                        data-sala="<?php echo htmlspecialchars($sp['nome_sala'], ENT_QUOTES); ?>"
                        data-data="<?php echo $sp['data_spettacolo']; ?>">

                        <img class="spett-poster"
                            src="../../img/<?php echo htmlspecialchars($sp['locandina'] ?? 'default-film.webp'); ?>"
                            alt="<?php echo htmlspecialchars($sp['titolo_film'], ENT_QUOTES); ?>"
                            onerror="this.src='../../img/default-film.webp'">

                        <div class="spett-body">
                            <div class="spett-title"><?php echo htmlspecialchars($sp['titolo_film']); ?></div>

                            <div class="spett-meta">
                                <span class="spett-badge badge-gold">🏛️ <?php echo htmlspecialchars($sp['nome_sala']); ?></span>
                                <span class="spett-badge badge-muted">📅 <?php echo $dataFmt; ?></span>
                                <span class="spett-badge badge-muted">🕐 <?php echo substr($sp['ora_inizio'],0,5); ?></span>
                                <span class="spett-badge badge-muted">⏱ <?php echo htmlspecialchars($sp['durata']); ?></span>
                                <?php if ($isPassato): ?>
                                    <span class="spett-badge" style="color:#e05c5c;background:rgba(224,92,92,.1);border:1px solid rgba(224,92,92,.25);">Passato</span>
                                <?php elseif ($sp['data_spettacolo'] === $oggi): ?>
                                    <span class="spett-badge badge-green">Oggi</span>
                                <?php endif; ?>
                                <?php if ($isEsaurito): ?>
                                    <span class="spett-badge" style="color:#e05c5c;background:rgba(224,92,92,.1);border:1px solid rgba(224,92,92,.25);">Esaurito</span>
                                <?php endif; ?>
                            </div>

                            <div class="spett-occupancy">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span class="spett-badge badge-green" style="font-size:.68rem;">
                                        🎟️ <?php echo (int)$sp['biglietti_venduti']; ?> / <?php echo (int)$sp['posti']; ?> posti
                                    </span>
                                    <span style="font-size:.7rem; color:var(--text-dim);"><?php echo $pct; ?>%</span>
                                </div>
                                <div class="occupancy-bar-wrap">
                                    <div class="occupancy-bar-fill" style="width:<?php echo $pct; ?>%; background:<?php echo $barColor; ?>;"></div>
                                </div>
                                <div class="occupancy-label"><?php echo (int)$sp['posti'] - (int)$sp['biglietti_venduti']; ?> posti disponibili</div>
                            </div>
                        </div>

                        <div class="spett-actions">
                            <?php if (!$isPassato && !$isEsaurito): ?>
                                <a class="btn-acquista"
                                href="../biglietti/seleziona_film.php?id_spettacolo=<?php echo (int)$sp['id_spettacolo']; ?>"
                                style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:.3rem;">
                                    🎟️ Biglietto
                                </a>
                            <?php else: ?>
                                <span class="btn-acquista" style="opacity:.4; cursor:default; display:inline-flex; align-items:center; justify-content:center;">
                                    <?php echo $isPassato ? '✖ Passato' : '✖ Esaurito'; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="no-results" id="no-results" style="grid-column:1/-1;">
                    <p>Nessuno spettacolo trovato</p>
                    <span>Prova a cambiare i filtri o la ricerca</span>
                </div>

            </div>
        </main>
    </div>

    <script>
    (function () {
        const searchInput = document.getElementById('search-input');
        const searchClear = document.getElementById('search-clear');
        const noResults   = document.getElementById('no-results');
        const cards       = document.querySelectorAll('.spett-card');
        const salaBtns    = document.querySelectorAll('[data-sala]');

        let activeSala = 'tutte';
        let searchTerm = '';

        function filter() {
            let visible = 0;
            cards.forEach(card => {
                const titolo = card.dataset.titolo || '';
                const sala   = card.dataset.sala   || '';
                const data   = card.dataset.data   || '';

                const matchSearch = !searchTerm ||
                    titolo.includes(searchTerm) ||
                    sala.toLowerCase().includes(searchTerm) ||
                    data.includes(searchTerm);

                const matchSala = activeSala === 'tutte' || sala === activeSala;

                if (matchSearch && matchSala) {
                    card.style.display = '';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });
            noResults.style.display = visible === 0 ? 'flex' : 'none';
        }

        searchInput.addEventListener('input', function () {
            searchTerm = this.value.toLowerCase().trim();
            searchClear.style.display = searchTerm ? 'flex' : 'none';
            filter();
        });

        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            searchTerm = '';
            this.style.display = 'none';
            searchInput.focus();
            filter();
        });

        salaBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                salaBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeSala = this.dataset.sala;
                filter();
            });
        });

        filter();
    })();
    </script>
</body>
</html>
