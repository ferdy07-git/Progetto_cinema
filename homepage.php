<?php
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
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Palladino</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body class="page-home">

    <header class="site-header">
        <h1>Cinema Palladino</h1>
        <p class="site-tagline">La magia del grande schermo</p>
    </header>

    <div class="hero-strip">
        <h2>Film in <span>programmazione</span></h2>
        <p>Scegli il tuo spettacolo e acquista il biglietto</p>
    </div>

    <div class="container">

        <?php while ($row = $result->fetch_assoc()) {
            $titolo_esc = htmlspecialchars($row['titolo'], ENT_QUOTES, 'UTF-8');
        ?>

            <div class="film-card">
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
                            <p>Sala: <?php echo htmlspecialchars($row['nome_sala']); ?></p>
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

        <?php } ?>

    </div>

</body>
</html>