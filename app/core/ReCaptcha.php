<?php
/**
 * ReCaptcha v3 Integration
 *
 * Unsichtbare Bot-Erkennung für Formulare.
 */
class ReCaptcha
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Prüft ob reCAPTCHA aktiviert ist
     */
    public static function isEnabled(): bool
    {
        return defined('RECAPTCHA_ENABLED') && RECAPTCHA_ENABLED === true;
    }

    /**
     * Validiert ein reCAPTCHA Token
     *
     * @param string $token Das Token vom Frontend
     * @param string $expectedAction Die erwartete Action (z.B. 'register', 'login')
     * @return array ['success' => bool, 'score' => float, 'message' => string]
     */
    public static function verify(string $token, string $expectedAction = ''): array
    {
        if (!self::isEnabled()) {
            return ['success' => true, 'score' => 1.0, 'message' => 'reCAPTCHA deaktiviert'];
        }

        if (empty($token)) {
            return ['success' => false, 'score' => 0, 'message' => 'Kein reCAPTCHA Token'];
        }

        $secretKey = defined('RECAPTCHA_SECRET_KEY') ? RECAPTCHA_SECRET_KEY : '';
        if (empty($secretKey)) {
            Logger::error('reCAPTCHA Secret Key nicht konfiguriert');
            return ['success' => false, 'score' => 0, 'message' => 'Konfigurationsfehler'];
        }

        // API-Anfrage
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        $ch = curl_init(self::VERIFY_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            Logger::error('reCAPTCHA API Fehler', ['http_code' => $httpCode]);
            return ['success' => false, 'score' => 0, 'message' => 'Verifizierung fehlgeschlagen'];
        }

        $result = json_decode($response, true);

        if (!$result || !isset($result['success'])) {
            return ['success' => false, 'score' => 0, 'message' => 'Ungültige API-Antwort'];
        }

        // Fehler von Google
        if (!$result['success']) {
            $errors = $result['error-codes'] ?? [];
            Logger::warning('reCAPTCHA Fehler', ['errors' => $errors]);
            return ['success' => false, 'score' => 0, 'message' => 'Verifizierung fehlgeschlagen'];
        }

        $score = $result['score'] ?? 0;
        $action = $result['action'] ?? '';
        $minScore = defined('RECAPTCHA_MIN_SCORE') ? RECAPTCHA_MIN_SCORE : 0.5;

        // Action prüfen wenn angegeben
        if (!empty($expectedAction) && $action !== $expectedAction) {
            Logger::warning('reCAPTCHA Action mismatch', [
                'expected' => $expectedAction,
                'actual' => $action
            ]);
            return ['success' => false, 'score' => $score, 'message' => 'Ungültige Aktion'];
        }

        // Score prüfen
        if ($score < $minScore) {
            Logger::warning('reCAPTCHA niedriger Score', [
                'score' => $score,
                'min' => $minScore,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
            return [
                'success' => false,
                'score' => $score,
                'message' => 'Verdächtige Aktivität erkannt. Bitte versuche es erneut.'
            ];
        }

        return ['success' => true, 'score' => $score, 'message' => 'OK'];
    }

    /**
     * Gibt das Script-Tag für den Head-Bereich zurück
     */
    public static function getScriptTag(): string
    {
        if (!self::isEnabled()) {
            return '';
        }

        $siteKey = defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '';
        if (empty($siteKey)) {
            return '';
        }

        return '<script src="https://www.google.com/recaptcha/api.js?render=' . htmlspecialchars($siteKey) . '"></script>';
    }

    /**
     * Gibt den Site Key zurück
     */
    public static function getSiteKey(): string
    {
        return defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '';
    }

    /**
     * Generiert JavaScript für Formular-Submit
     *
     * @param string $formId Die ID des Formulars
     * @param string $action Die reCAPTCHA Action
     * @param string $tokenFieldId Die ID des Hidden-Inputs für das Token
     */
    public static function getFormScript(string $formId, string $action, string $tokenFieldId = 'recaptcha_token'): string
    {
        if (!self::isEnabled()) {
            return '';
        }

        $siteKey = self::getSiteKey();
        if (empty($siteKey)) {
            return '';
        }

        return <<<JS
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('{$formId}');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var tokenField = document.getElementById('{$tokenFieldId}');

            grecaptcha.ready(function() {
                grecaptcha.execute('{$siteKey}', {action: '{$action}'}).then(function(token) {
                    tokenField.value = token;
                    form.submit();
                });
            });
        });
    }
});
</script>
JS;
    }
}
