-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mag 03, 2026 alle 21:36
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cinema`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `biglietto`
--

CREATE TABLE `genere` (
  `id_genere` int(11) NOT NULL,
  `nome` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `genere`
--

INSERT INTO `genere` (`id_genere`, `nome`) VALUES
(1, 'Commedia'),
(2, 'Drammatico'),
(3, 'Thriller'),
(4, 'Storico'),
(5, 'Fantascienza'),
(6, 'Animazione'),
(7, 'Avventura'),
(8, 'Romantico'),
(9, 'Crime'),
(10, 'Fantasy'),
(11, 'Guerra'),
(12, 'Poliziesco'),
(13, 'Spionaggio'),
(14, 'Commedia romantica'),
(15, 'Biografico'),
(16, 'Storico'),
(17, 'Dramma familiare'),
(18, 'Supereroi'),
(19, 'Distopico'),
(20, 'Thriller psicologico'),
(21, 'Noir'),
(22, 'Documentario'),
(23, 'Musical'),
(24, 'Western'),
(25, 'Sportivo');

-- --------------------------------------------------------

--
-- Struttura della tabella `film`
--

CREATE TABLE `film` (
  `id_film` int(11) NOT NULL,
  `genere` int(11) NOT NULL,
  `titolo` varchar(50) NOT NULL,
  `trama` text NOT NULL,
  `durata` varchar(30) NOT NULL,
  `locandina` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `film`
--

INSERT INTO `film` (`id_film`, `genere`, `titolo`, `trama`, `durata`, `locandina`) VALUES
(1, 1, 'Benvenuti al Sud', 'Un impiegato delle poste viene trasferito dal nord al sud Italia e scopre una realtà diversa da quella immaginata.', '1h 42m', 'benvenuti_al_sud.webp'),
(2, 2, 'La vita è bella', 'Un padre usa l’immaginazione per proteggere il figlio dagli orrori della guerra.', '1h 56m', 'la_vita_è_bella.webp'),
(3, 3, 'Perfetti sconosciuti', 'Durante una cena tra amici, tutti decidono di condividere messaggi e chiamate ricevute sui loro telefoni.', '1h 37m', 'perfetti_sconosciuti.webp'),
(4, 4, 'Il primo re', 'La storia delle origini di Roma attraverso il conflitto tra Romolo e Remo.', '2h 03m', 'il_primo_re.webp'),
(5, 5, 'Lo chiamavano Jeeg Robot', 'Un uomo acquisisce poteri straordinari dopo un incidente e diventa un improbabile eroe.', '1h 52m', 'lo_chiamavano_jeeg_robot.webp'),
(6, 6, 'Il Re Leone', 'Un giovane leone affronta il proprio destino per diventare re.', '1h 58m', 'Il_Re_Leone.webp'),
(7, 7, 'Titanic', 'Una storia d’amore nasce a bordo del celebre transatlantico.', '3h 14m', 'Titanic.webp'),
(8, 8, 'Il Padrino', 'La storia della famiglia mafiosa Corleone.', '2h 55m', 'Il_Padrino.webp'),
(9, 9, 'Harry Potter e la Pietra Filosofale', 'Un ragazzo scopre di essere un mago.', '2h 32m', 'Harry_Potter_e_la_Pietra_Filosofale.webp'),
(10, 10, 'Il Signore degli Anelli: La Compagnia dell Anello', 'Un gruppo di eroi parte per distruggere un anello malvagio.', '2h 58m', 'Il_Signore_degli_Anelli_La_Compagnia_dell_Anello.webp'),
(11, 1, 'Tre uomini e una gamba', 'Tre amici affrontano un viaggio pieno di imprevisti.', '1h 37m', 'Tre_uomini_e_una_gamba.webp'),
(12, 3, 'Shutter Island', 'Un investigatore arriva su un isola per un caso misterioso.', '2h 18m', 'Shutter_Island.webp'),
(13, 4, 'Schindler s List', 'La storia vera di un uomo che salva centinaia di ebrei.', '3h 15m', 'Schindlers_List.webp'),
(14, 11, 'Dunkirk', 'Soldati alleati cercano di evacuare durante la Seconda guerra mondiale.', '1h 46m', 'Dunkirk.webp'),
(15, 12, 'Il silenzio degli innocenti', 'Una giovane agente dell FBI cerca l aiuto di un serial killer per catturarne un altro.', '1h 58m', 'Il_silenzio_degli_innocenti.webp'),
(16, 13, '007 Skyfall', 'James Bond affronta un nemico legato al suo passato.', '2h 23m', '007_Skyfall.webp'),
(17, 14, 'Pretty Woman', 'Una storia d amore tra un uomo d affari e una giovane donna.', '1h 59m', 'Pretty_Woman.webp'),
(18, 15, 'La teoria del tutto', 'La vita dello scienziato Stephen Hawking.', '2h 03m', 'La_teoria_del_tutto.webp'),
(19, 16, 'Il nome della rosa', 'Un monaco indaga su misteriosi omicidi in un monastero medievale.', '2h 10m', 'Il_nome_della_rosa.webp'),
(20, 17, 'La famiglia Bélier', 'Una ragazza sorda con talento per il canto deve scegliere tra famiglia e sogno.', '1h 46m', 'La_famiglia_Belier.webp'),
(21, 18, 'Iron Man', 'Un inventore diventa un supereroe con un’armatura tecnologica.', '2h 06m', 'Iron_Man.webp'),
(22, 19, 'Blade Runner 2049', 'Un replicante scopre un segreto che può cambiare il mondo.', '2h 44m', 'Blade_Runner_2049.webp'),
(23, 20, 'Shining', 'Una famiglia isolata in un hotel inizia a vivere eventi inquietanti.', '2h 26m', 'Shining.webp'),
(24, 21, 'Se7en', 'Due detective inseguono un serial killer ispirato ai sette peccati capitali.', '2h 07m', 'Se7en.webp'),
(25, 22, 'Super Size Me', 'Un uomo analizza gli effetti del fast food sul corpo umano.', '1h 40m', 'Super_Size_Me.webp'),
(26, 23, 'La La Land', 'Una storia d amore tra un musicista e un attrice a Los Angeles.', '2h 08m', 'La_La_Land.webp'),
(27, 24, 'Il buono, il brutto, il cattivo', 'Tre uomini inseguono un tesoro durante la guerra civile americana.', '2h 58m', 'Il_buono_il brutto_il cattivo.webp'),
(28, 25, 'Rocky', 'Un pugile sconosciuto ha l opportunità di combattere per il titolo mondiale.', '1h 59m', 'Rocky.webp');

-- --------------------------------------------------------

--
-- Struttura della tabella `genere`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sala`
--

