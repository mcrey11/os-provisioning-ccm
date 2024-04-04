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
@extends ('provbase::layouts.cpe_mta_split')


@section('content_dash')
    <div class="flex justify-between items-start">
        <div>
            @foreach ($dash as $line)
                @foreach ($line as $description => $content)
                    <div class="flex text-gray-600 space-x-2">
                        <b>{{ $description }}:</b>
                        <div>{{ $content }}</div>
                    </div>
                @endforeach
            @endforeach
        </div>

        @include('Generic.documentation', ['documentation' => $modem->help])
    </div>
@stop

@section('content_lease')
    @if ($lease)
        @if (isset($lease['ipv6']))
            <h4 class="h4 mt-0"> IPv4 </h4>
        @endif
        <div class="{{ $lease['state'] }} mb-3"><b>{{ $lease['forecast'] }}</b></div>
        <div class="space-y-3 mb-3">
            @foreach ($lease['text'] as $line)
                <pre class="text-gray-500 whitespace-pre-wrap">{{ $line }}</pre>
            @endforeach
        </div>

        @if (isset($lease['ipv6']))
            <h4 class="h4"> IPv6 </h4>
            <div class="table-responsive">
            <table class="table streamtable table-bordered" width="auto">
                <thead>
                    <tr class="active">
                        <th width="20px"></th>
                        <th class="text-center">#</th>
                        <th class="text-center">MAC</th>
                        <th class="text-center">{{ trans('messages.Address') }}</th>
                        <th class="text-center">{{ trans('messages.prefix') }}</th>
                        <th class="text-center">{{ trans('provbase::view.dhcp.lifetime') }}</th>
                        <th class="text-center">{{ trans('provbase::view.dhcp.expiration') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lease['ipv6'] as $key => $lease6)
                    <tr class="{{ $lease6->bsclass }}">
                        {{-- <td class="text-center"><span color="grey">{{}}</span></td> --}}
                        <td class="text-center"></td>
                        <td class="text-center">{{ $key }}</td>
                        <td class="text-center">{{ $lease6->hwaddr }}</td>
                        <td class="text-center">{{ $lease6->address }}</td>
                        <td class="text-center">{{ $lease6->prefix_len }}</td>
                        <td class="text-center">{{ $lease6->valid_lifetime }}</td>
                        <td class="text-center">{{ $lease6->expire }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    @else
        <span class="text-red-600">{{ trans('messages.modem_lease_error')}}</span>
    @endif

@stop

@section('content_log')
    @if ($log)
        <div class="text-green-600 mb-3"><b>{{$type}} Logs</b></div>
        <table>
            @foreach ($log as $line)
                <tr><td><span color="grey">{{$line}}</span></td></tr>
            @endforeach
        </table>
    @else
        <span class="text-red-600">{{$type.' '.trans('messages.cpe_log_error')}}</span>
    @endif
@stop

@section('content_configfile')
    @if (isset($configfile))
        <div class="text-green-600 mb-3"><b>{{$type}} Configfile ({{$configfile['mtime']}})</b></div>
        @if (isset($configfile['warn']))
            <div class="text-red-600"><b>{{ $configfile['warn'] }}</b></div>
        @endif
        <div class="space-y-1">
            @foreach ($configfile['text'] as $line)
                <pre class="text-gray-500 whitespace-pre-wrap">{{ $line }}</pre>
            @endforeach
        </div>
    @else
        <span class="text-red-600">{{ trans('messages.mta_configfile_error')}}</span>
    @endif
@stop

@section('content_ping')
    @if ($ping)
        <div class="mb-3">
            @isset ($ping[1])
                <div class="text-green-600">
                    <b>{{ trans('messages.deviceOnline', ['Device' => $type]) }}</b>
                </div>
            @else
                <div class="text-orange-600">
                    <b>{{ trans('messages.device_probably_online', ['type' => $type]) }}</b>
                </div>
            @endisset
        </div>
        <table>
            @foreach ($ping as $line)
                <tr><td><span color="grey">{{$line}}</span></td></tr>
            @endforeach
        </table>
    @else
        <div class="text-red-600">{{ trans('messages.deviceOffline', ['Device' => $type]) }}</div>
    @endif

@stop

@section('javascript')

    @include('Generic.handlePanel')

    <script language="javascript">

        $(document).ready(function() {
            $('table.streamtable').DataTable({
                {{-- Translate Datatables Base --}}
                @include('datatables.lang')
                responsive: {
                    details: {
                        type: 'column' {{-- auto resize the Table to fit the viewing device --}}
                    }
                },
                fixedHeader: true,
                autoWidth: true,
                paging: false,
                info: false,
                searching: false,
                aoColumnDefs: [ {
                    className: 'control',
                    orderable: false,
                    searchable: false,
                    targets:   [0]
                } ]
            });
        });

    </script>

@stop
