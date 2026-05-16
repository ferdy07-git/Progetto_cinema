<?php
session_start();
require "../../database/connessione.php";

if (!isset($_SESSION["user"]) || ($_SESSION["tipo"] ?? 0) !== 2) {
    header("Location: ../../homepage.php");
    exit;
}

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

// Gestione POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf"] ?? "";
    if (!hash_equals($csrf, $token)) {
        set_flash("error", "Richiesta non valida (CSRF). Riprova.");
        redirect_to_list();
    }

    $action = $_POST["action"] ?? "";

    if ($action === "delete") {
        $id = (int)($_POST["id_spettacolo"] ?? 0);
        if ($id <= 0) {
            set_flash("error", "Spettacolo non valido.");
        } else {
            // Elimina prima i biglietti collegati
            $s1 = $conn->prepare("DELETE FROM biglietto WHERE spettacolo = ?");
            if ($s1) { $s1->bind_param("i", $id); $s1->execute(); $s1->close(); }

            $s2 = $conn->prepare("DELETE FROM spettacolo WHERE id_spettacolo = ?");
            if ($s2) {
                $s2->bind_param("i", $id);
                if (!$s2->execute()) set_flash("error", "Errore durante l'eliminazione.");
                $s2->close();
            }
        }
        redirect_to_list();
    }

    if ($action === "create" || $action === "update") {
        $id          = (int)($_POST["id_spettacolo"] ?? 0);
        $id_film     = (int)($_POST["film"] ?? 0);
        $id_sala     = (int)($_POST["sala"] ?? 0);
        $data_sp     = trim($_POST["data_spettacolo"] ?? "");
        $ora_inizio  = trim($_POST["ora_inizio"] ?? "");

        if (!$id_film || !$id_sala || !$data_sp || !$ora_inizio) {
            set_flash("error", "Compila tutti i campi.");
            redirect_to_list();
        }

        if ($action === "create") {
            $stmt = $conn->prepare("INSERT INTO spettacolo (film, sala, data_spettacolo, ora_inizio) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iisss", $id_film, $id_sala, $data_sp, $ora_inizio);
                if (!$stmt->execute()) set_flash("error", "Errore durante l'aggiunta.");
                $stmt->close();
            }
        } else {
            if ($id <= 0) { set_flash("error", "Spettacolo non valido."); redirect_to_list(); }
            $stmt = $conn->prepare("UPDATE spettacolo SET film=?, sala=?, data_spettacolo=?, ora_inizio=? WHERE id_spettacolo=?");
            if ($stmt) {
                $stmt->bind_param("iisssi", $id_film, $id_sala, $data_sp, $ora_inizio, $id);
                if (!$stmt->execute()) set_flash("error", "Errore durante la modifica.");
                $stmt->close();
            }
        }
        redirect_to_list();
    }
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

