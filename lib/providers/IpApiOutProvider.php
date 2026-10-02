<?php
namespace Vendor\Geoip\Provider;

use Vendor\Geoip\Service\HttpClient;

class IpApiOutProvider implements GeoIpProviderInterface
{
    private const API_URL = 'http://ip-api.com';
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getName(): string
    {
        return 'ip-api';
    }

    public function lookup(string $ip): ?array
    {
        $response = $this->httpClient->get(self::API_URL . $ip . '?lang=ru');
        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);
        if (($data['status'] ?? '') !== 'success') {
            return null;
        }

        return [
            'country' => $data['country'] ?? '',
            'city' => $data['city'] ?? ''
        ];
    }
}
