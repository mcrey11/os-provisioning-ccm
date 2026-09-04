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

use Nwidart\Modules\Facades\Module;

if (! function_exists('Modules\ProvBase\Entities\convertThresholdStrings')) {
    function convertThresholdStrings($valueIn)
    {
        $valueTmp = preg_replace('/[^0-9,-\.]/', '', $valueIn);
        $valueTmp = explode(',', $valueTmp);
        $valueOut = [];
        if (! in_array(count($valueTmp), [2, 4])) {
            throw new \Exception('Expecting 2 or 4 values for thresholds. String "'.$valueIn.'" is not valid.');
        }
        foreach ($valueTmp as $key => $value) {
            $valueOut[$key] = (float) $value;
        }

        return $valueOut;
    }
}

if (! function_exists('Modules\ProvBase\Entities\getAvailableModemClasses')) {
    function getAvailableModemClasses()
    {
        $ret = [
            'cm' => 'Modules\ProvBase\Entities\Modem',
            'ont' => 'Modules\ProvBase\Entities\Modem',
            'tr069' => 'Modules\ProvBase\Entities\Modem',
        ];
        if (Module::collections()->has('Calix')) {
            if (config('app.nmsprimeCustomerId') == 1001) {
                $ret['calixont'] = 'Modules\Calix\Entities\Customer1001CalixOnt';
            } else {
                $ret['calixont'] = 'Modules\Calix\Entities\CalixOnt';
            }
        }
        if (Module::collections()->has('Zyxel')) {
            $ret['zyxelont'] = 'Modules\Zyxel\Entities\ZyxelOnt';
        }

        return $ret;
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
        'RADIUS' => [
            'link' => 'Radius.index',
            'icon' => 'fa-shield',
            'class' => \Modules\ProvBase\Entities\RadAcct::class,
            'submenu' => [
                'Overview'    => ['link' => 'Radius.index',     'icon' => 'fa-dashboard',  'class' => \Modules\ProvBase\Entities\RadAcct::class],
                'Sessions'    => ['link' => 'RadAcct.index',    'icon' => 'fa-exchange',    'class' => \Modules\ProvBase\Entities\RadAcct::class],
                'Auth Log'    => ['link' => 'RadPostAuth.index', 'icon' => 'fa-key',        'class' => \Modules\ProvBase\Entities\RadPostAuth::class],
                'Check'       => ['link' => 'RadCheck.index',   'icon' => 'fa-check-circle','class' => \Modules\ProvBase\Entities\RadCheck::class],
                'Reply'       => ['link' => 'RadReply.index',   'icon' => 'fa-reply',       'class' => \Modules\ProvBase\Entities\RadReply::class],
                'User Groups' => ['link' => 'RadUserGroup.index', 'icon' => 'fa-users',     'class' => \Modules\ProvBase\Entities\RadUserGroup::class],
                'Groups'      => ['link' => 'RadGroupReply.index', 'icon' => 'fa-list',     'class' => \Modules\ProvBase\Entities\RadGroupReply::class],
                'IP Pool'     => ['link' => 'RadIpPool.index',  'icon' => 'fa-map-marker',  'class' => \Modules\ProvBase\Entities\RadIpPool::class],
                'NAS'         => ['link' => 'Nas.index',        'icon' => 'fa-server',      'class' => \Modules\ProvBase\Entities\Nas::class],
            ],
        ],
        'Subscriber' => [
            'link' => 'Subscriber.index',
            'icon' => 'fa-users',
            'class' => \Modules\ProvBase\Entities\Contract::class,
            'submenu' => [
                'Overview'  => ['link' => 'Subscriber.index',          'icon' => 'fa-dashboard', 'class' => \Modules\ProvBase\Entities\Contract::class],
                'Sessions'  => ['link' => 'Subscriber.Session.index',  'icon' => 'fa-exchange',   'class' => \Modules\ProvBase\Entities\Contract::class],
                'Usage'     => ['link' => 'Subscriber.Usage.index',    'icon' => 'fa-tachometer', 'class' => \Modules\ProvBase\Entities\Contract::class],
            ],
        ],
        'Billing' => [
            'link' => 'Product.index',
            'icon' => 'fa-money',
            'class' => \Modules\BillingBase\Entities\Product::class,
            'submenu' => [
                'Products'      => ['link' => 'Product.index',        'icon' => 'fa-cube',            'class' => \Modules\BillingBase\Entities\Product::class],
                'Invoices'      => ['link' => 'Invoice.index',        'icon' => 'fa-file-text',       'class' => \Modules\BillingBase\Entities\Invoice::class],
                'Accounting'    => ['link' => 'AccountingRecord.index', 'icon' => 'fa-calculator',     'class' => \Modules\BillingBase\Entities\AccountingRecord::class],
                'Settlements'   => ['link' => 'SettlementRun.index',  'icon' => 'fa-clipboard',       'class' => \Modules\BillingBase\Entities\SettlementRun::class],
                'Debts'         => ['link' => 'Debt.index',           'icon' => 'fa-exclamation-triangle', 'class' => \Modules\BillingBase\Entities\Debt::class],
                'SEPA Accounts' => ['link' => 'SepaAccount.index',    'icon' => 'fa-university',      'class' => \Modules\BillingBase\Entities\SepaAccount::class],
                'SEPA Mandates' => ['link' => 'SepaMandate.index',    'icon' => 'fa-file-signature',  'class' => \Modules\BillingBase\Entities\SepaMandate::class],
                'Cost Centers'  => ['link' => 'CostCenter.index',     'icon' => 'fa-briefcase',       'class' => \Modules\BillingBase\Entities\CostCenter::class],
                'Salesmen'      => ['link' => 'Salesman.index',       'icon' => 'fa-user',            'class' => \Modules\BillingBase\Entities\Salesman::class],
                'Billing Config'=> ['link' => 'BillingBase.index',    'icon' => 'fa-cogs',            'class' => \Modules\BillingBase\Entities\BillingBase::class],
            ],
        ],
        'Monitoring' => [
            'link' => 'Monitoring.index',
            'icon' => 'fa-heartbeat',
            'class' => \Modules\HfcReq\Entities\NetElement::class,
            'submenu' => [
                'Overview'      => ['link' => 'Monitoring.index',        'icon' => 'fa-dashboard',  'class' => \Modules\HfcReq\Entities\NetElement::class],
                'Device Health' => ['link' => 'Monitoring.DeviceHealth', 'icon' => 'fa-stethoscope', 'class' => \Modules\HfcReq\Entities\NetElement::class],
                'Bandwidth'     => ['link' => 'Monitoring.Bandwidth',    'icon' => 'fa-tachometer',  'class' => \Modules\HfcReq\Entities\NetElement::class],
                'Topology'      => ['link' => 'Monitoring.Topology',     'icon' => 'fa-sitemap',     'class' => \Modules\HfcReq\Entities\NetElement::class],
                'Alerts'        => ['link' => 'Monitoring.Alerts',       'icon' => 'fa-bell',        'class' => \Modules\HfcReq\Entities\NetElement::class],
            ],
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
    'availableModemClasses' => getAvailableModemClasses(),
];