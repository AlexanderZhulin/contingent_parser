<?php
namespace ContingentParser;

class Printer
{
    public static function print(string $text = '', string $color = '') : void
    {
        $color = Color::create($color);
        print($color->tostring().$text.Color::WHITE->tostring());
    }

    public static function println(string $text = '', string $color = '') : void
    {
        $color = Color::create($color);
        print($color->tostring().$text.Color::WHITE->tostring());
        print(PHP_EOL);
    }

    public static function print_r(mixed $value, string $color = '') : void
    {
        $color = Color::create($color);
        print($color->tostring());
        print_r($value);
        print(Color::WHITE->tostring());
    }
}