<?php

use PHPUnit\Framework\TestCase;
use ContingentParser\Parser\ContingentRow;

class ContingentRowTest extends TestCase
{
    #[Test]
    public function testValidConstructorArguments()
    {
        $contingentRow = new ContingentRow('eduCode', 'eduName', 'eduLevel', 'eduForm', 10);
        $this->assertInstanceOf(ContingentRow::class, $contingentRow);
    }

    #[Test]
    public function testInvalidContingentValue()
{
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage("Недействительная численность обучающихся!");
    new ContingentRow('eduCode', 'eduName', 'eduLevel', 'eduForm', -1);
}

    #[Test]
    public function testGetDataReturnsExpectedArray()
    {
        $contingentRow = new ContingentRow('eduCode', 'eduName', 'eduLevel', 'eduForm', 10);
        $data = $contingentRow->getData();
        $this->assertEquals([
            "spec_code" => 'eduCode',
            "spec_name" => 'eduName',
            "edu_level" => 'eduLevel',
            "edu_forms" => 'eduForm',
            "contingent" => 10
        ], $data);
    }

    #[Test]
    public function testConstructorTrimsInputValues()
    {
        $contingentRow = new ContingentRow('  eduCode  ', '  eduName  ', '  eduLevel  ', '  eduForm  ', 10);
        $data = $contingentRow->getData();
        $this->assertEquals([
            "spec_code" => 'eduCode',
            "spec_name" => 'eduName',
            "edu_level" => 'eduLevel',
            "edu_forms" => 'eduForm',
            "contingent" => 10
        ], $data);
    }
}