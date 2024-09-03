<?php
namespace ContingentParser\Database;

use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;

class DatabaseFacade
{
    private Database $opendata;
    private Database $niimko;
    private array $specialties;
    private array $universities;
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->niimko = new Database(new DatabaseConfig('niimko'));
        $this->opendata = new Database(new DatabaseConfig('opendata'));
        $this->specialties = $this->getSpecialties();
        $this->universities = $this->getUniversities();
    }
    /**
     * Извлечение URL сайтов из базы данных niimko
     * @return array
     */
    public function getSitesFromNiimko() : array
    {
        /*
        SELECT kod AS org_id, site FROM niimko.s_vuzes
        WHERE ootype = 'vuz' AND deleted = 'n' AND fake = 'n'
        */
        $builder = new GenericBuilder();
        $params = ['vuz', 'n', 'n', 'RU'];
        $query = $builder->select()
            ->setTable('s_vuzes')
            ->setColumns(['org_id' => 'kod', 'site'])
            ->where('AND')
            ->equals('ootype', 'vuz')
            ->equals('deleted', 'n')
            ->equals('fake', 'n')
            ->equals('country', 'RU')
            ->end();
        $sql = $builder->write($query);
        $sites = $this->niimko->select($sql, $params);
        
        return $sites;
    }
    /**
     * Извлечение сайтов базы данных opendata
     * из таблицы miccedu_monitoring.
     * @param array $params
     * Сайты, у которых устаревшие URL
     * @return array
     */
    public function getSitesFromMiccedu(array $params) : array
    {
        /*
        SELECT site, vuzkod AS org_id FROM opendata.miccedu_monitoring 
        WHERE year = 2023 AND (vuzkod = :val1 OR vuzkod = :val2 OR ...)
        */
        $builder = new GenericBuilder();
        $year = 2023;
        foreach ($params as $key => $org) {
            $params[$key] = (int)$org['org_id'];
        }
        $query = $builder->select()
            ->setTable('miccedu_monitoring')
            ->setColumns(['org_id' => 'vuzkod','site'])
            ->where('AND')
            ->equals('year', $year)
            ->subWhere('OR');
        foreach ($params as $orgId) {
            $query->equals('vuzkod', $orgId);
        }
        $query = $query->end();
        $sql = $builder->write($query);
        array_unshift($params, $year);
        $sites = $this->opendata->select($sql, $params);

        return $sites;
    }
    /**
     * Внесение данных численности обучающихся в базу данных opendata
     * @param array $contingent
     * Массив записей численности по специальностям
     * @return void
     */
    public function insertContingent(array $contingent) : void
    {
        /*
        INSERT INTO sveden_education_contingent
            (org_id, spec_id, spec_code, spec_name, edu_level, edu_forms, contingent)
        VALUES
            (:v1, :v2, :v3, :v4, :v5, :v6, :v7)
            ...
        */
        $builder = new GenericBuilder();
        $countAtributes = count($contingent[0]);
        $size = $countAtributes * (count($contingent) - 1);
        $query = $builder->insert()
            ->setTable('sveden_education_contingent')
            ->setValues(
                $contingent[0]
            );
        $sql = $builder->write($query);
        for ($i = $countAtributes; $i <= $size;) { 
            $sql .= "    (:v".(++$i).", :v".(++$i).", :v".(++$i).", :v"
                .(++$i).", :v".(++$i).", :v".(++$i).", :v".(++$i).")\n";
        }
        $sql = preg_replace('/\)\s*VALUES\s*/', ') VALUES ', $sql);
        $sql = preg_replace('/\)\s*\(/', '), (', $sql);
        $this->opendata->insert($sql, $contingent);
    }
    /**
     * Публичное получение специальностей
     * @return array
     */
    public function specialties() : array
    {
        return $this->specialties ? $this->universities : [];
    }
    /**
     * Публичное получение id вузов, занесенных в базу opendata
     * @return array
     */
    public function universities() : array
    {
        return $this->universities ? $this->specialties : [];
    }
    /**
     * Извлечение кодов специальности из базы данных niimko
     * @return array
     */
    private function getSpecialties() : array
    {
        /*
        SELECT id AS spec_id, kod AS spec_code FROM niimko.s_specs 
        WHERE oopkodes = 'gos3p'
        */
        $builder = new GenericBuilder();
        $params = ['gos3p'];
        $query = $builder->select()
            ->setTable('s_specs')
            ->setColumns(['spec_id' =>'id', 'spec_code' => 'kod'])
            ->where()
            ->equals('oopkodes','gos3p')
            ->end();
        $sql = $builder->write($query);
        $specialties = $this->niimko->select($sql, $params);
        
        return $specialties;
    }
    /**
     * Извлечение id вузов, занесенных в базу opendata
     * @return array
     */
    private function getUniversities() : array
    {
        /*
        SELECT DISTINCT org_id FROM sveden_education_contingent
        */
        $builder = new GenericBuilder();
        $query = $builder->select()
            ->setTable('sveden_education_contingent')
            ->setColumns(['org_id'])
            ->where()
            ->greaterThan('org_id', 0)
            ->end();
        $sql = $builder->write($query);
        $sql = preg_replace("/ WHERE.*/", '', $sql);
        $sql = preg_replace('/SELECT/', 'SELECT DISTINCT', $sql);
        $universities = $this->opendata->select($sql);

        return array_column($universities, 'org_id');
    }
    /**
     * Обновление сайтов в базе данных niimko
     * @param array $params
     * Массив [['org_id' => val1, 'site' => val1,],...]
     * @return void
     */
    public function updateSitesOpendata(array $params) : void
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
