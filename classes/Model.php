<?php
require_once __DIR__ . '/../config/database.php';

abstract class Model
{
    protected PDO $db;
    protected string $table;

    // Model.php
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(string $orderBy = 'id DESC'): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    protected function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    protected function updateById(int $id, array $data): bool
    {
        $assignments = implode(', ', array_map(
            fn($col) => "{$col} = :{$col}",
            array_keys($data)
        ));

        $data['id'] = $id;

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET {$assignments} WHERE id = :id"
        );
        return $stmt->execute($data);
    }
}