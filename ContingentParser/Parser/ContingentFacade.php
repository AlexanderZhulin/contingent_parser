<?php
namespace ContingentParser\Parser;

class ContingentFacade
{
    /**
     * Получить данные о численности
     * @param string $html
     * Разметка сайта вуза
     * @param mixed $specialties
     * Массив специальностей
     * @param int $orgId
     * Идентификатор организации
     * @return array
     */
    public function getContingent(
        string $html,
        array $specialties,
        int $orgId
    ) : array {
        $parser = new ContingentParser($html);
        $contingent = $parser->getDataTable();
        $this->addSpecId($contingent, $specialties);
        $this->addOrgId($contingent, $orgId);

        return $contingent;
    }
    /**
     * Проверка на валидность записи численнести
     * @param array $contingent
     * Массив численности по специальностям
     * @return bool
     */
    public function isValidContingent(array $contingent) : bool
    {
        $count = 0;
        foreach ($contingent as $value) {
            $count += $value['contingent'];
        }
        return $count ? true : false;
    }
    /**
     * Добавить идентификатор специальности в запись численности
     * @param array $contingent
     * Массив численности по специальностям
     * @param array $specialties
     * Массив специальностей
     * @return void
     */
    private function addSpecId(array &$contingent, array $specialties) : void
    {
        $specIdMap = array_column($specialties, 'spec_id', 'spec_code');
        foreach ($contingent as $key => $con) {
            $contingent[$key]['spec_id'] = $specIdMap[$con['spec_code']] ?? null;
        }
    }
    /**
     * Добавить идентификатор организации в запись численности
     * @param array $contingent
     * Массив численности по специальностям
     * @param int $orgId
     * Идентифиактор организации
     * @return void
     */
    private function addOrgId(array &$contingent, int $orgId): void
    {
        foreach ($contingent as &$con) {
            $con['org_id'] = $orgId;
        }
    }
}
