<?php
namespace ContingentParser;

use ContingentParser\Database\Database;
use ContingentParser\Parser\ContingentParser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\TransferStats;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;

class Facade
{
    private GenericBuilder $_builder;

    public function __construct()
    {
        $this->_builder = new GenericBuilder();
    }

    public function getSitesFromDatabase(Database $db) : array
    {
        // SELECT kod AS org_id, site FROM niimko.s_vuzes
        // WHERE ootype = 'vuz' AND deleted = 'n' AND fake = 'n'
        $params = [1 => 'vuz', 'n', 'n', 'RU'];
        $query = $this->_builder->select()
            ->setTable('s_vuzes')
            ->setColumns(['org_id' => 'kod', 'site'])
            ->where('AND')
            ->equals('ootype', 'vuz')
            ->equals('deleted', 'n')
            ->equals('fake', 'n')
            ->equals('country', 'RU')
            ->end();
        $sql = $this->_builder->write($query);
        $sites = $db->select($sql, $params);
        
        return $sites;
    }

    public function getSpecialtiesFromDatabase(Database $db) : array
    {
        // SELECT id AS spec_id, kod AS spec_code FROM niimko.s_specs 
        // WHERE oopkodes = 'gos3p'
        $params = [1 => 'gos3p'];
        $query = $this->_builder->select()
            ->setTable('s_specs')
            ->setColumns(['spec_id' =>'id', 'spec_code' => 'kod'])
            ->where()
            ->equals('oopkodes','gos3p')
            ->end();
        $sql = $this->_builder->write($query);
        var_dump($sql);
        $specialties = $db->select($sql, $params);
        
        return $specialties;
    }

    public function getUniversitiesFromDatabase(Database $db) : array
    {
        // SELECT DISTINCT org_id FROM sveden_education_contingent
        $params = [1 => 'org_id'];
        $query = $this->_builder->select()
            ->setTable('sveden_education_contingent')
            ->setColumns(['org_id'])
            ->where()
            ->greaterThan('org_id', 0)
            ->end();
        $sql = $this->_builder->write($query);
        $sql = preg_replace("/ WHERE.*/", '', $sql);
        $sql = preg_replace('/SELECT/', 'SELECT DISTINCT', $sql);
        $specialties = $db->select($sql, $params);
        return $specialties;
    }

    public function getBaseUri(string $url) : string
    {
        // Строит -> https://<base_uri>
        $url = trim(strtolower($url));
        $url = preg_replace('/\s+/', '', $url);
        $url = str_replace("www/", "www.", $url);
        $url = str_replace("http:\\\\", "", $url);
        if (!preg_match('#^https?://#', $url)) {
            $url = "https://$url";
        }
        // $url = str_replace("http://", "https://", $url);
        $arr = parse_url($url);
        $url = $arr['scheme'] . '://' . $arr['host'] . '/';
        // $url = str_replace("www.", "", $url);
        $url = str_replace("_", "/", $url);
        return trim($url);
    }

    public function handleEducationContingentSites(
        string $uri,
        array $site
    ) : string {
        try {
            $client = new Client(
                $this->setConfigClient($uri)
            );
            // Запрос по базовому uri
            $response = $client->get('', [
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                }
            ]);
            print("Redirect $uri -> $url" . PHP_EOL);
            if (substr($url, -1) == '/') {
                $url .= "sveden/education/";
            } else {
                $url .= "/sveden/education/";
            }
            print("Parsing for $url" . PHP_EOL);
            $response = $client->get($url);
            $html = $response->getBody()->getContents();
        } catch (ClientException
            | RequestException
            | ConnectException
            | ServerException
            | MalformedUriException $e
        ) {
            $html = '';
        } finally {
            return $html;
        }
    }

    private function setConfigClient(string $baseUri) : array
    {
        return [
            'force_ip_resolve' => 'v4',
            'debug' => fopen("debug-http.log", "a"),
            'base_uri' => $baseUri,
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
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 YaBrowser/24.6.0.0 Safari/537.36',
                'Content-Type' => 'text/html;charset=utf-8'
            ]
        ];
    }

    public function getContingent(
        string $html,
        ?array $specialties,
        int $orgId
    ) : array {
        $parser = new ContingentParser($html, '//tr[@itemprop="eduChislen"]//');
        $contingent = $parser->getDataTable();
        // $this->addSpecId($contingent, $specialties);
        $this->addOrgId($contingent, $orgId);

        return $contingent;
    }

    private function addSpecId(array &$contingent, array $specialties) : void
    {
        $specIdMap = array_column($specialties, 'spec_id', 'spec_code');
        print_r($specIdMap);
        foreach ($contingent as $key => $con) {
            $contingent[$key]['spec_id'] = $specIdMap[$con['spec_code']] ?? null;
        }
    }

    private function addOrgId(array &$contingent, int $orgId): void
    {
        foreach ($contingent as &$con) {
            $con['org_id'] = $orgId;
        }
    }

    public function isValidContingent(array $contingent) : bool
    {
        $count = 0;
        foreach ($contingent as $value) {
            $count += $value['contingent'];
        }
        return $count ? true : false;
    }

    public function insertContingent(array $contingent) : void
    {
        $countAtributes = count($contingent[0]);
        $size = count($contingent) * ($countAtributes - 1);
        
        $query = $this->_builder->insert()
            ->setTable('sveden_education_contingent')
            ->setValues([
                'org_id' => '',
                'spec_id' => '',
                'edu_code' => '',
                'edu_name' => '',
                'edu_form' => '',
                'edu_level' => '',
                'contingent' => ''
            ]);
        $sql = $this->_builder->writeFormatted($query);
        
        for ($i = $countAtributes; $i <= $size;) { 
            $sql .= "    (:v".(++$i).", :v".(++$i).", :v".(++$i).", :v".(++$i).", :v".(++$i).", :v".(++$i).", :v".(++$i).")\n";
        }
        echo $sql;
    }
}