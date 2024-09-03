<?php
namespace ContingentParser;

enum Color : string
{
    case WHITE = "\033[0m";
    case GREEN = "\033[92m";
    case RED = "\033[91m";
    case BLUE = "\033[94m";

    public static function create(string $color) : Color
    {
        switch ($color) {
            case 'green':
                return self::GREEN;
            case 'red':
                return self::RED;
            case 'blue':
                return self::BLUE;
            default:
                return self::WHITE;
        }
    }

    public function tostring() : string
    {
        return $this->value;
    }
}
