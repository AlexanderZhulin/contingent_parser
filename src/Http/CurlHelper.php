<?php
namespace SvedenParser\Http;

use SvedenParser\Color;
use SvedenParser\Logger\HttpLogger;
use SvedenParser\Logger\Logger;
use SvedenParser\Printer;
use CurlHandle;
use Symfony\Component\Yaml\Yaml;

final class CurlHelper
{
    private CurlHandle|bool $curl;
    private string $url;
    private array $site;
    private int $countRedirect;
    private const MAX_REDIRECT = 5;
    /**
     * Коснтруктор
     * Инициализация сессии
     * @param string $url URL сайта
     * @param array $site Идентификатор организации и базовый URL сайта
     */
    public function __construct(string $url, array $site)
    {
        $this->countRedirect = 0;
        $this->url = $url;
        $this->site = $site;

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_USERAGENT, 
            'Mozilla/5.0 (X11; Linux x86_64) '
            .'AppleWebKit/537.36 (KHTML, like Gecko) '
            .'Chrome/124.0.0.0 YaBrowser/24.6.0.0 Safari/537.36'
        );
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 30);
    }
    /**
     * Прекратить сессию
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }
    /**
     * Получить html-разметку
     * @return string
     */
    public function getContent(): string
    {
        Logger::log("Curl is trying to parse ". $this->url);
        if ($this->countRedirect < self::MAX_REDIRECT) {
            curl_setopt($this->curl, CURLOPT_URL, $this->url);
            $html = curl_exec($this->curl);
            if ($this->checkLocation($this->url, $html)) {
                $this->countRedirect++;
                $html = $this->getContent();
            }
            return $html;
        }
        return '';
    }
    /**
     * Summary of checkLocation
     * @param string $html
     * @return bool
     */
    private function checkLocation(string &$url, string $html): bool
    {
        preg_match('/location:(.*?)\n/i', $html, $matches);
        if (!$matches) return false;
        $target = $matches[1];
        $target = preg_replace("/[^a-z0-9\-:.\/,]/iu", '', $target);
        $url = $target ? $target : $url;

        return $target ? true : false;
    }
    /**
     * Сообщить об ошибке
     * @return void
     */
    public function isError(): bool
    {
        // $httpLogger = new HttpLogger(SVEDEN_PARSER . '/log');

        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($httpCode != 200 && $httpCode != 0) {
            Logger::log("HTTP-code: $httpCode");
            file_put_contents(SVEDEN_PARSER.'/data/'.TYPE_PARSER.'-http-curl.yaml', Yaml::dump([$this->site]), FILE_APPEND);
            return true;
        } else if ($httpCode == 0) {
            $errno = curl_errno($this->curl);
            $message = implode(' ', $this->site);
            $message .= " cURL error ({$errno}): " . curl_strerror($errno);
            Logger::log($message);
            file_put_contents(SVEDEN_PARSER.'/data/'.TYPE_PARSER.'-http-curl.yaml', Yaml::dump([$this->site]), FILE_APPEND);
            return true;
        } else {
            Logger::log("HTTP-code: $httpCode");
            return false;
        }
    }
}