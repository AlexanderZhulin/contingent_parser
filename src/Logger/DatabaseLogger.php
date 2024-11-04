<?php
namespace SvedenParser\Logger;

final class DatabaseLogger
{
    private string $path = SVEDEN_PARSER . '/log/' . TYPE_PARSER . '-database.log';

    public function log(string $message): void
    {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] $message\n";
        file_put_contents($this->path, $logMessage, FILE_APPEND);
    }
}