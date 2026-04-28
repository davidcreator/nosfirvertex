<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private PDO $pdo;

    public function __construct(string $host, int $port, string $name, string $user, string $password)
    {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        $this->pdo = new PDO(
            $dsn,
            $user,
            $password,
            $options
        );
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function fetch(string $sql, array $params = []): array|null
    {
        $result = $this->query($sql, $params)->fetch();

        return $result === false ? null : $result;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool
    {
        return $this->query($sql, $params) !== false;
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback($this);
            $this->pdo->commit();

            return $result;
        } catch (PDOException $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
