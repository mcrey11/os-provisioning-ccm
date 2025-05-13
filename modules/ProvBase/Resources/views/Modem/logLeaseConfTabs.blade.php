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

<div class="tab-pane fade in" id="dhcpLog">
    <div id='workaround-dhcplog' class="group">
        <div class="group-has-[[data-dhcp-log]]:hidden flex items-center justify-center p-4">
            <svg class="size-14 animate-spin" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.364 5.63604L16.9497 7.05025C15.683 5.7835 13.933 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19C15.866 19 19 15.866
                    19 12H21C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C14.4853 3 16.7353 4.00736 18.364 5.63604Z"></path>
            </svg>
        </div>
    </div>
</div>

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

@if (in_array('eventlog', $pills))
<div class="tab-pane fade in" id="eventlog">
    <div id='workaround-eventlog' class="group">
        <div class="group-has-[[data-event-log]]:hidden flex items-center justify-center p-4">
            <svg class="size-14 animate-spin" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.364 5.63604L16.9497 7.05025C15.683 5.7835 13.933 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19C15.866 19 19 15.866
                    19 12H21C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C14.4853 3 16.7353 4.00736 18.364 5.63604Z"></path>
            </svg>
        </div>
    </div>
</div>
@endif

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
