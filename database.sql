-- ============================================================
--  DINATECA – Database v3
--  Esegui con: mysql -u root < database.sql
-- ============================================================

DROP DATABASE IF EXISTS biblioteca;
CREATE DATABASE biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biblioteca;

-- Tabella utenti
CREATE TABLE utenti (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    email    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Tabella libri (con colore_copertina per personalizzazione)
CREATE TABLE libri (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    id_utente          INT          NOT NULL,
    titolo             VARCHAR(200) NOT NULL,
    autore             VARCHAR(150) NOT NULL,
    anno_pubblicazione YEAR,
    genere             VARCHAR(50),
    stato              VARCHAR(20)  NOT NULL DEFAULT 'Da leggere',
    voto               INT          CHECK (voto BETWEEN 1 AND 5),
    commento           TEXT,
    colore_copertina   VARCHAR(7)   NOT NULL DEFAULT '#2d6a4f',
    FOREIGN KEY (id_utente) REFERENCES utenti(id) ON DELETE CASCADE
);

