<?php
include "connessione.php";
if (!isset($_SESSION['id_utente'])) { header("Location: index.php"); exit; }

$id_utente = $_SESSION['id_utente'];

// ── Ricerca ───────────────────────────────────────────────
$cerca = "";
if (isset($_GET['cerca']) && $_GET['cerca'] != "") {
    $cerca = $_GET['cerca'];
    $param = "%" . $cerca . "%";
    $stmt = $conn->prepare(
        "SELECT * FROM libri WHERE id_utente=? AND (titolo LIKE ? OR autore LIKE ?) ORDER BY id DESC"
    );
    $stmt->bind_param("iss", $id_utente, $param, $param);
} else {
    $stmt = $conn->prepare("SELECT * FROM libri WHERE id_utente=? ORDER BY id DESC");
    $stmt->bind_param("i", $id_utente);
}
$stmt->execute();
$result = $stmt->get_result();
$libri  = $result->fetch_all(MYSQLI_ASSOC);

// ── Statistiche ───────────────────────────────────────────
$totale    = count($libri);
$letti     = count(array_filter($libri, fn($l) => $l['stato'] === 'Letto'));
$inLettura = count(array_filter($libri, fn($l) => $l['stato'] === 'In lettura'));
$voti      = array_filter(array_column($libri, 'voto'));
$mediaVoto = count($voti) > 0 ? round(array_sum($voti) / count($voti), 1) : 0;

