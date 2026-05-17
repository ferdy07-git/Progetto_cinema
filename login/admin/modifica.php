<?php
session_start();
require "../../database/connessione.php";

if (!isset($_SESSION["user"]) || ($_SESSION["tipo"] ?? 0) !== 3) {
    header("Location: ../../homepage.php");
    exit;
}

if (!isset($_SESSION["csrf_admin"])) {
    $_SESSION["csrf_admin"] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION["csrf_admin"];

// Flash message (persistente dopo redirect)
$flash = $_SESSION["flash_admin"] ?? ["type" => "", "text" => ""];
unset($_SESSION["flash_admin"]);

function set_flash_session(string $type, string $text): void {
    $_SESSION["flash_admin"] = ["type" => $type, "text" => $text];
}

function redirect_to_list(): void {
    if (!headers_sent()) {
        header("Location:./modifica.php");
    } else {
        echo "<script>window.location.href='./modifica.php';</script>";
    }
    exit;
}

// Carica generi per select
$generi = [];
$rGen = $conn->query("SELECT id_genere, nome FROM genere ORDER BY nome");
if ($rGen !== false) {
    while ($g = $rGen->fetch_assoc()) {
        $generi[] = $g;
    }
}

// Carica sale per il form spettacolo
$sale = [];
$rSala = $conn->query("SELECT id_sala, nome, posti FROM sala ORDER BY nome");
if ($rSala) {
    while ($s = $rSala->fetch_assoc()) $sale[] = $s;
}

// Gestione azioni (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf"] ?? "";
    if (!hash_equals($csrf, $token)) {
        set_flash_session("error", "Richiesta non valida (CSRF). Riprova.");
        redirect_to_list();
    } else {
        $action = $_POST["action"] ?? "";

        if ($action === "delete") {
            $id = (int)($_POST["id_film"] ?? 0);
            if ($id <= 0) {
                set_flash_session("error", "Film non valido.");
                redirect_to_list();
            } else {
                $ok1 = true;
                $stmt1 = $conn->prepare("DELETE FROM spettacolo WHERE film = ?");
                if ($stmt1) {
                    $stmt1->bind_param("i", $id);
                    $ok1 = $stmt1->execute();
                    $stmt1->close();
                }

                $ok2 = false;
                $stmt2 = $conn->prepare("DELETE FROM film WHERE id_film = ?");
                if ($stmt2) {
                    $stmt2->bind_param("i", $id);
                    $ok2 = $stmt2->execute();
                    $stmt2->close();
                }

                if (!($ok1 && $ok2)) {
                    set_flash_session("error", "Errore durante l'eliminazione del film.");
                }
                redirect_to_list();
            }
        }

        if ($action === "update") {
            $id = (int)($_POST["id_film"] ?? 0);
            $titolo = trim((string)($_POST["titolo"] ?? ""));
            $trama = trim((string)($_POST["trama"] ?? ""));
            $durata = trim((string)($_POST["durata"] ?? ""));
            $locandina = trim((string)($_POST["locandina"] ?? ""));
            $id_genere = (int)($_POST["genere"] ?? 0);

            if ($id <= 0 || $titolo === "" || $trama === "" || $durata === "" || $locandina === "" || $id_genere <= 0) {
                set_flash_session("error", "Compila tutti i campi per modificare il film.");
                redirect_to_list();
            } else {
                $stmt = $conn->prepare("UPDATE film SET genere = ?, titolo = ?, trama = ?, durata = ?, locandina = ? WHERE id_film = ?");
                if ($stmt) {
                    $stmt->bind_param("issssi", $id_genere, $titolo, $trama, $durata, $locandina, $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    if (!$ok) {
                        set_flash_session("error", "Errore durante la modifica del film.");
                    }
                } else {
                    set_flash_session("error", "Errore durante la preparazione della modifica.");
                }
                redirect_to_list();
            }
        }

        if ($action === "create") {
            $titolo = trim((string)($_POST["titolo"] ?? ""));
            $trama = trim((string)($_POST["trama"] ?? ""));
            $durata = trim((string)($_POST["durata"] ?? ""));
            $locandina = trim((string)($_POST["locandina"] ?? ""));
            $id_genere = (int)($_POST["genere"] ?? 0);

            if ($titolo === "" || $trama === "" || $durata === "" || $locandina === "" || $id_genere <= 0) {
                set_flash_session("error", "Compila tutti i campi per aggiungere il film.");
                redirect_to_list();
            } else {
                $stmt = $conn->prepare("INSERT INTO film (genere, titolo, trama, durata, locandina) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issss", $id_genere, $titolo, $trama, $durata, $locandina);
                    $ok = $stmt->execute();
                    $stmt->close();
                    if (!$ok) {
                        set_flash_session("error", "Errore durante l'aggiunta del film.");
                    }
                } else {
                    set_flash_session("error", "Errore durante la preparazione dell'aggiunta.");
                }
                redirect_to_list();
            }
        }

        if ($action === "create_spettacolo") {
            $id_film    = (int)($_POST["film"] ?? 0);
            $id_sala    = (int)($_POST["sala"] ?? 0);
            $data_sp    = trim($_POST["data_spettacolo"] ?? "");
            $ora_inizio = trim($_POST["ora_inizio"] ?? "");

            if (!$id_film || !$id_sala || !$data_sp || !$ora_inizio) {
                set_flash_session("error", "Compila tutti i campi per aggiungere lo spettacolo.");
                redirect_to_list();
            } else {
                $stmt = $conn->prepare("INSERT INTO spettacolo (film, sala, data_spettacolo, ora_inizio) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("iiss", $id_film, $id_sala, $data_sp, $ora_inizio);
                    $ok = $stmt->execute();
                    $stmt->close();
                    if (!$ok) {
                        set_flash_session("error", "Errore durante l'aggiunta dello spettacolo.");
                    }
                } else {
                    set_flash_session("error", "Errore durante la preparazione dello spettacolo.");
                }
                redirect_to_list();
            }
        }
    }
}

