<?php
declare(strict_types=1);

namespace Vendor\Geoip\Provider;

interface GeoIpProviderInterface
{
    public function getName(): string;

    /**
     * Выполняет поиск геоданных по IP
     * @return array{country: string, city: string}|null
     */
    public function lookup(string $ip): ?array;
}
