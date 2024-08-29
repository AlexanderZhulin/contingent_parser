<?php
namespace ContingentParser\Database;

final class DatabaseConfig
{
    private string $_driver;
    private string $_host;
    private string $_dbname;
    private string $_port;
    private string $_charset;
    private string $_username;
    private string $_password;

    public function __construct(string $db)
    {
        $config = $this->getDataEnv($db);
        
        $this->_driver = $config['DB_DRIVER'];
        $this->_host = $config['DB_HOST'];
        $this->_dbname = $config['DB_NAME'];
        $this->_port = $config['DB_PORT'];
        $this->_charset = $config["DB_CHARSET"];
        $this->_username = $config['DB_USERNAME'];
        $this->_password = $config['DB_PASSWORD'];
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
        return $this->_dbname;
    }

    public function getDsn() : string
    {
        return $this->_driver.":host=".$this->_host
            .";dbname=".$this->_dbname
            .";charset=".$this->_charset
            .";port=".$this->_port;
    }

    public function getUsername() : string
    {
        return $this->_username;
    }

    public function getPassword() : string
    {
        return $this->_password;
    } 
}