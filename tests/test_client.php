<?php
use SvedenParser\ContingentParser\ContingentManager;
use SvedenParser\ContingentParser\ContingentParser;
use SvedenParser\ContingentParser\ContingentRepository;
use SvedenParser\ContingentParser\ContingentService;
use SvedenParser\EmployeesParser\EmployeesParser;
use SvedenParser\Http\CurlHelper;
use SvedenParser\Http\HttpClient;
use SvedenParser\PriemParser\PriemManager;
use SvedenParser\PriemParser\PriemParser;

define('SVEDEN_PARSER', '/home/developer/sveden_parser');

require_once SVEDEN_PARSER . "/vendor/autoload.php";

// $client = new HttpClient();
// $html = $client->getContentOfSite('https://amchs.ru/', [], '/sveden/education/priem');
$html = file_get_contents(SVEDEN_PARSER . '/data/emp.html');
// $curl = new CurlHelper('https://www.rgiis.ru/sveden/education/', []);
// $html = $curl->getContent();
// echo $html;
$parser = new EmployeesParser($html);
$data = $parser->getDataTable();

// (new ContingentService())->getData(html);
print_r($data);
// print_r();
// (new ContingentRepository())->save($data);
// $manager = new ContingentManager();
// $manager->collectData(['org_id' => 410, 'site' => 'http://www.volgatech.net']);