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

use Exception;
use Log;
use Session;

class DocsisModem extends Modem implements ModemType
{
    public function __construct(protected $modem)
    {
        $this->modem = $modem;
    }

    public function executeTask()
    {
        $task = request('task');

        if ($task == 'tasks/setWifi') {
            return $this->modem->dataModel()->setWifi();
        }

        if ($task == 'tasks/factoryReset') {
            return $this->modem->dataModel()->factoryReset();
        }
    }

    public function restart($mac_changed = false, $modem_reset = false, $factoryReset = false, $silent = false)
    {
        // Since PHP8.x snmpset doesn't throw an exception anymore - By that we temporarily treat warnings as exceptions and restore the functionality
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, $severity, $severity, $file, $line);
        }, E_WARNING);

        // if hostname cant be resolved we dont want to have an php error
        try {
            $config = ProvBase::first();
            $fqdn = $this->modem->hostname.'.'.$config->domain_name;
            $ip = gethostbyname($fqdn);
            $netgw = self::get_netgw($ip);
            $mac = $mac_changed ? $this->modem->getRawOriginal('mac') : $this->modem->mac;

            if ($modem_reset) {
                throw new Exception('Reset Modem directly');
            }

            if ($fqdn == $ip) {
                Session::push('tmp_warning_above_form', trans('messages.modem_restart_warning_dns'));
                throw new Exception(trans('messages.modem_restart_warning_dns'));
            }

            if (! $netgw) {
                throw new Exception('NetGw could not be determined for modem');
            }

            if (! in_array($netgw->company, ['Casa', 'Cisco', 'Motorola'])) {
                throw new Exception("Modem restart via NetGw vendor $netgw->company not yet implemented");
            }

            if ($netgw->company == 'Cisco') {
                $param = [
                    '1.3.6.1.4.1.9.9.116.1.3.1.1.9.'.implode('.', array_map('hexdec', explode(':', $mac))),
                    'i',
                    '1',
                ];
            }

            if ($netgw->company == 'Casa') {
                $param = [
                    '1.3.6.1.4.1.20858.10.12.1.3.1.7.'.implode('.', array_map('hexdec', explode(':', $mac))),
                    'i',
                    '1',
                ];
            }

            if ($netgw->company == 'Motorola') {
                $param = [
                    '1.3.6.1.4.1.4981.2.2.2.0',
                    'lng',
                    implode(' ', explode(':', $mac)),
                ];
            }

            snmpset($netgw->ip, $netgw->get_rw_community(), $param[0], $param[1], $param[2], 300000, 1);

            // success message
            Session::push('tmp_info_above_form', trans('messages.modem_restart_success_netgw'));
        } catch (Exception $e) {
            Log::warning("Could not delete {$this->modem->hostname} from NETGW ('".$e->getMessage()."'). Let's try to restart it directly.");

            try {
                // restart modem - DOCS-CABLE-DEV-MIB::docsDevResetNow
                snmpset($fqdn, $config->rw_community, '1.3.6.1.2.1.69.1.1.3.0', 'i', '1', 300000, 1);

                // success message - make it a warning as sth is wrong when it's not already restarted by NETGW??
                Session::push('tmp_info_above_form', trans('messages.modem.restartedViaSnmp'));
            } catch (Exception $e) {
                Log::error("Could not restart {$this->modem->hostname} directly ('".$e->getMessage()."')");

                if (((strpos($e->getMessage(), 'php_network_getaddresses: getaddrinfo failed: Name or service not known') !== false) ||
                    (strpos($e->getMessage(), 'snmpset(): No response from') !== false)) ||
                    // this is not necessarily an error, e.g. the modem was deleted (i.e. Cisco) and user clicked on restart again
                    (strpos($e->getMessage(), 'noSuchName') !== false)) {
                    Session::push('tmp_error_above_form', trans('messages.modem_restart_error'));
                } else {
                    // Inform and log for all other exceptions
                    Session::push('tmp_error_above_form', \App\Http\Controllers\BaseViewController::translate_label('Unexpected exception').': '.$e->getMessage());
                }
            }
        }

        restore_error_handler();
    }

    /**
     * Generic method to refresh a SNMP Object.
     *
     * @author Roy Schneider
     */
    public function refreshObject(): void
    {
        // for now do nothing
        // when websockets are used, we could simply perform a snmp2_get() here
    }
}
