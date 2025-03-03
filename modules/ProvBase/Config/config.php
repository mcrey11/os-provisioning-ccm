<?php
/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Modules\ProvBase\Entities;

// this file seems to be included multiple times (e.g. in “php artisan route:cache”
// or “php artisan config:cache”)
// to avoid redeclaration of this function check if already defined
if (! function_exists('Modules\ProvBase\Entities\convertThresholdStrings')) {
    function convertThresholdStrings($valueIn)
    {
        $valueTmp = preg_replace('/[^0-9,-\.]/', '', $valueIn);
        $valueTmp = explode(',', $valueTmp);
        $valueOut = [];
        if (! in_array(count($valueTmp), [2, 4])) {
            throw new \Exception('Expecting 2 or 4 values for thresholds. String “'.$valueIn.'” is not valid.');
        }
        foreach ($valueTmp as $key => $value) {
            $valueOut[$key] = (float) $value;
        }

        return $valueOut;
    }
}

return [
    'link' => 'ProvBase.index',
    'MenuItems' => [
        'Contracts' => [
            'link'	=> 'Contract.index',
            'icon'	=> 'fa-address-book-o',
            'class' => Contract::class,
        ],
        // 'Domains' => [
        //     'link' 	=> 'Domain.index',
        //     'icon'	=> 'fa-tag',
        //     'class' => Domain::class,
        // ],
        'Modems' => [
            'link'	=> 'Modem.index',
            'icon'	=> 'fa-hdd-o',
            'class' => Modem::class,
        ],
        'Endpoint' => [
            'link'	=> 'Endpoint.index',
            'icon'	=> 'fa-map-marker',
            'class' => Endpoint::class,
        ],
        'Configfile' => [
            'link'	=> 'Configfile.index',
            'icon'	=> 'fa-file-code-o',
            'class' => Configfile::class,
        ],
        'FirmwareUpgrade' => [
            'link'	=> 'FirmwareUpgrade.index',
            'icon'	=> 'fa-arrow-circle-up',
            'class' => FirmwareUpgrade::class,
        ],
        'Qos' => [
            'link'	=> 'Qos.index',
            'icon'	=> 'fa-ticket',
            'class' => Qos::class,
        ],
        'NetGw' => [
            'link'	=> 'NetGw.index',
            'icon'	=> 'fa-server',
            'class' => NetGw::class,
        ],
        'Ip-Pools' => [
            'link'	=> 'IpPool.index',
            'icon'	=> 'fa-tags',
            'class' => IpPool::class,
        ],
    ],
    'cwmpConnectionRequest' => env('CWMP_CONNECTION_REQUEST', 1),
    'cwmpConnectionRequestTimeout' => env('CWMP_CONNECTION_REQUEST_TIMEOUT', 3000),
    'cwmpMonitoringEvents' => env('CWMP_MONITORING_EVENTS', 2),
    'qualityColorThresholds' => [
        'AvgUtilization' => convertThresholdStrings(env('STATUS_THRESHOLDS_AVG_UTILIZATION', '0, 0, 70, 90')),
        'DocsisDsPwr' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_DS_PWR', '20, -10, 15, 20')),
        'DocsisDsUs' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_DS_US', '-12, -5, 5, 12')),
        'DocsisMicRef' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_MICREF', '20, 30')),
        'DocsisRxPwr' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_RX_PWR', '-3, -1, 15, 20')),
        'DocsisUsPwr' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_US_PWR', '22, 27, 50, 56')),
        'DocsisSnrQpsk' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_SNR_QPSK', '14, 17')),
        'DocsisSnrQam16' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_SNR_QAM16', '20, 23')),
        'DocsisSnrQam32' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_SNR_QAM32', '22, 25')),
        'DocsisSnrQam64' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_SNR_QAM64', '26, 29')),
        'DocsisSnrQam256' => convertThresholdStrings(env('STATUS_THRESHOLDS_DOCSIS_SNR_QAM256', '32, 35')),
    ],
];
