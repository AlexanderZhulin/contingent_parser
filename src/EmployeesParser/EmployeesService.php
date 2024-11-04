<?php
namespace SvedenParser\EmployeesParser;

use SvedenParser\Service;

final class EmployeesService extends Service
{
    public function getLink(string $html): string
    {
        return '';
    }
    
    public function getData(string $html, array $specialties, int $orgId): array
    {
        $parser = new EmployeesParser($html);
        $employees = $parser->getDataTable();
        $this->addOrgId($employees, $orgId);

        return $employees;
    }
    public function isValidData(array $data): bool
    {
        return true;
    }
}