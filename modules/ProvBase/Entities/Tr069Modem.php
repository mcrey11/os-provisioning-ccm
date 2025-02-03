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

use Session;

class Tr069Modem extends Modem implements ModemType
{
    public function __construct(protected $modem)
    {
        $this->modem = $modem;
    }

    public function executeTask()
    {
        // setWlan and setDns
        $formInput = request('taskName');
        $task = request('task');

        if (! $genieId = $this->modem->getGenieId()) {
            return;
        }

        // used for commands like: "cmd;Fernzugang aktivieren;set;InternetGatewayDevice.User.1.Enable;1"
        if (is_array($task) && ! $formInput) {
            foreach ($task as $data) {
                $this->modem->callGenieAcsApi("devices/$genieId/tasks?connection_request", 'POST', json_encode($data));
            }

            return trans('messages.modemAnalysis.actionExecuted');
        }

        // setWlan, setDns, blockDhcp, unblockDhcp
        if ($formInput || \Str::startsWith($task, 'custom/')) {
            $cwmpModel = (new DataModel($this->modem))->getDataModel();
            $task = request('taskName') ?? $task;
            $taskName = \Str::after($task, 'custom/');

            return $cwmpModel->$taskName();
        }

        // manually delete tasks
        if (\Str::startsWith($task, 'tasks/')) {
            Modem::callGenieAcsApi($task, 'DELETE');

            return trans('messages.modemAnalysis.actionExecuted');
        }

        $taskDecode = json_decode($task, true);
        if ($taskDecode === null) {
            return trans('messages.JsonDecodeFailed');
        }

        if ($taskDecode == ['name' => 'connection_request']) {
            $this->modem->callGenieAcsApi("devices/$genieId/tasks?timeout=3000&connection_request", 'POST', '');

            Session::push('tmp_info_above_form', trans('messages.modemAnalysis.actionExecuted'));

            return trans('messages.modemAnalysis.actionExecuted');
        }

        foreach (['factoryReset', 'reboot'] as $action) {
            if (
                $taskDecode === ['name' => $action] &&
                json_decode(Modem::callGenieAcsApi("tasks?query={\"device\":\"$genieId\",\"name\":\"$action\"}", 'GET'))
            ) {
                return $action.trans('messages.modemAnalysis.actionAlreadyScheduled');
            }
        }
    }

    public function restart($mac_changed = false, $modem_reset = false, $factoryReset = false, $silent = false)
    {
        $id = $this->modem->getGenieId();
        if (! $id) {
            if (! $silent) {
                Session::push('tmp_error_above_form', trans('messages.modem_restart_error'));
            }

            return;
        }

        $action = $factoryReset ? 'factoryReset' : 'reboot';

        if (json_decode(self::callGenieAcsApi("tasks?query={\"device\":\"$id\",\"name\":\"$action\"}", 'GET'))) {
            // A factoryReset/reboot of device has already been scheduled, no need to spawn another task
            return 'isScheduled';
        }

        $timeout = config('provbase.cwmpConnectionRequestTimeout');
        $conReq = config('provbase.cwmpConnectionRequest') ? '&connection_request' : '';
        $success = self::callGenieAcsApi("devices/$id/tasks?timeout={$timeout}{$conReq}", 'POST', "{ \"name\" : \"$action\" }");

        if (! $success) {
            if (! $silent) {
                Session::push('tmp_error_above_form', trans('messages.modem_restart_error'));
            }

            return;
        }

        if (! $silent) {
            Session::push('tmp_info_above_form', trans('messages.modem.restartedViaTr069'));
        }
    }

    /**
     * Generic method to refresh a CWMP Object.
     *
     * @author Roy Schneider
     */
    public function refreshObject(): void
    {
        $this->modem::callGenieAcsApi(
            'devices/'.$this->modem->getGenieId().'/tasks?timeout=3000&connection_request',
            'POST',
            json_encode(['name' => 'refreshObject', 'objectName' => request('object')])
        );
    }
}
