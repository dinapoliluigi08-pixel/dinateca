<?php
require_once "env_loader.php";
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Connessione DB diretta (non include connessione.php per evitare conflitti di sessione)
$conn = new mysqli(
    env("DB_HOST", "localhost"),
    env("DB_USER", "root"),
    env("DB_PASS", ""),
    env("DB_NAME", "biblioteca")
);
$conn->set_charset("utf8mb4");

// ── Statistiche globali ───────────────────────────────────
$totale_utenti  = $conn->query("SELECT COUNT(*) as n FROM utenti")->fetch_assoc()['n'];
$totale_libri   = $conn->query("SELECT COUNT(*) as n FROM libri")->fetch_assoc()['n'];
$media_voti_raw = $conn->query("SELECT AVG(voto) as m FROM libri WHERE voto IS NOT NULL")->fetch_assoc()['m'];
$media_voti     = $media_voti_raw ? round($media_voti_raw, 1) : 0;

$letti      = $conn->query("SELECT COUNT(*) as n FROM libri WHERE stato='Letto'")->fetch_assoc()['n'];
$in_lettura = $conn->query("SELECT COUNT(*) as n FROM libri WHERE stato='In lettura'")->fetch_assoc()['n'];
$da_leggere = $conn->query("SELECT COUNT(*) as n FROM libri WHERE stato='Da leggere'")->fetch_assoc()['n'];

// Genere più popolare
$genere_pop = $conn->query("SELECT genere, COUNT(*) as n FROM libri WHERE genere != '' GROUP BY genere ORDER BY n DESC LIMIT 1")->fetch_assoc();

