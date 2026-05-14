<?php
    session_start();
    include("../database/connessione.php");

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
        spettacolo.ora_fine,
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
    <link rel="stylesheet" href="../style.css">
    <style>
    .film-card {
        display: grid;
        grid-template-columns: 180px 1fr 1fr;
        gap: 1.5rem;
        align-items: start;
        border-radius: 12px;
        padding: 1.5rem;
        margin: 2rem auto;
        max-width: 1200px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    }

    .film-poster img {
        width: 100%;
        border-radius: 8px;
        object-fit: cover;
        display: block;
        box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }

    .film-info h2 {
        font-size: 1.2rem;
        margin: 0 0 0.5rem 0;
    }

    /* ── Selezione posti ── */
    .posti-right {
        border-radius: 10px;
        padding: 1.2rem;
        border: 1px solid #333;
        background: #111;
    }

    .posti-right h3 {
        margin: 0 0 0.8rem;
        font-size: 1rem;
        color: #fff;
        text-align: center;
    }

    /* Schermo */
    .schermo {
        width: 80%;
        margin: 0 auto 1.2rem;
        padding: 6px 0;
        background: linear-gradient(to bottom, #555, #222);
        border-radius: 4px 4px 40% 40% / 4px 4px 20px 20px;
        text-align: center;
        font-size: 0.7rem;
        color: #aaa;
        letter-spacing: 2px;
    }

    /* Griglia posti */
    .griglia-posti {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        margin-bottom: 1rem;
        overflow-y: auto;
        max-height: 300px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.25) transparent;
    }

    /* Riga singola */
    .row-label {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .row-letter {
        width: 18px;
        font-size: 0.65rem;
        color: #888;
        text-align: center;
        flex-shrink: 0;
    }

    /* Corridoio centrale */
    .corridoio {
        width: 44px;
        height: 26px;
        flex-shrink: 0;
    }

    .posto-btn {
        width: 26px;
        height: 26px;
        border-radius: 4px 4px 0 0;
        border: none;
        cursor: pointer;
        font-size: 0;
        transition: transform 0.1s, background 0.15s;
    }
    .posto-btn:hover:not(.occupato) {
        transform: scale(1.15);
    }
    .posto-btn.libero      { background: #2ecc71; }
    .posto-btn.selezionato { background: #f39c12; }
    .posto-btn.occupato    { background: #555; cursor: not-allowed; }

    /* Legenda */
    .legenda {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.75rem;
        color: #ccc;
    }
    .legenda span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .legenda-box {
        width: 14px;
        height: 14px;
        border-radius: 3px;
        display: inline-block;
    }

    /* Riepilogo */
    .riepilogo {
        border-top: 1px solid #333;
        padding-top: 0.8rem;
        font-size: 0.85rem;
        color: #ccc;
        text-align: center;
    }
    .riepilogo strong {
        color: #f39c12;
    }

    /* Bottone conferma */
    .btn-conferma {
        display: block;
        width: 100%;
        margin-top: 0.8rem;
        padding: 0.6rem;
        background: #c9a84c;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-conferma:hover    { background: #d9ba5c; }
    .btn-conferma:disabled { background: #555; cursor: not-allowed; }
</style>
</head>
<body>

<main>
    <div class="container" id="film-container">

        <?php foreach ($films as $row):
            $titolo_esc    = htmlspecialchars($row['titolo'],     ENT_QUOTES, 'UTF-8');
            $genere_esc    = htmlspecialchars($row['nome_genere'],ENT_QUOTES, 'UTF-8');
            $sala_esc      = htmlspecialchars($row['nome_sala']  ?? '', ENT_QUOTES, 'UTF-8');
            $totale_posti  = (int)$row['totale_posti'];
            $colonne       = 10;
            $righe         = $totale_posti / $colonne; // 10 o 20
        ?>

        <div class="film-card">

            <!-- COLONNA 1: locandina -->
            <div class="film-poster">
                <img
                    src="../img/<?php echo htmlspecialchars($row['locandina'] ?? 'default-film.webp', ENT_QUOTES, 'UTF-8'); ?>"
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
                        <p><?php echo htmlspecialchars($row['ora_inizio']); ?> &ndash; <?php echo htmlspecialchars($row['ora_fine']); ?></p>
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

    [...sel].forEach(p => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'posti[]';
        inp.value = p;
        form.appendChild(inp);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>

</body>
</html>
