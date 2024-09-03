<?php
namespace ContingentParser;

use ContingentParser\Database\DatabaseFacade;
use ContingentParser\Http\HttpClientFacade;
use ContingentParser\Http\UrlBuilder;
use ContingentParser\Logger\HtmlLogger;
use ContingentParser\Parser\ContingentFacade;

class Facade
{
    private DatabaseFacade $databaseFacade;
    private HttpClientFacade $httpClientFacade;
    private ContingentFacade $contingentFacade;
    private HtmlLogger $htmlLogger;
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->databaseFacade = new DatabaseFacade();
        $this->httpClientFacade = new HttpClientFacade();
        $this->contingentFacade = new ContingentFacade();
        $this->htmlLogger = new HtmlLogger('log/html.log');
    }
    /**
     * Получить массив сайтов
     * @param array $params
     * Массив сайтов, у которых нужны обновиленные URL
     * @return array
     */
    public function getSites(array $params = []) : array
    {
        if (empty($params)) {
            return $this->databaseFacade->getSitesFromNiimko();
        } else {
            return $this->databaseFacade->getSitesFromMiccedu($params);
        }
    }
    /**
     * Cобирает из микроразметки данные таблицы 
     * "Информация о численности обучающихся" в разделе "Образование"
     * @param array $site
     * Сайт содержащий id организации и URL
     * @return void
     */
    public function collectDataFromContingent(array $site) : void
    {
        list('org_id' => $orgId, 'site' => $url) = $site;
        // Нет URL сайта вуза
        if (empty($site)) {
            // $httpLogger->log($orgId);
            return;
        }
        // Уже в базе
        if (in_array($orgId, $this->databaseFacade->universities())) {
            return;
        }
        $urlBuilder = new UrlBuilder();
        $url = $urlBuilder->build($url);
        Printer::println(implode(' ', $site), 'green');
        $html = $this->httpClientFacade->processEducationContingentSites(
            $url,
            $site
        );

        $contingent = $this->contingentFacade->getContingent(
            $html,
            $this->databaseFacade->specialties(),
            $orgId
        );

        if (empty($contingent)) {
            Printer::println("No result", 'red');
            $this->htmlLogger->log("$orgId $url");
        } else {
            if ($this->contingentFacade->isValidContingent($contingent)) {
                // Заносим в базу
                Printer::print_r($contingent, 'blue');
                // $this->databaseFacade->insertContingent($contingent);
            } else {
                $this->htmlLogger->log("$orgId $url");
                Printer::println("No result", 'red');
            }
        }
        Printer::println();
    }

    public function getExclusionSites(string $path) : array
    {
        $logs = file($path);
        $result = [];
        foreach ($logs as $log) {
            $data = explode(' ', $log);
            $result[] = [
                'org_id' => $data[2],
                'site' => $data[3] ? $data[3] : ''
            ];
        }
        return $result;
    }
}