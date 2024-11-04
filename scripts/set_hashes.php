<?php

use SvedenParser\ContingentParser\ContingentRepository;
use SvedenParser\PriemParser\PriemRepository;
define('SVEDEN_PARSER', '/home/developer/sveden_parser');

require_once SVEDEN_PARSER . "/vendor/autoload.php";

// $rep = new PriemRepository();
// $ids = $rep->universities();
// foreach ($ids as $id) {
//     echo $id . PHP_EOL;
//     $data = $rep->getData($id);
//     array_multisort($data);
//     $str = '';
//     foreach ($data as $dt) {
//         $str .= implode('', $dt);
//     }
//     $hashData = md5($str);
//     $rep->updateHash($id, $hashData);
// }
$rep = new ContingentRepository();
$data = $rep->getData(64);
array_multisort($data);
$str = '';
foreach ($data as $dt) {
    $str .= implode('', $dt);
}
$hashData = md5($str);
$rep->updateHash(64, $hashData);

// file_put_contents('test1.txt', $str);
// print_r($data);
// echo md5($str) . PHP_EOL;

// echo file_get_contents('test.txt') === file_get_contents('test1.txt');