<?php
namespace SvedenParser\Database;

use SvedenParser\Logger\DatabaseLogger;
use PDOException;
use PDO;

final class Database
{
    private PDO $pdo;
    private DatabaseLogger $logger;
    /**
     * Конструктор
     * @param \SvedenParser\Database\DatabaseConfig $config
     * Конфигурация подключения к базе данных
     */
    public function __construct(private DatabaseConfig $config)
    {
        $this->logger = new DatabaseLogger();
        $this->config = $config;
        $this->init();
    }
    /**
     * Сообщение о разрыве соединения
     */
    public function __destruct()
    {
        $message = "Подключение к {$this->config->dbname} прервано!";
        $this->logger->log($message);
    }
    /**
     * Выборка данных из базы
     * @param string $sql SQL-запрос
     * @param array $params Параметры запроса
     * @return array
     */
    public function select(string $sql, array $params = []): array
    {
        try {
            if (!$this->ping()) return [];
            $stmt = $this->pdo->prepare($sql);
            if (!empty($params)) {
                for ($i = 0; $i < count($params); $i++) {
                    $stmt->bindParam(":v".($i+1), $params[$i]);
                }
            }
            $stmt->execute();
            $this->logger->log("Запрос SELECT выполнен успешно!");
            $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $message = "Ошибка запроса: " . $e->getMessage();
            $this->logger->log($message);
        } finally {
            return $array;
        }
    }
    /**
     * Добавление данных в базу
     * @param string $sql SQL-запрос
     * @param array $params Параметры запроса
     * @return void
     */
    public function insert(string $sql, array $params): void
    {
        try {
            if (!$this->ping()) return;
            $stmt = $this->pdo->prepare($sql);
            $count = 1;
            $size = count($params[0]);
            foreach ($params as $param) {
                for ($i = $count; $i <= $size; $i++) { 
                    $param = array_values($param);
                    $stmt->bindParam(":v$i", $param[$i-$count]);
                }
                $count += count($param);
                $size += count($param);
            }
            $stmt->execute();
            $this->logger->log("Запрос INSERT выполнен успешно!");
        } catch (PDOException $e) {
            $message = "Ошибка запроса:" . $e->getMessage();
            $this->logger->log($message);
        }
    }
    /**
     * Обновление данных в базе
     * @param string $sql SQL-запрос
     * @param array $params Параметры запроса
     * @return void
     */
    public function update(string $sql, array $params): void
    {
        try {
            if (!$this->ping()) return;
            $stmt = $this->pdo->prepare($sql);
            $count = count($params);
           for ($i = 0; $i < $count; $i++) {
                $stmt->bindParam(":v".($i+1), $params[$i]);
            }
            $stmt->execute();
            $this->logger->log("Запрос UPDATE выполнен успешно!");
        } catch (PDOException $e) {
            $message = "Ошибка запроса:" . $e->getMessage();
            $this->logger->log($message);
        }
    }

    public function ping(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            $this->init();
        }

        return true;
    }

    private function init(): void
    {
        unset($this->pdo);
        try {
            $this->pdo = new PDO(
                $this->config->dsn,
                $this->config->username,
                $this->config->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $message = "Подключение к {$this->config->dbname} успешно!";
            $this->logger->log($message);
        } catch (PDOException $e) {
            $message = "Ошибка подключения к {$this->config->dbname}: {$e->getMessage()}";
            $this->logger->log($message);
        }
    }
}