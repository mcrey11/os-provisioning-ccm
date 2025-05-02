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
?>

@include('provbase::Modem.log', ['log' => $dhcpLog, 'id' => 'dhcpLog'])
@include('provbase::Modem.log', ['log' => $tr069Log, 'id' => 'tr069Log'])

<div class="tab-pane fade in" id="lease">
    @if ($lease)
        <div class="{{ $lease['state'] }} pb-2"><b>{{ $lease['forecast'] }}</b></div>
        <div v-pre class="space-y-3">
            @foreach ($lease['text'] as $line)
                <pre class="text-gray-500 whitespace-pre-wrap">{{ $line }}</pre>
            @endforeach
        </div>
    @else
        <div class="{{ $lease['state'] }}"><b>{{ $lease['forecast'] }}</b></div>
    @endif
</div>

@include('provbase::Modem.configfileTab')

<div class="tab-pane fade in" id="eventlog">
    @if ($eventlog)
        <div class="table-responsive">
            <table class="table streamtable table-bordered" width="100%">
                <thead>
                    <tr class='active'>
                        @foreach (array_shift($eventlog) as $col_name)
                            <th class='text-center'>{{$col_name}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach ($eventlog as $row)
                    <tr class = "{{$row[2] ?? ''}}">
                        @foreach ($row as $idx => $data)
                            @if($idx != 2)
                                <td><span>{{$data}}</span></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach (['wifi' => $wifi, 'lan' => $lan] as $tab => $configInterface)
<div class="tab-pane fade in" id="{{ $tab }}">
    @if ($configInterface)
        <div class="flex w-full justify-end mb-3">
            <button
                id="{{ 'refresh'.ucfirst($tab) }}"
                v-on:click="refreshTable('{{ $tab }}')"
                type="button"
                class="btn mr-4 border border-gray-800 btn-dark">
                <i class="fa fa-refresh" aria-hidden="true"></i>
                {{ trans('view.modemAnalysis.refreshTab', ['tab' => ucfirst($tab)]) }}
            </button>
        </div>
        @if (is_array($configInterface))
            <div class="overflow-x-scroll">
                <table class="table streamtable table-bordered">
                    <thead>
                        <tr class="active">
                            <th class="text-center" style="min-width: 20px;">{{ trans('view.modemAnalysis.index') }}</th>
                            @foreach ($configInterface[array_key_first($configInterface)] as $name => $value)
                            <th class="text-center">{{ $name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($configInterface as $entry => $config)
                        <tr>
                            <td class="text-center">{{ $entry }}</td>
                            @foreach ($config as $name => $value)
                            <td class="text-center">
                                <p style="color: grey; margin-bottom: 0px;">{{ $value }}</p>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
@endforeach
