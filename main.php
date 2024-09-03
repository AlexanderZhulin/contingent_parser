<?php
use ContingentParser\Printer;
use Symfony\Component\Yaml\Yaml;
require_once(dirname(__FILE__) ."/vendor/autoload.php");

use ContingentParser\Facade;

$facade = new Facade();
// $sites = $facade->getSites();
$sites = Yaml::parse(file_get_contents(dirname(__FILE__) ."/sites.yaml"));

for ($i = 0; $i < count($sites); $i++) {
    Printer::print(($i+1). '. ', 'green');
    $facade->collectDataFromContingent($sites[$i]);
}