<?php
/**
 * env_loader.php – Carica le variabili dal file .env
 *
 * Come funziona:
 *  - Legge il file .env riga per riga
 *  - Salta righe vuote e commenti (che iniziano con #)
 *  - Divide ogni riga su "=" e carica la variabile in $_ENV e getenv()
 *
 * Come usarlo negli altri file:
 *  require_once __DIR__ . "/env_loader.php";
 *  $host = env("DB_HOST");   // legge DB_HOST dal .env
 */

function loadEnv(string $path): void {
    if (!file_exists($path)) {
        die("⚠️  File .env non trovato in: $path — copialo dalla cartella del progetto.");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Salta commenti e righe vuote
        if ($line === '' || str_starts_with($line, '#')) continue;

        // Divide su "=" prendendo solo il primo "=" (il valore può contenere "=")
        $pos   = strpos($line, '=');
        if ($pos === false) continue;

        $key   = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        // Rimuove eventuali virgolette attorno al valore
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

/**
 * Legge una variabile d'ambiente.
 * Se non esiste restituisce $default (null di default).
 */
function env(string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Carica automaticamente il .env nella stessa cartella
loadEnv(__DIR__ . '/.env');
