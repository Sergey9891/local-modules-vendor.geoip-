<?php
namespace Vendor\Geoip\Provider;

interface GeoIpProviderInterface
{
    /**
     * Возвращает уникальное кодовое имя провайдера
     */
    public function getName(): string;

    /**
     * Выполняет запрос к API провайдера.
     * Возвращает массив ['country' => string, 'city' => string] или null при сбое.
     */
    public function lookup(string $ip): ?array;
}
