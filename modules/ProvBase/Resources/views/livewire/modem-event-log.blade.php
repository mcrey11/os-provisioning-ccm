@script
    <script>
        window.onerror = function (message, source, lineno, colno, error) {
            console.warn('Caught by window.onerror:', message, error, source);
            return message.includes('Could not find Livewire component in DOM tree') ? true : false;
        };

    </script>
@endscript
<div>
    @teleport('#workaround-eventlog')
        <div data-event-log>
        @if ($this->eventlog)
            @php $eventlog = $this->eventlog; $theads = array_shift($eventlog);
            @endphp

            <div class="table-responsive">
                <table class="table streamtable table-bordered" width="100%">
                    <thead wire:click='$refresh' wire:confirm="If this works on clicking table header. It means livewire is working">
                        <tr class='active'>
                            @foreach ($theads as $col_name)
                                <th class='text-center' wire:key="eventlog-col-{{ $col_name }}">{{$col_name}}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($eventlog as $index => $row)
                        <tr class="{{$row[2] ?? ''}}" wire:key="{{$index}}">
                            @foreach ($row as $idx => $data)
                                @if($idx != 2)
                                    <td wire:key="eventlog-td-{{$index}}-{{$idx}}"><span>{{$data}}</span></td>
                                @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-red-600">{{ trans('messages.modem_eventlog_error')}}</div>
        @endif
        </div>
    @endteleport
</div>
