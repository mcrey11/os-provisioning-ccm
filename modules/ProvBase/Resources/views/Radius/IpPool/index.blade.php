@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search username, IP, MAC..." class="form-control w-64">
        <input type="text" name="pool" value="{{ request('pool') }}" placeholder="Pool name..." class="form-control w-40">
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
        <a href="{{ route('RadIpPool.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded shadow overflow-x-auto">
        <table class="table table-sm text-sm w-full">
            <thead>
                <tr>
                    <th>Pool Name</th>
                    <th>Framed IP</th>
                    <th>NAS IP</th>
                    <th>Username</th>
                    <th>Calling Station</th>
                    <th>Expiry</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ippools as $ip)
                <tr>
                    <td><span class="badge badge-info">{{ $ip->pool_name }}</span></td>
                    <td>{{ $ip->framedipaddress }}</td>
                    <td>{{ $ip->nasipaddress }}</td>
                    <td>{{ $ip->username ?: '-' }}</td>
                    <td>{{ $ip->callingstationid }}</td>
                    <td>{{ $ip->expiry_time ? $ip->expiry_time->format('Y-m-d H:i') : '-' }}</td>
                    <td>
                        @if($ip->expiry_time && $ip->expiry_time->isPast())
                            <span class="badge badge-secondary">Expired</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-gray-400 py-4">No IP pool entries found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ippools->withQueryString()->links() }}
    </div>
</div>
@endsection
