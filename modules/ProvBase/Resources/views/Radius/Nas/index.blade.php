@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <div class="bg-white dark:bg-slate-800 rounded shadow overflow-x-auto">
        <table class="table table-sm text-sm w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NAS Name</th>
                    <th>Short Name</th>
                    <th>Type</th>
                    <th>Ports</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($nasList as $n)
                <tr>
                    <td>{{ $n->id }}</td>
                    <td><a href="{{ route('Nas.show', $n->id) }}">{{ $n->nasname }}</a></td>
                    <td>{{ $n->shortname ?: '-' }}</td>
                    <td>{{ $n->type ?: '-' }}</td>
                    <td>{{ $n->ports ?: '-' }}</td>
                    <td>{{ $n->description ?: '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-gray-400 py-4">No NAS devices configured</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $nasList->links() }}
    </div>
</div>
@endsection
