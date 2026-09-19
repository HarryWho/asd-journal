<?php
require_once __DIR__ . '/Database.php';

/**
 * Model - base class all models extend. Wraps common PDO query patterns
 * so individual models (Post, User, etc.) stay short and readable.
 */
class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}