// Film in elenco
$query = "
SELECT
    film.id_film,
    film.titolo,
    film.trama,
    film.durata,
    film.locandina,
    genere.nome AS nome_genere,
    film.genere AS id_genere
FROM film
INNER JOIN genere ON film.genere = genere.id_genere
ORDER BY film.titolo
";
$result = $conn->query($query);
if ($result === false) {
    die("Errore nel caricamento dei film: " . htmlspecialchars($conn->error));
}

$films = [];
while ($row = $result->fetch_assoc()) {
    $films[] = $row;
}

// Dati utente dalla sessione
$nomeUtente  = htmlspecialchars($_SESSION['user']  ?? 'Admin');
$emailUtente = htmlspecialchars($_SESSION['email'] ?? '');
$iniziali    = strtoupper(substr($_SESSION['user'] ?? 'A', 0, 1));

$editId = (int)($_GET["edit"] ?? 0);
$addMode = (int)($_GET["add"] ?? 0) === 1;
$addSpettMode = (int)($_GET["add_spettacolo"] ?? 0) === 1;
$filmToEdit = null;
if ($editId > 0) {
    foreach ($films as $f) {
        if ((int)$f["id_film"] === $editId) {
            $filmToEdit = $f;
            break;
        }
    }
}

$oggi = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestione Film</title>
    <link rel="stylesheet" href="../style/style.css?v=<?php echo time(); ?>">
    <style>
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
        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .full { grid-column: 1; }
        }
    </style>
