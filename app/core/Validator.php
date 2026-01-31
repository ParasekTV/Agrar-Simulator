<?php
/**
 * Input Validator
 *
 * Validierung und Sanitierung von Benutzereingaben.
 */
class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Validiert eine E-Mail-Adresse
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validiert einen Benutzernamen
     */
    public static function validateUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username) === 1;
    }

    /**
     * Validiert ein Passwort (min. 8 Zeichen)
     */
    public static function validatePassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    /**
     * Validiert ein starkes Passwort
     */
    public static function validateStrongPassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Passwort muss mindestens einen Grossbuchstaben enthalten';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Passwort muss mindestens einen Kleinbuchstaben enthalten';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Passwort muss mindestens eine Zahl enthalten';
        }

        return $errors;
    }

    /**
     * Sanitiert einen String
     */
    public static function sanitizeString(string $string): string
    {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiert einen Integer
     */
    public static function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitiert einen Float
     */
    public static function sanitizeFloat(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Prueft ob ein Wert erforderlich ist
     */
    public function required(string $field, string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[$field] = $message ?? "{$field} ist erforderlich";
        }

        return $this;
    }

    /**
     * Prueft minimale Laenge
     */
    public function minLength(string $field, int $min, string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if (strlen($value) < $min) {
            $this->errors[$field] = $message ?? "{$field} muss mindestens {$min} Zeichen lang sein";
        }

        return $this;
    }

    /**
     * Prueft maximale Laenge
     */
    public function maxLength(string $field, int $max, string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if (strlen($value) > $max) {
            $this->errors[$field] = $message ?? "{$field} darf maximal {$max} Zeichen lang sein";
        }

        return $this;
    }

    /**
     * Prueft auf gueltige E-Mail
     */
    public function email(string $field, string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if (!self::validateEmail($value)) {
            $this->errors[$field] = $message ?? "Bitte gib eine gueltige E-Mail-Adresse ein";
        }

        return $this;
    }

    /**
     * Prueft auf gueltigen Benutzernamen
     */
    public function username(string $field, string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if (!self::validateUsername($value)) {
            $this->errors[$field] = $message ?? "Benutzername darf nur Buchstaben, Zahlen und Unterstriche enthalten (3-20 Zeichen)";
        }

        return $this;
    }

    /**
     * Prueft ob Werte uebereinstimmen
     */
    public function matches(string $field, string $otherField, string $message = null): self
    {
        $value = $this->data[$field] ?? '';
        $otherValue = $this->data[$otherField] ?? '';

        if ($value !== $otherValue) {
            $this->errors[$field] = $message ?? "{$field} stimmt nicht ueberein";
        }

        return $this;
    }

    /**
     * Prueft auf numerischen Wert
     */
    public function numeric(string $field, string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if (!is_numeric($value)) {
            $this->errors[$field] = $message ?? "{$field} muss eine Zahl sein";
        }

        return $this;
    }

    /**
     * Prueft Minimalwert
     */
    public function min(string $field, float $min, string $message = null): self
    {
        $value = $this->data[$field] ?? 0;

        if ((float) $value < $min) {
            $this->errors[$field] = $message ?? "{$field} muss mindestens {$min} sein";
        }

        return $this;
    }

    /**
     * Prueft Maximalwert
     */
    public function max(string $field, float $max, string $message = null): self
    {
        $value = $this->data[$field] ?? 0;

        if ((float) $value > $max) {
            $this->errors[$field] = $message ?? "{$field} darf maximal {$max} sein";
        }

        return $this;
    }

    /**
     * Prueft ob Wert in Liste enthalten ist
     */
    public function in(string $field, array $allowed, string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = $message ?? "{$field} hat einen ungueltigen Wert";
        }

        return $this;
    }

    /**
     * Benutzerdefinierte Validierung
     */
    public function custom(string $field, callable $callback, string $message): self
    {
        $value = $this->data[$field] ?? null;

        if (!$callback($value)) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Prueft ob Validierung erfolgreich war
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Gibt Fehler zurueck
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Gibt ersten Fehler zurueck
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Fuegt manuell einen Fehler hinzu
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Holt und sanitiert einen Wert
     */
    public function getValue(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    /**
     * Holt alle sanitierten Daten
     */
    public function getSanitizedData(): array
    {
        $sanitized = [];

        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
