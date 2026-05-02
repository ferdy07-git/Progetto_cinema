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
    durata VARCHAR(30) NOT NULL
    FOREIGN KEY (genere) REFERENCES genere(id_genere)
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
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    FOREIGN KEY (film) REFERENCES film(id_film)
    FOREIGN KEY(sala) REFERENCES sala(id_sala)
)";
$conn->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Tipologia_utente(
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL,
)";
$conn->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Utente(
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    pw VARCHAR(40) NOT NULL,
    email VARCHAR(40)  NULL ,
    tipo INT NOT NULL,
    FOREIGN KEY(tipo)  REFERENCES tipo(id_tipo)
)";
$conn->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Biglietto(
    id_biglietto INT AUTO_INCREMENT PRIMARY KEY,
    importo FLOAT NOT NULL,
    spettacolo int NOT NULL,
    utente int NOT NULL,
    FOREIGN KEY(spettacolo) REFERENCES spettacolo(id_spettacolo)
    FOREIGN KEY(utente) REFERENCES utente(id_utente)

)";
$conn->query($sql);
