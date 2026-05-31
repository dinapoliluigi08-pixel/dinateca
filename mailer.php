<?php
/**
 * mailer.php – Invio email tramite API HTTPS di Brevo
 *
 * Perché l'API e non SMTP:
 *  - Railway BLOCCA le porte SMTP (587/465) sui piani Free/Trial/Hobby.
 *  - L'API di Brevo viaggia su HTTPS (porta 443), che non è mai bloccata.
 *
 * Variabili d'ambiente richieste (impostarle su Railway → Variables,
 * e nel file .env per il locale):
 *  - BREVO_API_KEY : la API key generata su Brevo
 *  - MAIL_FROM     : email del mittente VERIFICATO su Brevo (es. noreplydinateca@gmail.com)
 *  - MAIL_NAME     : nome mostrato come mittente (es. Dinateca)
 *
 * Come usarlo (invariato rispetto a prima):
 *  require_once "mailer.php";
 *  $risultato = inviaEmail("dest@email.it", "Oggetto", "<b>Corpo HTML</b>");
 *  if ($risultato === true) { ... } else { echo $risultato; }
 */

require_once __DIR__ . "/env_loader.php";

/**
 * Invia una email tramite l'API transazionale di Brevo.
 *
 * @param string $to        Email destinatario
 * @param string $subject   Oggetto dell'email
 * @param string $body      Corpo in HTML
 * @return true|string      true se ok, stringa con l'errore se fallisce
 */
function inviaEmail(string $to, string $subject, string $body) {
    $apiKey   = env('BREVO_API_KEY');
    $fromMail = env('MAIL_FROM');
    $fromName = env('MAIL_NAME', 'Dinateca');

    if (!$apiKey) {
        return "BREVO_API_KEY mancante: impostala nelle Variables di Railway (e nel .env in locale).";
    }
    if (!$fromMail) {
        return "MAIL_FROM mancante: impostala nelle Variables di Railway (e nel .env in locale).";
    }

    // Versione testo semplice per i client che non leggono l'HTML
    $altBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

    $payload = [
        'sender'      => ['name' => $fromName, 'email' => $fromMail],
        'to'          => [['email' => $to]],
        'subject'     => $subject,
        'htmlContent' => $body,
        'textContent' => $altBody,
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    // Errore di rete/cURL (es. timeout)
    if ($response === false) {
        return "Errore di connessione a Brevo: " . $curlErr;
    }

    // Brevo restituisce 201 (Created) quando l'email è accettata
    if ($httpCode === 201) {
        return true;
    }

    // Qualsiasi altro codice: prova a estrarre il messaggio d'errore di Brevo
    $data = json_decode($response, true);
    $msg  = $data['message'] ?? $response;
    return "Brevo ha rifiutato l'invio (HTTP $httpCode): " . $msg;
}

/**
 * Template HTML per l'email di benvenuto. (INVARIATO)
 */
function templateBenvenuto(string $username): string {
    $anno = date('Y');
    return "
    <!DOCTYPE html>
    <html lang='it'>
    <head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#f0f7f2;font-family:sans-serif;'>
      <table width='100%' cellpadding='0' cellspacing='0'>
        <tr><td align='center' style='padding:40px 20px;'>
          <table width='560' cellpadding='0' cellspacing='0'
                 style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(26,71,49,.1);'>

            <!-- Header -->
            <tr>
              <td style='background:linear-gradient(135deg,#1a4731,#2d6a4f);padding:36px 40px;text-align:center;'>
                <div style='font-size:2.2rem;'>📗</div>
                <h1 style='margin:8px 0 0;color:#ffffff;font-size:1.6rem;letter-spacing:-0.5px;'>
                  <span style='color:#95d5b2;'>Dina</span>teca
                </h1>
                <p style='color:#b7e4c7;margin:4px 0 0;font-size:.9rem;'>La tua biblioteca digitale personale</p>
              </td>
            </tr>

            <!-- Corpo -->
            <tr>
              <td style='padding:36px 40px;'>
                <h2 style='color:#1a4731;margin:0 0 12px;font-size:1.25rem;'>
                  Benvenuto, <strong>$username</strong>! 🎉
                </h2>
                <p style='color:#374151;line-height:1.7;margin:0 0 16px;'>
                  La tua registrazione su <strong>Dinateca</strong> è avvenuta con successo.
                  Ora puoi iniziare a costruire la tua libreria digitale personale.
                </p>
                <p style='color:#374151;line-height:1.7;margin:0 0 24px;'>
                  Puoi aggiungere libri, tenere traccia di quelli che stai leggendo,
                  assegnare voti e scrivere le tue impressioni.
                </p>

                <!-- CTA -->
                <div style='text-align:center;margin:28px 0;'>
                  <a href='#' style='background:#2d6a4f;color:#ffffff;text-decoration:none;
                     padding:14px 32px;border-radius:10px;font-weight:700;font-size:.95rem;
                     display:inline-block;'>
                    📚 Vai alla tua libreria
                  </a>
                </div>

                <hr style='border:none;border-top:1px solid #d8f3dc;margin:24px 0;'>
                <p style='color:#6b7280;font-size:.83rem;margin:0;line-height:1.6;'>
                  Hai ricevuto questa email perché ti sei registrato su Dinateca.<br>
                  Se non sei stato tu, ignora questa email.
                </p>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td style='background:#f0f7f2;padding:18px 40px;text-align:center;'>
                <p style='color:#74a882;font-size:.8rem;margin:0;'>
                  📗 Dinateca &copy; $anno — Progetto di maturità
                </p>
              </td>
            </tr>

          </table>
        </td></tr>
      </table>
    </body>
    </html>";
}
