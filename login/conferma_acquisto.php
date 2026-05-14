<?php
session_start();
include("../database/connessione.php");

$id_spettacolo = isset($_POST['id_spettacolo']) ? intval($_POST['id_spettacolo']) : 0;
$posti_num     = isset($_POST['posti_num'])   && is_array($_POST['posti_num'])   ? $_POST['posti_num']   : [];
$posti_label   = isset($_POST['posti_label']) && is_array($_POST['posti_label']) ? $_POST['posti_label'] : [];

if ($id_spettacolo === 0 || empty($posti_num)) {
    header('Location: index.php?errore=dati_mancanti');
    exit;
}

$posti_sanitizzati       = array_map('intval', $posti_num);
$posti_label_sanitizzati = array_map('htmlspecialchars', $posti_label);

$_SESSION['posti_selezionati'] = $posti_sanitizzati;
$_SESSION['id_spettacolo']     = $id_spettacolo;

$sql = "
    SELECT
        film.titolo,
        spettacolo.data_spettacolo,
        spettacolo.ora_inizio,
        sala.nome AS nome_sala
    FROM film
    LEFT JOIN spettacolo ON film.id_film = spettacolo.film
    LEFT JOIN sala       ON spettacolo.sala = sala.id_sala
    WHERE spettacolo.id_spettacolo = $id_spettacolo
";

$result = $conn->query($sql);
$row    = $result->fetch_assoc();

if (!$row) {
    header('Location: index.php?errore=spettacolo_non_trovato');
    exit;
}

$nome_film       = $row['titolo'];
$data_spettacolo = date('d/m/Y', strtotime($row['data_spettacolo']));
$ora_spettacolo  = substr($row['ora_inizio'], 0, 5);
$nome_sala       = $row['nome_sala'];
$prezzo_unitario = 9.50;

$num_posti = count($posti_sanitizzati);
$totale    = $num_posti * $prezzo_unitario;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Acquisto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="./style_conferma.css">
    
</head>
<body>
    <div class="confirm-page">

        <a href="acquista_biglietto.php" class="confirm-back" title="Torna alla selezione">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </a>

        <a href="index.php" class="confirm-brand">🎬 Cinema Palladino</a>

        <div class="confirm-card">

            <div class="confirm-card__header">
                <div class="confirm-card__eyebrow">Riepilogo ordine</div>
                <h1 class="confirm-card__title"><?= htmlspecialchars($nome_film) ?></h1>
            </div>

            <div class="confirm-card__body">

                <div class="detail-block">
                    <div class="detail-block__label">🎬 Dettagli spettacolo</div>
                    <div class="detail-row">
                        <span class="icon">📅</span>
                        <span>Data</span>
                        <strong><?= htmlspecialchars($data_spettacolo) ?></strong>
                    </div>
                    <div class="detail-row">
                        <span class="icon">🕐</span>
                        <span>Orario</span>
                        <strong><?= htmlspecialchars($ora_spettacolo) ?></strong>
                    </div>
                    <div class="detail-row">
                        <span class="icon">🏛️</span>
                        <span>Sala</span>
                        <strong><?= htmlspecialchars($nome_sala) ?></strong>
                    </div>
                </div>

                <div class="seats-block">
                    <div class="seats-block__label">
                        💺 Posti selezionati
                        <span style="color:var(--text-dim);font-weight:400;font-size:.65rem;margin-left:auto;">
                            <?= $num_posti ?> <?= $num_posti === 1 ? 'posto' : 'posti' ?>
                        </span>
                    </div>
                    <div class="seats-grid">
                        <?php foreach ($posti_label_sanitizzati as $label): ?>
                            <span class="seat-chip"><?= $label ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="price-block">
                    <div class="price-block__left">
                        <span class="price-block__desc">Totale da pagare</span>
                        <span class="price-block__detail">
                            <?= $num_posti ?> × €<?= number_format($prezzo_unitario, 2, ',', '.') ?>
                        </span>
                    </div>
                    <span class="price-block__total">€<?= number_format($totale, 2, ',', '.') ?></span>
                </div>

            </div>

            <div class="confirm-card__footer">
                <form method="POST" action="acquisto_definitivo.php">
                    <input type="hidden" name="id_spettacolo" value="<?= $id_spettacolo ?>">
                    <?php foreach ($posti_sanitizzati as $i => $num): ?>
                        <input type="hidden" name="posti_num[]"   value="<?= $num ?>">
                        <input type="hidden" name="posti_label[]" value="<?= htmlspecialchars($posti_label_sanitizzati[$i]) ?>">
                    <?php endforeach; ?>
                    <button type="submit" class="btn-confirm">✓ Conferma e Paga</button>
                </form>
                <a href="acquista_biglietto.php" class="btn-annulla">Annulla</a>
            </div>

        </div>

    </div>
</body>
</html>
```