$editId = (int)($_GET["edit"] ?? 0);
$addMode = (int)($_GET["add"] ?? 0) === 1;
$spettToEdit = null;
if ($editId > 0) {
    foreach ($spettacoli as $sp) {
        if ((int)$sp["id_spettacolo"] === $editId) {
            $spettToEdit = $sp;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venditore - Gestione Spettacoli</title>
    <link rel="stylesheet" href="../style/style.css?v=<?php echo time(); ?>">
    <style>
        /* ── SPETTACOLO CARD ────────────────────── */
        .spett-card {
            display: flex;
            gap: 1.25rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            transition: border-color .25s, transform .25s, box-shadow .25s;
            position: relative;
            overflow: hidden;
        }

        .spett-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: var(--radius);
            box-shadow: inset 0 0 0 1px var(--gold);
            opacity: 0;
            transition: opacity .25s;
            pointer-events: none;
        }

        .spett-card:hover { border-color: transparent; transform: translateY(-3px); box-shadow: var(--shadow-gold); }
        .spett-card:hover::after { opacity: 1; }

        .spett-poster {
            flex-shrink: 0;
            width: 90px;
            height: 135px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .spett-body {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .spett-title {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
        }

        .spett-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem .75rem;
            margin-top: .1rem;
        }

        .spett-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: .22rem .65rem;
            border-radius: 20px;
            white-space: nowrap;
        }

        .badge-gold {
            color: var(--gold);
            background: var(--gold-dim);
            border: 1px solid rgba(201,168,76,.25);
        }

        .badge-muted {
            color: var(--text-muted);
            background: var(--surface-2);
            border: 1px solid var(--border);
        }

        .badge-green {
            color: #6fcf97;
            background: rgba(111,207,151,.1);
            border: 1px solid rgba(111,207,151,.25);
        }

        .spett-occupancy {
            margin-top: auto;
        }

        .occupancy-bar-wrap {
            height: 5px;
            background: var(--surface-2);
            border-radius: 3px;
            overflow: hidden;
            margin-top: .3rem;
        }

        .occupancy-bar-fill {
            height: 100%;
            border-radius: 3px;
            transition: width .4s ease;
        }

        .occupancy-label {
            font-size: .7rem;
            color: var(--text-dim);
            margin-top: .25rem;
        }

        .spett-actions {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            justify-content: flex-start;
            flex-shrink: 0;
        }

        .spett-actions form { margin: 0; }
        .spett-actions .btn-acquista {
            width: 100px;
            font-size: .75rem;
            padding: .55rem .5rem;
            margin-top: 0;
        }

        /* ── DATE FILTER PILLS ──────────────────── */
        .date-filter {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .date-pill {
            padding: .35rem .9rem;
            border-radius: 50px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-muted);
            font-size: .78rem;
            font-weight: 500;
            cursor: pointer;
            transition: background .2s, color .2s, border-color .2s;
        }

        .date-pill:hover { background: var(--surface-2); color: var(--text); border-color: var(--border-hover); }
        .date-pill.active { background: var(--gold-dim); color: var(--gold); border-color: rgba(201,168,76,.4); font-weight: 600; }

        /* ── FORM GRID ─────────────────────────── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem 1rem;
        }

        .form-grid .full { grid-column: 1 / -1; }

        .form-label {
            display: flex;
            flex-direction: column;
            gap: .35rem;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-muted);
        }

        .form-label .search-bar,
        .form-label select {
            padding: .75rem 1rem;
            font-size: .9rem;
        }

        /* ── STATS ROW ─────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .stat-card .stat-label {
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--text-dim);
        }

        .stat-card .stat-value {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gold);
            line-height: 1.1;
        }

        .stat-card .stat-sub {
            font-size: .75rem;
            color: var(--text-muted);
        }

        @media (max-width: 700px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .full { grid-column: 1; }
            .stats-row { grid-template-columns: 1fr; }
            .spett-card { flex-wrap: wrap; }
            .spett-poster { width: 70px; height: 105px; }
            .spett-actions { flex-direction: row; flex-wrap: wrap; }
            .spett-actions .btn-acquista { width: auto; }
        }
    </style>
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
        <p>Gestisci gli spettacoli in programmazione</p>

        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:14px;">
            <a class="btn-acquista" href="./venditore.php?add=1" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                + Nuovo spettacolo
            </a>
            <?php if ($addMode || $spettToEdit): ?>
                <a class="btn-acquista" href="./venditore.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; background:var(--surface-2); color:var(--text-muted); border:1px solid var(--border);">
                    ← Torna alla lista
                </a>
            <?php endif; ?>
        </div>

        <div class="search-bar-wrapper" style="margin-top:14px;">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-input" class="search-bar" placeholder="Cerca per titolo, sala, data..." autocomplete="off">
            <button class="search-clear" id="search-clear" title="Cancella" style="display:none;">✕</button>
        </div>
    </div>

    <div class="page-layout">
        <!-- SIDEBAR sale -->
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
            <!-- STATS -->
            <?php
                $totSpett = count($spettacoli);
                $totBiglietti = array_sum(array_column($spettacoli, 'biglietti_venduti'));
                $oggi = date('Y-m-d');
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

                <!-- FORM AGGIUNTA -->
                <?php if ($addMode): ?>
                <div class="film-card" style="margin-bottom:16px; grid-column:1/-1;">
                    <div class="film-info" style="width:100%">
                        <h2 style="margin-bottom:.75rem;">Aggiungi nuovo spettacolo</h2>
                        <form method="POST" class="admin-form">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
                            <input type="hidden" name="action" value="create">
                            <div class="form-grid">
                                <label class="form-label full">
                                    Film
                                    <select class="search-bar" name="film" required>
                                        <option value="">Seleziona film</option>
                                        <?php foreach ($films as $f): ?>
                                            <option value="<?php echo (int)$f['id_film']; ?>"><?php echo htmlspecialchars($f['titolo']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-label full">
                                    Sala
                                    <select class="search-bar" name="sala" required>
                                        <option value="">Seleziona sala</option>
                                        <?php foreach ($sale as $s): ?>
                                            <option value="<?php echo (int)$s['id_sala']; ?>"><?php echo htmlspecialchars($s['nome']); ?> (<?php echo (int)$s['posti']; ?> posti)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-label">
                                    Data spettacolo
                                    <input class="search-bar" type="date" name="data_spettacolo" min="<?php echo $oggi; ?>" required>
                                </label>
                                <div></div>
                                <label class="form-label">
                                    Ora inizio
                                    <input class="search-bar" type="time" name="ora_inizio" required>
                                </label>
                            </div>
                            <div class="admin-form-actions" style="margin-top:1rem;">
                                <button class="btn-acquista" type="submit">Aggiungi spettacolo</button>
                                <a class="btn-acquista" href="./venditore.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; background:var(--surface-2); color:var(--text-muted); border:1px solid var(--border);">Annulla</a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- FORM MODIFICA -->
                <?php if ($spettToEdit): ?>
                <div class="film-card" style="margin-bottom:16px; grid-column:1/-1;">
                    <div class="film-info" style="width:100%">
                        <h2 style="margin-bottom:.75rem;">Modifica: <?php echo htmlspecialchars($spettToEdit['titolo_film']); ?> — <?php echo date('d/m/Y', strtotime($spettToEdit['data_spettacolo'])); ?></h2>
                        <form method="POST" class="admin-form">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id_spettacolo" value="<?php echo (int)$spettToEdit['id_spettacolo']; ?>">
                            <div class="form-grid">
                                <label class="form-label full">
                                    Film
                                    <select class="search-bar" name="film" required>
                                        <option value="">Seleziona film</option>
                                        <?php foreach ($films as $f): ?>
                                            <option value="<?php echo (int)$f['id_film']; ?>" <?php echo ((int)$f['id_film'] === (int)$spettToEdit['id_film']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($f['titolo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-label full">
                                    Sala
                                    <select class="search-bar" name="sala" required>
                                        <option value="">Seleziona sala</option>
                                        <?php foreach ($sale as $s): ?>
                                            <option value="<?php echo (int)$s['id_sala']; ?>" <?php echo ((int)$s['id_sala'] === (int)$spettToEdit['id_sala']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['nome']); ?> (<?php echo (int)$s['posti']; ?> posti)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-label">
                                    Data spettacolo
                                    <input class="search-bar" type="date" name="data_spettacolo" value="<?php echo htmlspecialchars($spettToEdit['data_spettacolo'], ENT_QUOTES); ?>" required>
                                </label>
                                <div></div>
                                <label class="form-label">
                                    Ora inizio
                                    <input class="search-bar" type="time" name="ora_inizio" value="<?php echo htmlspecialchars(substr($spettToEdit['ora_inizio'], 0, 5), ENT_QUOTES); ?>" required>
                                </label>
                            </div>
                            <div class="admin-form-actions" style="margin-top:1rem;">
                                <button class="btn-acquista" type="submit">Salva modifiche</button>
                                <a class="btn-acquista" href="./venditore.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; background:var(--surface-2); color:var(--text-muted); border:1px solid var(--border);">Annulla</a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- LISTA SPETTACOLI -->
                <?php foreach ($spettacoli as $sp):
                    $pct = $sp['posti'] > 0 ? round(($sp['biglietti_venduti'] / $sp['posti']) * 100) : 0;
                    $barColor = $pct >= 90 ? '#e05c5c' : ($pct >= 60 ? '#f2994a' : '#6fcf97');
                    $dataFmt = date('d/m/Y', strtotime($sp['data_spettacolo']));
                    $isPassato = $sp['data_spettacolo'] < $oggi;
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
                                <span class="spett-badge badge-muted">🕐 <?php echo substr($sp['ora_inizio'],0,5); ?> </span>
                                <span class="spett-badge badge-muted">⏱ <?php echo htmlspecialchars($sp['durata']); ?></span>
                                <?php if ($isPassato): ?>
                                    <span class="spett-badge" style="color:#e05c5c;background:rgba(224,92,92,.1);border:1px solid rgba(224,92,92,.25);">Passato</span>
                                <?php elseif ($sp['data_spettacolo'] === $oggi): ?>
                                    <span class="spett-badge badge-green">Oggi</span>
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
                            <a class="btn-acquista" href="./venditore.php?edit=<?php echo (int)$sp['id_spettacolo']; ?>" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                                Modifica
                            </a>
                            <form method="POST" onsubmit="return confirm('Eliminare questo spettacolo? Verranno eliminati anche i biglietti collegati.');">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_spettacolo" value="<?php echo (int)$sp['id_spettacolo']; ?>">
                                <button class="btn-acquista" type="submit" style="background:#e05c5c;">Elimina</button>
                            </form>
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
        const searchInput  = document.getElementById('search-input');
        const searchClear  = document.getElementById('search-clear');
        const noResults    = document.getElementById('no-results');
        const cards        = document.querySelectorAll('.spett-card');
        const salaBtns     = document.querySelectorAll('[data-sala]');

        let activeSala  = 'tutte';
        let searchTerm  = '';

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
