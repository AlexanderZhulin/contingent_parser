<?php
namespace ContingentParser\Http;

use ContingentParser\Logger\HttpLogger;
use ContingentParser\Printer;
use CurlHandle;
/**
 * Summary of CurlHelper
 */
final class CurlHelper
{
    private CurlHandle|bool $curl;
    private string $url;
    private array $site;
    /**
     * Коснтруктор
     * Инициализация сессии
     * @param string $url
     * URL сайта
     * @param array $site
     * Идентификатор организации и базовый URL сайта
     */
    public function __construct(string $url, array $site)
    {
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
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 90);
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
    public function getContent() : string
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        $html = curl_exec($this->curl);
        if ($this->checkLocation($this->url, $html)) {
            $html = $this->getContent();
        }
        return $html;
    }
    /**
     * Summary of checkLocation
     * @param string $html
     * @return bool
     */
    private function checkLocation(string &$url, string $html) : bool
    {
        preg_match('/location:(.*?)\n/i', $html, $matches);
        if (empty($matches)) return false;
        $target = $matches[1];
        $target = preg_replace("/[^a-z0-9\-:.\/,]/iu", '', $target);
        $url = $target ? $target : $url;

        return $target ? true : false;
    }
    /**
     * Сообщить об ошибке
     * @return void
     */
    public function reportError() : void
    {
        $httpLogger = new HttpLogger('log/http-curl.log');

        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($httpCode != 200 && $httpCode != 0) {
            Printer::println("HTTP-code: $httpCode", 'red');
            $message = implode(' ', $this->site) . ' HTTP-code(' . $httpCode.')';
            $httpLogger->log($message, $httpCode);
        } else if ($httpCode == 0) {
            $errno = curl_errno($this->curl);
            $message = implode(' ', $this->site);
            $message .= " cURL error ({$errno}): ".curl_strerror($errno);
            $httpLogger->log($message);
        } else {
            Printer::println("HTTP-code: $httpCode", 'blue');
        }
    }
}
