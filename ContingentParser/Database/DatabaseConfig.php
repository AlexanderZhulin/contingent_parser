<?php
namespace ContingentParser\Database;

final class DatabaseConfig
{
    private string $driver;
    private string $host;
    private string $dbname;
    private string $port;
    private string $charset;
    private string $username;
    private string $password;

    public function __construct(string $db)
    {
        $config = $this->getDataEnv($db);
        
        $this->driver = $config['DB_DRIVER'];
        $this->host = $config['DB_HOST'];
        $this->dbname = $config['DB_NAME'];
        $this->port = $config['DB_PORT'];
        $this->charset = $config["DB_CHARSET"];
        $this->username = $config['DB_USERNAME'];
        $this->password = $config['DB_PASSWORD'];
    }

    private function getDataEnv(string $db) : array
    {
        $envVars = parse_ini_file('.env', true);
        $db = strtoupper($db);
        $config = [];
        foreach ($envVars as $dbname => $dbconfig) {
            if ($dbname == $db) {
                $config = $dbconfig;
            }
        }
        return $config;
    }

    public function getDBName(): string
    {
        return $this->dbname;
    }

    public function getDsn() : string
    {
        return $this->driver.":host=".$this->host
            .";dbname=".$this->dbname
            .";charset=".$this->charset
            .";port=".$this->port;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function getPassword() : string
    {
        return $this->password;
    } 
}
