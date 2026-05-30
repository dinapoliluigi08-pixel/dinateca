<?php
/**
 * mailer.php – Wrapper per PHPMailer
 *
 * Come funziona:
 *  - Usa le credenziali Gmail dal file .env (MAIL_FROM, MAIL_PASS, MAIL_NAME)
 *  - Si connette al server SMTP di Gmail sulla porta 587 con cifratura TLS
 *  - Manda l'email e restituisce true (successo) o una stringa con l'errore
 *
 * Come usarlo:
 *  require_once "mailer.php";
 *  $risultato = inviaEmail("dest@email.it", "Oggetto", "<b>Corpo HTML</b>");
 *  if ($risultato === true) { ... } else { echo $risultato; }
 */

require_once __DIR__ . "/env_loader.php";
require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/Exception.php";
require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/PHPMailer.php";
require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Invia una email tramite Gmail SMTP.
 *
 * @param string $to        Email destinatario
 * @param string $subject   Oggetto dell'email
 * @param string $body      Corpo in HTML
 * @return true|string      true se ok, stringa con errore se fallisce
 */
function inviaEmail(string $to, string $subject, string $body) {
    $mail = new PHPMailer(true); // true = lancia eccezioni in caso di errore

    try {
        // ── Impostazioni server SMTP ──────────────────────────
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';   // Server Gmail
        $mail->SMTPAuth   = true;               // Richiede autenticazione
        $mail->Username   = env('MAIL_FROM');   // Dal .env
        $mail->Password   = env('MAIL_PASS');   // App Password Gmail dal .env
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        $mail->Port       = 587;                // Porta Gmail TLS

        // ── Mittente e destinatario ───────────────────────────
        $mail->setFrom(env('MAIL_FROM'), env('MAIL_NAME', 'Dinateca'));
        $mail->addAddress($to);
        $mail->CharSet = 'UTF-8';

        // ── Contenuto ─────────────────────────────────────────
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        // Versione testo semplice per client che non supportano HTML
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

        $mail->send();
        return true;

    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

/**
 * Template HTML per l'email di benvenuto.
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
