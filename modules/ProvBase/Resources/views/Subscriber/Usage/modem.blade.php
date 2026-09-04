@extends('Layout.default')

@section('title', 'Modem Usage: ' . $modem->hostname)

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Modem Usage: {{ $modem->hostname ?? $modem->mac }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('Dashboard.index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('Subscriber.index') }}">Subscribers</a></li>
            <li class="active">Modem Usage</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-server"></i> Modem Information</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr><th>Hostname</th><td>{{ $modem->hostname }}</td></tr>
                            <tr><th>MAC</th><td>{{ $modem->mac }}</td></tr>
                            <tr><th>PPP Username</th><td>{{ $modem->ppp_username }}</td></tr>
                            <tr><th>Contract#</th><td>
                                @if($modem->contract)
                                    <a href="{{ route('Subscriber.show', $modem->contract->id) }}">{{ $modem->contract->number }}</a>
                                @else
                                    -
                                @endif
                            </td></tr>
                            <tr><th>Customer</th><td>{{ $modem->contract->firstname ?? '' }} {{ $modem->contract->lastname ?? '' }}</td></tr>
                            <tr><th>Gateway</th><td>{{ $modem->netgw->ip ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Traffic Summary</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr><th>Total Download</th><td><strong>{{ round($totals->total_out / 1048576, 2) }} MB</strong> ({{ round($totals->total_out / 1073741824, 2) }} GB)</td></tr>
                            <tr><th>Total Upload</th><td><strong>{{ round($totals->total_in / 1048576, 2) }} MB</strong> ({{ round($totals->total_in / 1073741824, 2) }} GB)</td></tr>
                            <tr><th>Total Traffic</th><td><strong>{{ round(($totals->total_in + $totals->total_out) / 1048576, 2) }} MB</strong> ({{ round(($totals->total_in + $totals->total_out) / 1073741824, 2) }} GB)</td></tr>
                            <tr><th>Total Sessions</th><td>{{ number_format($totals->session_count) }}</td></tr>
                            <tr><th>Total Time</th><td>{{ gmdate('H:i:s', $totals->total_time) }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-exchange"></i> Sessions</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Framed IP</th>
                                    <th>NAS</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Duration</th>
                                    <th>In (MB)</th>
                                    <th>Out (MB)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                <tr>
                                    <td>{{ $session->acctsessionid }}</td>
                                    <td>{{ $session->framedipaddress }}</td>
                                    <td>{{ $session->nasipaddress }}</td>
                                    <td>{{ $session->acctstarttime }}</td>
                                    <td>{{ $session->acctstoptime ?? '-' }}</td>
                                    <td>{{ gmdate('H:i:s', $session->acctsessiontime) }}</td>
                                    <td>{{ round($session->acctinputoctets / 1048576, 2) }}</td>
                                    <td>{{ round($session->acctoutputoctets / 1048576, 2) }}</td>
                                    <td>
                                        @if(is_null($session->acctstoptime))
                                            <span class="label label-success">Active</span>
                                        @else
                                            <span class="label label-default">Ended</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No sessions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer clearfix">
                        {{ $sessions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
