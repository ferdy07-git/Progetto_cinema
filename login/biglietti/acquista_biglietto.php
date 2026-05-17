<?php
    session_start();
    include("../../database/connessione.php");
    include("../../utils/password.php");
    if(login()){
        header("Location:../auth/form_accesso.php");
    }
    // Recupera dati film + spettacolo
    $id_spettacolo = (int)$_SESSION['id_spettacolo'];

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
        sala.id_sala,
        sala.nome AS nome_sala,
        sala.posti AS totale_posti
    FROM film
    INNER JOIN genere 
        ON film.genere = genere.id_genere
    LEFT JOIN spettacolo 
        ON film.id_film = spettacolo.film
    LEFT JOIN sala 
        ON spettacolo.sala = sala.id_sala
    WHERE spettacolo.id_spettacolo = $id_spettacolo
    ORDER BY film.titolo
    ";

    $result = $conn->query($query);
    $films  = [];
    while ($row = $result->fetch_assoc()) {
        $films[] = $row;
    }

    // Recupera posti già occupati per questo spettacolo
    $posti_occupati = [];
    $qp = "SELECT posto FROM biglietto WHERE spettacolo = $id_spettacolo";
    $rp = $conn->query($qp);
    while ($p = $rp->fetch_assoc()) {
        $posti_occupati[] = (int)$p['posto'];
    }
    $posti_occupati_js = json_encode($posti_occupati);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acquista biglietto</title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/style_acquista.css">
</head>
<body>

