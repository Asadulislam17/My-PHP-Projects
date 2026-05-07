<?php

namespace Core\Database;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Database – Singleton PDO wrapper
 *
 * Provides a single shared connection, prepared-statement helpers,
 * transaction support, and basic query logging.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;
    private array $config;
    private array $queryLog = [];
    private bool $logQueries = false;

    // ------------------------------------------------------------------ //
    //  Construction                                                        //
    // ------------------------------------------------------------------ //

    private function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /** Prevent cloning */
    private function __clone() {}

    /**
     * Return (or create) the singleton instance.
     */
    public static function getInstance(array $config = []): static
    {
        if (static::$instance === null) {
            if (empty($config)) {
                throw new \RuntimeException('Database config required on first call.');
            }
            static::$instance = new static($config);
        }
        return static::$instance;
    }

    // ------------------------------------------------------------------ //
    //  Connection                                                          //
    // ------------------------------------------------------------------ //

    private function connect(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['name'],
            $this->config['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['pass'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            // Never expose credentials in error messages
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ------------------------------------------------------------------ //
    //  Query helpers                                                       //
    // ------------------------------------------------------------------ //

    /**
     * Execute a SELECT query and return all matching rows.
     *
     * @param  string $sql    SQL with ? or :name placeholders
     * @param  array  $params Bound parameters
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->run($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Return the first matching row or null.
     *
     * @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->run($sql, $params);
        $row  = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Execute an INSERT and return the last inserted ID.
     */
    public function insert(string $table, array $data): int|string
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql          = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";

        $this->run($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    /**
     * Execute an UPDATE and return the number of affected rows.
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set  = implode(', ', array_map(fn($col) => "`$col` = ?", array_keys($data)));
        $sql  = "UPDATE `$table` SET $set WHERE $where";
        $stmt = $this->run($sql, [...array_values($data), ...$whereParams]);
        return $stmt->rowCount();
    }

    /**
     * Execute a DELETE and return the number of affected rows.
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $stmt = $this->run("DELETE FROM `$table` WHERE $where", $params);
        return $stmt->rowCount();
    }

    /**
     * Run any raw SQL (INSERT/UPDATE/DELETE) and return affected rows.
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->run($sql, $params)->rowCount();
    }

    /**
     * Prepare, bind, and execute – the core method.
     */
    private function run(string $sql, array $params = []): PDOStatement
    {
        $start = microtime(true);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            throw new \RuntimeException('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
        }

        if ($this->logQueries) {
            $this->queryLog[] = [
                'sql'      => $sql,
                'params'   => $params,
                'time_ms'  => round((microtime(true) - $start) * 1000, 2),
            ];
        }

        return $stmt;
    }

    // ------------------------------------------------------------------ //
    //  Transactions                                                        //
    // ------------------------------------------------------------------ //

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Run a callable inside a transaction; rolls back on exception.
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    // ------------------------------------------------------------------ //
    //  Pagination                                                          //
    // ------------------------------------------------------------------ //

    /**
     * Return paginated results + meta data.
     *
     * @return array{data: array, total: int, per_page: int, current_page: int, last_page: int}
     */
    public function paginate(string $sql, array $params = [], int $page = 1, int $perPage = 12): array
    {
        // Count total
        $countSql = 'SELECT COUNT(*) as total FROM (' . $sql . ') as _subq';
        $total    = (int) ($this->selectOne($countSql, $params)['total'] ?? 0);

        // Fetch page
        $offset = ($page - 1) * $perPage;
        $rows   = $this->select("$sql LIMIT $perPage OFFSET $offset", $params);

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
            'from'         => $offset + 1,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    // ------------------------------------------------------------------ //
    //  Utilities                                                           //
    // ------------------------------------------------------------------ //

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function enableQueryLog(): void
    {
        $this->logQueries = true;
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    public function tableExists(string $table): bool
    {
        $dbName = $this->config['name'];
        $result = $this->selectOne(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
            [$dbName, $table]
        );
        return $result !== null;
    }
}
