<?php
namespace Vendor\Geoip\Provider;

use Vendor\Geoip\Service\HttpClient;

class SypexGeoProvider implements GeoIpProviderInterface
{
    private const API_URL = 'https://sypexgeo.net';
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getName(): string
    {
        return 'sypexgeo';
    }

    public function lookup(string $ip): ?array
    {
        $response = $this->httpClient->get(self::API_URL . $ip);
        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);
        if (!isset($data['country']['name_ru'])) {
            return null;
        }

        return [
            'country' => $data['country']['name_ru'] ?? '',
            'city' => $data['city']['name_ru'] ?? ''
        ];
    }
}
