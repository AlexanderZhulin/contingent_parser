<?php
namespace SvedenParser\ContingentParser;

final class ContingentRow
{
    public function __construct(
        private string $eduCode,
        private string $eduName,
        private string $eduLevel,
        private string $eduForm,
        private string $contingent,
        private array $numbers
    ) {
        if (is_numeric($contingent)) {
            if ($contingent < 0) {
                throw new \Exception("Недействительная численность обучающихся!");
            }
            $this->contingent = (int)$contingent;
        } else {
            $this->contingent = 0;
            foreach ($numbers as $number) {
                $this->contingent += (int)$number;
            }
        }
        $this->eduCode = trim($eduCode);
        $this->eduName = trim($eduName);
        $this->eduLevel = trim($eduLevel);
        $this->eduForm = trim($eduForm);
    }

    public function getData(): array
    {
        return [
            "spec_code" => $this->eduCode,
            "spec_name" => $this->eduName,
            "edu_level" => $this->eduLevel,
            "edu_forms"=> $this->eduForm,
            "contingent" => $this->contingent
        ];
    }
}