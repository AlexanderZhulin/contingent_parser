<?php
namespace ContingentParser\Parser;

use DOMDocument;
use DOMXPath;

class ContingentParser
{
    private DOMXPath $xpath;
    private string $template;
    private const ENCODING = "UTF-8"; 
    private const FIELDS = [
        "eduCode" => "td",
        "eduName" => "td",
        "eduLevel" => "td",
        "eduForm" => "td",
        "numberAll" => ["th", "td"]
    ];

    public function __construct(string $html, string $template)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument(
            encoding: self::ENCODING
        );
        $this->setEncoding($html);
        $dom->loadHTML($html);
        
        $this->xpath = new DOMXPath($dom);
        $this->template = $template;
    }
    
    private function setEncoding(string &$html) : void
    {
        $encoding = mb_detect_encoding($html, 'UTF-8, windows-1251');
        if ($encoding != self::ENCODING) {
            $html = mb_convert_encoding(
                $html,
                self::ENCODING,
                $encoding
            );
            $html = str_replace('windows-1251',self::ENCODING, $html); 
        }
    }
    private function parse() : array
    {
        $data = [];
        foreach (self::FIELDS as $field => $tag) {
            if (!is_array($tag)) {
                $data[$field] = $this->xpath->query($this->template . $tag . "[@itemprop=\"$field\"]");
            } else {
                $th = $this->xpath->query($this->template . $tag[0] . "[@itemprop=\"$field\"]");
                $td = $this->xpath->query($this->template . $tag[1] . "[@itemprop=\"$field\"]");
                $data[$field] = $th->length > $td->length ? $th : $td;
            }
            
        }
        return $data;
    }

    public function getDataTable() : array
    {
        $data = $this->parse();
        $records = [];
        if ($data == null) return [];

        $equal = $data['eduName']->length;
        foreach ($data as $field) {
            if ($field->length == 0) {
                return [];
            }
            if ($field->length != $equal) {
                return [];
            }
        }

        for ($i = 0; $i < $data['eduCode']->length; $i++) { 
            $contingentRow = new ContingentRow(
                $data['eduCode']->item($i)->textContent,
                $data['eduName']->item($i)->textContent,
                $data['eduLevel']->item($i)->textContent,
                $data['eduForm']->item($i)->textContent,
                (int)$data['numberAll']->item($i)->textContent
            );
            $records[] = $contingentRow->getData();
        }
        return $records;
    }
}