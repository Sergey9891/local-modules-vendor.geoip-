<?php
namespace GeoIpSearch\lib;

interface ProviderInterface
{
    public function getGeoData(string $ip): ?array;
}
