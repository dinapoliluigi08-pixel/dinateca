<?php
include "connessione.php";
if (!isset($_SESSION['id_utente'])) { header("Location: index.php"); exit; }

$id                 = (int)$_POST['id'];
$id_utente          = $_SESSION['id_utente'];
$titolo             = $_POST['titolo'];
$autore             = $_POST['autore'];
$anno_pubblicazione = $_POST['anno_pubblicazione'] != "" ? (int)$_POST['anno_pubblicazione'] : null;
$genere             = $_POST['genere'];
$stato              = $_POST['stato'];
$voto               = $_POST['voto'] != "" ? (int)$_POST['voto'] : null;
$commento           = $_POST['commento'];
$colore_copertina   = $_POST['colore_copertina'] ?? '#2d6a4f';

if (!preg_match('/^#[0-9a-fA-F]{6}$/', $colore_copertina)) {
    $colore_copertina = '#2d6a4f';
}

$stmt = $conn->prepare(
    "UPDATE libri SET titolo=?, autore=?, anno_pubblicazione=?, genere=?, stato=?, voto=?, commento=?, colore_copertina=?
     WHERE id=? AND id_utente=?"
);
$stmt->bind_param("ssissisiii", $titolo, $autore, $anno_pubblicazione, $genere, $stato, $voto, $commento, $colore_copertina, $id, $id_utente);
$stmt->execute();

header("Location: visualizza.php?msg=modificato");
exit;
?>
