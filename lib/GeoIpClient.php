<?php
namespace GeoIpSearch\lib;

use Psr\Http\Client\ClientInterface;

class GeoIpClient
{
    private $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }
}
