<?php
include "connessione.php";
if (!isset($_SESSION['id_utente'])) { header("Location: index.php"); exit; }

$colori_preset = [
    '#2d6a4f','#1a4731','#52b788','#457b9d','#e63946',
    '#f4a261','#6d2e46','#3a3a5c','#a07850','#607a6a',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Libro – Dinateca</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>

<div class="navbar">
    <a class="logo" href="visualizza.php">📗 <span>Dina</span>teca</a>
    <nav>
        <a href="visualizza.php">📚 Libreria</a>
        <a href="form_inserimento.php" class="nav-active">➕ Aggiungi</a>
        <a href="logout.php">Esci (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
    </nav>
</div>

<div class="container">
<div class="form-box" style="max-width:620px;">
    <h2>➕ Aggiungi un libro</h2>
    <form action="salva.php" method="POST" id="form-libro">

        <div class="form-gruppo">
            <label>Titolo *</label>
            <input type="text" name="titolo" id="titolo" required placeholder="Es. Il nome della rosa">
        </div>

        <div class="form-gruppo">
            <label>Autore *</label>
            <input type="text" name="autore" id="autore" required placeholder="Es. Umberto Eco">
        </div>

        <div class="form-gruppo">
            <label>Anno di pubblicazione</label>
            <input type="number" name="anno_pubblicazione" id="anno_pubblicazione"
                   min="1000" max="<?php echo date('Y'); ?>" placeholder="Es. 1980">
        </div>

        <div class="form-gruppo">
            <label>Genere</label>
            <select name="genere" id="genere">
                <option value="">-- Scegli --</option>
                <?php
                $generi = ['Romanzo','Romanzo Psicologico','Fantascienza','Fantasy',
                           'Thriller','Horror','Storico','Saggistica','Altro'];
                foreach ($generi as $g) echo "<option value='$g'>$g</option>";
                ?>
            </select>
        </div>

        <div class="form-gruppo">
            <label>Stato lettura</label>
            <select name="stato" id="stato">
                <option value="Da leggere">Da leggere</option>
                <option value="In lettura">In lettura</option>
                <option value="Letto">Letto</option>
            </select>
        </div>

        <div class="form-gruppo">
            <label>Voto (1–5)</label>
            <select name="voto" id="voto">
                <option value="">Nessun voto</option>
                <?php for ($i=1;$i<=5;$i++) echo "<option value='$i'>$i " . str_repeat('★',$i) . "</option>"; ?>
            </select>
        </div>

        <!-- ── Colore copertina ──────────────────────────── -->
        <div class="form-gruppo">
            <label>🎨 Colore copertina</label>
            <div class="color-picker-wrap">
                <div class="color-swatches">
                    <?php foreach ($colori_preset as $i => $c): ?>
                        <span class="color-swatch <?php echo $i===0?'active':''; ?>"
                              data-color="<?php echo $c; ?>"
                              style="background:<?php echo $c; ?>;"
                              title="<?php echo $c; ?>"></span>
                    <?php endforeach; ?>
                </div>
                <input type="color" id="custom-color" class="color-custom"
                       value="<?php echo $colori_preset[0]; ?>" title="Colore personalizzato">
                <input type="hidden" name="colore_copertina" id="colore_copertina"
                       value="<?php echo $colori_preset[0]; ?>">
            </div>
        </div>

        <div class="form-gruppo">
            <label>Commento personale</label>
            <textarea name="commento" placeholder="Cosa ne pensi di questo libro?"></textarea>
        </div>

        <div style="display:flex;gap:.6rem;margin-top:.6rem;">
            <button type="submit" class="btn btn-verde">💾 Salva libro</button>
            <a href="visualizza.php" class="btn btn-grigio">Annulla</a>
        </div>
    </form>
</div>
</div>

<footer>📗 Dinateca</footer>
<script src="script.js"></script>
</body>
</html>
