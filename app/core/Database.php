<?php
/**
 * Database Singleton Wrapper
 *
 * PDO-basierte Datenbankverbindung mit praktischen Hilfsmethoden.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require CONFIG_PATH . '/database.php';

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            Logger::error('Database connection failed', ['error' => $e->getMessage()]);
            die('Datenbankverbindung fehlgeschlagen. Bitte versuche es später erneut.');
        }
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Führt eine vorbereitete Abfrage aus
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Holt alle Ergebnisse einer Abfrage
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Holt eine einzelne Zeile
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Holt einen einzelnen Wert
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }

    /**
     * Fügt einen neuen Datensatz ein
     */
    public function insert(string $table, array $data): int
    {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $this->query($sql, $data);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Aktualisiert Datensätze
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :set_{$key}";
        }
        $setClause = implode(', ', $set);

        // Präfixiere Data-Keys um Konflikte mit Where-Params zu vermeiden
        $prefixedData = [];
        foreach ($data as $key => $value) {
            $prefixedData["set_{$key}"] = $value;
        }

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($prefixedData, $whereParams);

        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Löscht Datensätze
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Startet eine Transaktion
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Bestätigt eine Transaktion
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Macht eine Transaktion rückgängig
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Prüft ob ein Datensatz existiert
     */
    public function exists(string $table, string $where, array $params = []): bool
    {
        $sql = "SELECT 1 FROM {$table} WHERE {$where} LIMIT 1";
        return $this->fetchColumn($sql, $params) !== false;
    }

    /**
     * Zählt Datensätze
     */
    public function count(string $table, string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        return (int) $this->fetchColumn($sql, $params);
    }
}
