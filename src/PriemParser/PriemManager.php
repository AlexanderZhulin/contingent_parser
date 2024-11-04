<?php
namespace SvedenParser\PriemParser;

use SvedenParser\Manager;

final class PriemManager extends Manager
{
    public function __construct()
    {
        parent::__construct();
        $this->templateUri = 'sveden/education';
        $this->repository = new PriemRepository();
        $this->service = new PriemService();
    }
}