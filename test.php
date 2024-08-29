<?php
use ContingentParser\Facade;
require_once(dirname(__FILE__) ."/vendor/autoload.php");

$specialties = [
    [
        "spec_code"=> "1",
        "spec_id"=> 1
    ],
    [
        "spec_code"=> "2",
        "spec_id"=> 2
    ],
    [
        "spec_code"=> "3",
        "spec_id"=> 3
    ],
    [
        "spec_code"=> "4",
        "spec_id"=> 4
    ]
];

$contingent = [
    [
        'org_id' => 1,
        'spec_id' => 2,
        'edu_code' => '11.11.11',
        'edu_name' => 'qwerty',
        'edu_form' => 'qaz',
        'edu_level' => 'qsz',
        'contingent' => 123
    ],
    [
        'org_id' => 2,
        'spec_id' => 21,
        'edu_code' => '71.11.11',
        'edu_name' => 'pwerty',
        'edu_form' => 'haz',
        'edu_level' => 'gsz',
        'contingent' => 23
    ],
    [
        'org_id' => 1,
        'spec_id' => 2,
        'edu_code' => '11.11.11',
        'edu_name' => 'qwerty',
        'edu_form' => 'qaz',
        'edu_level' => 'qsz',
        'contingent' => 123
    ],
    [
        'org_id' => 2,
        'spec_id' => 21,
        'edu_code' => '71.11.11',
        'edu_name' => 'pwerty',
        'edu_form' => 'haz',
        'edu_level' => 'gsz',
        'contingent' => 23
    ]
];

$facade = new Facade();
// $facade->addSpecId($contingent, $specialties);
// $facade->addOrgId($contingent, 23);
// print_r($contingent);
$facade->insertContingent($contingent);