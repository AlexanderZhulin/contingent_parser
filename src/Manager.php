<?php
namespace SvedenParser;

use SvedenParser\Http\HttpClient;
use SvedenParser\Http\UrlBuilder;
use SvedenParser\Logger\Logger;
use Symfony\Component\Yaml\Yaml;

abstract class Manager
{
    protected Service $service;
    protected Repository $repository;
    protected HttpClient $httpClient;
    protected string $templateUri;

    public function __construct()
    {
        $this->httpClient = new HttpClient();
    }
    /**
     * Cобирает из микроразметки данные таблицы 
     * "Информация о численности обучающихся" в разделе "Образование"
     * @param array $site Массив [id организации, URL]
     * @return void
     */
    public function collectData(array $site): void
    {
        list('org_id' => $orgId, 'site' => $url) = $site;
        if (!$url) return;
        Logger::log($orgId . ' ' . $url);

        $count = 0;
        foreach (UrlBuilder::PARAMS_URL as $params) {
            $uri = $params['slash'] ? $this->templateUri.'/' : $this->templateUri;
            $url = UrlBuilder::build($url, $params);

            $html = $this->httpClient->getContentOfSite($url, $site, $uri);
            if (!$html) {
                $count++;
                continue;
            }

            $uri = $this->service->getLink($html);
            Logger::log("URI: $uri");

            $data = $this->service->getData(
                $html,
                $this->repository->specialties(),
                $orgId
            );

            if (!$data && $uri) {
                if (UrlBuilder::isUrl($uri) && UrlBuilder::checkUri($uri)) {
                    $html = $this->httpClient->getContentOfSite($uri, $site);
                } else if (UrlBuilder::checkUri($uri)) {
                    if (UrlBuilder::slashIsFirst($uri)) {
                        $html = $this->httpClient->getContentOfSite($url, $site, $uri);
                    } else {
                        $html = $this->httpClient->getContentOfSite($url, $site, $this->templateUri."/$uri");
                    }
                } else {
                    Logger::log("Data in Document");
                    file_put_contents(
                        SVEDEN_PARSER.'/data/'.TYPE_PARSER.'-doc.yaml', 
                        Yaml::dump([$site]), 
                        FILE_APPEND
                    );
                    break;
                }
                // Получаем данные таблицы приема
                $data = $this->service->getData(
                    $html,
                    $this->repository->specialties(),
                    $orgId
                );
            }

            if ($data && $this->service->isValidData($data)) {
                // Заносим в базу
                $this->repository->save($data);
            } else {
                Logger::log("No result");
                file_put_contents(
                    SVEDEN_PARSER.'/data/'.TYPE_PARSER.'-html.yaml', 
                    Yaml::dump([$site]), 
                    FILE_APPEND
                );
            }
            break;
        }

        if ($count === count(UrlBuilder::PARAMS_URL)) {
            file_put_contents(
                SVEDEN_PARSER.'/data/'.TYPE_PARSER.'-http-curl.yaml', 
                Yaml::dump([$site]), 
                FILE_APPEND
            );
        }
    }

    /**
     * Получить массив сайтов
     * @param array $params Массив сайтов, у которых нужны обновиленные URL
     * @return array
     */
    public function getSites(array $params = []): array
    {
        if (!$params) {
            return $this->repository->getSitesFromNiimko();
        } else {
            return $this->repository->getSitesFromMiccedu($params);
        }
    }
}