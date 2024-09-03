<?php
namespace ContingentParser\Http;

use ContingentParser\Printer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\TransferStats;

final class HttpClientFacade
{
    private Client $client;
    private array $config;
    
    public function __construct() {}
    /**
     * Обработка численности обучающихся
     * @param string $url
     * URL сайта
     * @param array $site
     * Идентификатор организации, и базовый URL
     * @return string
     */
    public function processEducationContingentSites(
        string $url,
        array $site
    ) : string {
        try {
            $client = $this->createClient($url);
            // Запрос по базовому uri
            $response = $client->get('', [
                'on_stats' => function (TransferStats $stats) use (&$redirectUrl) {
                    $redirectUrl = $stats->getEffectiveUri();
                }
            ]);
            Printer::println("Redirect $url -> $redirectUrl");
            $url .= substr($url, -1) == '/' ? '':'/';
            $url .= "sveden/education/";
            Printer::println("Parsing for $url");

            $response = $client->get($url);
            $httpCode = $response->getStatusCode();
            Printer::println("HTTP-code: $httpCode", 'blue');

            $html = $response->getBody()->getContents();
        } catch (ClientException
            | RequestException
            | ConnectException
            | ServerException
            | MalformedUriException $e
        ) {
            Printer::println("HTTP-code: ".$e->getCode(), 'blue');
            $html = $this->handleException($url, $site);
        } finally {
            return $html;
        }
    }
    /**
     * Обработка исключения
     * Повторная попытка спомощью CurlHelper
     * @param string $url
     * URL сайта
     * @param array $site
     * @return string
     */
    private function handleException(string $url, array $site) : string
    {
        $curlHelper = new CurlHelper($url, $site);
        $html = $curlHelper->getContent();
        $curlHelper->reportError();
        return $html;
    }
    /**
     * Создать клиента с базовым URL
     * @param string $url
     * @return \GuzzleHttp\Client
     */
    private function createClient(string $url) : Client
    {
        $this->config = $this->config() + ["base_uri" => $url];
        return new Client($this->config);
    }
    /**
     * Конфигурация клиента
     * @return array
     */
    private function config() : array
    {
        return [
            'force_ip_resolve' => 'v4',
            'debug' => fopen("log/debug-http.log", "w"),
            'allow_directs'   => [ 
                'max' => 5, 
                'strict' => true, 
                'referer' => true, 
                'protocols' => ['http', 'https'],
                'track_redirects' => true 
            ],
            'connect_timeout' => 300.0,
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
