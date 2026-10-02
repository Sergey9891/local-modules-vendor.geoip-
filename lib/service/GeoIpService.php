<?php
declare(strict_types=1);

namespace Vendor\Geoip\Service;

use Bitrix\Main\ArgumentException;
use Vendor\Geoip\Entity\GeoIpHistoryTable;
use Vendor\Geoip\Provider\GeoIpProviderInterface;

class GeoIpService
{
    private array $providers = [];

    public function __construct(array $providers)
    {
        foreach ($providers as $provider) {
            if ($provider instanceof GeoIpProviderInterface) {
                $this->providers[] = $provider;
            }
        }
    }

    /**
     * Основной сценарий выполнения поиска
     */
    public function handle(string $ip): array
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new ArgumentException('Передан некорректный формат IP-адреса.');
        }

        // Пытаемся получить данные из базы данных (HL-блок)
        $localData = $this->getFromHistory($ip);
        if ($localData) {
            $localData['SOURCE'] = 'Database (HL-Block)';
            return $localData;
        }

        // 2. Горячее переключение внешних API
        foreach ($this->providers as $provider) {
            try {
                $geoData = $provider->lookup($ip);
                if ($geoData) {
                    // 3. Запись в БД при успешном ответе внешнего сервиса
                    $this->saveToHistory($ip, $geoData, $provider->getName());
                    $geoData['SOURCE'] = 'API (' . $provider->getName() . ')';
                    return $geoData;
                }
            } catch (\Throwable $e) {
                // Обрабатываем ошибку конкретного API и переходим к следующему в цепочке
                continue;
            }
        }

        throw new \RuntimeException('Все доступные GeoIP сервис-провайдеры вернули ошибку или недоступны.');
    }

    private function getFromHistory(string $ip): ?array
    {
        $entity = GeoIpHistoryTable::getEntity();
        if (!$entity) {
            return null;
        }

        $row = $entity::getList([
            'filter' => ['=UF_IP' => $ip],
            'select' => ['UF_COUNTRY', 'UF_CITY', 'UF_PROVIDER']
        ])->fetch();

        if ($row) {
            return [
                'country' => (string)$row['UF_COUNTRY'],
                'city'    => (string)$row['UF_CITY'],
                'provider'=> (string)$row['UF_PROVIDER']
            ];
        }

        return null;
    }

    private function saveToHistory(string $ip, array $data, string $providerName): void
    {
        $entity = GeoIpHistoryTable::getEntity();
        if (!$entity) {
            return;
        }

        $entity::add([
            'UF_IP'       => $ip,
            'UF_COUNTRY'  => $data['country'],
            'UF_CITY'     => $data['city'],
            'UF_PROVIDER' => $providerName
        ]);
    }
}
