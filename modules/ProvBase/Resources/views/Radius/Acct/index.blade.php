@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search username, IP, MAC..." class="form-control w-64">
        <select name="status" class="form-control w-40">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
        <a href="{{ route('RadAcct.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded shadow overflow-x-auto">
        <table class="table table-sm text-sm w-full">
            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>Username</th>
                    <th>NAS</th>
                    <th>Framed IP</th>
                    <th>Calling Station</th>
                    <th>Start Time</th>
                    <th>Duration</th>
                    <th>Input</th>
                    <th>Output</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $s)
                <tr>
                    <td><a href="{{ route('RadAcct.show', $s->radacctid) }}">{{ substr($s->acctsessionid, 0, 12) }}...</a></td>
                    <td>{{ $s->username }}</td>
                    <td>{{ $s->nasipaddress }}</td>
                    <td>{{ $s->framedipaddress }}</td>
                    <td>{{ $s->callingstationid }}</td>
                    <td>{{ $s->acctstarttime ? $s->acctstarttime->format('M d H:i') : '-' }}</td>
                    <td>{{ $s->acctsessiontime ? gmdate('H:i:s', $s->acctsessiontime) : '-' }}</td>
                    <td>{{ $s->acctinputoctets ? round($s->acctinputoctets / 1048576, 2) . ' MB' : '-' }}</td>
                    <td>{{ $s->acctoutputoctets ? round($s->acctoutputoctets / 1048576, 2) . ' MB' : '-' }}</td>
                    <td>
                        @if(is_null($s->acctstoptime))
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">{{ $s->acctterminatecause ?: 'Ended' }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-gray-400 py-4">No sessions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sessions->withQueryString()->links() }}
    </div>
</div>
@endsection
