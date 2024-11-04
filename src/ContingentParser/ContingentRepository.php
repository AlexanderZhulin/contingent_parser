<?php
namespace SvedenParser\ContingentParser;

use SvedenParser\Repository;

final class ContingentRepository extends Repository
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'sveden_education_contingent';
        $this->specialties = $this->getSpecialties();
        $this->universities = $this->getUniversities();
    }

    public function getData(int $orgId): array
    {
        $sql = "SELECT spec_code, spec_name, edu_level, edu_forms, contingent, spec_id, org_id
            FROM " . $this->table . " WHERE org_id = :v1";
        return $this->opendata->select($sql, [$orgId]);
    }
    /**
     * Внесение данных численности обучающихся в базу данных opendata
     * @param array $data
     * Массив записей численности по специальностям
     * @return void
     */
    protected function insert(array $data): void
    {
        $sql = "INSERT INTO " . $this->table
            . "\n(spec_code, spec_name, edu_level, edu_forms, contingent, spec_id, org_id, is_actual)\nVALUES";
        $count = count($data) * count($data[0]);
        for ($i = 0; $i < $count;) { 
            $sql .= "(:v".(++$i).", :v".(++$i).", :v".(++$i).", :v".(++$i)
                .", :v".(++$i).", :v".(++$i).", :v".(++$i).", :v".(++$i)."),\n";
        }
        $sql = substr($sql, 0, -2);
        $this->opendata->insert($sql, $data);
    }    
}