<?php
/**
 * Парсер информации об образовательной организации
 * с её сайта с использованием микроразметки
 */
namespace SvedenParser\ContingentParser;

use SvedenParser\Color;
use SvedenParser\Parser;
use SvedenParser\Printer;
final class ContingentParser extends Parser
{
    private const TEMPLATE = '//tr[@itemprop="eduChislen"]//';
    //priem
    private const FIELDS = [
        "eduCode" => ["th", "td", "span"],
        "eduName" => ["th", "td", "span"],
        "eduLevel" => ["th", "td", "span"],
        "eduForm" => ["th", "td", "span"],
        "numberBF" => ["th", "td", "span"],
        "numberBR" => ["th", "td", "span"],
        "numberBM" => ["th", "td", "span"],
        "numberP" => ["th", "td", "span"],
        "numberAll" => ["th", "td", "span"],
    ];

    public function getDataTable(): array
    {
        if (!$this->xpath) return [];
        
        $data = $this->parse('');
        if (!$data || $this->checkData($data)) return [];
        $records = [];
        for ($i = 0; $i < $data['eduCode']->length; $i++) { 
            try {
                $contingentRow = new ContingentRow(
                    $data['eduCode']->item($i)->textContent ?? '',
                    $data['eduName']->item($i)->textContent ?? '',
                    $data['eduLevel']->item($i)->textContent ?? '',
                    $data['eduForm']->item($i)->textContent ?? '',
                    $data['numberAll']->item($i)->textContent ?? '',
                    $data['numbers'][$i] ?? []
                );
                $records[] = $contingentRow->getData();    
            } catch (\Exception $e) {
                Printer::println($e->getMessage(), Color::RED);
            }
            
        }
        return $records;
    }

    private function checkData(array $data): bool
    {
        $count = 0;
        foreach($data as $dt) {
            $count += $dt->length ?? 0;
        }
        return $count ? false : true;
    }

    protected function parse(string $_): array
    {
        $data = [];
        foreach (self::FIELDS as $field => $tags) {
            $data[$field] = new \DOMNodeList();
            foreach ($tags as $tag) {
                $domNode = $this->xpath->query(self::TEMPLATE . $tag . "[@itemprop=\"$field\"]");
                if ($domNode->length) {
                    $data[$field] = $domNode;
                }
            }
        }

        if ($data && !$data['numberAll']->length ?? '') {
            for ($i = 0; $i < $data['eduCode']->length; $i++) {
                $data['numbers'][$i]= [
                    $data['numberBR']->item($i)->textContent ?? '',
                    $data['numberBM']->item($i)->textContent ?? '',
                    $data['numberBF']->item($i)->textContent ?? '',
                    $data['numberP']->item($i)->textContent ?? '',
                ];
            }
        }
        return $data;
    }

    public function getLink(): string
    {
        $needle = "Информация о численности обучающихся";
        $data = $this->dom->getElementsByTagName('a');
        for ($i = 0; $i < $data->length; $i++) {
            $haystack = $data->item($i)->textContent;
            $isInformationOfContingent = strpos($haystack, $needle) !== false;
            if ($isInformationOfContingent) {
                return $data->item($i)->getAttribute('href');
            }
        }
        return '';
    }
}