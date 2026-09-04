@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <div class="bg-white dark:bg-slate-800 rounded shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-lg mb-3 dark:text-slate-100">Session Info</h3>
                <table class="table table-sm text-sm">
                    <tr><th class="w-40">Session ID</th><td>{{ $session->acctsessionid }}</td></tr>
                    <tr><th>Unique ID</th><td>{{ $session->acctuniqueid }}</td></tr>
                    <tr><th>Username</th><td>{{ $session->username }}</td></tr>
                    <tr><th>Realm</th><td>{{ $session->realm ?: '-' }}</td></tr>
                    <tr><th>NAS IP</th><td>{{ $session->nasipaddress }}</td></tr>
                    <tr><th>NAS Port</th><td>{{ $session->nasportid ?: '-' }}</td></tr>
                    <tr><th>NAS Port Type</th><td>{{ $session->nasporttype ?: '-' }}</td></tr>
                    <tr><th>Framed IP</th><td>{{ $session->framedipaddress }}</td></tr>
                    <tr><th>Calling Station</th><td>{{ $session->callingstationid }}</td></tr>
                    <tr><th>Called Station</th><td>{{ $session->calledstationid }}</td></tr>
                </table>
            </div>
            <div>
                <h3 class="font-semibold text-lg mb-3 dark:text-slate-100">Accounting</h3>
                <table class="table table-sm text-sm">
                    <tr><th class="w-40">Start Time</th><td>{{ $session->acctstarttime ? $session->acctstarttime->format('Y-m-d H:i:s') : '-' }}</td></tr>
                    <tr><th>Stop Time</th><td>{{ $session->acctstoptime ? $session->acctstoptime->format('Y-m-d H:i:s') : '-' }}</td></tr>
                    <tr><th>Duration</th><td>{{ $session->acctsessiontime ? gmdate('H:i:s', $session->acctsessiontime) : '-' }}</td></tr>
                    <tr><th>Authentic</th><td>{{ $session->acctauthentic ?: '-' }}</td></tr>
                    <tr><th>Input Octets</th><td>{{ $session->acctinputoctets ? number_format($session->acctinputoctets) . ' bytes (' . round($session->acctinputoctets / 1048576, 2) . ' MB)' : '-' }}</td></tr>
                    <tr><th>Output Octets</th><td>{{ $session->acctoutputoctets ? number_format($session->acctoutputoctets) . ' bytes (' . round($session->acctoutputoctets / 1048576, 2) . ' MB)' : '-' }}</td></tr>
                    <tr><th>Terminate Cause</th><td>{{ $session->acctterminatecause ?: '-' }}</td></tr>
                    <tr><th>Service Type</th><td>{{ $session->servicetype ?: '-' }}</td></tr>
                    <tr><th>Framed Protocol</th><td>{{ $session->framedprotocol ?: '-' }}</td></tr>
                    <tr><th>Connect Info Start</th><td>{{ $session->connectinfo_start ?: '-' }}</td></tr>
                    <tr><th>Connect Info Stop</th><td>{{ $session->connectinfo_stop ?: '-' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('RadAcct.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Sessions</a>
        </div>
    </div>
</div>
@endsection
