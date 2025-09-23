<?php
namespace GeoIpSearch\lib;

use Psr\Log\LoggerInterface;
use Psr\Http\Client\ClientInterface;
use GeoIpSearch\lib\providers\ProviderFactory;

class GeoIpService
{
    private $client;
    private $logger;
    private $provider;
    private $providerName;
    private $providerConfig;
    private $availableProviders = ['sypexgeo', 'ipstack'];
    private $currentProviderIndex = 0;

    public function __construct(ClientInterface $client, LoggerInterface $logger, array $providerConfig)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->providerConfig = $providerConfig;
        $this->initializeProvider();
    }

    private function initializeProvider()
    {
        $providerName = $this->availableProviders[$this->currentProviderIndex];
        try {
            $this->provider = ProviderFactory::create($providerName, $this->client, $this->providerConfig, $this->logger);
            $this->providerName = $providerName;
        } catch (\Exception $e) {
            $this->logger->error("Failed to initialize provider {$providerName}: " . $e->getMessage());
            $this->switchProvider();
        }
    }

    public function switchProvider()
    {
        $this->currentProviderIndex = ($this->currentProviderIndex + 1) % count($this->availableProviders);
        $this->initializeProvider();
    }

    public function getGeoData(string $ip): ?array
    {
        // Попытка получить из базы
        $data = $this->getFromDatabase($ip);
        if ($data) {
            return $data;
        }

        // Запрос к провайдеру
        $geoData = $this->provider->getGeoData($ip);
        if ($geoData) {
            $this->saveToDatabase($ip, $geoData);
            return $geoData;
        } else {
            // Переключение провайдера при ошибке
            $this->logger->warning("Switching provider due to failure");
            $this->switchProvider();
            return null;
        }
    }

    private function getFromDatabase(string $ip): ?array
    {
        $res = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => ['TABLE_NAME' => 'GeoIp'],
        ])->fetch();

        if (!$res) {
            return null;
        }

        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($res);
        $dataClass = $entity->getDataClass();

        $result = $dataClass::getList([
            'filter' => ['UF_IP' => $ip],
            'limit' => 1,
        ])->fetch();

        if ($result) {
            return [
                'country' => $result['UF_COUNTRY'],
                'city' => $result['UF_CITY'],
                'lat' => $result['UF_LAT'],
                'lon' => $result['UF_LON'],
            ];
        }
        return null;
    }

    private function saveToDatabase(string $ip, array $data)
    {
        $res = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => ['TABLE_NAME' => 'GeoIp'],
        ])->fetch();

        if (!$res) {
            // Создать HL блок, если не существует
            // Для краткости пропущено
            return;
        }

        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($res);
        $dataClass = $entity->getDataClass();

        $dataClass::add([
            'UF_IP' => $ip,
            'UF_COUNTRY' => $data['country'],
            'UF_CITY' => $data['city'],
            'UF_LAT' => $data['lat'],
            'UF_LON' => $data['lon'],
        ]);
    }
}