</head>
<body class="page-home">

    <div class="hero-strip">
        <div class="hero-topbar">
            <h1 class="hero-site-title">Itis "Luigi di Maggio"</h1>

            <div class='profile-menu'>
                <label for='toggle-menu' class='avatar-btn'><?php echo $iniziali; ?></label>
                <input type='checkbox' id='toggle-menu'>

                <div class='dropdown'>
                    <div class='user-info'>
                        <span class='name'><?php echo $nomeUtente; ?></span>
                        <span class='email'><?php echo $emailUtente; ?></span>
                    </div>

                    <a href='../../homepage.php' class='menu-link'><span>🏠</span> Vai alla home</a>
                    <a href='../auth/logout.php' class='menu-link logout'><span>👋</span> Esci</a>
                </div>
            </div>
        </div>

        <h2 class="hero-title">Pannello <span>Admin</span></h2>
        <p>Modifica o elimina i film in programmazione</p>

        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:14px;">
            <a class="btn-acquista" href="./modifica.php?add=1" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                + Aggiungi film
            </a>
            <a class="btn-acquista" href="./modifica.php?add_spettacolo=1" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                🎬 Aggiungi spettacolo
            </a>
            <?php if ($addMode || $addSpettMode || $filmToEdit): ?>
                <a class="btn-acquista" href="./modifica.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                    ← Torna alla lista
                </a>
            <?php endif; ?>
        </div>

        <div class="search-bar-wrapper" style="margin-top: 14px;">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
                type="text"
                id="search-input"
                class="search-bar"
                placeholder="Cerca un film per titolo..."
                autocomplete="off"
            >
            <button class="search-clear" id="search-clear" title="Cancella ricerca" style="display:none;">✕</button>
        </div>
    </div>

    <div class="page-layout">
        <aside class="sidebar-generi">
            <h3 class="sidebar-title">Generi</h3>
            <div class="genere-list-wrapper">
                <ul class="genere-list">
                    <li>
                        <button class="genere-btn active" data-genere="tutti">
                            <span class="genere-dot"></span>
                            Tutti i film
                        </button>
                    </li>
                    <?php foreach ($generi as $g): ?>
                    <li>
                        <button class="genere-btn" data-genere="<?php echo htmlspecialchars($g['nome'], ENT_QUOTES); ?>">
                            <span class="genere-dot"></span>
                            <?php echo htmlspecialchars($g['nome']); ?>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
        <main style="width:100%">
            <div class="container" id="film-container">

                <?php if (($flash["type"] ?? "") === "error" && $flash["text"]): ?>
                    <div class="no-results" style="display:flex; margin: 0 0 16px 0;">
                        <p>⚠️ <?php echo htmlspecialchars($flash["text"]); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($addSpettMode): ?>
                    <div class="film-card" style="margin-bottom:16px;">
                        <div class="film-info" style="width:100%">
                            <h2>🎬 Aggiungi nuovo spettacolo</h2>
                            <p style="font-size:.85rem; color:var(--text-muted); margin:.25rem 0 .75rem;">Associa un film a una sala con data e orario.</p>

                            <form method="POST" class="admin-form">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
                                <input type="hidden" name="action" value="create_spettacolo">

                                <div class="form-grid">
                                    <label class="form-label full">
                                        Film
                                        <select class="search-bar" name="film" required>
                                            <option value="">Seleziona film</option>
                                            <?php foreach ($films as $f): ?>
                                                <option value="<?php echo (int)$f['id_film']; ?>">
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
                                                <option value="<?php echo (int)$s['id_sala']; ?>">
                                                    <?php echo htmlspecialchars($s['nome']); ?> (<?php echo (int)$s['posti']; ?> posti)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>

                                    <label class="form-label">
                                        Data spettacolo
                                        <input class="search-bar" type="date" name="data_spettacolo" min="<?php echo $oggi; ?>" required>
                                    </label>

                                    <label class="form-label">
                                        Ora inizio
                                        <input class="search-bar" type="time" name="ora_inizio" required>
                                    </label>
                                </div>

                                <div class="admin-form-actions" style="margin-top:1rem;">
                                    <button class="btn-acquista" type="submit">Aggiungi spettacolo</button>
                                    <a class="btn-acquista" href="./modifica.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Annulla</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($addMode): ?>
                    <div class="film-card" style="margin-bottom:16px;">
                        <div class="film-info">
                            <h2>Aggiungi nuovo film</h2>

                            <form method="POST" class="admin-form" style="display:grid; gap:10px; margin-top:10px;">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
                                <input type="hidden" name="action" value="create">

                                <label>
                                    Titolo
                                    <input class="search-bar" name="titolo" required>
                                </label>

                                <label>
                                    Trama
                                    <textarea class="admin-textarea" name="trama" rows="6" required></textarea>
                                </label>

                                <label>
                                    Durata
                                    <input class="search-bar" name="durata" placeholder="es. 1h 45m" required>
                                </label>

                                <label>
                                    Locandina (nome file in `img/`)
                                    <input class="search-bar" name="locandina" placeholder="es. film.webp" required>
                                </label>

                                <label>
                                    Genere
                                    <select class="search-bar" name="genere" required>
                                        <option value="">Seleziona genere</option>
                                        <?php foreach ($generi as $g): ?>
                                            <option value="<?php echo (int)$g["id_genere"]; ?>">
                                                <?php echo htmlspecialchars($g["nome"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <div class="admin-form-actions">
                                    <button class="btn-acquista" type="submit">Aggiungi</button>
                                    <a class="btn-acquista" href="./modifica.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Annulla</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($filmToEdit): ?>
                    <div class="film-card" style="margin-bottom:16px;">
                        <div class="film-info">
                            <h2>Modifica: <?php echo htmlspecialchars($filmToEdit["titolo"]); ?></h2>

                            <form method="POST" class="admin-form" style="display:grid; gap:10px; margin-top:10px;">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id_film" value="<?php echo (int)$filmToEdit["id_film"]; ?>">

                                <label>
                                    Titolo
                                    <input class="search-bar" name="titolo" value="<?php echo htmlspecialchars($filmToEdit["titolo"], ENT_QUOTES); ?>" required>
                                </label>

                                <label>
                                    Trama
                                    <textarea class="admin-textarea" name="trama" rows="6" required><?php echo htmlspecialchars($filmToEdit["trama"]); ?></textarea>
                                </label>

                                <label>
                                    Durata
                                    <input class="search-bar" name="durata" value="<?php echo htmlspecialchars($filmToEdit["durata"], ENT_QUOTES); ?>" required>
                                </label>

                                <label>
                                    Locandina (nome file in `img/`)
                                    <input class="search-bar" name="locandina" value="<?php echo htmlspecialchars($filmToEdit["locandina"], ENT_QUOTES); ?>" required>
                                </label>

                                <label>
                                    Genere
                                    <select class="search-bar" name="genere" required>
                                        <option value="">Seleziona genere</option>
                                        <?php foreach ($generi as $g): ?>
                                            <option value="<?php echo (int)$g["id_genere"]; ?>" <?php echo ((int)$g["id_genere"] === (int)$filmToEdit["id_genere"]) ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($g["nome"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <div class="admin-form-actions">
                                    <button class="btn-acquista" type="submit">Salva modifiche</button>
                                    <a class="btn-acquista" href="./modifica.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Annulla</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ($films as $row):
                    $id = (int)$row["id_film"];
                    $titolo_esc = htmlspecialchars($row['titolo'], ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="film-card admin-film-card"
                    data-titolo="<?php echo strtolower(htmlspecialchars($row['titolo'], ENT_QUOTES, 'UTF-8')); ?>"
                    data-genere="<?php echo htmlspecialchars($row['nome_genere'], ENT_QUOTES, 'UTF-8'); ?>">
                        <img
                            src="../../img/<?php echo htmlspecialchars($row['locandina'] ?? 'default-film.webp'); ?>"
                            alt="Locandina <?php echo $titolo_esc; ?>"
                            onerror="this.src='../../img/default-film.webp'"
                        >
                        <div class="film-info">
                            <h2><?php echo $titolo_esc; ?></h2>
                            <p class="genere"><?php echo htmlspecialchars($row['nome_genere']); ?></p>
                            <p class="trama"><?php echo htmlspecialchars($row['trama']); ?></p>
                            <p class="durata">Durata: <?php echo htmlspecialchars($row['durata']); ?></p>
                        </div>
                        <div class="admin-actions">
                            <a class="btn-acquista" href="./modifica.php?edit=<?php echo $id; ?>" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                                Modifica
                            </a>

                            <form method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo film?');">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_film" value="<?php echo $id; ?>">
                                <button class="btn-acquista" type="submit">Elimina</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </main>
    </div>

    <script>
    (function () {
        const searchInput = document.getElementById('search-input');
        const searchClear = document.getElementById('search-clear');
        const cards = document.querySelectorAll('.film-card');
        const noResults = document.getElementById('no-results');
        const risultatiInfo = document.getElementById('risultati-info');
        const genereBtns = document.querySelectorAll('.genere-btn');
        const salaBtns = document.querySelectorAll('.sala-btn');

        let activeGenere = 'tutti';
        let activeSala = 'tutte';
        let searchTerm = '';

        function filter() {
            let visible = 0;

            cards.forEach(card => {
                const titolo = card.dataset.titolo || '';
                const trama = card.dataset.trama || '';
                const genere = card.dataset.genere || '';
                const sala = card.dataset.sala || '';

                const matchSearch =
                    !searchTerm ||
                    titolo.includes(searchTerm) ||
                    trama.includes(searchTerm);

                const matchGenere =
                    activeGenere === 'tutti' ||
                    genere === activeGenere;

                const matchSala =
                    activeSala === 'tutte' ||
                    sala === activeSala;

                if (matchSearch && matchGenere && matchSala) {
                    card.style.display = '';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (noResults) noResults.style.display = visible === 0 ? 'flex' : 'none';

            if (risultatiInfo) {
                risultatiInfo.textContent = visible > 0
                    ? visible + (visible === 1 ? ' film trovato' : ' film trovati')
                    : '';
            }
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

        genereBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                genereBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeGenere = this.dataset.genere;
                filter();
            });
        });

        salaBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                salaBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeSala = this.dataset.sala;
                filter();
            });
        });

    })();
    </script>
</body>
</html>
