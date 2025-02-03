<div class="tab-pane fade in" id="{{ $id }}">
    @if ($log)
        <div class="divide-y">
            @foreach ($log as $line)
                <div class="text-gray-500 whitespace-pre-wrap py-1">{{ $line }}</div>
            @endforeach
        </div>
    @else
        <div class="text-red-600">{{ trans('messages.modem_log_error') }}</div>
    @endif
</div>
