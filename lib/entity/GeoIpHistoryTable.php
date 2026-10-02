<?php
declare(strict_types=1);

namespace Vendor\Geoip\Entity;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ORM\Data\DataManager;

class GeoIpHistoryTable
{
    private static ?string $className = null;

    /**
     * Динамически возвращает скомпилированный класс ORM для работы с HL-блоком истории
     */
    public static function getEntity(): ?DataManager
    {
        if (self::$className !== null) {
            $class = self::$className;
            return new $class();
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

        $class = self::$className;
        return new $class();
    }
}
