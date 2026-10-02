<?php
namespace Vendor\Geoip\Entity;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * Класс-прослойка для динамического получения ORM Highload-блока
 */
class GeoIpHistoryTable
{
    private static ?string $className = null;

    public static function getEntity(): ?DataManager
    {
        if (self::$className !== null) {
            return new self::$className();
        }

        if (!Loader::includeModule('highloadblock')) {
            return null;
        }

        $hlblock = HighloadBlockTable::getList([
            'filter' => ['=NAME' => 'GeoIpHistory']
        ])->fetch();

        if (!$hlblock) {
            return null;
        }

        $entity = HighloadBlockTable::compileEntity($hlblock);
        self::$className = $entity->getDataClass();

        return new self::$className();
    }
}
