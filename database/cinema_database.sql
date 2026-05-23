INSERT INTO `genere` (`id_genere`, `nome`) VALUES
(1, 'Commedia'),
(2, 'Drammatico'),
(3, 'Thriller'),
(4, 'Fantascienza'),
(5, 'Animazione'),
(6, 'Avventura'),
(7, 'Crime'),
(8, 'Fantasy'),
(9, 'Supereroi'),
(10, 'Azione');

INSERT INTO `film` (`id_film`, `genere`, `titolo`, `trama`, `durata`, `locandina`) VALUES
(1, 7, 'Le città di pianura', 'Un epico viaggio attraverso le terre basse, dove un gruppo di esploratori deve navigare tra intrighi politici e pericoli naturali in una civiltà costruita su antiche paludi bonificate.', '2h 15m', 'le_citta_di_pianura.webp'),
(2, 5, 'Hopper - Il segreto della marmotta', 'Hopper, una marmotta curiosa e coraggiosa, scopre un antico segreto nascosto nelle montagne che potrebbe salvare la sua colonia da un inverno senza fine. Un''avventura animata piena di cuore.', '1h 35m', 'hopper_il_segreto_marmotta.webp'),
(3, 1, 'Pecore sotto copertura', 'Un gruppo di pecore apparentemente innocue viene reclutato da un''agenzia di intelligence internazionale per sventare un complotto globale, usando il loro aspetto adorabile come camuffamento perfetto.', '1h 48m', 'pecore_sotto_copertura.webp'),
(4, 4, 'The Mandalorian and Grogu', 'Din Djarin e Grogu continuano il loro viaggio nella Galassia Lontana, Lontana. Mentre Grogu inizia ad addestrarsi nei modi della Forza, una nuova minaccia imperiale emerge dalle ombre, costringendo il Mandaloriano a scegliere tra il suo credo e il destino del bambino.', '2h 10m', 'mandalorian_grogu.webp'),
(5, 2, 'Michael', 'La storia biografica definitiva di Michael Jackson, che esplora la sua ascesa stellare, il suo genio artistico e le complesse vicende personali che hanno definito l''icona della musica pop, dalla giovinezza agli ultimi anni.', '2h 30m', 'michael_biopic.webp'),
(6, 1, 'Il diavolo veste Prada 2', 'Anni dopo gli eventi del primo film, Andy Sachs, ora una redattrice di successo, deve tornare nel mondo della alta moda quando Miranda Priestly le chiede un favore impossibile, rimettendo in gioco vecchie rivalità e nuove ambizioni.', '1h 55m', 'diavolo_veste_prada_2.webp'),
(7, 2, 'Amarga Navidad', 'Durante un Natale apparentemente perfetto, i segreti di una famiglia disfunzionale vengono a galla, trasformando la festa in un campo di battaglia emotivo dove perdono e verità si scontrano sotto la neve.', '1h 50m', 'amarga_navidad.webp'),
(8, 5, 'Super Mario Galaxy', 'Mario e Luigi vengono lanciati nello spazio profondo quando Bowser rapisce Peach utilizzando il potere delle Super Stelle. Attraversando galassie gravitazionali bizzarre, i fratelli devono salvare la principessa e l''universo intero.', '1h 45m', 'super_mario_galaxy.webp'),
(9, 3, 'Obsession', 'Uno psicoterapeuta di successo diventa ossessionato da un paziente misterioso che sembra conoscere i dettagli più oscuri del suo passato. La linea tra cura e follia si assottiglia pericolosamente.', '1h 52m', 'obsession_thriller.webp'),
(10, 3, 'Passenger', 'Su un volo transatlantico, un passeggero solitario nota comportamenti strani nell''equipaggio. Quando inizia a indagare, scopre che il velivolo non sta andando dove dovrebbe, e nessuno a bordo è chi dice di essere.', '1h 40m', 'passenger_thriller.webp'),
(11, 10, 'In the Grey', 'Un ex agente operativo viene richiamato in servizio per una missione "ombra" che non esiste ufficialmente. Braccato da entrambe le parti della legge, deve scoprire chi lo ha tradito prima di essere cancellato per sempre.', '2h 05m', 'in_the_grey.webp'),
(12, 6, 'La Mummia', 'Un nuovo archeologo risveglia accidentalmente un''antica principessa maledetta nel deserto egiziano. Ora deve fermare la sua marcia di distruzione globale prima che l''oscurità inghiotta il mondo moderno.', '2h 12m', 'la_mummia_reboot.webp'),
(13, 10, 'Kill Bill: The Whole Bloody Affair', 'La versione integrale e senza censure della saga di vendetta della Sposa. Beatrix Kiddo affronta la Vipera Assassina e Bill in una sequenza di combattimenti stilizzati e brutali che ridefiniscono il cinema d''azione.', '2h 50m', 'kill_bill_whole_bloody.webp');

INSERT INTO `sala` (`id_sala`, `nome`, `posti`) VALUES
(1, 'Sala Piccola', 100),
(2, 'Sala Grande', 200);

INSERT INTO `tipologia_utente` (`id_tipo`, `nome`) VALUES
(1,"Cliente"),
(2,"Venditore"),
(3,"Amministratore");

INSERT INTO `spettacolo` (`id_spettacolo`, `film`, `sala`, `data_spettacolo`, `ora_inizio`) VALUES
(1, 1, 1, '2026-05-24', '18:00:00'),
(2, 2, 2, '2026-05-24', '20:00:00'),
(3, 3, 1, '2026-05-25', '19:30:00'),
(4, 4, 2, '2026-05-25', '21:30:00'),
(5, 5, 1, '2026-05-26', '20:15:00'),
(6, 6, 2, '2026-05-26', '17:00:00'),
(7, 7, 1, '2026-05-27', '20:00:00'),
(8, 8, 2, '2026-05-27', '18:30:00'),
(9, 9, 1, '2026-05-28', '16:00:00'),
(10, 10, 2, '2026-05-28', '21:00:00'),
(11, 11, 1, '2026-05-29', '19:00:00'),
(12, 12, 2, '2026-05-29', '21:00:00'),
(13, 13, 1, '2026-05-30', '20:00:00');

INSERT INTO `utente` (`id_utente`, `nome`, `password_hash`, `email`, `tipo`) VALUES
(1, 'admin', '2f8f8acba3134e694faf23803e0b64b940bc5037d602a9c582ddea4d6dcef2dd', 'cinema2026@gmail.com', 3),
(2, 'venditore', '2f8f8acba3134e694faf23803e0b64b940bc5037d602a9c582ddea4d6dcef2dd', 'venditore2026@gmail.com', 2)
