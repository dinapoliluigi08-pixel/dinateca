<?php
session_start();

$ADMIN_USER = 'admin';
$ADMIN_PASS = 'Dinadmin2026@';

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: admin.php");
    exit;
}

$errore = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $errore = "Credenziali errate.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Dinateca</title>
    <link rel="stylesheet" href="stile.css">
    <style>
        body { background: #1a4731; }
        .admin-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-box {
            border: 2px solid #52b788;
            max-width: 420px;
            width: 100%;
        }
        .admin-badge {
            display: inline-block;
            background: #52b788;
            color: #1a4731;
            font-size: .72rem;
            font-weight: 700;
            padding: .2rem .6rem;
            border-radius: 99px;
            letter-spacing: .8px;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="admin-wrap">
    <div class="form-box">
        <span class="admin-badge">⚙️ Area Amministratore</span>
        <h2>🔐 Accesso Admin</h2>

        <?php if ($errore): ?>
            <div class="messaggio-errore"><?php echo htmlspecialchars($errore); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-gruppo">
                <label>Username Admin</label>
                <input type="text" name="username" required placeholder="admin">
            </div>
            <div class="form-gruppo">
                <label>Password Admin</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" name="login" class="btn btn-verde" style="width:100%;padding:.75rem;">
                Accedi →
            </button>
        </form>

        <div class="link-tab" style="margin-top:1rem;">
            <a href="index.php">← Torna al sito</a>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>
