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

use Illuminate\Support\Str;
use Session;

class Tr069Modem extends Modem implements ModemType
{
    public function __construct(protected $modem)
    {
        $this->modem = $modem;
    }

    public function executeTask()
    {
        if (! $genieId = $this->modem->getGenieId()) {
            // TODO: Fail message
            return;
        }

        $task = request('task');

        // Manually delete tasks
        if (Str::startsWith($task, 'delete/tasks/')) {
            return $this->deleteTask($task);
        }

        // setWifi, setDns, blockDhcp, unblockDhcp
        if (Str::startsWith($task, 'tasks/')) {
            return $this->executePredefinedTask($task);
        }

        // Used for commands like: "cmd;Fernzugang aktivieren;set;InternetGatewayDevice.User.1.Enable;1"
        if (! Str::startsWith($task, '[')) {
            $task = '['.$task.']';
        }

        if (($tasks = json_decode($task)) === null) {
            // TODO: Improve message
            return trans('messages.JsonDecodeFailed');
        }

        foreach ($tasks as $task) {
            Modem::callGenieAcsApi("devices/$genieId/tasks?connection_request", 'POST', json_encode($task));
        }

        return trans('messages.modemAnalysis.actionExecuted');
    }

    private function deleteTask($task)
    {
        $task = str_replace('delete/', '', $task);

        Modem::callGenieAcsApi($task, 'DELETE');

        return trans('messages.modemAnalysis.actionExecuted');
    }

    /**
     * Tasks: Connection Request|Factory Reset|Reboot|setWifi|setDns|blockDhcp|unblockDhcp
     */
    private function executePredefinedTask($task)
    {
        $taskName = Str::after($task, 'tasks/');

        if ($taskName == 'connection_request') {
            $this->connectionRequest();

            return trans('messages.modemAnalysis.actionExecuted');
        }

        if (in_array($taskName, ['factoryReset', 'reboot'])) {
            $ret = $this->restart(false, false, $taskName == 'factoryReset', true);

            if ($ret == 'isScheduled') {
                return trans('messages.modemAnalysis.actionAlreadyScheduled');
            }

            return trans('messages.modemAnalysis.actionExecuted');
        }

        // setWifi, setDns, blockDhcp, unblockDhcp
        $cwmpModel = (new DataModel($this->modem))->getDataModel();

        return $cwmpModel->$taskName();
    }

    public function connectionRequest()
    {
        $genieId = $this->modem->getGenieId();
        $timeout = config('provbase.cwmpConnectionRequestTimeout');

        self::callGenieAcsApi("devices/$genieId/tasks?timeout=$timeout&connection_request", 'POST', '');
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
        self::callGenieAcsApi(
            'devices/'.$this->modem->getGenieId().'/tasks?timeout=3000&connection_request',
            'POST',
            json_encode(['name' => 'refreshObject', 'objectName' => request('object')])
        );
    }
}
