<?php
    include("connessione.php");

    $query = "
    SELECT 
        film.id_film,
        film.titolo,
        film.trama,
        film.durata,
        genere.nome AS nome_genere,
        spettacolo.id_spettacolo,
        spettacolo.data,
        spettacolo.ora_inizio,
        spettacolo.ora_fine,
        sala.nome AS nome_sala
    FROM film
    INNER JOIN genere 
        ON film.genere = genere.id_genere
    INNER JOIN spettacolo 
        ON film.id_film = spettacolo.film
    INNER JOIN sala 
        ON spettacolo.sala = sala.id_sala
    ORDER BY film.titolo
    ";

    $result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Homepage Cinema</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Film in programmazione</h1>

    <div class="container">

        <?php while($row = $result->fetch_assoc()) { ?>

            <div class="film-card">
                <img src="img/default-film.jpg" alt="Locandina film">

                <div class="film-info">

                    <h2><?php echo $row['titolo']; ?></h2>

                    <p class="genere">
                        Genere: <?php echo $row['nome_genere']; ?>
                    </p>

                    <p class="trama">
                        <?php echo $row['trama']; ?>
                    </p>

                    <p class="durata">
                        Durata: <?php echo $row['durata']; ?>
                    </p>

                    <div class="spettacolo-info">
                        <h3>Spettacolo disponibile</h3>

                        <p>
                            Data: <?php echo $row['data']; ?>
                        </p>

                        <p>
                            Ora: <?php echo $row['ora_inizio']; ?> - 
                            <?php echo $row['ora_fine']; ?>
                        </p>

                        <p>
                            Sala: <?php echo $row['nome_sala']; ?>
                        </p>
                    </div>

                    <a href="acquista_biglietto.php?id_spettacolo=<?php echo $row['id_spettacolo']; ?>">
                        <button>Acquista Biglietto</button>
                    </a>

                </div>
            </div>

        <?php } ?>

    </div>

</body>
</html>