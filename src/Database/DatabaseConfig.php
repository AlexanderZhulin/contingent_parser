<?php
namespace SvedenParser\Database;

final class DatabaseConfig
{
    public readonly string $driver;
    public readonly string $host;
    public readonly string $dbname;
    public readonly string $port;
    public readonly string $charset;
    public readonly string $username;
    public readonly string $password;
    public readonly string $dsn;

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

        $this->dsn = $this->driver.":host=".$this->host
            .";dbname=".$this->dbname
            .";charset=".$this->charset
            .";port=".$this->port;
    }

    private function getDataEnv(string $db): array
    {
        $envVars = parse_ini_file(SVEDEN_PARSER . '/.env', true);
        $db = strtoupper($db);
        $config = [];
        foreach ($envVars as $dbname => $dbconfig) {
            if ($dbname == $db) {
                $config = $dbconfig;
            }
        }
        return $config;
    }
}