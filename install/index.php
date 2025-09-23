<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

function RegisterModule()
{
    $arModuleVersion = [];
    include(__DIR__ . "/version.php");
    \Bitrix\Main\ModuleManager::registerModule("geoipsearch");
}

function UnRegisterModule()
{
    \Bitrix\Main\ModuleManager::unRegisterModule("geoipsearch");
}

if (!\Bitrix\Main\Loader::includeModule('main')) {
    die('Main module not loaded');
}

if (!\Bitrix\Main\ModuleManager::isModuleInstalled('geoipsearch')) {
    RegisterModule();
    echo "Модуль установлен.";
} else {
    echo "Модуль уже установлен.";
}
?>
