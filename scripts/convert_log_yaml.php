<?php
use Symfony\Component\Yaml\Yaml;
define('SVEDEN_PARSER', '/home/developer/sveden_parser');

require_once SVEDEN_PARSER . "/vendor/autoload.php";
$filePath = SVEDEN_PARSER . "/data/doc.yaml";
$file = file($filePath);

$sites = [];
foreach ($file as $line) {
    $site = explode(' ', $line);
    $sites[] = [
        'org_id' => (int)$site[0],
        'site' => trim($site[1]),
    ];
}

$yaml = Yaml::dump($sites);
file_put_contents($filePath, $yaml);