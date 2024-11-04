<?php
namespace SvedenParser\ContingentParser;

use SvedenParser\Manager;

final class ContingentManager extends Manager
{
    public function __construct()
    {
        parent::__construct();
        $this->templateUri = 'sveden/education';
        $this->repository = new ContingentRepository();
        $this->service = new ContingentService();
    }
}