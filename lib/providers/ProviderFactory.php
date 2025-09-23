<?php
namespace GeoIpSearch\lib\providers;

use Psr\Http\Client\ClientInterface;
use GeoIpSearch\lib\ProviderInterface;
use GeoIpSearch\lib\Logger;

class ProviderFactory
{
    public static function create(string $providerName, ClientInterface $client, array $config, Logger $logger): ProviderInterface
    {
        switch (strtolower($providerName)) {
            case 'sypexgeo':
                return new SypexGeoProvider($client, $logger);
            case 'ipstack':
                return new IpStackProvider($client, $config['access_key'], $logger);
            default:
                throw new \Exception("Unknown provider: {$providerName}");
        }
    }
}
