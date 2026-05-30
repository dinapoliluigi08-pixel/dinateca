<?php
include "connessione.php";
session_destroy();
header("Location: index.php");
exit;
?>
