<?php
    session_start();
    include("./database/connessione.php");
    // locandine film
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
    if(isset($_SESSION["user"])){
    // Dati utente dalla sessione
    $nome  = htmlspecialchars($_SESSION['user']  ?? 'Utente');
    $email = htmlspecialchars($_SESSION['email'] ?? '');
    $iniziali = strtoupper(substr($_SESSION['user'] ?? 'U', 0, 1));
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Itis "Luigi di Maggio"</title>
    <link rel="stylesheet" href="./login/style/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-home">

    <div class="hero-strip">
        <div class="hero-topbar">
            <h1 class="hero-site-title">Itis "Luigi di Maggio"</h1>

            
            <?php if (!isset($_SESSION['user'])): ?>
                <a class='btn-accedi' href='./login/auth/form_accesso.php'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='8' r='5'/><path d='M3 21a9 9 0 0 1 18 0'/></svg>
                    Accedi
                </a>
            <?php else: ?>
                <div class='profile-menu'>
                    <label for='toggle-menu' class='avatar-btn'><?php echo $iniziali; ?></label>
                    <input type='checkbox' id='toggle-menu'>

                    <div class='dropdown'>
                        <div class='user-info'>
                            <span class='name'><?php echo $nome; ?></span>
                            <span class='email'><?php echo $email; ?></span>
                        </div>

                        <?php 
                            $sql = "SELECT tipo FROM utente WHERE nome = '$nome'";
                            $res = $conn->query($sql)->fetch_assoc()["tipo"];
                            switch($res){
                                case 1:
                                    print"<a href='#' class='menu-link'><span>🎫</span> Visualizza biglietti</a>
                                          <a href='./login/auth/recupera_password.html' class='menu-link'><span>🔑</span> Modifica password</a>";
                                break;
                                case 2:
                                    print "";
                                    break;
                                case 3:
                                    print"<a href='./login/admin/modifica.php' class='menu-link'><span>🛠️</span> Pannello admin</a>";
                                    break; 
                            }
                        ?>    
                        <a href='./login/auth/logout.php' class='menu-link logout'><span>👋</span> Esci</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <h2 class="hero-title">Film in <span>programmazione</span></h2>
        <p>Scegli il tuo spettacolo e acquista il biglietto</p>
 
        <a href="https://wa.me/393515793820" target="_blank" style="
            display:inline-flex;
            align-items:center;
            gap:10px;
            margin-top:16px;
            padding:12px 28px;
            background:#25D366;
            color:white;
            font-size:17px;
            font-weight:600;
            border-radius:30px;
            text-decoration:none;
            box-shadow:0 4px 15px rgba(37,211,102,0.4);
            transition:transform 0.2s, box-shadow 0.2s;
            font-family:Arial, sans-serif;
        "
        onmouseover="this.style.transform='scale(1.07)';this.style.boxShadow='0 6px 20px rgba(37,211,102,0.55)'"
        onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 15px rgba(37,211,102,0.4)'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 32 32" fill="white">
                <path d="M16 2C8.268 2 2 8.268 2 16c0 2.478.643 4.801 1.766 6.82L2 30l7.38-1.734A13.94 13.94 0 0 0 16 30c7.732 0 14-6.268 14-14S23.732 2 16 2zm0 25.6a11.547 11.547 0 0 1-5.89-1.608l-.422-.25-4.38 1.03 1.062-4.268-.276-.44A11.56 11.56 0 0 1 4.4 16C4.4 9.59 9.59 4.4 16 4.4S27.6 9.59 27.6 16 22.41 27.6 16 27.6zm6.34-8.62c-.347-.174-2.055-1.014-2.374-1.13-.318-.116-.55-.174-.78.174-.232.347-.896 1.13-1.1 1.362-.202.232-.404.26-.75.087-.347-.174-1.464-.54-2.788-1.72-1.03-.92-1.726-2.055-1.928-2.402-.202-.347-.022-.535.152-.708.156-.155.347-.405.52-.608.174-.202.232-.347.347-.578.116-.232.058-.434-.029-.608-.087-.174-.78-1.882-1.07-2.578-.282-.676-.568-.584-.78-.594l-.664-.012c-.232 0-.608.087-.926.434-.318.347-1.214 1.187-1.214 2.895s1.243 3.357 1.416 3.59c.174.232 2.447 3.733 5.928 5.235.828.358 1.474.572 1.977.732.83.264 1.587.227 2.184.138.666-.1 2.055-.84 2.345-1.652.29-.812.29-1.508.202-1.652-.086-.144-.318-.232-.664-.405z"/>
            </svg>
            Contattaci su WhatsApp
        </a>

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
                        <button class="genere-btn" data-genere="<?php echo htmlspecialchars($g, ENT_QUOTES); ?>">
                            <span class="genere-dot"></span>
                            <?php echo htmlspecialchars($g); ?>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main>
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

                                 <a class="btn-acquista" href="./login/biglietti/seleziona_film.php?id_spettacolo=<?php echo (int)$row['id_spettacolo']; ?>">
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

            noResults.style.display = visible === 0 ? 'flex' : 'none';

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
