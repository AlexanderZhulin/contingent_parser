<?php
namespace SvedenParser;

abstract class Parser
{
    protected ?\DOMXPath $xpath;
    protected \DOMDocument $dom;
    protected const ENCODING = "UTF-8";

    public function __construct(string $html)
    {
        $this->dom = new \DOMDocument(
            encoding: self::ENCODING
        );
        libxml_use_internal_errors(true);
        if (!$html) {
            $this->xpath = null;
        } else {
            $this->setEncoding($html);
            $this->dom->loadHTML($html);
            $this->xpath = new \DOMXPath($this->dom);
            libxml_clear_errors();
        }
    }
    
    private function setEncoding(string &$html): void
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
        $html = mb_convert_encoding($html,'HTML-ENTITIES','UTF-8');
    }

    abstract public function getDataTable(): array;
    abstract protected function parse(string $tag): array;
    abstract public function getLink(): string;
}