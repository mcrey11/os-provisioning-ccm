@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <div class="bg-white dark:bg-slate-800 rounded shadow p-6">
        <table class="table table-sm text-sm">
            <tr><th class="w-40">ID</th><td>{{ $reply->id }}</td></tr>
            <tr><th>Username</th><td>{{ $reply->username }}</td></tr>
            <tr><th>Attribute</th><td>{{ $reply->attribute }}</td></tr>
            <tr><th>Op</th><td><code>{{ $reply->op }}</code></td></tr>
            <tr><th>Value</th><td>{{ $reply->value }}</td></tr>
        </table>
        <div class="mt-4">
            <a href="{{ route('RadReply.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
</div>
@endsection
