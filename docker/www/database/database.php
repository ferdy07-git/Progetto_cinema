<?php
require "connessione.php";

// Helper function to safely create a table only if it does not exist
function safeCreateTable($conn, $tableName, $sql) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    if ($result->num_rows === 0) {
        $conn->query($sql);
    }
}

// 1. Create genere table
safeCreateTable($conn, 'genere', "CREATE TABLE IF NOT EXISTS genere(
    id_genere INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL
)");

// 2. Create film table
safeCreateTable($conn, 'film', "CREATE TABLE IF NOT EXISTS film(
    id_film INT AUTO_INCREMENT PRIMARY KEY,
    genere INT NOT NULL,
    titolo VARCHAR(50) NOT NULL,
    trama TEXT NOT NULL,
    durata VARCHAR(30) NOT NULL,
    locandina VARCHAR(255) NOT NULL,
    FOREIGN KEY (genere) REFERENCES genere(id_genere)
)");

// 3. Create sala table
safeCreateTable($conn, 'sala', "CREATE TABLE IF NOT EXISTS sala(
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL,
    posti INT NOT NULL 
)");

// 4. Create spettacolo table
safeCreateTable($conn, 'spettacolo', "CREATE TABLE IF NOT EXISTS spettacolo(
    id_spettacolo INT AUTO_INCREMENT PRIMARY KEY,
    film INT NOT NULL,
    sala INT NOT NULL,
    data_spettacolo DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    FOREIGN KEY (film) REFERENCES film(id_film),
    FOREIGN KEY(sala) REFERENCES sala(id_sala)
)");

// 5. Create tipologia_utente table
safeCreateTable($conn, 'tipologia_utente', "CREATE TABLE IF NOT EXISTS tipologia_utente(
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL
)");

// 6. Create utente table
safeCreateTable($conn, 'utente', "CREATE TABLE IF NOT EXISTS utente(
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(64) NOT NULL,
    email VARCHAR(40) UNIQUE,
    tipo INT NOT NULL,
    FOREIGN KEY(tipo) REFERENCES tipologia_utente(id_tipo)
)");

// 7. Create biglietto table
safeCreateTable($conn, 'biglietto', "CREATE TABLE IF NOT EXISTS biglietto(
    id_biglietto INT AUTO_INCREMENT PRIMARY KEY,
    importo FLOAT NOT NULL,
    posto VARCHAR(4) NOT NULL,
    spettacolo int NOT NULL,
    utente int,
    FOREIGN KEY(spettacolo) REFERENCES spettacolo(id_spettacolo),
    FOREIGN KEY(utente) REFERENCES utente(id_utente)
)");

// Only run the seeder if the database is actually unpopulated (no genres loaded)
$check = $conn->query("SELECT COUNT(*) as count FROM genere");
if ($check && $check->fetch_assoc()['count'] == 0) {
    $array = explode(";", file_get_contents(__DIR__ . '/cinema_database.sql'));
    foreach ($array as $q) {
        $sql = trim($q);
        if (!empty($sql)) {
            $conn->query($sql);
        }
    }
}
?>