// Utente con più libri
$utente_top = $conn->query("
    SELECT u.username, COUNT(l.id) as n
    FROM utenti u
    LEFT JOIN libri l ON l.id_utente = u.id
    GROUP BY u.id ORDER BY n DESC LIMIT 1
")->fetch_assoc();

// Ultimi 5 utenti registrati
$ultimi_utenti = $conn->query("SELECT id, username, email FROM utenti ORDER BY id DESC LIMIT 5");

// Distribuzione generi
$generi = $conn->query("SELECT genere, COUNT(*) as n FROM libri WHERE genere != '' GROUP BY genere ORDER BY n DESC");

// Logout admin
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – Dinateca</title>
    <link rel="stylesheet" href="stile.css">
    <style>
        .admin-navbar {
            background: linear-gradient(135deg, #1a2e22, #1a4731);
            padding: .9rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 2px 20px rgba(0,0,0,.3);
        }
        .admin-navbar .logo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }
        .admin-navbar .logo span { color: #95d5b2; }
        .admin-badge {
            background: #52b788;
            color: #1a4731;
            font-size: .72rem;
            font-weight: 700;
            padding: .2rem .7rem;
            border-radius: 99px;
            letter-spacing: .8px;
            text-transform: uppercase;
            margin-left: .6rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e8f4ec;
            border-radius: 14px;
            padding: 1.4rem 1.2rem;
            text-align: center;
            box-shadow: 0 2px 12px rgba(26,71,49,.08);
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(26,71,49,.13); }
        .stat-icon { font-size: 2rem; margin-bottom: .5rem; }
        .stat-n { font-family: 'Playfair Display', serif; font-size: 2.4rem; font-weight: 800; color: #1a4731; line-height: 1; }
        .stat-l { font-size: .78rem; color: #607a6a; font-weight: 600; margin-top: .3rem; text-transform: uppercase; letter-spacing: .5px; }

        .section-card {
            background: #fff;
            border: 1px solid #e8f4ec;
            border-radius: 14px;
            padding: 1.5rem 1.8rem;
            box-shadow: 0 2px 12px rgba(26,71,49,.08);
            margin-bottom: 1.2rem;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a4731;
            margin-bottom: 1rem;
            padding-bottom: .6rem;
            border-bottom: 2px solid #d8f3dc;
        }

        .stato-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .55rem 0;
            border-bottom: 1px solid #f0f7f2;
            font-size: .92rem;
        }
        .stato-row:last-child { border-bottom: none; }
        .stato-bar-wrap { flex: 1; margin: 0 1rem; height: 8px; background: #f0f7f2; border-radius: 99px; overflow: hidden; }
        .stato-bar { height: 100%; border-radius: 99px; }

        .genere-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .45rem 0;
            font-size: .88rem;
            border-bottom: 1px solid #f0f7f2;
        }
        .genere-row:last-child { border-bottom: none; }

        table { font-size: .88rem; }
        th { font-size: .72rem; }
    </style>
</head>
<body style="background:#f4f7f4;">

<div class="admin-navbar">
    <div>
        <a class="logo" href="admin.php">📗 <span>Dina</span>teca</a>
        <span class="admin-badge">⚙️ Admin</span>
    </div>
    <div style="display:flex;gap:.8rem;align-items:center;">
        <a href="index.php" class="btn btn-grigio" style="font-size:.82rem;">← Vai al sito</a>
        <a href="admin.php?logout=1" class="btn btn-rosso" style="font-size:.82rem;">Esci</a>
    </div>
</div>

<div class="container">
    <h1 style="margin-top:1.5rem;">Dashboard Amministratore</h1>

    <!-- ── Statistiche principali ───────────────────────── -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-n"><?php echo $totale_utenti; ?></div>
            <div class="stat-l">Utenti registrati</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-n"><?php echo $totale_libri; ?></div>
            <div class="stat-l">Libri totali</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-n"><?php echo $media_voti ?: '–'; ?></div>
            <div class="stat-l">Media voti</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📖</div>
            <div class="stat-n"><?php echo $totale_utenti > 0 ? round($totale_libri / $totale_utenti, 1) : 0; ?></div>
            <div class="stat-l">Libri per utente</div>
        </div>
        <?php if ($genere_pop): ?>
        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-n" style="font-size:1.4rem;"><?php echo htmlspecialchars($genere_pop['genere']); ?></div>
            <div class="stat-l">Genere più letto</div>
        </div>
        <?php endif; ?>
        <?php if ($utente_top): ?>
        <div class="stat-card">
            <div class="stat-icon">👑</div>
            <div class="stat-n" style="font-size:1.4rem;"><?php echo htmlspecialchars($utente_top['username']); ?></div>
            <div class="stat-l">Utente più attivo (<?php echo $utente_top['n']; ?> libri)</div>
        </div>
        <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.2rem;">

        <!-- ── Stato lettura ─────────────────────────────── -->
        <div class="section-card">
            <div class="section-title">📊 Stato lettura</div>
            <?php
            $stati = [
                ['label' => '✅ Letti',       'n' => $letti,      'color' => '#52b788'],
                ['label' => '📖 In lettura',  'n' => $in_lettura, 'color' => '#f59e0b'],
                ['label' => '🕐 Da leggere',  'n' => $da_leggere, 'color' => '#94a3b8'],
            ];
            foreach ($stati as $st):
                $pct = $totale_libri > 0 ? round($st['n'] / $totale_libri * 100) : 0;
            ?>
            <div class="stato-row">
                <span style="min-width:110px;"><?php echo $st['label']; ?></span>
                <div class="stato-bar-wrap">
                    <div class="stato-bar" style="width:<?php echo $pct; ?>%;background:<?php echo $st['color']; ?>;"></div>
                </div>
                <span style="min-width:60px;text-align:right;font-weight:700;color:#1a4731;">
                    <?php echo $st['n']; ?> <span style="color:#607a6a;font-weight:400;">(<?php echo $pct; ?>%)</span>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Distribuzione generi ──────────────────────── -->
        <div class="section-card">
            <div class="section-title">📂 Distribuzione generi</div>
            <?php while ($g = $generi->fetch_assoc()): ?>
            <div class="genere-row">
                <span><?php echo htmlspecialchars($g['genere']); ?></span>
                <strong style="color:#1a4731;"><?php echo $g['n']; ?></strong>
            </div>
            <?php endwhile; ?>
        </div>

    </div>

    <!-- ── Ultimi utenti registrati ──────────────────────── -->
    <div class="section-card">
        <div class="section-title">🆕 Ultimi utenti registrati</div>
        <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Email</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($u = $ultimi_utenti->fetch_assoc()): ?>
            <tr>
                <td style="color:#607a6a;"><?php echo $u['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                <td style="color:#607a6a;"><?php echo htmlspecialchars($u['email']); ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>

</div>

<footer>📗 Dinateca Admin Panel</footer>
</body>
</html>
