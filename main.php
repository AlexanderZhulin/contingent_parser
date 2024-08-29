<?php
require_once(dirname(__FILE__) ."/vendor/autoload.php");

use Symfony\Component\Yaml\Yaml;
use ContingentParser\Facade;
use ContingentParser\Database\Database;
use ContingentParser\Database\DatabaseConfig;

$facade = new Facade();
$dbOpendata = new Database(new DatabaseConfig('opendata'));
// $dbNiimko = new Database(new DatabaseConfig('niimko'));

// $sites = $facade->getSitesFromDatabase($dbNiimko);
// $specialties = $facade->getSpecialtiesFromDatabase($dbNiimko);
// $universities = $facade->getUniversitiesFromDatabase($dbOpendata);
$sites = Yaml::parse(file_get_contents('sites.yaml'));
// print_r($sites);
for ($i = 0; $i < count($sites); $i++) {
    list('org_id' => $orgId, 'site' => $url) = $sites[$i];
    // Нет URL сайта вуза
    $check = empty($url);
    // Уже в базе
    // $check &= in_array($orgId, $universities);
    // С ошибками разметки игнорируем
    // $check &= in_array($orgId, $exceptionsOrgHtml);
    // Без ошибок http игнорируем
    // $check &= !in_array($orgId, $exceptionsOrgHttpCurl);
    if ($check) continue;

    $baseUri = $facade->getBaseUri($url);
    print(($i+1). '. ' . implode(' ', $sites[$i]) . PHP_EOL);
    $html = $facade->handleEducationContingentSites($baseUri, $sites[$i]);

    if (empty($html)) {
        continue;
    }
    $specialties = null;
    $contingent = $facade->getContingent(
        $html,
        $specialties,
        $orgId
    );
    print_r($contingent);
    if (empty($contingent)) {

    } else {
        if ($facade->isValidContingent($contingent)) {
            // Заносим в базу
            // $facade->
        }
    }
    unset($contingent);
}