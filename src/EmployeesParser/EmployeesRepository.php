<?php
namespace SvedenParser\EmployeesParser;

use SvedenParser\Repository;

final class EmployeesRepository extends Repository
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'sveden_education_employees';
        $this->specialties = $this->getSpecialties();
        $this->universities = $this->getUniversities();
    }

    public function getData(int $orgId): array
    {
        $sql = "SELECT fio, disciplines, org_id
            FROM " . $this->table . " WHERE org_id = :v1";
        return $this->opendata->select($sql, [$orgId]);
    }
    protected function insert(array $data): void
    {
        $sql = "INSERT INTO " . $this->table
            . "\n(fio, disciplines, org_id)\nVALUES";
        $count = count($data) * count($data[0]);
        for ($i = 0; $i < $count;) { 
            $sql .= "(:v".(++$i).", :v".(++$i).", :v".(++$i)."),\n";
        }
        $sql = substr($sql, 0, -2);
        $this->opendata->insert($sql, $data);
    } 
}