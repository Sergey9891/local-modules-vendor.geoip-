<?php
namespace GeoIpSearch\lib\providers;

use GeoIpSearch\lib\ProviderInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class SypexGeoProvider implements ProviderInterface
{
    private $client;
    private $logger;

    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function getGeoData(string $ip): ?array
    {
        $url = "https://api.sypexgeo.net/json/{$ip}";
        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                $this->logger->warning("SypexGeo API returned status " . $response->getStatusCode());
                return null;
            }
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            if (!$data) {
                $this->logger->warning("Invalid response from SypexGeo");
                return null;
            }
            return [
                'country' => $data['country']['name_en'] ?? null,
                'city' => $data['city']['name_en'] ?? null,
                'lat' => $data['city']['lat'] ?? null,
                'lon' => $data['city']['lon'] ?? null,
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error fetching SypexGeo data: " . $e->getMessage());
            return null;
        }
    }
}
