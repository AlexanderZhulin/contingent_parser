<?php
use SvedenParser\Color;
use SvedenParser\PriemParser\PriemManager;
use SvedenParser\PriemParser\PriemRepository;
use SvedenParser\Printer;
use Symfony\Component\Yaml\Yaml;

define('SVEDEN_PARSER', '/home/developer/sveden_parser');

require_once SVEDEN_PARSER . "/vendor/autoload.php";

$manager = new PriemManager();
$allOrg = $manager->getSites();
Printer::println("All: " . count($allOrg), Color::GREEN);
$inDB = (new PriemRepository())->universities();
Printer::println("In DB: " . count($inDB), Color::GREEN);
$docs = Yaml::parse(file_get_contents(SVEDEN_PARSER . "/data/doc.yaml"));
Printer::println("Docs: " . count($docs), Color::GREEN);
$remains = array_values(array_filter($allOrg, function($var) {
    global $inDB, $docs;
    return !in_array($var['org_id'], array_merge($inDB, array_column($docs, 'org_id')));
}));
Printer::println("Remains: " . count($remains), Color::RED);
print_r($remains);

$yaml = Yaml::dump($remains);
file_put_contents(SVEDEN_PARSER . "/data/remains.yaml", $yaml);

