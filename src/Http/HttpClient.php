<?php
namespace SvedenParser\Http;

use SvedenParser\Color;
use SvedenParser\Logger\Logger;
use SvedenParser\Printer;
use GuzzleHttp\Client;

final class HttpClient
{    
    /**
     * Обработка численности обучающихся
     * @param string $url URL сайта
     * @param array $site Идентификатор организации, и базовый URL
     * @return string|bool
     */
    public function getContentOfSite(string $url, array $site, string $uri = ''): string|bool
    {
        try {
            $client = $this->createClient($url);
            $url = UrlBuilder::addUri($url , $uri);
            Logger::log("Parsing for $url");

            $response = $client->get($url);
            $httpCode = $response->getStatusCode();
            Logger::log("HTTP-code: $httpCode");

            $html = $response->getBody()->getContents();
        } catch (\Exception $e) {
            $message = $e->getCode() ? "HTTP-code: " . $e->getCode() : "Error cURL";
            Logger::log($message);
            $html = $this->handleException($url, $site);
        } finally {
            return $html;
        }
    }
    /**
     * Обработка исключения
     * Повторная попытка с помощью CurlHelper
     * @param string $url URL сайта
     * @param array $site
     * @return string|bool
     */
    private function handleException(string $url, array $site): string|bool
    {
        $curlHelper = new CurlHelper($url, $site);
        $html = $curlHelper->getContent();
        
        if ($curlHelper->isError()) {
            return false;
        }
        return $html;
    }
    /**
     * Создать клиента с базовым URL
     * @param string $url
     * @return \GuzzleHttp\Client
     */
    private function createClient(string $url): Client
    {
        $config = $this->config() + ["base_uri" => $url];
        return new Client($config);
    }
    /**
     * Конфигурация клиента
     * @return array
     */
    private function config(): array
    {
        return [
            'force_ip_resolve' => 'v4',
            'allow_directs'   => [ 
                'max' => 5, 
                'strict' => true, 
                'referer' => true, 
                'protocols' => ['http', 'https'],
                'track_redirects' => true 
            ],
            'connect_timeout' => 30.0,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) '
                    .'AppleWebKit/537.36 (KHTML, like Gecko) '
                    .'Chrome/124.0.0.0 YaBrowser/24.6.0.0 Safari/537.36',
                'Content-Type' => 'text/html;charset=utf-8'
            ]
        ];
    }
}