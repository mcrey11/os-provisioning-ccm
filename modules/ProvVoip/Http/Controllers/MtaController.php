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

namespace Modules\ProvVoip\Http\Controllers;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\ProvBase;
use Modules\ProvVoip\Entities\Mta;
use Request;
use View;

class MtaController extends \BaseController
{
    protected $index_create_allowed = false;
    protected $save_button_name = 'Save / Restart';

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Mta;
        }

        $mac = Request::get('mac', '');
        if ($mac === '') {
            $modem_id = Request::get('modem_id', 0);
            if (boolval($modem_id)) {
                $modem = Modem::find($modem_id);
                if ($modem) {
                    $mac = $modem->mac;
//                    Uncomment this block if you want to suggest mac address of next mta (if modem has already a mta)
//                    if($last_mta = $modem->mtas()->orderBy('updated_at', 'desc')->first()){
//                        $mac = $last_mta->mac;
//                    }
                    if ($mac) {
                        $dec_mac = hexdec(preg_replace('/[^[:xdigit:]]/', '', $mac));
                        $dec_mac++;
                        $mac = rtrim(strtoupper(chunk_split(str_pad(dechex($dec_mac), 12, '0', STR_PAD_LEFT), 2, ':')), ':');
                    }
                }
            }
        }

        // label has to be the same like column in sql table
        // TODO: Type is without functionality -> hidden
        return [
            ['form_type' => 'text', 'name' => 'mac', 'description' => 'MAC Address', 'init_value' => $mac, 'options' => ['placeholder' => 'AA:BB:CC:DD:EE:FF'], 'help' => trans('helper.mac_formats')],
            ['form_type' => 'text', 'name' => 'hostname', 'description' => 'Hostname', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'modem_id', 'description' => 'Modem', 'hidden' => 1],
            ['form_type' => 'select', 'name' => 'configfile_id', 'description' => 'Configfile', 'value' => $this->setupSelect2Field($model, 'Configfile'), 'help' => trans('helper.configfile_count'), 'options' => ['class' => 'select2-ajax', 'ajax-route' => route('Mta.select2', ['relation' => 'configfiles'])]],

            // ATM there is only SIP
            /* ['form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => Mta::getPossibleEnumValues('type', false)], */
            ['form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => 'sip', 'hidden' => 1],
        ];
    }

    protected function prepare_input($data)
    {
        $data = parent::prepare_input($data);
        $data['mac'] = unifyMac($data['mac'] ?? null);

        return $data;
    }

    /**
     * Create tabs for Mta page.
     * See: BaseController native function for more information
     *
     * @param Modules\ProvVoip\Entities\Mta
     * @return array
     *
     * @author Roy Schneider
     */
    protected function editTabs($model)
    {
        \Session::put('Edit', 'MTA');

        $tabs = parent::editTabs($model);
        $analysisTabs = $model->modem->analysisTabs();
        unset($analysisTabs[0]);

        return array_merge($tabs, $analysisTabs);
    }

    /**
     * Restart MTA via API
     *
     * @return JsonResponse
     *
     * @author Ole Ernst
     */
    public function api_restart($ver, $id)
    {
        if ($ver !== '0') {
            return response()->v0ApiReply(['messages' => ['errors' => ["Version $ver not supported"]]]);
        }

        $mta = static::get_model_obj()->findOrFail($id);
        $mta->restart();

        return response()->v0ApiReply([], true, $id);
    }

    public function prepare_rules($rules, $data)
    {
        $modem = Modem::where('id', $data['modem_id'])->with('configfile')->first();

        if ($modem->configfile->device == 'cm') {
            $id = $data['id'] ?? 0;
            $rules['mac'][] = 'required';
            $rules['mac'][] = 'unique:mta,mac,'.$id.',id,deleted_at,NULL'; //|unique:mta,mac',
        }

        return parent::prepare_rules($rules, $data);
    }

    /**
     * Returns view of mta analysis page
     *
     * Note: This is never called if ProvVoip Module is not active
     */
    public function analysis($id)
    {
        $ping = $lease = $log = $realtime = $configfile = null;
        $dash = [];
        $modem = Modem::with('mtas')->find($id);
        $type = 'MTA';
        $modem->help = 'mta_analysis';

        $mtas = $modem->mtas;       // Note: we should use one-to-one relationship here
        if (isset($mtas[0])) {
            $mta = $mtas[0];
        } else {
            goto end;
        }

        // Ping
        $hostname = $mta->hostname.'.'.ProvBase::first()->domain_name;

        exec('sudo ping -c3 -i0 -w1 '.$hostname, $ping);
        if (count(array_keys($ping)) <= 7) {
            $ping = null;
        }

        $lease['text'] = Modem::searchLease("mta-$mta->id\.");
        $lease = Modem::validateLease($lease, $type);

        $configfile = Modem::getConfigfileText("/tftpboot/mta/$mta->hostname");
        $log = $mta->getDhcpLogEntries();

        end:

        $tabs = $modem->analysisTabs();
        $view_header = 'Provmon-MTA';

        return View::make('provbase::Modem.cpeAnalysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'tabs', 'lease', 'log', 'dash', 'realtime', 'configfile', 'view_header')));
    }
}
