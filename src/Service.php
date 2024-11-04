<?php
namespace SvedenParser;

abstract class Service
{
    /**
     * Добавить идентификатор специальности в запись численности
     * @param array $data Массив численности по специальностям
     * @param array $specialties Массив специальностей
     * @return void
     */
    protected function addSpecId(array &$data, array $specialties): void
    {
        $specIdMap = array_column($specialties, 'spec_id', 'spec_code');
        foreach ($data as $key => $con) {
            $data[$key]['spec_id'] = $specIdMap[$con['spec_code']] ?? null;
        }
    }
    /**
     * Добавить идентификатор организации в запись численности
     * @param array $data Массив численности по специальностям
     * @param int $orgId Идентифиактор организации
     * @return void
     */
    protected function addOrgId(array &$data, int $orgId): void
    {
        foreach ($data as &$con) {
            $con['org_id'] = $orgId;
        }
    }
    protected function addIsActual(array &$data): void
    {
        foreach ($data as &$con) {
            $con['is_actual'] = 1;
        }
    }

    abstract public function getLink(string $html): string;
    abstract public function getData(string $html, array $specialties, int $orgId): array;
    abstract public function isValidData(array $data): bool;
}