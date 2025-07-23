<?php

namespace App\Services\ApiServices;

class IpAddressService
{
    public function getIpAddress(string $realIp): ?string
    {
        if (app()->environment('local')) {
            $fakeIps = [
                '193.238.153.17',
                '102.22.45.67',
                '172.16.5.89',
                '203.0.113.10',
                '198.51.100.5',
                '145.22.33.144',
                '10.0.0.2',
                '192.0.2.55',
                '8.8.8.8',
                '66.249.66.1',
            ];
            return $fakeIps[array_rand($fakeIps)];
        }
        return $realIp;
    }
}
