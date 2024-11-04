<?php
namespace SvedenParser;

final class Printer
{
    /**
     * Вывод строки
     * @param string $text Строка
     * @param \SvedenParser\Color $color Цвет
     * @return void
     */
    public static function print(string $text = '', Color $color = Color::WHITE): void
    {
        print($color->tostring().$text.Color::WHITE->tostring());
    }
    /**
     * Вывод строки с EOL
     * @param string $text Строка
     * @param \SvedenParser\Color $color Цвет
     * @return void
     */
    public static function println(string $text = '', Color $color = Color::WHITE): void
    {
        print($color->tostring().$text.Color::WHITE->tostring());
        print(PHP_EOL);
    }
    /**
     * Удобочитаемый вывод переменной
     * @param mixed $value Переменная
     * @param \SvedenParser\Color $color Цвет
     * @return void
     */
    public static function print_r(mixed $value, Color $color = Color::WHITE): void
    {
        print($color->tostring());
        print_r($value);
        print(Color::WHITE->tostring());
    }

    public static function dd(mixed $value): void
    {
        print(Color::ORANGE->tostring());
        var_dump($value);
        print(Color::WHITE->tostring());
        exit;
    }
}
