<?php
include "connessione.php";
if (!isset($_SESSION['id_utente'])) { header("Location: index.php"); exit; }

$id_utente          = $_SESSION['id_utente'];
$titolo             = $_POST['titolo'];
$autore             = $_POST['autore'];
$anno_pubblicazione = $_POST['anno_pubblicazione'] != "" ? (int)$_POST['anno_pubblicazione'] : null;
$genere             = $_POST['genere'];
$stato              = $_POST['stato'];
$voto               = $_POST['voto'] != "" ? (int)$_POST['voto'] : null;
$commento           = $_POST['commento'];
$colore_copertina   = $_POST['colore_copertina'] ?? '#2d6a4f';

// Sanitizza il colore: deve essere un hex valido
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $colore_copertina)) {
    $colore_copertina = '#2d6a4f';
}

$stmt = $conn->prepare(
    "INSERT INTO libri (id_utente, titolo, autore, anno_pubblicazione, genere, stato, voto, commento, colore_copertina)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("issississ", $id_utente, $titolo, $autore, $anno_pubblicazione, $genere, $stato, $voto, $commento, $colore_copertina);
$stmt->execute();

header("Location: visualizza.php?msg=aggiunto");
exit;
?>
