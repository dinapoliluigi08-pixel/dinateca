// script.js – Dinateca v3

document.addEventListener("DOMContentLoaded", function () {

  // ─────────────────────────────────────────────────────────
  // 1. COLOR PICKER COPERTINA
  // ─────────────────────────────────────────────────────────
  var swatches   = document.querySelectorAll(".color-swatch");
  var colorInput = document.getElementById("colore_copertina");
  var customPick = document.getElementById("custom-color");

  swatches.forEach(function (sw) {
    sw.addEventListener("click", function () {
      swatches.forEach(s => s.classList.remove("active"));
      sw.classList.add("active");
      if (colorInput) colorInput.value = sw.dataset.color;
      if (customPick) customPick.value = sw.dataset.color;
    });
  });

  if (customPick && colorInput) {
    customPick.addEventListener("input", function () {
      colorInput.value = customPick.value;
      swatches.forEach(s => s.classList.remove("active"));
    });
  }

  // ─────────────────────────────────────────────────────────
  // 2. TOGGLE VISTA CARD / TABELLA
  // ─────────────────────────────────────────────────────────
  var btnCard    = document.getElementById("btn-view-card");
  var btnTable   = document.getElementById("btn-view-table");
  var gridView   = document.getElementById("libri-grid-view");
  var tableView  = document.getElementById("libri-table-view");

  function setView(view) {
    localStorage.setItem("dinateca_view", view);
    if (view === "card") {
      if (gridView)  gridView.style.display  = "grid";
      if (tableView) tableView.style.display = "none";
      if (btnCard)   btnCard.classList.add("active");
      if (btnTable)  btnTable.classList.remove("active");
    } else {
      if (gridView)  gridView.style.display  = "none";
      if (tableView) tableView.style.display = "block";
      if (btnCard)   btnCard.classList.remove("active");
      if (btnTable)  btnTable.classList.add("active");
    }
  }

  if (btnCard && btnTable) {
    var saved = localStorage.getItem("dinateca_view") || "card";
    setView(saved);
    btnCard.addEventListener("click",  function () { setView("card"); });
    btnTable.addEventListener("click", function () { setView("table"); });
  }

  // ─────────────────────────────────────────────────────────
  // 3. FORZA PASSWORD
  // ─────────────────────────────────────────────────────────
  var pwInput  = document.getElementById("password");
  var pwBar    = document.getElementById("pw-bar");
  var pwHint   = document.getElementById("pw-hint");
  var confInput= document.getElementById("conferma_password");
  var pwMatch  = document.getElementById("pw-match");

  function calcolaForza(pw) {
    var s = 0;
    if (pw.length >= 6)  s++;
    if (pw.length >= 10) s++;
    if (/[A-Z]/.test(pw)) s++;
    if (/[0-9]/.test(pw)) s++;
    if (/[^A-Za-z0-9]/.test(pw)) s++;
    return s;
  }

  if (pwInput && pwBar) {
    pwInput.addEventListener("input", function () {
      var pw = pwInput.value, f = calcolaForza(pw);
      var pct = pw.length === 0 ? 0 : Math.max(15, f * 20);
      var color, label;
      if      (!pw)   { pct=0; color=""; label=""; }
      else if (f <= 1){ color="#dc2626"; label="🔴 Debole"; }
      else if (f <= 2){ color="#f59e0b"; label="🟠 Discreta"; }
      else if (f <= 3){ color="#eab308"; label="🟡 Media"; }
      else if (f <= 4){ color="#22c55e"; label="🟢 Buona"; }
      else            { color="#15803d"; label="✅ Ottima!"; }
      pwBar.style.width = pct + "%";
      pwBar.style.background = color;
      if (pwHint) { pwHint.textContent = label; pwHint.style.color = color; }
      if (confInput && confInput.value) controllaMatch();
    });
  }

  function controllaMatch() {
    if (!confInput || !pwMatch || !pwInput) return;
    var ok = pwInput.value === confInput.value && confInput.value !== "";
    pwMatch.textContent = confInput.value === "" ? "" : (ok ? "✅ Coincidono" : "❌ Non coincidono");
    pwMatch.style.color = ok ? "#15803d" : "#dc2626";
    confInput.classList.toggle("input-ok",    ok);
    confInput.classList.toggle("input-errore", !ok && confInput.value !== "");
  }
  if (confInput) confInput.addEventListener("input", controllaMatch);

  // ─────────────────────────────────────────────────────────
  // 4. VALIDAZIONE FORM REGISTRAZIONE
  // ─────────────────────────────────────────────────────────
  var formRegistra = document.getElementById("form-registra");
  if (formRegistra) {
    formRegistra.addEventListener("submit", function (e) {
      var u = document.getElementById("username").value.trim();
      var em= document.getElementById("email").value.trim();
      var pw= pwInput ? pwInput.value : "";
      var co= confInput ? confInput.value : "";
      var err = [];
      if (u.length < 3)   err.push("Username: minimo 3 caratteri.");
      if (!em.includes("@") || !em.includes(".")) err.push("Email non valida.");
      if (pw.length < 6)  err.push("Password: minimo 6 caratteri.");
      if (pw !== co)      err.push("Le password non coincidono.");
      if (err.length) { e.preventDefault(); mostraErrori(err); }
    });
  }

  // ─────────────────────────────────────────────────────────
  // 5. VALIDAZIONE FORM LOGIN
  // ─────────────────────────────────────────────────────────
  var formLogin = document.getElementById("form-login");
  if (formLogin) {
    formLogin.addEventListener("submit", function (e) {
      var u = document.getElementById("username").value.trim();
      var p = document.getElementById("password").value;
      var err = [];
      if (!u) err.push("Inserisci il tuo username.");
      if (!p) err.push("Inserisci la tua password.");
      if (err.length) { e.preventDefault(); mostraErrori(err); }
    });
  }

  // ─────────────────────────────────────────────────────────
  // 6. VALIDAZIONE FORM LIBRO
  // ─────────────────────────────────────────────────────────
  var formLibro = document.getElementById("form-libro");
  if (formLibro) {
    formLibro.addEventListener("submit", function (e) {
      var titolo = document.getElementById("titolo").value.trim();
      var autore = document.getElementById("autore").value.trim();
      var err = [];
      if (!titolo) err.push("Il titolo è obbligatorio.");
      if (!autore) err.push("L'autore è obbligatorio.");
      var annoEl = document.getElementById("anno_pubblicazione");
      if (annoEl && annoEl.value) {
        var a = parseInt(annoEl.value), oggi = new Date().getFullYear();
        if (a < 1000 || a > oggi) err.push("Anno non valido (1000–" + oggi + ").");
      }
      var stato = document.getElementById("stato");
      var voto  = document.getElementById("voto");
      if (stato && voto && stato.value === "Letto" && !voto.value)
        err.push("Inserisci un voto per i libri 'Letto'.");
      if (err.length) { e.preventDefault(); mostraErrori(err); }
    });
  }

  // ─────────────────────────────────────────────────────────
  // 7. CONFERMA ELIMINAZIONE
  // ─────────────────────────────────────────────────────────
  document.querySelectorAll(".link-elimina").forEach(function (link) {
    link.addEventListener("click", function (e) {
      var t = link.dataset.titolo || "questo libro";
      if (!confirm('Eliminare "' + t + '"?\nOperazione non reversibile.')) e.preventDefault();
    });
  });

  // ─────────────────────────────────────────────────────────
  // 8. AUTO-DISMISS messaggi OK dopo 5 secondi
  // ─────────────────────────────────────────────────────────
  var msg = document.querySelector(".messaggio-ok");
  if (msg && !msg.querySelector("a.btn")) {
    setTimeout(function () {
      msg.style.transition = "opacity .5s, max-height .5s, padding .5s, margin .5s";
      msg.style.opacity = "0"; msg.style.maxHeight = "0";
      msg.style.padding = "0"; msg.style.margin = "0"; msg.style.overflow = "hidden";
    }, 5000);
  }

  // ─────────────────────────────────────────────────────────
  // 9. FUNZIONE – mostra errori
  // ─────────────────────────────────────────────────────────
  function mostraErrori(err) {
    var box = document.getElementById("js-errori");
    if (!box) {
      box = document.createElement("div");
      box.id = "js-errori";
      box.className = "messaggio-errore";
      var form = document.querySelector("form");
      form.parentNode.insertBefore(box, form);
    }
    box.innerHTML = "<strong>Correggi i seguenti campi:</strong><br>" +
      err.map(function (e) { return "• " + e; }).join("<br>");
    box.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

});
