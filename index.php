<?php
include "connessione.php";

if (isset($_SESSION['id_utente'])) { header("Location: visualizza.php"); exit; }

$errore = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM utenti WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $utente = $stmt->get_result()->fetch_assoc();

    if ($utente && password_verify($password, $utente['password'])) {
        $_SESSION['id_utente'] = $utente['id'];
        $_SESSION['username']  = $utente['username'];
        header("Location: visualizza.php");
        exit;
    } else {
        $errore = "Username o password errati.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi – Dinateca</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>

<div class="navbar">
    <a class="logo" href="index.php">📗 <span>Dina</span>teca</a>
</div>

<div class="container">
    <br>
    <div class="form-box">
        <h2>👤 Accedi</h2>

        <?php if ($errore): ?>
            <div class="messaggio-errore"><?php echo htmlspecialchars($errore); ?></div>
        <?php endif; ?>

        <form method="POST" id="form-login">
            <div class="form-gruppo">
                <label>Username</label>
                <input type="text" name="username" id="username" required placeholder="Il tuo username">
            </div>
            <div class="form-gruppo">
                <label>Password</label>
                <input type="password" name="password" id="password" required placeholder="••••••••">
            </div>
            <button type="submit" name="login" class="btn btn-verde"
                    style="width:100%;padding:.75rem;">Entra →</button>
        </form>

        <div class="link-tab">
            Non hai un account? <a href="registra.php">Registrati</a>
        </div>
    </div>
</div>

<footer>📗 Dinateca</footer>
<script src="script.js"></script>
</body>
</html>
