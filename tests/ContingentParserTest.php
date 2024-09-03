<?php

use ContingentParser\Parser\ContingentParser;
use PHPUnit\Framework\TestCase;

class ContingentParserTest extends TestCase
{
    private $parser;

    protected function setUp(): void
    {
        $html = <<< HTML
        <table>
            <tr itemprop="eduChislen">
                <td itemprop="eduCode">09.03.01</td>
                <td itemprop="eduName">Информатика и вычислительная техника</td>
                <td itemprop="eduLevel">Бакалавриат</td>
                <td itemprop="eduForm">Очная</td>
                <td itemprop="numberAll">98</td>
            </tr>
            <tr itemprop="eduChislen">
                <td itemprop="eduCode">09.03.04</td>
                <td itemprop="eduName">Программная инженерия</td>
                <td itemprop="eduLevel">Бакалавриат</td>
                <td itemprop="eduForm">Очная</td>
                <td itemprop="numberAll">198</td>
            </tr>
        </table>
        HTML;
        $this->parser = new ContingentParser($html);
    }

    public function testParse()
    {
        $data = $this->parser->getDataTable();
        $this->assertArrayHasKey('spec_code', $data[0]);
        $this->assertArrayHasKey('spec_name', $data[0]);
        $this->assertArrayHasKey('edu_level', $data[0]);
        $this->assertArrayHasKey('edu_forms', $data[0]);
        $this->assertArrayHasKey('contingent', $data[0]);
    }

    public function testGetDataTable()
    {
        $dataTable = $this->parser->getDataTable();
        $this->assertCount(2, $dataTable);
        $this->assertEquals([
            'spec_code' => '09.03.01',
            'spec_name' => 'Информатика и вычислительная техника',
            'edu_level' => 'Бакалавриат',
            'edu_forms' => 'Очная',
            'contingent' => 98,
        ], $dataTable[0]);
        $this->assertEquals([
            'spec_code' => '09.03.04',
            'spec_name' => 'Программная инженерия',
            'edu_level' => 'Бакалавриат',
            'edu_forms' => 'Очная',
            'contingent' => 198,
        ], $dataTable[1]);
    }

    public function testGetDataTableEmpty()
    {
        $parser = new ContingentParser('');
        $dataTable = $parser->getDataTable();
        $this->assertCount(0, $dataTable);
    }

    public function testGetDataTableDifferentLengths()
    {
        $html = <<< HTML
        <table>
            <tr itemprop="eduChislen">
                <td itemprop="eduCode">123</td>
                <td itemprop="eduName">Test Edu Name</td>
                <td itemprop="eduLevel">Test Edu Level</td>
                <td itemprop="eduForm">Test Edu Form</td>
            </tr>
            <tr itemprop="eduChislen">
                <td itemprop="eduCode">456</td>
                <td itemprop="eduName">Test Edu Name 2</td>
                <td itemprop="eduLevel">Test Edu Level 2</td>
            </tr>
        </table>
        HTML;
        $parser = new ContingentParser($html);
        $dataTable = $parser->getDataTable();
        $this->assertCount(0, $dataTable);
    }
}