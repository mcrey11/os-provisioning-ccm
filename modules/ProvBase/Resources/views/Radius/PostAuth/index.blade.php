@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search username..." class="form-control w-64">
        <select name="status" class="form-control w-40">
            <option value="">All</option>
            <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
            <option value="fail" {{ request('status') === 'fail' ? 'selected' : '' }}>Failed</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
        <a href="{{ route('RadPostAuth.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded shadow overflow-x-auto">
        <table class="table table-sm text-sm w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Reply</th>
                    <th>Auth Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auths as $a)
                <tr>
                    <td>{{ $a->id }}</td>
                    <td>{{ $a->username }}</td>
                    <td>
                        @if($a->reply === 'Access-Accept')
                            <span class="badge badge-success">{{ $a->reply }}</span>
                        @else
                            <span class="badge badge-danger">{{ $a->reply }}</span>
                        @endif
                    </td>
                    <td>{{ $a->authdate->format('Y-m-d H:i:s') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-gray-400 py-4">No auth attempts found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $auths->withQueryString()->links() }}
    </div>
</div>
@endsection
