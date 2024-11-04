<?php
namespace SvedenParser\EmployeesParser;

use SvedenParser\Manager;

final class EmployeesManager extends Manager
{
    public function __construct()
    {
        parent::__construct();
        $this->templateUri = 'sveden/employees';
        $this->repository = new EmployeesRepository();
        $this->service = new EmployeesService();
    }
}