<?php
require "connessione.php";

$sql = "CREATE TABLE IF NOT EXISTS Genere(
    id_genere INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL
)";
$conn->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Film(
    id_film INT AUTO_INCREMENT PRIMARY KEY,
    genere INT NOT NULL,
    titolo VARCHAR(50) NOT NULL,
    trama TEXT NOT NULL,
    durata VARCHAR(30) NOT NULL,
    FOREIGN KEY (genere) REFERENCES Genere(id_genere)
)";
$conn->query($sql); 
$sql = "CREATE TABLE IF NOT EXISTS Sala(
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL,
    posti INT NOT NULL 
)";
$conn->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Spettacolo(
    id_spettacolo INT AUTO_INCREMENT PRIMARY KEY,
    film INT NOT NULL,
    sala INT NOT NULL,
    data_spettacolo DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    FOREIGN KEY (film) REFERENCES Film(id_film),
    FOREIGN KEY(sala) REFERENCES Sala(id_sala)
)";
$conn->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Tipologia_utente(
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL
)";
$conn->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Utente(
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    password_hash VARCHAR(40) NOT NULL,
    email VARCHAR(40) UNIQUE ,
    tipo INT NOT NULL,
    FOREIGN KEY(tipo)  REFERENCES Tipologia_utente(id_tipo)
)";
$conn->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Biglietto(
    id_biglietto INT AUTO_INCREMENT PRIMARY KEY,
    importo FLOAT NOT NULL,
    posto INT NOT NULL,
    spettacolo int NOT NULL,
    utente int NOT NULL,
    FOREIGN KEY(spettacolo) REFERENCES Spettacolo(id_spettacolo),
    FOREIGN KEY(utente) REFERENCES Utente(id_utente)

)";
$conn->query($sql);
