@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <div class="bg-white dark:bg-slate-800 rounded shadow p-6 mb-6">
        <h3 class="font-semibold text-lg mb-3 dark:text-slate-100">NAS Info</h3>
        <table class="table table-sm text-sm">
            <tr><th class="w-40">ID</th><td>{{ $nas->id }}</td></tr>
            <tr><th>NAS Name (IP)</th><td>{{ $nas->nasname }}</td></tr>
            <tr><th>Short Name</th><td>{{ $nas->shortname ?: '-' }}</td></tr>
            <tr><th>Type</th><td>{{ $nas->type ?: '-' }}</td></tr>
            <tr><th>Ports</th><td>{{ $nas->ports ?: '-' }}</td></tr>
            <tr><th>Secret</th><td>****</td></tr>
            <tr><th>Description</th><td>{{ $nas->description ?: '-' }}</td></tr>
        </table>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded shadow p-6">
        <h3 class="font-semibold text-lg mb-3 dark:text-slate-100">Recent Sessions</h3>
        <div class="overflow-x-auto">
            <table class="table table-sm text-sm w-full">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>IP</th>
                        <th>Start</th>
                        <th>Duration</th>
                        <th>Input</th>
                        <th>Output</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSessions as $s)
                    <tr>
                        <td>{{ $s->username }}</td>
                        <td>{{ $s->framedipaddress }}</td>
                        <td>{{ $s->acctstarttime ? $s->acctstarttime->format('M d H:i') : '-' }}</td>
                        <td>{{ $s->acctsessiontime ? gmdate('H:i:s', $s->acctsessiontime) : '-' }}</td>
                        <td>{{ $s->acctinputoctets ? round($s->acctinputoctets / 1048576, 2) . ' MB' : '-' }}</td>
                        <td>{{ $s->acctoutputoctets ? round($s->acctoutputoctets / 1048576, 2) . ' MB' : '-' }}</td>
                        <td>
                            @if(is_null($s->acctstoptime))
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Ended</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-4">No sessions from this NAS</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('Nas.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to NAS List</a>
    </div>
</div>
@endsection
