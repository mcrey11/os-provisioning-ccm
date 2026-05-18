<div class="tab-pane fade in" id="configfile">
    @if ($configfile && data_get($configfile, 'text', null))
        @include('Generic.above_infos')

        <form v-if="taskOptions?.length" v-on:submit.prevent="updateTasks" class="mb-3">
            <div class="flex">
                <div style="flex: 1;">
                    <select2 v-model="selectedTask" v-on:input="setTask" :as-array="true">
                        <option v-for="(option, i) in taskOptions" :key="i" v-bind:value="option.task" v-text="option.name"></option>
                    </select2>
                </div>
                <button v-if="! isForm" type="submit" class="btn btn-primary ml-3">{{ trans('view.Button_Submit') }}</button>
            </div>
        </form>
        <div v-cloak v-if="selectedTask == 'tasks/setWifi'" class="mb-3">
            <form v-on:submit.prevent="setWifi" class="space-y-2">
                <div v-if="isTr069">
                    <div class="form-group row">
                        <label for="WLANIndex" class="flex items-center col-sm-2 col-form-label">{{ trans('view.modemAnalysis.index') }}</label>
                        <div class="col-sm-10">
                            <input v-model="wifiSettings['index']" type="number" class="form-control" id="WLANIndex">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Channel" class="flex items-center col-sm-2 col-form-label">{{ trans('view.modemAnalysis.channel') }}</label>
                        <div class="col-sm-10">
                            <input v-model="wifiSettings['channel']" type="number" class="form-control" id="Channel" placeholder="{{ trans('view.modemAnalysis.wlanChannelInfo') }}" title="{{ trans('view.modemAnalysis.wlanChannelInfo') }}">
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div class="flex flex-row">
                        <label for="Channel" class="flex items-center col-sm-2 col-form-label pl-0">{{ trans('view.modemAnalysis.channel') }}</label>
                        <select2 v-model="wifiSettings['channel']" v-on:input="setChannel">
                            <option v-for="channel in channels" v-bind:value="channel" v-text="channel"></option>
                        </select2>
                    </div>
                    <div class="flex flex-row">
                        <label for="Encryption" class="flex items-center col-sm-2 col-form-label pl-0">{{ trans('view.modemAnalysis.encryption') }}</label>
                        <input
                            v-model="wifiSettings['encryption']"
                            class="form-control mt-2"
                            type="checkbox">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="SSID" class="flex items-center col-sm-2 col-form-label">SSID</label>
                    <div class="col-sm-10">
                        <input v-model="wifiSettings['ssid']" type="text" class="form-control" id="SSID" placeholder="SSID">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="Password" class="flex items-center col-sm-2 col-form-label">{{ trans('messages.Password') }}</label>
                    <div class="col-sm-10">
                        <input v-model="wifiSettings['password']" type="password" class="form-control" id="Password" placeholder="{{ trans('messages.Password') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">{{ trans('view.Button_Submit') }}</button>
            </form>
        </div>
        <div v-cloak v-if="selectedTask == 'tasks/setDns'" class="mb-3">
            <form v-on:submit.prevent="setDns" style="margin-top: 10px;">
                <div class="form-group row">
                    <label for="DNS" class="flex items-center col-sm-2 col-form-label">DNS Server</label>
                    <div class="col-sm-10">
                        <input v-model="dnsSettings['servers']" type="text" class="form-control" id="DNS" placeholder="0.0.0.0,0.0.0.0">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">{{ trans('view.Button_Submit') }}</button>
            </form>
        </div>
        @if (isset($configfile['mtime']))
            <div class="text-green-600 pb-2">
                <b>{{ trans('view.modemAnalysis.lastUpdatedOn', ['date' => $configfile['mtime']]) }}</b>
            </div>
        @endif
        @if (isset($configfile['warn']))
            <div class="text-red-600"><b>{{ $configfile['warn'] }}</b></div>
        @endif
        <div v-pre class="space-y-1">
            @foreach ($configfile['text'] as $line)
                <pre class="text-gray-500 whitespace-pre-wrap">{{ $line }}</pre>
            @endforeach
        </div>
    @else
        <div class="text-red-600">{{ trans('messages.modem_configfile_error')}}</div>
    @endif
</div>
