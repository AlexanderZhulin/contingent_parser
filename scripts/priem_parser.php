<?php

use SvedenParser\PriemParser\PriemManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

define('SVEDEN_PARSER', '/home/developer/sveden_parser');
define('TYPE_PARSER', explode('_', basename(__FILE__, '.php'))[0]);

require_once SVEDEN_PARSER . "/vendor/autoload.php";


$manager = new PriemManager();
$sites = $manager->getSites();
$progressBar = new ProgressBar(new ConsoleOutput(), count($sites));
$progressBar->start();

$start = 0; $end = count($sites);
for ($i = $start; $i < $end; $i++) {
    $manager->collectData($sites[$i]);
    $progressBar->advance();
}
$progressBar->finish();
echo PHP_EOL;
