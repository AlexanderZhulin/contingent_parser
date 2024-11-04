<?php
namespace SvedenParser;

use SvedenParser\Database\Database;
use SvedenParser\Database\DatabaseConfig;
use SvedenParser\Logger\Logger;

abstract class Repository
{
    public string $table;
    protected Database $opendata;
    protected Database $niimko;
    public const TABLE_HASH = 'sveden_education_hashes';
    protected array $specialties;
    protected array $universities;

    public function __construct()
    {
        $this->niimko = new Database(new DatabaseConfig('niimko'));
        $this->opendata = new Database(new DatabaseConfig('opendata'));
    }
    abstract protected function insert(array $data): void;
    abstract public function getData(int $orgId): array;
    public function save(array $data): void
    {
        $orgId = $data[0]['org_id'];
        $hashOrg = $this->getHash($orgId); // получить хэши
        $string = '';
        array_multisort($data);
        foreach ($data as $dt) {
            $string .= substr(implode('', $dt), 0, -1);
        }
        $hashData = md5($string);
        if ($hashOrg === $hashData) {
            Logger::log("Данные с сайта {$orgId} не изменились");
            return;
        }
        // сброс is_actual в базе для :org_id
        if ('employees' !== TYPE_PARSER) {
            $this->resetIsActual($orgId);
        }
        // добавить данные в таблицу
        $this->insert($data);
        // обновить таблицу хэшей
        $this->updateHash($orgId, $hashData);
        Logger::log("Данные с сайта {$orgId} изменились!");
    }
    protected function getHash(int $orgId): string
    {
        $sql = "SELECT data_hash FROM " . self::TABLE_HASH . " WHERE org_id = :v1 AND sveden_table = :v2";
        $result = $this->opendata->select($sql, [$orgId, $this->table])[0]['data_hash'] ?? '';
        return  $result;
    }
    protected function updateHash(int $orgId, string $hash): void
    {
        $sql = "INSERT INTO " . self::TABLE_HASH . "(org_id, sveden_table, data_hash)
            VALUES (:v1, :v2, :v3) 
            ON DUPLICATE KEY UPDATE data_hash = :v3";
        $this->opendata->update($sql, [$orgId, $this->table, $hash]);
    }
    public function resetIsActual(int $orgId): void
    {
        $sql = "UPDATE " . $this->table . " SET is_actual = :v1 WHERE org_id = :v2";
        $this->opendata->update($sql, [0, $orgId]);
    }
    protected function getUniversities(): array
    {
        $sql = "SELECT DISTINCT org_id FROM " . $this->table;
        $universities = $this->opendata->select($sql);
        return array_column($universities, 'org_id');
    }
    /**
     * Извлечение URL сайтов из базы данных niimko
     * @return array
     */
    public function getSitesFromNiimko(): array
    {
        $sql = "SELECT kod AS org_id, site FROM niimko.s_vuzes 
            WHERE ootype = :v1 AND deleted = :v2 AND fake = :v3 AND country = :v4";
        return $this->niimko->select($sql, ['vuz', 'n', 'n', 'RU']);

    }
    /**
     * Извлечение сайтов базы данных opendata
     * из таблицы miccedu_monitoring.
     * @param array $params ids сайтов с устаревшим URL
     * @return array
     */
    public function getSitesFromMiccedu(array $params): array
    {
        $sql = "SELECT site, vuzkod AS org_id FROM opendata.miccedu_monitoring 
            WHERE year = :v1 AND (vuzkod = :val1 OR vuzkod = :val2 OR ...)";
        for($i = 2; $i <= count($params); $i++) {
            $sql .= "vuzkod = :v$i OR ";
        }
        $sql = substr($sql, 0, -4);

        return $this->opendata->select($sql, $params);

    }
    /**
     * Публичное получение специальностей
     * @return array
     */
    public function specialties(): array
    {
        return $this->specialties ? $this->specialties : [];
    }
    /**
     * Публичное получение id вузов, занесенных в базу opendata
     * @return array
     */
    public function universities(): array
    {
        return $this->universities ? $this->universities : [];
    }
    /**
     * Извлечение кодов специальности из базы данных niimko
     * @return array
     */
    protected function getSpecialties(): array
    {
        $sql = "SELECT id AS spec_id, kod AS spec_code FROM niimko.s_specs WHERE oopkodes = :v1";
        return $this->niimko->select($sql, ['gos3p']);
    }
    /**
     * Обновление сайтов в базе данных niimko
     * @param array $params
     * Массив [['org_id' => val1, 'site' => val2,],...]
     * @return void
     */
    public function updateSitesOpendata(array $params): void
    {
        /*
        UPDATE niimko.s_vuzes
        SET site = CASE kod
        WHEN :v1 THEN :v2
        WHEN :v3 THEN :v4
        ...
        ELSE kod
        END
        WHERE kod IN (:v1, :v2...)
        */
        $count = count($params);
        for ($i = 0; $i < $count; $i++) {
            if ($i % 2 == 0) {
                $params[] = $params[$i];
            }
        }
        $sql = "UPDATE niimko.s_vuzes\nSET site = CASE kod\n";

        for ($i = 0; $i < $count;) {
            $sql .= "WHEN :v".++$i." THEN :v".++$i."\n";
        }
        $sql .= "ELSE kod\nEND\nWHERE kod in(";
        for ($i = $count++; $i < count($params);) {
            $sql .= ":v".++$i.",\n";
        }
        $sql = rtrim($sql,",\n") .")\n";

        $this->opendata->update($sql, $params);
    }
}