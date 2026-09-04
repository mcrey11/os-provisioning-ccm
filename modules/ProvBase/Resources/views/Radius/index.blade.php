@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-exchange',
            'title' => 'Active Sessions',
            'value' => number_format($stats['active_sessions']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('RadAcct.active'),
        ])
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-database',
            'title' => 'Total Sessions',
            'value' => number_format($stats['total_sessions']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('RadAcct.index'),
        ])
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-key',
            'title' => 'Auth (24h)',
            'value' => number_format($stats['auth_success']) . ' / ' . number_format($stats['auth_attempts']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('RadPostAuth.index'),
        ])
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-server',
            'title' => 'NAS Devices',
            'value' => number_format($stats['nas_count']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('Nas.index'),
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded shadow p-4">
            <h3 class="font-semibold text-lg mb-3 dark:text-slate-100">Recent Sessions</h3>
            <div class="overflow-x-auto">
                <table class="table table-sm text-sm w-full">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>NAS</th>
                            <th>IP</th>
                            <th>Start</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSessions as $s)
                        <tr>
                            <td>{{ $s->username }}</td>
                            <td>{{ $s->nasipaddress }}</td>
                            <td>{{ $s->framedipaddress }}</td>
                            <td>{{ $s->acctstarttime ? $s->acctstarttime->format('M d H:i') : '-' }}</td>
                            <td>
                                @if(is_null($s->acctstoptime))
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Ended</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-400">No sessions yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded shadow p-4">
            <h3 class="font-semibold text-lg mb-3 dark:text-slate-100">Recent Auth Attempts</h3>
            <div class="overflow-x-auto">
                <table class="table table-sm text-sm w-full">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Reply</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAuth as $a)
                        <tr>
                            <td>{{ $a->username }}</td>
                            <td>
                                @if($a->reply === 'Access-Accept')
                                    <span class="badge badge-success">{{ $a->reply }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $a->reply }}</span>
                                @endif
                            </td>
                            <td>{{ $a->authdate->format('M d H:i:s') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-gray-400">No auth attempts yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
