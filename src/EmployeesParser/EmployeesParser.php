<?php
namespace SvedenParser\EmployeesParser;

use SvedenParser\Color;
use SvedenParser\Parser;
use SvedenParser\Printer;

final class EmployeesParser extends Parser
{
    private const TEMPLATE = '//tr[@itemprop="teachingStaff"]//';
    private const FIELDS = [
        "fio" => ["span", "td"],
        "teachingDiscipline" => ["span", "td"]
    ];


    public function getDataTable(): array
    {
        if (!$this->xpath) return [];
        
        $data = $this->parse('');
        if (!$data) return [];
        $records = [];
        $equal = $data['fio']->length;
        foreach ($data as $field) {
            if ($field->length == 0 || $field->length != $equal) {
                return [];
            }
        }
        for ($i = 0; $i < $data['fio']->length; $i++) { 
            try {
                $employessRow = new EmployeesRow(
                    $data['fio']->item($i)->textContent,
                    $data['teachingDiscipline']->item($i)->textContent,
                );
                $records[] = $employessRow->getData();    
            } catch (\Exception $e) {
                Printer::println($e->getMessage(), Color::RED);
            }
        }
        return $records;
    }

    protected function parse(string $_): array
    {
        $data = [];
        foreach (self::FIELDS as $field => $tag) {
            $span = $this->xpath->query(self::TEMPLATE . $tag[0] . "[@itemprop=\"$field\"]");
            $td = $this->xpath->query(self::TEMPLATE . $tag[1] . "[@itemprop=\"$field\"]");
            $data[$field] = $span->length > $td->length ? $span : $td;
        }
        return $data;
    }

    public function getLink(): string
    {
        // $needle = "Информация о численности обучающихся";
        // $data = $this->dom->getElementsByTagName('a');
        // for ($i = 0; $i < $data->length; $i++) {
        //     $haystack = $data->item($i)->textContent;
        //     $isInformationOfContingent = strpos($haystack, $needle) !== false;
        //     if ($isInformationOfContingent) {
        //         return $data->item($i)->getAttribute('href');
        //     }
        // }
        return '';
    }
}