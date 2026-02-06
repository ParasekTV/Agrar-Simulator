<?php
/**
 * Mailer Klasse
 *
 * E-Mail-Versand über SMTP oder native mail() Funktion.
 */
class Mailer
{
    /**
     * Prüft ob E-Mail-Verifizierung aktiviert ist
     */
    public static function isVerificationEnabled(): bool
    {
        return defined('EMAIL_VERIFICATION_ENABLED') && EMAIL_VERIFICATION_ENABLED === true;
    }

    /**
     * Sendet eine E-Mail
     *
     * @param string $to Empfänger-Adresse
     * @param string $subject Betreff
     * @param string $htmlBody HTML-Inhalt
     * @param string $textBody Text-Inhalt (optional)
     * @return bool Erfolg
     */
    public static function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        // SMTP-Konfiguration prüfen
        $useSmtp = defined('SMTP_HOST') && !empty(SMTP_HOST);

        if ($useSmtp) {
            return self::sendSmtp($to, $subject, $htmlBody, $textBody);
        }

        return self::sendNative($to, $subject, $htmlBody, $textBody);
    }

    /**
     * Sendet E-Mail über SMTP
     */
    private static function sendSmtp(string $to, string $subject, string $htmlBody, string $textBody): bool
    {
        try {
            $host = SMTP_HOST;
            $port = defined('SMTP_PORT') ? SMTP_PORT : 587;
            $username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
            $password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : $username;
            $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'LSBG Agrar Simulator';
            $encryption = defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls';

            // Socket-Verbindung
            $socket = self::connectSmtp($host, $port, $encryption);
            if (!$socket) {
                throw new Exception("SMTP-Verbindung fehlgeschlagen");
            }

            // SMTP-Befehle
            self::smtpCommand($socket, "EHLO " . gethostname());

            // STARTTLS wenn nötig
            if ($encryption === 'tls') {
                self::smtpCommand($socket, "STARTTLS");
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                self::smtpCommand($socket, "EHLO " . gethostname());
            }

            // Authentifizierung
            if (!empty($username)) {
                self::smtpCommand($socket, "AUTH LOGIN");
                self::smtpCommand($socket, base64_encode($username));
                self::smtpCommand($socket, base64_encode($password));
            }

            // E-Mail senden
            self::smtpCommand($socket, "MAIL FROM:<{$fromEmail}>");
            self::smtpCommand($socket, "RCPT TO:<{$to}>");
            self::smtpCommand($socket, "DATA");

            // Header und Body
            $boundary = md5(time());
            $headers = [
                "From: {$fromName} <{$fromEmail}>",
                "To: {$to}",
                "Subject: {$subject}",
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
                "X-Mailer: LSBG-Agrar-Simulator"
            ];

            $message = implode("\r\n", $headers) . "\r\n\r\n";

            // Text-Teil
            if (!empty($textBody)) {
                $message .= "--{$boundary}\r\n";
                $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
                $message .= $textBody . "\r\n\r\n";
            }

            // HTML-Teil
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $message .= $htmlBody . "\r\n\r\n";
            $message .= "--{$boundary}--\r\n";
            $message .= ".";

            self::smtpCommand($socket, $message);
            self::smtpCommand($socket, "QUIT");

            fclose($socket);

            Logger::info('E-Mail gesendet', ['to' => $to, 'subject' => $subject]);
            return true;

        } catch (Exception $e) {
            Logger::error('SMTP-Fehler', ['error' => $e->getMessage(), 'to' => $to]);
            return false;
        }
    }

    /**
     * Stellt SMTP-Verbindung her
     */
    private static function connectSmtp(string $host, int $port, string $encryption): mixed
    {
        $protocol = ($encryption === 'ssl') ? 'ssl://' : '';
        $socket = @fsockopen($protocol . $host, $port, $errno, $errstr, 30);

        if (!$socket) {
            Logger::error('SMTP-Verbindung fehlgeschlagen', [
                'host' => $host,
                'port' => $port,
                'error' => $errstr
            ]);
            return false;
        }

        // Begrüßung lesen
        fgets($socket, 512);
        return $socket;
    }

    /**
     * Sendet SMTP-Befehl
     */
    private static function smtpCommand($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return fgets($socket, 512);
    }

    /**
     * Sendet E-Mail über native mail() Funktion
     */
    private static function sendNative(string $to, string $subject, string $htmlBody, string $textBody): bool
    {
        $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'LSBG Agrar Simulator';

        $boundary = md5(time());

        $headers = [
            "From: {$fromName} <{$fromEmail}>",
            "Reply-To: {$fromEmail}",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
            "X-Mailer: LSBG-Agrar-Simulator"
        ];

        $message = "";

        if (!empty($textBody)) {
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $message .= $textBody . "\r\n\r\n";
        }

        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $message .= $htmlBody . "\r\n\r\n";
        $message .= "--{$boundary}--";

        $result = @mail($to, $subject, $message, implode("\r\n", $headers));

        if ($result) {
            Logger::info('E-Mail gesendet (native)', ['to' => $to, 'subject' => $subject]);
        } else {
            Logger::error('E-Mail-Versand fehlgeschlagen (native)', ['to' => $to]);
        }

        return $result;
    }

    /**
     * Sendet Verifizierungs-E-Mail
     */
    public static function sendVerificationEmail(string $email, string $username, string $token): bool
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $verifyUrl = rtrim($baseUrl, '/') . '/auth/verify/' . $token;
        $expiryHours = defined('EMAIL_TOKEN_EXPIRY_HOURS') ? EMAIL_TOKEN_EXPIRY_HOURS : 24;

        $subject = 'Aktiviere deinen LSBG Agrar Simulator Account';

        $htmlBody = self::getVerificationEmailHtml($username, $verifyUrl, $expiryHours);
        $textBody = self::getVerificationEmailText($username, $verifyUrl, $expiryHours);

        return self::send($email, $subject, $htmlBody, $textBody);
    }

    /**
     * HTML-Template für Verifizierungs-E-Mail
     */
    private static function getVerificationEmailHtml(string $username, string $verifyUrl, int $expiryHours): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0f0f1a;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #0f0f1a; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; border-radius: 12px; overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4a9f4a 0%, #2d7d2d 100%); padding: 30px; text-align: center;">
                            <h1 style="color: white; margin: 0; font-size: 24px;">LSBG Agrar Simulator</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #ffffff; margin: 0 0 20px;">Willkommen, {$username}!</h2>

                            <p style="color: #b0b0c0; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                Vielen Dank für deine Registrierung beim LSBG Agrar Simulator.
                                Bitte klicke auf den Button unten, um deine E-Mail-Adresse zu bestätigen.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{$verifyUrl}"
                                           style="display: inline-block; background: linear-gradient(135deg, #4a9f4a 0%, #2d7d2d 100%);
                                                  color: white; text-decoration: none; padding: 15px 40px;
                                                  border-radius: 8px; font-size: 16px; font-weight: bold;">
                                            E-Mail bestätigen
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #b0b0c0; font-size: 14px; line-height: 1.6; margin: 20px 0 0;">
                                Dieser Link ist <strong>{$expiryHours} Stunden</strong> gültig.
                            </p>

                            <p style="color: #888; font-size: 12px; margin: 30px 0 0; padding-top: 20px; border-top: 1px solid #2d2d44;">
                                Falls der Button nicht funktioniert, kopiere diesen Link in deinen Browser:<br>
                                <a href="{$verifyUrl}" style="color: #4a9f4a; word-break: break-all;">{$verifyUrl}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #12121f; padding: 20px 30px; text-align: center;">
                            <p style="color: #666; font-size: 12px; margin: 0;">
                                Du erhältst diese E-Mail, weil du dich beim LSBG Agrar Simulator registriert hast.<br>
                                Falls du dich nicht registriert hast, ignoriere diese E-Mail.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Text-Template für Verifizierungs-E-Mail
     */
    private static function getVerificationEmailText(string $username, string $verifyUrl, int $expiryHours): string
    {
        return <<<TEXT
LSBG Agrar Simulator - E-Mail Bestätigung

Willkommen, {$username}!

Vielen Dank für deine Registrierung beim LSBG Agrar Simulator.
Bitte öffne folgenden Link, um deine E-Mail-Adresse zu bestätigen:

{$verifyUrl}

Dieser Link ist {$expiryHours} Stunden gültig.

---
Du erhältst diese E-Mail, weil du dich beim LSBG Agrar Simulator registriert hast.
Falls du dich nicht registriert hast, ignoriere diese E-Mail.
TEXT;
    }

    /**
     * Sendet Passwort-Reset E-Mail
     */
    public static function sendPasswordResetEmail(string $email, string $username, string $token): bool
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $resetUrl = rtrim($baseUrl, '/') . '/auth/reset-password/' . $token;

        $subject = 'Passwort zurücksetzen - LSBG Agrar Simulator';

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0f0f1a;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #0f0f1a; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; border-radius: 12px; padding: 40px;">
                    <tr>
                        <td>
                            <h2 style="color: #ffffff; margin: 0 0 20px;">Hallo {$username},</h2>
                            <p style="color: #b0b0c0;">Du hast eine Passwort-Zurücksetzung angefordert.</p>
                            <p style="text-align: center; padding: 20px;">
                                <a href="{$resetUrl}" style="background: #4a9f4a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px;">
                                    Passwort zurücksetzen
                                </a>
                            </p>
                            <p style="color: #888; font-size: 12px;">Link: {$resetUrl}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $textBody = "Hallo {$username},\n\nDu hast eine Passwort-Zurücksetzung angefordert.\n\nLink: {$resetUrl}\n";

        return self::send($email, $subject, $htmlBody, $textBody);
    }
}
