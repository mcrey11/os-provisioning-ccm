@extends('Layout.default')

@section('title', 'Subscriber: ' . $contract->number)

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Subscriber: {{ $contract->firstname }} {{ $contract->lastname }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('Dashboard.index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('Subscriber.index') }}">Subscribers</a></li>
            <li class="active">{{ $contract->number }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Contract Information</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr><th>Contract#</th><td>{{ $contract->number }}</td></tr>
                            <tr><th>Name</th><td>{{ $contract->firstname }} {{ $contract->lastname }}</td></tr>
                            <tr><th>Email</th><td>{{ $contract->email }}</td></tr>
                            <tr><th>Phone</th><td>{{ $contract->phone }}</td></tr>
                            <tr><th>Company</th><td>{{ $contract->company }}</td></tr>
                            <tr><th>Start</th><td>{{ $contract->contract_start }}</td></tr>
                            <tr><th>End</th><td>{{ $contract->contract_end ?? 'Ongoing' }}</td></tr>
                            <tr><th>QoS</th><td>{{ $contract->qos->name ?? '-' }}</td></tr>
                            <tr><th>Internet Access</th><td>{!! $contract->internet_access ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>' !!}</td></tr>
                            <tr><th>Telephony</th><td>{!! $contract->has_telephony ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>' !!}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-server"></i> Modems ({{ $modems->count() }})</h3>
                    </div>
                    <div class="box-body">
                        @forelse($modems as $modem)
                        @php
                            $activeSession = $modem->radacct()->whereNull('acctstoptime')->first();
                            $isOnline = $activeSession !== null;
                        @endphp
                        <table class="table table-bordered" style="margin-bottom: 10px;">
                            <tr>
                                <th colspan="2">
                                    {{ $modem->hostname ?? $modem->mac }}
                                    @if($isOnline)
                                        <span class="label label-success pull-right">Online</span>
                                    @else
                                        <span class="label label-default pull-right">Offline</span>
                                    @endif
                                </th>
                            </tr>
                            <tr><th>MAC</th><td>{{ $modem->mac }}</td></tr>
                            <tr><th>PPP Username</th><td>{{ $modem->ppp_username }}</td></tr>
                            <tr><th>Current IP</th><td>{{ $activeSession->framedipaddress ?? '-' }}</td></tr>
                            <tr><th>US Power</th><td>{{ $modem->us_pwr ?? '-' }} dBmV</td></tr>
                            <tr><th>DS SNR</th><td>{{ $modem->ds_snr ?? '-' }} dB</td></tr>
                            <tr><th>Gateway</th><td>{{ $modem->netgw->ip ?? '-' }}</td></tr>
                            <tr>
                                <td colspan="2">
                                    <a href="{{ route('Subscriber.Usage.modem', $modem->id) }}" class="btn btn-warning btn-xs">
                                        <i class="fa fa-tachometer"></i> Usage
                                    </a>
                                </td>
                            </tr>
                        </table>
                        @empty
                        <p class="text-muted">No modems found for this contract.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if($activeSessions->count() > 0)
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-exchange"></i> Active Sessions ({{ $activeSessions->count() }})</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>IP Address</th>
                                    <th>NAS</th>
                                    <th>Start Time</th>
                                    <th>Duration</th>
                                    <th>In (MB)</th>
                                    <th>Out (MB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeSessions as $session)
                                <tr>
                                    <td>{{ $session->username }}</td>
                                    <td>{{ $session->framedipaddress }}</td>
                                    <td>{{ $session->nasipaddress }}</td>
                                    <td>{{ $session->acctstarttime }}</td>
                                    <td>{{ gmdate('H:i:s', $session->acctsessiontime) }}</td>
                                    <td>{{ round($session->acctinputoctets / 1048576, 2) }}</td>
                                    <td>{{ round($session->acctoutputoctets / 1048576, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-xs-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-history"></i> Recent Sessions</h3>
                        <div class="box-tools pull-right">
                            <a href="{{ route('Subscriber.Session.history', $contract->id) }}" class="btn btn-info btn-sm">
                                Full History
                            </a>
                        </div>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Modem</th>
                                    <th>IP Address</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Duration</th>
                                    <th>In (MB)</th>
                                    <th>Out (MB)</th>
                                    <th>Cause</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSessions as $session)
                                <tr>
                                    <td>{{ $session->username }}</td>
                                    <td>{{ $session->username }}</td>
                                    <td>{{ $session->framedipaddress }}</td>
                                    <td>{{ $session->acctstarttime }}</td>
                                    <td>{{ $session->acctstoptime }}</td>
                                    <td>{{ gmdate('H:i:s', $session->acctsessiontime) }}</td>
                                    <td>{{ round($session->acctinputoctets / 1048576, 2) }}</td>
                                    <td>{{ round($session->acctoutputoctets / 1048576, 2) }}</td>
                                    <td>{{ $session->acctterminatecause }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No recent sessions</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
