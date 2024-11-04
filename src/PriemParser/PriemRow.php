<?php
namespace SvedenParser\PriemParser;

class PriemRow
{
    private int $all;
    private int $budget;
    private float $avgScore;
    public function __construct(
        private string $eduCode,
        private string $eduName,
        private string $eduLevel,
        private string $eduForm,
        string $avgScore,
        array $contingent,
    ) {
        if ((float)$avgScore < 0) {
            throw new \Exception('Недействительная средняя сумма набранных баллов обучающихся!');
        }
        $this->eduCode = trim($eduCode);
        $this->eduName = trim($eduName);
        $this->eduLevel = trim($eduLevel);
        $this->eduForm = trim($eduForm);
        $this->avgScore = (float)str_replace(',', '.', $avgScore    );
        $this->calcContingent($contingent);
    }

    public function getData(): array
    {
        return [
            'spec_code' => $this->eduCode,
            'spec_name' => $this->eduName,
            'edu_level' => $this->eduLevel,
            'edu_forms'=> $this->eduForm,
            'avg_score' => $this->avgScore,
            'contingent' => $this->all,
            'budget' => $this->budget,
        ];
    }

    private function calcContingent(array $contingent): void
    {
        $all = 0;
        $budget = 0;
        foreach ($contingent as $key => $con) {
            $all += (int)$con;
            if ($key !== 3) {
                $budget += (int)$con;
            }
        }
        $this->all = $all;
        $this->budget = $budget;
    }
}
