<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Highloadblock\HighloadBlockTable;

Loc.loadMessages(__FILE__);

class VendorGeoip extends CModule
{
    public $MODULE_ID = 'vendor.geoip';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');
        
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = 'GeoIP Lookup Module';
        $this->MODULE_DESCRIPTION = 'Поиск GeoIP с горячим переключением провайдеров и кэшированием в HL';
    }

    public function DoInstall(): void
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->createHighloadBlock();
    }

    public function DoUninstall(): void
    {
        $this->deleteHighloadBlock();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    private function createHighloadBlock(): void
    {
        if (!Bitrix\Main\Loader::includeModule('highloadblock')) {
            return;
        }

        // Проверяем существование
        $existing = HighloadBlockTable::getList(['filter' => ['=NAME' => 'GeoIpHistory']])->fetch();
        if ($existing) {
            return;
        }

        $result = HighloadBlockTable::add([
            'NAME' => 'GeoIpHistory',
            'TABLE_NAME' => 'v_geoip_history',
        ]);

        if ($result->isSuccess()) {
            $hlId = $result->getId();
            $userTypeEntity = new \CUserTypeEntity();
            
            $fields = [
                ['FIELD_NAME' => 'UF_IP', 'USER_TYPE_ID' => 'string', 'MANDATORY' => 'Y'],
                ['FIELD_NAME' => 'UF_COUNTRY', 'USER_TYPE_ID' => 'string', 'MANDATORY' => 'N'],
                ['FIELD_NAME' => 'UF_CITY', 'USER_TYPE_ID' => 'string', 'MANDATORY' => 'N'],
                ['FIELD_NAME' => 'UF_PROVIDER', 'USER_TYPE_ID' => 'string', 'MANDATORY' => 'N'],
            ];

            foreach ($fields as $field) {
                $field['ENTITY_ID'] = 'HLBLOCK_' . $hlId;
                $userTypeEntity->Add($field);
            }
        }
    }

    private function deleteHighloadBlock(): void
    {
        if (!Bitrix\Main\Loader::includeModule('highloadblock')) {
            return;
        }
        $hl = HighloadBlockTable::getList(['filter' => ['=NAME' => 'GeoIpHistory']])->fetch();
        if ($hl) {
            HighloadBlockTable::delete($hl['ID']);
        }
    }
}
