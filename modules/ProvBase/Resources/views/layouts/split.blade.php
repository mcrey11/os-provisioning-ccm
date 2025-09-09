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
@extends ('Layout.split-nopanel')

@section('content_top')

    @include ('provbase::layouts.top')

@stop


@section ('content_left')

@if(Module::collections()->has('ProvMon'))
<div
    v-pre
    x-init
    x-ignore
    id="EnterPriseModemAnalysis"
    class="row"
    data-realtime-broadcasting="{!! route('ProvMon.realtimeBroadcasting', ['id' => $modem->id]) !!}"
    data-picture="{{ isset($picture) ? url($picture) : '' }}"
    data-cmds='@json($cmds, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)'
    data-modem-analysis='@json(trans('view.modemAnalysis'))'
    data-messages-entries="{{ trans('messages.Entries') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-modem-id="{{ $modem->id }}"
    data-tr069="{{ $modem->configfile->device === 'tr069' }}"
    data-route-modem-refresh-table="{{ route('Modem.refreshAnalysisObject', $modem->id) }}"
    data-route-delete-poller-file="{{ route('ProvMon.deletePollerFile', $modem->id) }}"
    data-route-task="{{ route('Modem.executeTask', $modem->id) }}"
    data-messages-analysis-ping-in-progress="{{ trans('provmon::messages.analysis.pingInProgress') }}"
    data-route-modem-flood-ping="{{ route("Modem.floodPing", ["modem" => $modem->id]) }}"
    data-messages-please-wait="{{ trans('messages.pleaseWait') }}"
    data-route-create-spectrum="{{ route('ProvMon.createSpectrum', [$modem->id]) }}"
    data-messages-spectrum-processing="{{ trans('provmon::messages.spectrum.processing') }}"
    data-messages-no-spectrum="{{ trans('messages.noSpectrum') }}"
    data-i18ndt='{@include('datatables.lang', ['withoutTrailingComma' => true])}'
    data-channel="{{ \Modules\ProvMon\Events\NewRealtimeValues::getChannelName($modem->id) }}"
    data-realtime-update-interval="{{ \Modules\ProvMon\Entities\ProvMon::first()->realtime_update_interval }}"
    data-view-header="{!! isset($view_header) ? $view_header : 'undefined'!!}"
>

@else
<div
    v-pre
    x-init
    x-ignore
    id="OpenSourceModemAnalysis"
    class="row"
    data-modem-analysis-floodping-lowLoad="{{ trans('view.modemAnalysis.floodping.lowLoad') }}"
    data-modem-analysis-floodping-averageLoad="{{ trans('view.modemAnalysis.floodping.averageLoad') }}"
    data-modem-analysis-floodping-bigLoad="{{ trans('view.modemAnalysis.floodping.bigLoad') }}"
    data-modem-analysis-floodping-hugeLoad="{{ trans('view.modemAnalysis.floodping.hugeLoad') }}"
    data-messages-analysis-ping-in-progress="{{ trans('provmon::messages.analysis.pingInProgress') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-route-modem-flood-ping="{{ route("Modem.floodPing", ["modem" => $modem->id]) }}"
>
@endif

@if(in_array('dashboard', $viewPanels))
    <div class="col-md-7 ui-sortable">
        @include ('bootstrap.panel', ['content' => 'content_dash', 'view_header' => 'Dashboard', 'i' => 1, 'overflow' => 'visible'])
        @if (isset($realtime))
            @include ('bootstrap.panel', ['content' => 'content_realtime', 'view_header' => \App\Http\Controllers\BaseViewController::translate_label('Real Time Values'), 'i' => 2])
        @endif
        @if (isset($hostId))
            @include ('bootstrap.panel', ['content' => 'content_cacti', 'view_header' => 'Monitoring', 'i' => 3])
        @endif
    </div>
@endif

    <div class="col-md-5 ui-sortable">
    @if(in_array('ping', $viewPanels))
        @php
        $panelHeaderPing = '<ul class="nav nav-pills" id="ping-tab">
                <li role="presentation"><a href="#ping-test" data-toggle="pill">Default Ping</a></li>
                <li role="presentation"><a href="#flood-ping" data-toggle="pill">Flood-Ping</a></li>';
        if (Module::collections()->has('ProvMon') && config('provmon.arrisIperf')['enable'] && \Str::contains($modem->model, 'TG3442S')) {
            $panelHeaderPing .= '<li role="presentation"><a href="#arris-iperf" data-toggle="pill">Speedtest</a></li>';
        }
        $panelHeaderPing .= '</ul>';
        @endphp

        @include ('bootstrap.panel', ['content' => 'content_ping', 'view_header' => $panelHeaderPing, 'i' => 4])
    @endif

    @if(in_array('log', $viewPanels))
        @php
            $panelHeader = '<ul class="nav nav-pills" id="loglease">';
            foreach ($pills as $pill) {
                $panelHeader .= '<li role="presentation"><a href="#'.str_replace(' ', '_', $pill).'" data-toggle="pill">'.ucfirst($pill).'</a></li>';
            }
            $panelHeader .= '</ul>';
        @endphp

        @include ('bootstrap.panel', ['content' => 'content_log', 'view_header' => $panelHeader, 'i' => 5])
    @endif

        @if (\Module::collections()->has('HfcCustomer'))
            @include ('bootstrap.panel', ['content' => 'content_proximity_search', 'view_header' => trans('messages.proximity'), 'i' => 6])
        @endif
    </div>

@if(in_array('spectrum', $viewPanels))
    @if (Module::collections()->has('ProvMon') && ! $modem->isPPP())
        <div class="col-md-12 ui-sortable">
            @include ('bootstrap.panel', ['content' => 'spectrum-analysis', 'view_header' => trans('messages.spectrum'),  'i' => 7])
        </div>
    @endif
@endif

</div>
@stop
