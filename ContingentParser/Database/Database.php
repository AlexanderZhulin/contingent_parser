<?php
namespace ContingentParser\Database;

use ContingentParser\Logger\DatabaseLogger;
use Symfony\Component\Yaml\Yaml;
use PDOException;
use PDO;

final class Database
{
    private PDO $_pdo;
    private static $_logFile = 'log/database.log';
    private DatabaseConfig $_databaseConfig;
    private DatabaseLogger $_logger;
    public function __construct(DatabaseConfig $config)
    {
        $this->_logger = new DatabaseLogger(self::$_logFile);
        $this->_databaseConfig = $config;
        try {
            $dsn = $this->_databaseConfig->getDsn();
            $username = $this->_databaseConfig->getUsername();
            $password = $this->_databaseConfig->getPassword();
            $this->_pdo = new PDO(
                $dsn, 
                $username, 
                $password, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $message = "Подключение к {$this->_databaseConfig->getDBName()} успешно!";
            $this->_logger->log($message);
        } catch (PDOException $e) {
            $message = "Ошибка подключения к {$this->_databaseConfig->getDBName()}: {$e->getMessage()}";
            $this->_logger->log($message);
        }
    }

    public function __destruct()
    {
        $message = "Подключение к {$this->_databaseConfig->getDBName()} прервано!";
        $this->_logger->log($message);
    }
    
    // Массив $params должен начанаться с 1
    public function select(string $sql, array $params = []) : array
    {
        try {
            $stmt = $this->_pdo->prepare($sql);
            for ($i = 1; $i < count($params); $i++) {
                $stmt->bindParam(":v$i", $params[$i]);
            }
            $stmt->execute();
            $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "Ошибка запроса: " . $e->getMessage();
            $this->_logger->log($message);
        } finally {
            return $array;
        }
    }

    public function insert(string $sql, array $params)
    {
        try {
            $stmt = $this->_pdo->prepare($sql);
            for ($i = 0; $i < count($params); $i++) {
                $stmt->bindParam(":spec_code".$i+1, $params[$i]['spec_code']);
                $stmt->bindParam(":spec_name".$i+1, $params[$i]['spec_name']);
                $stmt->bindParam(":edu_forms".$i+1, $params[$i]['edu_forms']);
                $stmt->bindParam(":edu_level".$i+1, $params[$i]['edu_level']);
                $stmt->bindParam(":contingent".$i+1, $params[$i]['contingent']);
                $stmt->bindParam(":org_id".$i+1, $params[$i]['org_id']);
                $stmt->bindParam(":spec_id".$i+1, $params[$i]['spec_id']);
            }
            $stmt->execute();
            $this->_logger->log("Запрос выполнен успешно!");
        } catch (PDOException $e) {
            $message = "Ошибка запроса:" . $e->getMessage();
            $this->_logger->log($message);
            // При ошибке запроса сохраняем валидные данные в yaml-файл
            if ($e->getCode() === "HY000") {
                $yaml = Yaml::dump($params);
                file_put_contents('/not-recorded-in-db.yaml', $yaml, FILE_APPEND);
            }
        }
    }
}