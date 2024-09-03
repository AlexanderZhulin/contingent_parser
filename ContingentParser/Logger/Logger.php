<?php
namespace ContingentParser\Logger;

abstract class Logger
{
    protected string $_path;
    
    public function __construct(string $path)
    {
        $this->_path = $path;
    }
}
