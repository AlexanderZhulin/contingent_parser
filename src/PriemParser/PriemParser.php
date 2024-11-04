<?php
namespace SvedenParser\PriemParser;

use SvedenParser\Color;
use SvedenParser\Exception\HtmlException;
use SvedenParser\Parser;
use SvedenParser\Printer;

final class PriemParser extends Parser
{
    private const TEMPLATE = '//tr[@itemprop="eduPriem"]';
    //priem
    private const FIELDS = ["td", "span"];
    public function getDataTable(): array
    {
        if (!$this->xpath) return [];

        $data = [];
        foreach (self::FIELDS as $tag) {
            $data = $data ? $data : $this->parse($tag);
        }
        
        if (!$data) return [];

        $records = [];
        foreach ($data as $record) { 
            try {
                $priemRow = new PriemRow(
                    $record['eduCode'] ?? '',
                    $record['eduName'] ?? '',
                    $record['eduLevel'] ?? '',
                    $record['eduForm'],
                    $record['scorePriem'] ?? '',
                    [
                        $record['numberBFpriem'] ?? '',
                        $record['numberBRpriem'] ?? '',
                        $record['numberBMpriem'] ?? '',
                        $record['numberPpriem'] ?? '',
                    ],
                );
                $records[] = $priemRow->getData();    
            } catch (\Exception $e) {
                Printer::println($e->getMessage(), Color::RED);
            }
            
        }
        
        return $records;
    }
    protected function parse(string $tag): array
    {
        $data = [];
        $commonData = [];
        $rows = $this->xpath->query(self::TEMPLATE);
        
        foreach ($rows as $row) {
            $cells = $this->xpath->query($tag, $row);
            $rowData = [];

            foreach ($cells as $cell) {
                $itemprop = $cell->getAttribute('itemprop');
                $rowData[$itemprop] = $cell->nodeValue;
            }

            if (array_key_exists('eduCode', $rowData)) {
                $commonData = [
                    'eduCode' => $rowData['eduCode'] ?? '',
                    'eduName' => $rowData['eduName'] ?? '',
                    'eduLevel' => $rowData['eduLevel'] ?? '',
                ];
            }

            $data[] = array_merge($commonData, [
                'eduForm' => $rowData['eduForm'] ?? '',
                'numberBF' => $rowData['numberBFpriem'] ?? '',
                'numberBR' => $rowData['numberBRpriem'] ?? '',
                'numberBM' => $rowData['numberBMpriem'] ?? '',
                'numberP' => $rowData['numberPpriem'] ?? '',
                'score' => $rowData['scorePriem'] ?? '',
            ]);
        }
        
        return $data;
    }

    public function getLink(): string
    {
        $needle = "Информация о результатах приема";
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