<?php
namespace ContingentParser\Database;

use ContingentParser\Logger\DatabaseLogger;
use Symfony\Component\Yaml\Yaml;
use PDOException;
use PDO;

final class Database
{
    private PDO $pdo;
    private static $logfile = 'log/database.log';
    private DatabaseConfig $databaseConfig;
    private DatabaseLogger $logger;
    private const NO_CONNECT = "HY000";
    /**
     * Конструктор
     * @param \ContingentParser\Database\DatabaseConfig $config
     * Конфигурация подключения к базе данных
     */
    public function __construct(DatabaseConfig $config)
    {
        $this->logger = new DatabaseLogger(self::$logfile);
        $this->databaseConfig = $config;
        try {
            $dsn = $this->databaseConfig->getDsn();
            $username = $this->databaseConfig->getUsername();
            $password = $this->databaseConfig->getPassword();
            $this->pdo = new PDO(
                $dsn, 
                $username, 
                $password, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $message = "Подключение к {$this->databaseConfig->getDBName()} успешно!";
            $this->logger->log($message);
        } catch (PDOException $e) {
            $message = "Ошибка подключения к {$this->databaseConfig->getDBName()}: {$e->getMessage()}";
            $this->logger->log($message);
        }
    }
    /**
     * Сообщение о разрыве соединения
     */
    public function __destruct()
    {
        $message = "Подключение к {$this->databaseConfig->getDBName()} прервано!";
        $this->logger->log($message);
    }
    /**
     * Выборка данных из базы
     * @param string $sql
     * SQL-запрос
     * @param array $params
     * Параметры запроса
     * @return array
     */
    public function select(string $sql, array $params = []) : array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            if (!empty($params)) {
                for ($i = 0; $i < count($params); $i++) {
                    $stmt->bindParam(":v".($i+1), $params[$i]);
                }
            }
            $stmt->execute();
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
     * @param string $sql
     *  SQL-запрос
     * @param array $params
     * Параметры запроса
     * @return void
     */
    public function insert(string $sql, array $params)
    {
        try {
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
            $this->logger->log("Запрос выполнен успешно!");
        } catch (PDOException $e) {
            $message = "Ошибка запроса:" . $e->getMessage();
            $this->logger->log($message);
            // При ошибке запроса сохраняем валидные данные в yaml-файл
            if ($e->getCode() === self::NO_CONNECT) {
                $yaml = Yaml::dump($params);
                file_put_contents('not-recorded-in-db.yaml', $yaml, FILE_APPEND);
            }
        }
    }
    /**
     * Обновление данных в базе
     * @param string $sql
     * SQL-запрос
     * @param array $params
     * Параметры запроса
     * @return void
     */
    public function update(string $sql, array $params)
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $count = count($params);
           for ($i = 0; $i < $count; $i++) {
                $stmt->bindParam(":v".($i+1), $params[$i]);
            }
            $stmt->execute();
            $this->logger->log("Запрос выполнен успешно!");
        } catch (PDOException $e) {
            $message = "Ошибка запроса:" . $e->getMessage();
            $this->logger->log($message);
        }
    }
}
