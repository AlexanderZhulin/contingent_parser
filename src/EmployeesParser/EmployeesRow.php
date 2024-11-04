<?php
namespace SvedenParser\EmployeesParser;

final class EmployeesRow
{
    public function __construct(
        private string $fio,
        private string $disciplines
    ) {
        $this->fio = trim($fio);
        $this->disciplines = trim($disciplines);
    }

    public function getData(): array
    {
        return [
            'fio' => $this->fio,
            'disciplines' => $this->disciplines,
        ];
    }
}