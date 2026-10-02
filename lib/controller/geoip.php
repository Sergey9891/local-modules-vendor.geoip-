<?php
declare(strict_types=1);

namespace Vendor\Geoip\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Vendor\Geoip\Service\GeoIpService;

class GeoIp extends Controller
{
    public function configureActions(): array
    {
        return [
            'lookup' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(['POST']), 
                    new ActionFilter\Csrf(),               // Защита от межсайтовой подделки запросов
                ],
            ]
        ];
    }

    /**
     * Метод поиска локации. Внедрение зависимости $geoIpService 
     * происходит автоматически (Auto-wiring) благодаря Service Locator ядра Битрикса.
     */
    public function lookupAction(string $ip, GeoIpService $geoIpService): ?array
    {
        if (empty($ip)) {
            $this->addError(new Error('IP-адрес не может быть пустым', 'empty_ip'));
            return null;
        }

        try {
            $result = $geoIpService->handle($ip);

            return [
                'ip' => $ip,
                'country' => $result['country'],
                'city' => $result['city'],
                'source' => $result['SOURCE']
            ];
        } catch (\ArgumentException $e) {
            $this->addError(new Error($e->getMessage(), 'invalid_ip_format'));
        } catch (\Throwable $e) {
            $this->addError(new Error($e->getMessage(), 'service_error'));
        }

        return null;
    }
}
