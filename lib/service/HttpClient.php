<?php
namespace Vendor\Geoip\Service;

use Bitrix\Main\Web\HttpClient as BitrixHttpClient;

class HttpClient
{
    private array $options;

    public function __construct(array $options = ['timeout' => 3, 'socketTimeout' => 3])
    {
        $this->options = $options;
    }

    public function get(string $url): ?string
    {
        $client = new BitrixHttpClient($this->options);
        $result = $client->get($url);
        
        if ($client->getStatus() !== 200) {
            return null;
        }
        
        return $result ?: null;
    }
}
