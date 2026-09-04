@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search username..." class="form-control w-64">
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
        <a href="{{ route('RadReply.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded shadow overflow-x-auto">
        <table class="table table-sm text-sm w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Attribute</th>
                    <th>Op</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($replies as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->username }}</td>
                    <td>{{ $r->attribute }}</td>
                    <td><code>{{ $r->op }}</code></td>
                    <td>{{ $r->value }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-gray-400 py-4">No reply attributes found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $replies->withQueryString()->links() }}
    </div>
</div>
@endsection