function badgeStato($stato): string {
    return match($stato) {
        'Letto'      => '<span class="badge badge-letto">✅ Letto</span>',
        'In lettura' => '<span class="badge badge-in-lettura">📖 In lettura</span>',
        default      => '<span class="badge badge-da-leggere">🕐 Da leggere</span>',
    };
}
function stelle($v): string {
    if (!$v) return '–';
    return '<span class="stelle">' . str_repeat('★', $v) . str_repeat('☆', 5 - $v) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>La mia Libreria – Dinateca</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>

<div class="navbar">
    <a class="logo" href="visualizza.php">📗 <span>Dina</span>teca</a>
    <nav>
        <a href="visualizza.php" class="nav-active">📚 Libreria</a>
        <a href="form_inserimento.php">➕ Aggiungi</a>
        <a href="logout.php">Esci (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
    </nav>
</div>

<div class="container">
    <h1>La mia libreria</h1>

    <?php if (isset($_GET['msg'])): ?>
        <div class="messaggio-ok">
            <?php
            echo match($_GET['msg']) {
                'aggiunto'   => '✅ Libro aggiunto con successo!',
                'modificato' => '✅ Libro modificato con successo!',
                'eliminato'  => '🗑 Libro eliminato.',
                'benvenuto'  => '🎉 Benvenuto su Dinateca! Ti abbiamo inviato un\'email di benvenuto.',
                default      => '',
            };
            ?>
        </div>
    <?php endif; ?>

    <!-- ── Statistiche ─────────────────────────────────── -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon blu">📚</div>
            <div>
                <div class="stat-n"><?php echo $totale; ?></div>
                <div class="stat-l">Totale libri</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon verde">✅</div>
            <div>
                <div class="stat-n"><?php echo $letti; ?></div>
                <div class="stat-l">Letti</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon giallo">📖</div>
            <div>
                <div class="stat-n"><?php echo $inLettura; ?></div>
                <div class="stat-l">In lettura</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon arancio">⭐</div>
            <div>
                <div class="stat-n"><?php echo $mediaVoto ?: '–'; ?></div>
                <div class="stat-l">Media voto</div>
            </div>
        </div>
    </div>

    <!-- ── Barra azioni ────────────────────────────────── -->
    <div class="barra-azioni">
        <div style="display:flex;gap:.5rem;align-items:center;">
            <a href="form_inserimento.php" class="btn btn-verde">➕ Aggiungi libro</a>
            <div class="view-toggle">
                <button id="btn-view-card"  title="Vista card">⊞</button>
                <button id="btn-view-table" title="Vista tabella">☰</button>
            </div>
        </div>
        <form method="GET">
            <input type="text" name="cerca"
                   placeholder="🔍 Cerca titolo o autore..."
                   value="<?php echo htmlspecialchars($cerca); ?>">
            <button type="submit" class="btn btn-blu">Cerca</button>
            <?php if ($cerca): ?>
                <a href="visualizza.php" class="btn btn-grigio">✖ Tutti</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($libri)): ?>
    <!-- ── Empty state ─────────────────────────────────── -->
    <div class="empty-state">
        <div class="empty-icon">📖</div>
        <h3>La tua libreria è vuota</h3>
        <p>Aggiungi il tuo primo libro e inizia a tracciare le tue letture!</p>
        <a href="form_inserimento.php" class="btn btn-verde">➕ Aggiungi il primo libro</a>
    </div>

    <?php else: ?>

    <!-- ── Vista CARD ──────────────────────────────────── -->
    <div class="libri-grid" id="libri-grid-view">
        <?php foreach ($libri as $libro): ?>
        <div class="libro-card">
            <div class="libro-copertina"
                 style="background:<?php echo htmlspecialchars($libro['colore_copertina'] ?? '#2d6a4f'); ?>;"></div>
            <div class="libro-body">
                <div class="libro-titolo"><?php echo htmlspecialchars($libro['titolo']); ?></div>
                <div class="libro-autore"><?php echo htmlspecialchars($libro['autore']); ?></div>
                <?php if ($libro['anno_pubblicazione']): ?>
                    <div class="libro-anno"><?php echo $libro['anno_pubblicazione']; ?></div>
                <?php endif; ?>
                <div class="libro-meta">
                    <?php echo badgeStato($libro['stato']); ?>
                    <?php if ($libro['voto']): ?>
                        <span class="stelle" style="font-size:.8rem;"><?php echo str_repeat('★', $libro['voto']); ?></span>
                    <?php endif; ?>
                    <?php if ($libro['genere']): ?>
                        <span style="font-size:.72rem;color:var(--muted);background:#f0f7f2;padding:.1rem .45rem;border-radius:99px;">
                            <?php echo htmlspecialchars($libro['genere']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($libro['commento'])): ?>
                    <div class="libro-commento"><?php echo htmlspecialchars($libro['commento']); ?></div>
                <?php endif; ?>
            </div>
            <div class="libro-azioni">
                <a href="modifica.php?id=<?php echo $libro['id']; ?>" class="btn btn-blu" style="flex:1;font-size:.8rem;">✏ Modifica</a>
                <a href="elimina.php?id=<?php echo $libro['id']; ?>"
                   class="btn btn-rosso link-elimina"
                   data-titolo="<?php echo htmlspecialchars($libro['titolo']); ?>"
                   style="flex:1;font-size:.8rem;">🗑 Elimina</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Vista TABELLA ───────────────────────────────── -->
    <div id="libri-table-view" style="display:none;">
    <div class="table-wrapper">
    <table>
        <thead>
        <tr>
            <th>Colore</th>
            <th>Titolo</th>
            <th>Autore</th>
            <th>Anno</th>
            <th>Genere</th>
            <th>Stato</th>
            <th>Voto</th>
            <th>Commento</th>
            <th>Azioni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($libri as $libro): ?>
        <tr>
            <td>
                <span class="copertina-dot"
                      style="background:<?php echo htmlspecialchars($libro['colore_copertina'] ?? '#2d6a4f'); ?>;"></span>
            </td>
            <td><strong><?php echo htmlspecialchars($libro['titolo']); ?></strong></td>
            <td><?php echo htmlspecialchars($libro['autore']); ?></td>
            <td class="anno-cell"><?php echo $libro['anno_pubblicazione'] ?: '–'; ?></td>
            <td><?php echo htmlspecialchars($libro['genere'] ?: '–'); ?></td>
            <td><?php echo badgeStato($libro['stato']); ?></td>
            <td><?php echo stelle($libro['voto']); ?></td>
            <td>
                <?php if (!empty($libro['commento'])): ?>
                    <span class="commento-testo" title="<?php echo htmlspecialchars($libro['commento']); ?>">
                        <?php echo htmlspecialchars($libro['commento']); ?>
                    </span>
                <?php else: echo '–'; endif; ?>
            </td>
            <td style="white-space:nowrap;">
                <a href="modifica.php?id=<?php echo $libro['id']; ?>" class="btn btn-blu">✏</a>
                <a href="elimina.php?id=<?php echo $libro['id']; ?>"
                   class="btn btn-rosso link-elimina"
                   data-titolo="<?php echo htmlspecialchars($libro['titolo']); ?>">🗑</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    </div>

    <?php endif; ?>
</div>

<footer>📗 Dinateca — <?php echo htmlspecialchars($_SESSION['username']); ?>'s Library</footer>
<script src="script.js"></script>
</body>
</html>
