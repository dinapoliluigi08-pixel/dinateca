<?php
// Carica le variabili d'ambiente dal file .env
require_once __DIR__ . "/env_loader.php";

// Legge le credenziali del database dal .env
$conn = new mysqli(
    env("DB_HOST", "localhost"),
    env("DB_USER", "root"),
    env("DB_PASS", ""),
    env("DB_NAME", "biblioteca")
);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
