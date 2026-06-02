<?php
include "connessione.php";
require_once "mailer.php";

$errore = "";

if (isset($_POST['registra'])) {
    $username    = trim($_POST['username']);
    $email       = trim($_POST['email']);
    $password    = $_POST['password'];
    $conferma_pw = $_POST['conferma_password'];

    if ($password !== $conferma_pw) {
        $errore = "Le password non coincidono. Riprova.";
    } elseif (strlen($password) < 6) {
        $errore = "La password deve avere almeno 6 caratteri.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO utenti (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hash);

        if ($stmt->execute()) {
            // ── Login automatico dopo la registrazione ────────
            $stmt2 = $conn->prepare("SELECT id FROM utenti WHERE username = ?");
            $stmt2->bind_param("s", $username);
            $stmt2->execute();
            $row = $stmt2->get_result()->fetch_assoc();
            $_SESSION['id_utente'] = $row['id'];
            $_SESSION['username']  = $username;

            // ── Invia email di benvenuto automaticamente ──────
            $corpo = templateBenvenuto($username);
           $esitoMail = inviaEmail($email, "Benvenuto su Dinateca, $username! 📗", $corpo);
            if ($esitoMail !== true) {
                error_log("ERRORE EMAIL BREVO: " . $esitoMail);  
            // finisce nei log di Railway
             // Per il debug puoi anche fermarti e vederlo a schermo:
            // die("DEBUG email: " . $esitoMail);
            }
            header("Location: visualizza.php?msg=benvenuto");
            exit;
        } else {
            $db_err = $stmt->error;
            if (strpos($db_err, "Duplicate entry") !== false) {
                if (strpos($db_err, "username") !== false)
                    $errore = "Username già in uso. Scegline un altro.";
                else
                    $errore = "Email già registrata. Usa un'altra email.";
            } else {
                $errore = "Errore: " . $db_err;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione – Dinateca</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>

<div class="navbar">
    <a class="logo" href="index.php">📗 <span>Dina</span>teca</a>
</div>

<div class="container">
    <br>
    <div class="form-box">
        <h2>✨ Crea account</h2>

        <?php if ($errore): ?>
            <div class="messaggio-errore"><?php echo htmlspecialchars($errore); ?></div>
        <?php endif; ?>

        <form method="POST" id="form-registra">
            <div class="form-gruppo">
                <label>Username</label>
                <input type="text" name="username" id="username" required minlength="3"
                       placeholder="min. 3 caratteri"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-gruppo">
                <label>Email</label>
                <input type="email" name="email" id="email" required
                       placeholder="la@tua.email"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-gruppo">
                <label>Password</label>
                <input type="password" name="password" id="password" required minlength="6"
                       placeholder="min. 6 caratteri">
                <div class="password-strength-bar"><div id="pw-bar"></div></div>
                <span class="pw-hint" id="pw-hint"></span>
            </div>
            <div class="form-gruppo">
                <label>Conferma Password</label>
                <input type="password" name="conferma_password" id="conferma_password" required
                       placeholder="ripeti la password">
                <span class="pw-match" id="pw-match"></span>
            </div>
            <button type="submit" name="registra" class="btn btn-verde"
                    style="width:100%;padding:.75rem;margin-top:.4rem;">
                Crea account →
            </button>
        </form>

        <div class="link-tab">
            Hai già un account? <a href="index.php">Accedi</a>
        </div>
    </div>
</div>

<footer>📗 Dinateca</footer>
<script src="script.js"></script>
</body>
</html>
