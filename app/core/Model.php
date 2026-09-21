<?php

class Model extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    protected function fetch(string $sql, array $params = [])
    {
        return $this->query($sql, $params)
                    ->fetch(PDO::FETCH_ASSOC);
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)
                    ->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function execute(string $sql, array $params = []): bool
    {
        return $this->query($sql, $params)->rowCount() > 0;
    }

    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    protected function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    protected function commit(): bool
    {
        return $this->db->commit();
    }

    protected function rollBack(): bool
    {
        return $this->db->rollBack();
    }
}