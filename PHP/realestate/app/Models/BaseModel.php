<?php

namespace App\Models;

use Core\Database\Database;

/**
 * BaseModel – All models extend this.
 *
 * Provides:
 *  - CRUD shortcuts
 *  - Pagination
 *  - Soft-delete support
 *  - Timestamp auto-fill
 */
abstract class BaseModel
{
    protected Database $db;

    /** @var string Table name – override in child models */
    protected string $table = '';

    /** @var string Primary key column */
    protected string $primaryKey = 'id';

    /** @var bool Auto-manage created_at / updated_at */
    protected bool $timestamps = true;

    /** @var bool Use soft deletes (deleted_at column) */
    protected bool $softDelete = false;

    /** @var array Columns that may be mass-assigned */
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = \core\App::db();
    }

    // ------------------------------------------------------------------ //
    //  Core CRUD                                                           //
    // ------------------------------------------------------------------ //

    public function find(int|string $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        if ($this->softDelete) $sql .= ' AND deleted_at IS NULL';
        return $this->db->selectOne($sql, [$id]);
    }

    public function findOrFail(int|string $id): array
    {
        $record = $this->find($id);
        if (!$record) {
            throw new \RuntimeException("{$this->table} record not found: $id");
        }
        return $record;
    }

    public function all(string $orderBy = '', int $limit = 0): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($this->softDelete) $sql .= ' WHERE deleted_at IS NULL';
        if ($orderBy)          $sql .= " ORDER BY $orderBy";
        if ($limit > 0)        $sql .= " LIMIT $limit";
        return $this->db->select($sql);
    }

    public function create(array $data): int|string
    {
        $data = $this->filterFillable($data);
        if ($this->timestamps) {
            $now = $this->now();
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }
        return $this->db->insert($this->table, $data);
    }

    public function update(int|string $id, array $data): int
    {
        $data = $this->filterFillable($data);
        if ($this->timestamps) {
            $data['updated_at'] = $this->now();
        }
        return $this->db->update(
            $this->table,
            $data,
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    public function delete(int|string $id): int
    {
        if ($this->softDelete) {
            return $this->db->update(
                $this->table,
                ['deleted_at' => $this->now()],
                "`{$this->primaryKey}` = ?",
                [$id]
            );
        }
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]);
    }

    public function restore(int|string $id): int
    {
        return $this->db->update(
            $this->table,
            ['deleted_at' => null],
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    public function forceDelete(int|string $id): int
    {
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]);
    }

    // ------------------------------------------------------------------ //
    //  Query helpers                                                       //
    // ------------------------------------------------------------------ //

    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `$column` $operator ?";
        if ($this->softDelete) $sql .= ' AND deleted_at IS NULL';
        return $this->db->select($sql, [$value]);
    }

    public function whereFirst(string $column, mixed $value, string $operator = '='): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `$column` $operator ?";
        if ($this->softDelete) $sql .= ' AND deleted_at IS NULL';
        return $this->db->selectOne($sql, [$value]);
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        if ($this->softDelete) {
            $sql .= $where ? " WHERE deleted_at IS NULL AND ($where)" : ' WHERE deleted_at IS NULL';
        } elseif ($where) {
            $sql .= " WHERE $where";
        }
        return (int) ($this->db->selectOne($sql, $params)['total'] ?? 0);
    }

    public function paginate(string $sql, array $params = [], int $page = 1): array
    {
        $perPage = (int) \core\App::config('pagination.per_page', 12);
        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    public function exists(string $column, mixed $value, ?int $excludeId = null): bool
    {
        $sql    = "SELECT {$this->primaryKey} FROM `{$this->table}` WHERE `$column` = ?";
        $params = [$value];
        if ($excludeId !== null) {
            $sql    .= " AND `{$this->primaryKey}` != ?";
            $params[] = $excludeId;
        }
        return $this->db->selectOne($sql, $params) !== null;
    }

    // ------------------------------------------------------------------ //
    //  Utilities                                                           //
    // ------------------------------------------------------------------ //

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
}
