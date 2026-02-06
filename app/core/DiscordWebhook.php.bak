<?php
/**
 * Discord Webhook Integration
 *
 * Sendet Nachrichten an einen Discord-Kanal via Webhook.
 */
class DiscordWebhook
{
    private string $webhookUrl;

    public function __construct(?string $webhookUrl = null)
    {
        $this->webhookUrl = $webhookUrl ?? (defined('DISCORD_WEBHOOK_URL') ? DISCORD_WEBHOOK_URL : '');
    }

    /**
     * Prueft ob der Webhook aktiviert und konfiguriert ist
     */
    public function isEnabled(): bool
    {
        if (!defined('DISCORD_WEBHOOK_ENABLED') || !DISCORD_WEBHOOK_ENABLED) {
            return false;
        }

        return !empty($this->webhookUrl);
    }

    /**
     * Sendet eine einfache Nachricht
     */
    public function sendMessage(string $content): bool
    {
        return $this->send(['content' => $content]);
    }

    /**
     * Sendet einen News/Changelog Beitrag als Embed
     */
    public function sendNewsPost(string $title, string $content, string $category, bool $isPinned = false): bool
    {
        $categoryLabels = [
            'changelog' => 'Changelog',
            'admin_news' => 'News'
        ];

        $categoryColors = [
            'changelog' => 0x3498db, // Blau
            'admin_news' => 0xe74c3c  // Rot
        ];

        $categoryLabel = $categoryLabels[$category] ?? 'Beitrag';
        $color = $categoryColors[$category] ?? 0x95a5a6;

        // HTML-Tags entfernen und Text kuerzen
        $plainContent = strip_tags($content);
        if (strlen($plainContent) > 1000) {
            $plainContent = substr($plainContent, 0, 1000) . '...';
        }

        $embed = [
            'title' => $title,
            'description' => $plainContent,
            'color' => $color,
            'footer' => [
                'text' => $categoryLabel . ($isPinned ? ' | Angepinnt' : '')
            ],
            'timestamp' => date('c')
        ];

        // Link zur Website hinzufuegen
        if (defined('SITE_URL') && !empty(SITE_URL)) {
            $embed['url'] = SITE_URL . '/news';
        }

        $payload = [
            'embeds' => [$embed]
        ];

        return $this->send($payload);
    }

    /**
     * Sendet Daten an den Discord Webhook
     */
    private function send(array $payload): bool
    {
        if (!$this->isEnabled()) {
            Logger::info('Discord Webhook disabled or not configured');
            return false;
        }

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $ch = curl_init($this->webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonPayload)
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error('Discord Webhook CURL Error', ['error' => $error]);
            return false;
        }

        // Discord gibt 204 No Content bei Erfolg zurueck
        if ($httpCode >= 200 && $httpCode < 300) {
            Logger::info('Discord Webhook sent successfully');
            return true;
        }

        Logger::error('Discord Webhook failed', [
            'http_code' => $httpCode,
            'response' => $response
        ]);

        return false;
    }
}
