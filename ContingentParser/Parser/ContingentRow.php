<?php
namespace ContingentParser\Parser;

class ContingentRow
{
    public function __construct(
        private string $eduCode,
        private string $eduName,
        private string $eduLevel,
        private string $eduForm,
        private int $contingent
    ) {
        if ($contingent < 0) {
            throw new \Exception("Недействительная численность обучающихся!");
        }
        $this->eduCode = trim($eduCode);
        $this->eduName = trim($eduName);
        $this->eduLevel = trim($eduLevel);
        $this->eduForm = trim($eduForm);
        $this->contingent = $contingent;
    }

    public function getData() : array
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