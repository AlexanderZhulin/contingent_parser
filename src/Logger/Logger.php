<?php
namespace SvedenParser\Logger;

class Logger
{
    public const FILE = SVEDEN_PARSER . '/log/' . TYPE_PARSER . '.log';

    public static function log(string $message): void
    {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] $message\n";
        file_put_contents(self::FILE, $logMessage, FILE_APPEND);
    }
}