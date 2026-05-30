<?php
include "connessione.php";
if (!isset($_SESSION['id_utente'])) { header("Location: index.php"); exit; }

$id        = (int)$_GET['id'];
$id_utente = $_SESSION['id_utente'];

$stmt = $conn->prepare("DELETE FROM libri WHERE id=? AND id_utente=?");
$stmt->bind_param("ii", $id, $id_utente);
$stmt->execute();

header("Location: visualizza.php?msg=eliminato");
exit;
?>
