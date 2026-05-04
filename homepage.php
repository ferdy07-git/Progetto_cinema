<?php
    session_start();
    include("./database/connessione.php");

    $query = "
    SELECT 
        film.id_film,
        film.titolo,
        film.trama,
        film.durata,
        film.locandina,
        genere.nome AS nome_genere,
        spettacolo.id_spettacolo,
        spettacolo.data_spettacolo,
        spettacolo.ora_inizio,
        spettacolo.ora_fine,
        sala.nome AS nome_sala
    FROM film
    INNER JOIN genere 
        ON film.genere = genere.id_genere
    LEFT JOIN spettacolo 
        ON film.id_film = spettacolo.film
    LEFT JOIN sala 
        ON spettacolo.sala = sala.id_sala
    ORDER BY film.titolo
    ";

    $result = $conn->query($query);
    if ($result === false) {
        die("Errore nel caricamento dei film: " . htmlspecialchars($conn->error));
    }

    $films  = [];
    $generi = [];
    $sale   = [];

    while ($row = $result->fetch_assoc()) {
        $films[] = $row;

        $g = htmlspecialchars($row['nome_genere']);
        if (!in_array($g, $generi)) {
            $generi[] = $g;
        }

        if (!empty($row['nome_sala'])) {
            $s = htmlspecialchars($row['nome_sala']);
            if (!in_array($s, $sale)) {
                $sale[] = $s;
            }
        }
    }

    sort($generi);
    sort($sale);

    // Dati utente dalla sessione
    $nomeUtente  = htmlspecialchars($_SESSION['user']  ?? 'Utente');
    $emailUtente = htmlspecialchars($_SESSION['email'] ?? '');
    $iniziali    = strtoupper(substr($_SESSION['user'] ?? 'U', 0, 1));
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Palladino</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="page-home">

    <header class="site-header">
        <h1>Cinema Palladino</h1>
        <p class="site-tagline">La magia del grande schermo</p>

        <?php if (!isset($_SESSION['user'])): ?>
            <a class='btn-accedi' href='./login/accesso.html'>
                <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='8' r='5'/><path d='M3 21a9 9 0 0 1 18 0'/></svg>
                Accedi
            </a>
        <?php else: ?>
            <div class='profile-menu'>
                <label for='toggle-menu' class='avatar-btn'><?php echo $iniziali; ?></label>
                <input type='checkbox' id='toggle-menu'>

                <div class='dropdown'>
                    <div class='user-info'>
                        <span class='name'><?php echo $nomeUtente; ?></span>
                        <span class='email'><?php echo $emailUtente; ?></span>
                    </div>

                    <a href='#' class='menu-link'><span>🎫</span> Visualizza biglietti</a>
                    <a href='#' class='menu-link'><span>🔑</span> Modifica password</a>
                    <a href='./login/logout.php' class='menu-link logout'><span>👋</span> Esci</a>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <div class="hero-strip">
        <h2>Film in <span>programmazione</span></h2>
        <p>Scegli il tuo spettacolo e acquista il biglietto</p>

        <div class="search-bar-wrapper">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
                type="text"
                id="search-input"
                class="search-bar"
                placeholder="Cerca un film per titolo o trama..."
                autocomplete="off"
            >
            <button class="search-clear" id="search-clear" title="Cancella ricerca" style="display:none;">✕</button>
        </div>

        <div class="sala-filter">
            <span class="sala-filter__label">Filtra per sala</span>
            <div class="sala-filter__buttons">
                <button class="sala-btn active" data-sala="tutte">Tutte le sale</button>
                <?php foreach ($sale as $s): ?>
                <button class="sala-btn" data-sala="<?php echo htmlspecialchars($s, ENT_QUOTES); ?>">
                    <?php echo htmlspecialchars($s); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="page-layout">

        <!-- SIDEBAR GENERI -->
        <aside class="sidebar-generi">
            <h3 class="sidebar-title">Generi</h3>
            <ul class="genere-list">
                <li>
                    <button class="genere-btn active" data-genere="tutti">
                        <span class="genere-dot"></span>
                        Tutti i film
                    </button>
                </li>
                <?php foreach ($generi as $g): ?>
                <li>
                    <button class="genere-btn" data-genere="<?php echo htmlspecialchars($g, ENT_QUOTES); ?>">
                        <span class="genere-dot"></span>
                        <?php echo htmlspecialchars($g); ?>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main>
            <div class="risultati-info" id="risultati-info"></div>

            <div class="container" id="film-container">

                <?php foreach ($films as $row):
                    $titolo_esc = htmlspecialchars($row['titolo'], ENT_QUOTES, 'UTF-8');
                    $genere_esc = htmlspecialchars($row['nome_genere'], ENT_QUOTES, 'UTF-8');
                    $sala_esc   = htmlspecialchars($row['nome_sala'] ?? '', ENT_QUOTES, 'UTF-8');
                ?>

                    <div class="film-card"
                         data-titolo="<?php echo strtolower($titolo_esc); ?>"
                         data-trama="<?php echo strtolower(htmlspecialchars($row['trama'], ENT_QUOTES, 'UTF-8')); ?>"
                         data-genere="<?php echo $genere_esc; ?>"
                         data-sala="<?php echo $sala_esc; ?>">

                        <img
                            src="img/<?php echo htmlspecialchars($row['locandina'] ?? 'default-film.webp'); ?>"
                            alt="Locandina <?php echo $titolo_esc; ?>"
                            onerror="this.src='img/default-film.webp'"
                        >

                        <div class="film-info">

                            <h2><?php echo $titolo_esc; ?></h2>

                            <p class="genere"><?php echo htmlspecialchars($row['nome_genere']); ?></p>

                            <p class="trama"><?php echo htmlspecialchars($row['trama']); ?></p>

                            <p class="durata">Durata: <?php echo htmlspecialchars($row['durata']); ?></p>

                            <?php if ($row['id_spettacolo']): ?>
                                <div class="spettacolo-info">
                                    <h3>Spettacolo disponibile</h3>
                                    <p>Data: <?php echo htmlspecialchars($row['data_spettacolo']); ?></p>
                                    <p>Ore <?php echo htmlspecialchars($row['ora_inizio']); ?> &ndash; <?php echo htmlspecialchars($row['ora_fine']); ?></p>
                                    <p><?php echo htmlspecialchars($row['nome_sala']); ?></p>
                                </div>

                                <a class="btn-acquista" href="acquista_biglietto.php?id_spettacolo=<?php echo (int)$row['id_spettacolo']; ?>">
                                    Acquista Biglietto
                                </a>

                            <?php else: ?>
                                <div class="spettacolo-info">
                                    <h3>Nessuno spettacolo programmato</h3>
                                    <p>Torna presto per gli aggiornamenti</p>
                                </div>

                                <a class="btn-acquista" aria-disabled="true">Non disponibile</a>
                            <?php endif; ?>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

            <div class="no-results" id="no-results" style="display:none;">
                <p>🎬 Nessun film trovato</p>
                <span>Prova con un altro termine o cambia filtro</span>
            </div>
        </main>

    </div>

    <script>
    (function () {
        const searchInput   = document.getElementById('search-input');
        const searchClear   = document.getElementById('search-clear');
        const cards         = document.querySelectorAll('.film-card');
        const noResults     = document.getElementById('no-results');
        const risultatiInfo = document.getElementById('risultati-info');
        const genereBtns    = document.querySelectorAll('.genere-btn');
        const salaBtns      = document.querySelectorAll('.sala-btn');

        let activeGenere = 'tutti';
        let activeSala   = 'tutte';
        let searchTerm   = '';

        function filter() {
            let visible = 0;
            cards.forEach(card => {
                const titolo = card.dataset.titolo || '';
                const trama  = card.dataset.trama  || '';
                const genere = card.dataset.genere || '';
                const sala   = card.dataset.sala   || '';

                const matchSearch = !searchTerm ||
                    titolo.includes(searchTerm) ||
                    trama.includes(searchTerm);

                const matchGenere = activeGenere === 'tutti' ||
                    genere === activeGenere;

                const matchSala = activeSala === 'tutte' ||
                    sala === activeSala;

                if (matchSearch && matchGenere && matchSala) {
                    card.style.display = '';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });

            noResults.style.display = visible === 0 ? 'flex' : 'none';
            risultatiInfo.textContent = visible > 0
                ? visible + (visible === 1 ? ' film trovato' : ' film trovati')
                : '';
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
