@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="group" value="{{ request('group') }}" placeholder="Filter by groupname..." class="form-control w-64">
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
        <a href="{{ route('RadGroupReply.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded shadow overflow-x-auto">
        <table class="table table-sm text-sm w-full">
            <thead>
                <tr>
                    <th>Group Name</th>
                    <th>Attribute</th>
                    <th>Op</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupreplies as $gr)
                <tr>
                    <td><span class="badge badge-info">{{ $gr->groupname }}</span></td>
                    <td>{{ $gr->attribute }}</td>
                    <td><code>{{ $gr->op }}</code></td>
                    <td>{{ $gr->value }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-gray-400 py-4">No group reply attributes found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $groupreplies->withQueryString()->links() }}
    </div>
</div>
@endsection
