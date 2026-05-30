<?php
include "connessione.php";
if (!isset($_SESSION['id_utente'])) { header("Location: index.php"); exit; }

$id        = (int)$_GET['id'];
$id_utente = $_SESSION['id_utente'];

$stmt = $conn->prepare("SELECT * FROM libri WHERE id=? AND id_utente=?");
$stmt->bind_param("ii", $id, $id_utente);
$stmt->execute();
$libro = $stmt->get_result()->fetch_assoc();
if (!$libro) { header("Location: visualizza.php"); exit; }

$colore_attuale = $libro['colore_copertina'] ?? '#2d6a4f';
$colori_preset  = [
    '#2d6a4f','#1a4731','#52b788','#457b9d','#e63946',
    '#f4a261','#6d2e46','#3a3a5c','#a07850','#607a6a',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica – Dinateca</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>

<div class="navbar">
    <a class="logo" href="visualizza.php">📗 <span>Dina</span>teca</a>
    <nav>
        <a href="visualizza.php">📚 Libreria</a>
        <a href="form_inserimento.php">➕ Aggiungi</a>
        <a href="logout.php">Esci (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
    </nav>
</div>

<div class="container">
<div class="form-box" style="max-width:620px;">
    <h2>✏ Modifica libro</h2>
    <form action="aggiorna.php" method="POST" id="form-libro">
        <input type="hidden" name="id" value="<?php echo $libro['id']; ?>">

        <div class="form-gruppo">
            <label>Titolo *</label>
            <input type="text" name="titolo" id="titolo" required
                   value="<?php echo htmlspecialchars($libro['titolo']); ?>">
        </div>

        <div class="form-gruppo">
            <label>Autore *</label>
            <input type="text" name="autore" id="autore" required
                   value="<?php echo htmlspecialchars($libro['autore']); ?>">
        </div>

        <div class="form-gruppo">
            <label>Anno di pubblicazione</label>
            <input type="number" name="anno_pubblicazione" id="anno_pubblicazione"
                   min="1000" max="<?php echo date('Y'); ?>"
                   value="<?php echo htmlspecialchars($libro['anno_pubblicazione'] ?? ''); ?>">
        </div>

        <div class="form-gruppo">
            <label>Genere</label>
            <select name="genere" id="genere">
                <option value="">-- Scegli --</option>
                <?php
                $generi = ['Romanzo','Romanzo Psicologico','Fantascienza','Fantasy',
                           'Thriller','Horror','Storico','Saggistica','Altro'];
                foreach ($generi as $g) {
                    $sel = $libro['genere'] === $g ? 'selected' : '';
                    echo "<option value='$g' $sel>" . htmlspecialchars($g) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-gruppo">
            <label>Stato lettura</label>
            <select name="stato" id="stato">
                <?php
                foreach (['Da leggere','In lettura','Letto'] as $s) {
                    $sel = $libro['stato'] === $s ? 'selected' : '';
                    echo "<option value='$s' $sel>$s</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-gruppo">
            <label>Voto</label>
            <select name="voto" id="voto">
                <option value="">Nessun voto</option>
                <?php for ($i=1;$i<=5;$i++) {
                    $sel = $libro['voto'] == $i ? 'selected' : '';
                    echo "<option value='$i' $sel>$i " . str_repeat('★',$i) . "</option>";
                } ?>
            </select>
        </div>

        <!-- ── Colore copertina ──────────────────────────── -->
        <div class="form-gruppo">
            <label>🎨 Colore copertina</label>
            <div class="color-picker-wrap">
                <div class="color-swatches">
                    <?php foreach ($colori_preset as $c): ?>
                        <span class="color-swatch <?php echo $c===$colore_attuale?'active':''; ?>"
                              data-color="<?php echo $c; ?>"
                              style="background:<?php echo $c; ?>;"
                              title="<?php echo $c; ?>"></span>
                    <?php endforeach; ?>
                </div>
                <input type="color" id="custom-color" class="color-custom"
                       value="<?php echo htmlspecialchars($colore_attuale); ?>"
                       title="Colore personalizzato">
                <input type="hidden" name="colore_copertina" id="colore_copertina"
                       value="<?php echo htmlspecialchars($colore_attuale); ?>">
            </div>
        </div>

        <div class="form-gruppo">
            <label>Commento personale</label>
            <textarea name="commento"><?php echo htmlspecialchars($libro['commento'] ?? ''); ?></textarea>
        </div>

        <div style="display:flex;gap:.6rem;margin-top:.6rem;">
            <button type="submit" class="btn btn-verde">💾 Salva modifiche</button>
            <a href="visualizza.php" class="btn btn-grigio">Annulla</a>
        </div>
    </form>
</div>
</div>

<footer>📗 Dinateca</footer>
<script src="script.js"></script>
</body>
</html>
