<?php
namespace SvedenParser;

enum Color : string
{
    case WHITE = "\033[0m";
    case GREEN = "\033[92m";
    case RED = "\033[91m";
    case BLUE = "\033[94m";
    case YELLOW = "\033[33m";
    case ORANGE = "\033[48m";

    public function tostring(): string
    {
        return $this->value;
    }
}