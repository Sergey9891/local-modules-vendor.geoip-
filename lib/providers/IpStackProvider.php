<?php
namespace GeoIpSearch\lib\providers;

use GeoIpSearch\lib\ProviderInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class IpStackProvider implements ProviderInterface
{
    private $client;
    private $accessKey;
    private $logger;

    public function __construct(ClientInterface $client, string $accessKey, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->accessKey = $accessKey;
        $this->logger = $logger;
    }

    public function getGeoData(string $ip): ?array
    {
        $url = "http://api.ipstack.com/{$ip}?access_key={$this->accessKey}";
        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                $this->logger->warning("IpStack API returned status " . $response->getStatusCode());
                return null;
            }
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            if (!$data || isset($data['error'])) {
                $this->logger->warning("Invalid response from IpStack");
                return null;
            }
            return [
                'country' => $data['country_name'] ?? null,
                'city' => $data['city'] ?? null,
                'lat' => $data['latitude'] ?? null,
                'lon' => $data['longitude'] ?? null,
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error fetching IpStack data: " . $e->getMessage());
            return null;
        }
    }
}