CREATE TABLE `sala` (
  `id_sala` int(11) NOT NULL,
  `nome` varchar(30) NOT NULL,
  `posti` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `sala`
--

INSERT INTO `sala` (`id_sala`, `nome`, `posti`) VALUES
(1, 'Sala Piccola', 100),
(2, 'Sala Grande', 200);

-- --------------------------------------------------------

--
-- Struttura della tabella `spettacolo`
--

CREATE TABLE `spettacolo` (
  `id_spettacolo` int(11) NOT NULL,
  `film` int(11) NOT NULL,
  `sala` int(11) NOT NULL,
  `data_spettacolo` date NOT NULL,
  `ora_inizio` time NOT NULL,
  `ora_fine` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `spettacolo`
--

INSERT INTO `spettacolo` (`id_spettacolo`, `film`, `sala`, `data_spettacolo`, `ora_inizio`, `ora_fine`) VALUES
(1, 1, 1, '2026-05-10', '18:00:00', '19:42:00'),
(2, 2, 2, '2026-05-10', '20:00:00', '21:56:00'),
(3, 3, 1, '2026-05-11', '19:30:00', '21:07:00'),
(4, 4, 2, '2026-05-11', '21:30:00', '23:33:00'),
(5, 5, 1, '2026-05-12', '20:15:00', '22:07:00'),
(6, 6, 2, '2026-05-13', '17:00:00', '18:58:00'),
(7, 7, 1, '2026-05-13', '20:00:00', '23:14:00'),
(8, 8, 2, '2026-05-14', '18:30:00', '21:25:00'),
(9, 9, 1, '2026-05-14', '16:00:00', '18:32:00'),
(10, 10, 2, '2026-05-14', '21:00:00', '23:58:00'),
(11, 11, 1, '2026-05-15', '19:00:00', '20:37:00'),
(12, 12, 2, '2026-05-15', '21:00:00', '23:18:00'),
(13, 13, 1, '2026-05-16', '18:00:00', '21:15:00'),
(26, 14, 2, '2026-05-17', '18:00:00', '19:46:00'),
(27, 15, 1, '2026-05-17', '20:00:00', '21:58:00'),
(28, 16, 2, '2026-05-18', '18:30:00', '20:33:00'),
(29, 17, 1, '2026-05-18', '21:00:00', '22:46:00'),
(30, 18, 2, '2026-05-19', '17:30:00', '19:36:00'),
(31, 19, 1, '2026-05-19', '20:00:00', '22:44:00'),
(32, 20, 2, '2026-05-20', '21:00:00', '23:26:00'),
(33, 21, 1, '2026-05-20', '18:00:00', '20:07:00'),
(34, 22, 2, '2026-05-21', '17:00:00', '18:40:00'),
(35, 23, 1, '2026-05-21', '20:30:00', '22:38:00'),
(36, 24, 2, '2026-05-22', '19:00:00', '21:58:00'),
(37, 25, 1, '2026-05-22', '21:00:00', '23:10:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `tipologia_utente`
--

CREATE TABLE `tipologia_utente` (
  `id_tipo` int(11) NOT NULL,
  `nome` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `id_utente` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `password_hash` varchar(40) NOT NULL,
  `email` varchar(40) DEFAULT NULL,
  `tipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `biglietto` (
  `id_biglietto` int(11) NOT NULL,
  `importo` float NOT NULL,
  `posto` int(11) NOT NULL,
  `spettacolo` int(11) NOT NULL,
  `utente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `biglietto`
--
ALTER TABLE `biglietto`
  ADD PRIMARY KEY (`id_biglietto`),
  ADD KEY `spettacolo` (`spettacolo`),
  ADD KEY `utente` (`utente`);

--
-- Indici per le tabelle `film`
--
ALTER TABLE `film`
  ADD PRIMARY KEY (`id_film`),
  ADD KEY `genere` (`genere`);

--
-- Indici per le tabelle `genere`
--
ALTER TABLE `genere`
  ADD PRIMARY KEY (`id_genere`);

--
-- Indici per le tabelle `sala`
--
ALTER TABLE `sala`
  ADD PRIMARY KEY (`id_sala`);

--
-- Indici per le tabelle `spettacolo`
--
ALTER TABLE `spettacolo`
  ADD PRIMARY KEY (`id_spettacolo`),
  ADD KEY `film` (`film`),
  ADD KEY `sala` (`sala`);

--
-- Indici per le tabelle `tipologia_utente`
--
ALTER TABLE `tipologia_utente`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`id_utente`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `tipo` (`tipo`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `biglietto`
--
ALTER TABLE `biglietto`
  MODIFY `id_biglietto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `film`
--
ALTER TABLE `film`
  MODIFY `id_film` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT per la tabella `genere`
--
ALTER TABLE `genere`
  MODIFY `id_genere` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT per la tabella `sala`
--
ALTER TABLE `sala`
  MODIFY `id_sala` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `spettacolo`
--
ALTER TABLE `spettacolo`
  MODIFY `id_spettacolo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT per la tabella `tipologia_utente`
--
ALTER TABLE `tipologia_utente`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utente`
--
ALTER TABLE `utente`
  MODIFY `id_utente` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `biglietto`
--
ALTER TABLE `biglietto`
  ADD CONSTRAINT `biglietto_ibfk_1` FOREIGN KEY (`spettacolo`) REFERENCES `spettacolo` (`id_spettacolo`),
  ADD CONSTRAINT `biglietto_ibfk_2` FOREIGN KEY (`utente`) REFERENCES `utente` (`id_utente`);

--
-- Limiti per la tabella `film`
--
ALTER TABLE `film`
  ADD CONSTRAINT `film_ibfk_1` FOREIGN KEY (`genere`) REFERENCES `genere` (`id_genere`);

--
-- Limiti per la tabella `spettacolo`
--
ALTER TABLE `spettacolo`
  ADD CONSTRAINT `spettacolo_ibfk_1` FOREIGN KEY (`film`) REFERENCES `film` (`id_film`),
  ADD CONSTRAINT `spettacolo_ibfk_2` FOREIGN KEY (`sala`) REFERENCES `sala` (`id_sala`);

--
-- Limiti per la tabella `utente`
--
ALTER TABLE `utente`
  ADD CONSTRAINT `utente_ibfk_1` FOREIGN KEY (`tipo`) REFERENCES `tipologia_utente` (`id_tipo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
