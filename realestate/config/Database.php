<?php

require_once __DIR__ . '/config.php';

class Database {

    private static ?Database $instance = null;
    private PDO $pdo;

    // Private constructor — Singleton pattern
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST
             . ";port="      . DB_PORT
             . ";dbname="    . DB_NAME
             . ";charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("❌ Database Connection Failed: " . $e->getMessage());
            } else {
                die("❌ Database connection error. Please try later.");
            }
        }
    }

    // Singleton — একটাই instance থাকবে
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // PDO object return
    public function getConnection(): PDO {
        return $this->pdo;
    }

    // ✅ SELECT — multiple rows
    public function query(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ✅ SELECT — single row
    public function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ✅ INSERT / UPDATE / DELETE
    public function execute(string $sql, array $params = []): bool {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // ✅ Last inserted ID
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    // ✅ Row count
    public function rowCount(string $sql, array $params = []): int {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // ✅ Transaction support
    public function beginTransaction(): void {
        $this->pdo->beginTransaction();
    }

    public function commit(): void {
        $this->pdo->commit();
    }

    public function rollback(): void {
        $this->pdo->rollBack();
    }

    // Clone বন্ধ — Singleton নষ্ট হবে না
    private function __clone() {}
}