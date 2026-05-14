<?php
    session_start();
    include("../database/connessione.php");

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
    WHERE spettacolo.id_spettacolo = ".$_SESSION['id_spettacolo']."
    ORDER BY film.titolo
    ";

    $result = $conn->query($query);

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
            max-width: 1100px;
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

        .posti-right {
            border-radius: 10px;
            padding: 1.5rem;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            font-size: 0.9rem;
            border: 2px dashed #333;
        }
    </style>
</head>
<body>

<main>
    <div class="container" id="film-container">

        <?php foreach ($films as $row):
            $titolo_esc = htmlspecialchars($row['titolo'], ENT_QUOTES, 'UTF-8');
            $genere_esc = htmlspecialchars($row['nome_genere'], ENT_QUOTES, 'UTF-8');
            $sala_esc   = htmlspecialchars($row['nome_sala'] ?? '', ENT_QUOTES, 'UTF-8');
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
                        <p><?php echo $sala_esc; ?></p>
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
                <p>🪑 Selezione posti — da implementare</p>
            </div>

        </div>

        <?php endforeach; ?>

    </div>
</main>

</body>
</html>