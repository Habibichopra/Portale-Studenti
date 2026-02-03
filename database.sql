
CREATE DATABASE IF NOT EXISTS PortStud CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE PortStud;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    ruolo ENUM('studente', 'professore', 'admin') NOT NULL,
    matricola VARCHAR(20) DEFAULT NULL, -- NULL se admin o professore
    creato_il DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE corsi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_corso VARCHAR(100) NOT NULL,
    codice_corso VARCHAR(20) NOT NULL UNIQUE,
    descrizione TEXT,
    anno_accademico VARCHAR(10) NOT NULL, -- Es: "2023/2024"
    professore_id INT,
    crediti INT NOT NULL DEFAULT 6,
    FOREIGN KEY (professore_id) REFERENCES users(id) ON DELETE SET NULL
);


CREATE TABLE iscrizioni (
    id INT PRIMARY KEY AUTO_INCREMENT,
    studente_id INT NOT NULL,
    corso_id INT NOT NULL,
    data_iscrizione DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('attivo', 'completato', 'ritirato') DEFAULT 'attivo',
    FOREIGN KEY (studente_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (corso_id) REFERENCES corsi(id) ON DELETE CASCADE,
    UNIQUE(studente_id, corso_id) -- Previene doppie iscrizioni allo stesso corso
);

CREATE TABLE compiti (
    id INT PRIMARY KEY AUTO_INCREMENT,
    corso_id INT NOT NULL,
    titolo VARCHAR(150) NOT NULL,
    descrizione TEXT,
    data_assegnazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_scadenza DATETIME NOT NULL,
    punti_max INT DEFAULT 100,
    allegato VARCHAR(255) DEFAULT NULL, -- Path del file caricato dal prof
    FOREIGN KEY (corso_id) REFERENCES corsi(id) ON DELETE CASCADE
);


CREATE TABLE consegne (
    id INT PRIMARY KEY AUTO_INCREMENT,
    compito_id INT NOT NULL,
    studente_id INT NOT NULL,
    file_consegna VARCHAR(255) NOT NULL, -- Path del file caricato dallo studente
    note_studente TEXT,
    data_consegna DATETIME DEFAULT CURRENT_TIMESTAMP,
    voto DECIMAL(4,2) DEFAULT NULL, -- Es: 8.50, NULL se non ancora corretto
    feedback_professore TEXT,
    stato ENUM('consegnato', 'valutato', 'in_ritardo') DEFAULT 'consegnato',
    FOREIGN KEY (compito_id) REFERENCES compiti(id) ON DELETE CASCADE,
    FOREIGN KEY (studente_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE voti (
    id INT PRIMARY KEY AUTO_INCREMENT,
    studente_id INT NOT NULL,
    corso_id INT NOT NULL,
    tipo_valutazione ENUM('compito', 'esame', 'progetto') NOT NULL,
    voto DECIMAL(4,2) NOT NULL, -- Es: 28.00
    data_voto DATE NOT NULL,
    note TEXT,
    FOREIGN KEY (studente_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (corso_id) REFERENCES corsi(id) ON DELETE CASCADE
);


CREATE TABLE materiali (
    id INT PRIMARY KEY AUTO_INCREMENT,
    corso_id INT NOT NULL,
    titolo VARCHAR(150) NOT NULL,
    descrizione TEXT,
    tipo ENUM('pdf', 'video', 'slide', 'altro') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (corso_id) REFERENCES corsi(id) ON DELETE CASCADE
);

CREATE TABLE comunicazioni (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mittente_id INT NOT NULL,
    destinatario_id INT DEFAULT NULL, -- Se NULL, potrebbe essere un messaggio globale (opzionale)
    corso_id INT DEFAULT NULL, -- Se associato a un corso specifico
    oggetto VARCHAR(200) NOT NULL,
    messaggio TEXT NOT NULL,
    data_invio DATETIME DEFAULT CURRENT_TIMESTAMP,
    letto BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (mittente_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (corso_id) REFERENCES corsi(id) ON DELETE SET NULL
);


INSERT INTO users (username, password_hash, email, nome, cognome, ruolo, matricola) VALUES 
('admin', '$2y$10$e.wX/y/././././././././././././././././././././././.', 'admin@univ.it', 'Super', 'Admin', 'admin', NULL),
('prof.rossi', '$2y$10$8sA1N7L.t4././././././././././././././././././././.', 'rossi@univ.it', 'Mario', 'Rossi', 'professore', NULL),
('prof.verdi', '$2y$10$8sA1N7L.t4././././././././././././././././././././.', 'verdi@univ.it', 'Giuseppe', 'Verdi', 'professore', NULL),
('studente1', '$2y$10$8sA1N7L.t4././././././././././././././././././././.', 's1@univ.it', 'Luca', 'Bianchi', 'studente', 'MAT001'),
('studente2', '$2y$10$8sA1N7L.t4././././././././././././././././././././.', 's2@univ.it', 'Anna', 'Neri', 'studente', 'MAT002');

UPDATE users SET password_hash = '$2y$10$zC4z6bX.vV.vV.vV.vV.vV.vV.vV.vV.vV.vV.vV.vV.vV.vV.vV' WHERE id > 0;

UPDATE users SET password_hash = '$2y$10$7s2h/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y/Y'; 

UPDATE users SET password_hash = '$2y$10$I0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j0j' WHERE id > 0;

DELETE FROM users;
INSERT INTO users (id, username, password_hash, email, nome, cognome, ruolo, matricola) VALUES 
(1, 'admin', '$2y$10$StartWithRealHashForPass123!ExAmPlEHsH...', 'admin@univ.it', 'Super', 'Admin', 'admin', NULL),
(2, 'prof.rossi', '$2y$10$StartWithRealHashForPass123!ExAmPlEHsH...', 'rossi@univ.it', 'Mario', 'Rossi', 'professore', NULL),
(3, 'studente1', '$2y$10$StartWithRealHashForPass123!ExAmPlEHsH...', 's1@univ.it', 'Luca', 'Bianchi', 'studente', 'MAT001');


INSERT INTO corsi (nome_corso, codice_corso, descrizione, anno_accademico, professore_id, crediti) VALUES
('Matematica Analisi 1', 'MAT-01', 'Corso base di analisi matematica', '2023/2024', 2, 9),
('Programmazione PHP', 'INF-01', 'Corso completo di sviluppo Web backend', '2023/2024', 2, 6);

INSERT INTO iscrizioni (studente_id, corso_id, status) VALUES
(3, 1, 'attivo'),
(3, 2, 'attivo');

INSERT INTO compiti (corso_id, titolo, descrizione, data_scadenza, punti_max) VALUES
(2, 'Creazione Login', 'Creare una pagina di login sicura', DATE_ADD(NOW(), INTERVAL 7 DAY), 10);