<main>
    <a href="../../homepage.php" class="auth-back-home" title="Torna alla homepage">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
    </a>
    <div class="container" id="film-container">

        <?php foreach ($films as $row):
            $titolo_esc    = htmlspecialchars($row['titolo'],     ENT_QUOTES, 'UTF-8');
            $genere_esc    = htmlspecialchars($row['nome_genere'],ENT_QUOTES, 'UTF-8');
            $sala_esc      = htmlspecialchars($row['nome_sala']  ?? '', ENT_QUOTES, 'UTF-8');
            $totale_posti  = (int)$row['totale_posti'];
            $colonne       = 10;
            $righe         = $totale_posti / $colonne;
        ?>

        <div class="film-card">

            <!-- COLONNA 1: locandina -->
            <div class="film-poster">
                <img
                    src="../../img/<?php echo htmlspecialchars($row['locandina'] ?? 'default-film.webp', ENT_QUOTES, 'UTF-8'); ?>"
                    alt="Locandina <?php echo $titolo_esc; ?>"
                    onerror="this.src='../img/default-film.webp'"
                >
            </div>

            <!-- COLONNA 2: info film -->
            <div class="film-info">
                <h2><?php echo $titolo_esc; ?></h2>
                <span class="genere"><?php echo $genere_esc; ?></span>
                <p class="trama"><?php echo htmlspecialchars($row['trama'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="durata"><?php echo htmlspecialchars($row['durata']); ?></p>

                <?php if ($row['id_spettacolo']): ?>
                    <div class="spettacolo-info">
                        <h3>Spettacolo</h3>
                        <p><?php echo htmlspecialchars($row['data_spettacolo']); ?></p>
                        <p><?php echo htmlspecialchars($row['ora_inizio']); ?></p>
                        <p><?php echo $sala_esc; ?> (<?php echo $totale_posti; ?> posti)</p>
                    </div>
                <?php else: ?>
                    <div class="spettacolo-info">
                        <h3>Nessuno spettacolo programmato</h3>
                        <p>Torna presto per gli aggiornamenti</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- COLONNA 3: selezione posti -->
            <div class="posti-right">
                <h3>🎬 Seleziona i posti</h3>

                <div class="schermo">SCHERMO</div>

                <div class="legenda">
                    <span><span class="legenda-box" style="background:#2ecc71"></span> Libero</span>
                    <span><span class="legenda-box" style="background:#f39c12"></span> Selezionato</span>
                    <span><span class="legenda-box" style="background:#555"></span> Occupato</span>
                </div>

                <div
                    class="griglia-posti"
                    id="griglia-<?php echo $row['id_spettacolo']; ?>"
                    style="grid-template-columns: 18px repeat(<?php echo $colonne; ?>, 26px);"
                    data-spettacolo="<?php echo $row['id_spettacolo']; ?>"
                    data-totale="<?php echo $totale_posti; ?>"
                    data-colonne="<?php echo $colonne; ?>"
                    data-occupati="<?php echo htmlspecialchars($posti_occupati_js, ENT_QUOTES, 'UTF-8'); ?>"
                ></div>

                <div class="riepilogo" id="riepilogo-<?php echo $row['id_spettacolo']; ?>">
                    Nessun posto selezionato
                </div>

                <button
                    class="btn-conferma"
                    id="btn-<?php echo $row['id_spettacolo']; ?>"
                    disabled
                    onclick="conferma(<?php echo $row['id_spettacolo']; ?>)"
                >
                    Conferma acquisto
                </button>
            </div>

        </div>

        <?php endforeach; ?>

    </div>
</main>

<script>
const lettere = ['A','B','C','D','E','F','G','H','I','J',
                 'K','L','M','N','O','P','Q','R','S','T'];

// Mappa: id_spettacolo => Set di numeri posto selezionati
const selezioni = {};

document.querySelectorAll('.griglia-posti').forEach(griglia => {
    const idSpettacolo = griglia.dataset.spettacolo;
    const totale       = parseInt(griglia.dataset.totale);
    const colonne      = parseInt(griglia.dataset.colonne);
    const righe        = totale / colonne;
    const occupati     = new Set(JSON.parse(griglia.dataset.occupati));

    selezioni[idSpettacolo] = new Set();

    for (let r = 0; r < righe; r++) {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row-label';

        // Label lettera
        const label = document.createElement('span');
        label.className = 'row-letter';
        label.textContent = lettere[r];
        rowDiv.appendChild(label);

        // Posti sinistra (primi 5)
        for (let c = 1; c <= colonne / 2; c++) {
            const numPosto = r * colonne + c;
            const btn = document.createElement('button');
            btn.className = 'posto-btn';
            btn.title = `${lettere[r]}${c}`;
            btn.dataset.posto = numPosto;
            btn.dataset.label = `${lettere[r]}${c}`;

            if (occupati.has(numPosto)) {
                btn.classList.add('occupato');
                btn.disabled = true;
            } else {
                btn.classList.add('libero');
                btn.addEventListener('click', () => togglePosto(btn, idSpettacolo));
            }
            rowDiv.appendChild(btn);
        }

        // Corridoio centrale
        const corridoio = document.createElement('div');
        corridoio.className = 'corridoio';
        rowDiv.appendChild(corridoio);

        // Posti destra (ultimi 5)
        for (let c = colonne / 2 + 1; c <= colonne; c++) {
            const numPosto = r * colonne + c;
            const btn = document.createElement('button');
            btn.className = 'posto-btn';
            btn.title = `${lettere[r]}${c}`;
            btn.dataset.posto = numPosto;
            btn.dataset.label = `${lettere[r]}${c}`;

            if (occupati.has(numPosto)) {
                btn.classList.add('occupato');
                btn.disabled = true;
            } else {
                btn.classList.add('libero');
                btn.addEventListener('click', () => togglePosto(btn, idSpettacolo));
            }
            rowDiv.appendChild(btn);
        }

        griglia.appendChild(rowDiv);
    }
});

function togglePosto(btn, idSpettacolo) {
    const numPosto = parseInt(btn.dataset.posto);
    const sel = selezioni[idSpettacolo];

    if (btn.classList.contains('selezionato')) {
        btn.classList.replace('selezionato', 'libero');
        sel.delete(numPosto);
    } else {
        btn.classList.replace('libero', 'selezionato');
        sel.add(numPosto);
    }
    aggiornaRiepilogo(idSpettacolo);
}

function aggiornaRiepilogo(idSpettacolo) {
    const sel         = selezioni[idSpettacolo];
    const riepilogo   = document.getElementById(`riepilogo-${idSpettacolo}`);
    const btnConferma = document.getElementById(`btn-${idSpettacolo}`);
    const colonne     = parseInt(
        document.getElementById(`griglia-${idSpettacolo}`).dataset.colonne
    );

    if (sel.size === 0) {
        riepilogo.innerHTML = 'Nessun posto selezionato';
        btnConferma.disabled = true;
        return;
    }

    const etichette = [...sel].sort((a, b) => a - b).map(n => {
        const r = Math.floor((n - 1) / colonne);
        const c = ((n - 1) % colonne) + 1;
        return `${lettere[r]}${c}`;
    });

    riepilogo.innerHTML =
        `Selezionati: <strong>${etichette.join(', ')}</strong>` +
        ` &mdash; Totale: <strong>${sel.size} posto/i</strong>`;
    btnConferma.disabled = false;
}

function conferma(idSpettacolo) {
    const sel = selezioni[idSpettacolo];
    if (sel.size === 0) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'conferma_acquisto.php';

    const inputSpettacolo = document.createElement('input');
    inputSpettacolo.type  = 'hidden';
    inputSpettacolo.name  = 'id_spettacolo';
    inputSpettacolo.value = idSpettacolo;
    form.appendChild(inputSpettacolo);

    // ✅ Manda sia il numero (per il DB) che l'etichetta (per la visualizzazione)
    [...sel].forEach(numPosto => {
        const btn = document.querySelector(`[data-posto="${numPosto}"]`);
        const label = btn ? btn.dataset.label : numPosto;

        const inpNumero = document.createElement('input');
        inpNumero.type  = 'hidden';
        inpNumero.name  = 'posti_num[]';
        inpNumero.value = numPosto;
        form.appendChild(inpNumero);

        const inpLabel = document.createElement('input');
        inpLabel.type  = 'hidden';
        inpLabel.name  = 'posti_label[]';
        inpLabel.value = label;
        form.appendChild(inpLabel);
    });

    sessionStorage.setItem('posti_selezionati_' + idSpettacolo, JSON.stringify([...sel]));

    document.body.appendChild(form);
    form.submit();
}
document.querySelectorAll('.griglia-posti').forEach(griglia => {
    const idSpettacolo = griglia.dataset.spettacolo;
    const salvati = sessionStorage.getItem('posti_selezionati_' + idSpettacolo);
    if (!salvati) return;

    JSON.parse(salvati).forEach(numPosto => {
        const btn = griglia.querySelector(`[data-posto="${numPosto}"]`);
        if (btn && btn.classList.contains('libero')) {
            btn.classList.replace('libero', 'selezionato');
            selezioni[idSpettacolo].add(numPosto);
        }
    });
    aggiornaRiepilogo(idSpettacolo);
});
</script>

</body>
</html>
