<?php
namespace SvedenParser\ContingentParser;
use SvedenParser\Service;

final class ContingentService extends Service
{
    /**
     * Получить данные о численности
     * @param string $html Разметка сайта вуза
     * @param mixed $specialties Массив специальностей
     * @param int $orgId Идентификатор организации
     * @return array
     */
    public function getData(string $html, array $specialties, int $orgId): array
    {
        $parser = new ContingentParser($html);
        $contingent = $parser->getDataTable();
        $this->addSpecId($contingent, $specialties);
        $this->addOrgId($contingent, $orgId);
        $this->addIsActual($contingent);

        return $contingent;
    }
    /**
     * Проверка на валидность записи численнести
     * @param array $contingent Массив численности по специальностям
     * @return bool
     */
    public function isValidData(array $contingent): bool
    {
        $count = 0;
        foreach ($contingent as $value) {
            $count += $value['contingent'];
        }
        return $count ? true : false;
    }

    /**
     * 
     * @param string $html
     * @return string
     */
    public function getLink(string $html): string
    {
        $parser = new ContingentParser($html);
        return $parser->getLink();
    }
}