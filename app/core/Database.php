<?php

class Database
{
    private string $host = DB_HOST;
    private string $dbname = DB_NAME;
    private string $username = DB_USER;
    private string $password = DB_PASS;

    protected PDO $db;

    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

        try {
            $this->db = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
        $this->db->exec("SET time_zone = '+01:00'");
    }

    
}