<?php
use SvedenParser\Color;
use SvedenParser\ContingentParser\ContingentRepository;
use SvedenParser\ContingentParser\ContingentService;
use SvedenParser\Http\HttpClient;
use SvedenParser\Printer;
use Symfony\Component\Console\Output\ConsoleOutput;

define('SVEDEN_PARSER', '/home/developer/sveden_parser');

require_once SVEDEN_PARSER . "/vendor/autoload.php";

use Symfony\Component\Console\Helper\ProgressBar;

$output = new ConsoleOutput();

// creates a new progress bar (50 units)
$progressBar = new ProgressBar($output, 50);

// starts and displays the progress bar
$progressBar->start();
$i = 0;
while ($i++ < 50) {
    // ... do some wor
    sleep(1);
    echo "\r$i                                                      \n";
    // advances the progress bar 1 unit
    $progressBar->advance();

    // you can also advance the progress bar by more than 1 unit
    // $progressBar->advance(3);
}

// ensures that the progress bar is at 100%
$progressBar->finish();
echo PHP_EOL;