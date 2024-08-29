<?php
namespace ContingentParser\Logger;

final class DatabaseLogger extends Logger
{
    public function log(string $message) : void
    {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] $message\n";
        file_put_contents($this->_path, $logMessage, FILE_APPEND);
    }
}