<?php
namespace SvedenParser\PriemParser;
use SvedenParser\Printer;
use SvedenParser\Service;

final class PriemService extends Service
{
    /**
     * Получить данные о приеме
     * @param string $html Разметка сайта вуза
     * @param mixed $specialties Массив специальностей
     * @param int $orgId Идентификатор организации
     * @return array
     */
    public function getData(string $html, array $specialties, int $orgId): array
    {
        $parser = new PriemParser($html);
        $priem = $parser->getDataTable();
        $this->addSpecId($priem, $specialties);
        $this->addOrgId($priem, $orgId);
        $this->addIsActual($priem);

        return $priem;
    }
    /**
     * Проверка на валидность записи приема
     * @param array $priem Массив численности по специальностям
     * @return bool
     */
    public function isValidData(array $priem): bool
    {
        $countScore = 0;
        $countContingent = 0;
        foreach ($priem as $value) {
            $countScore += $value['avg_score'];
            $countContingent += $value['contingent'];
        }
        return $countScore || $countContingent ? true : false;
    }

    public function getLink(string $html): string
    {
        $parser = new PriemParser($html);
        return $parser->getLink();
    }
}
