<div>
    @if (is_array($this->dhcplog))
        <div class="divide-y">
            @foreach ($this->dhcplog as $key => $line)
                <div wire:key="dhcplog-td-{{ $key }}" class="text-gray-500 whitespace-pre-wrap py-1">{{ $line }}</div>
            @endforeach
        </div>
    @else
        <div wire:key="dhcplog-error" class="text-red-600">{{ trans('messages.modem_log_error') }}</div>
    @endif
</div>
