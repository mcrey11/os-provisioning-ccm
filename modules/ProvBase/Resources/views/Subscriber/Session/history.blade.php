@extends('Layout.default')

@section('title', 'Session History: ' . $contract->number)

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Session History: {{ $contract->firstname }} {{ $contract->lastname }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('Dashboard.index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('Subscriber.index') }}">Subscribers</a></li>
            <li><a href="{{ route('Subscriber.show', $contract->id) }}">{{ $contract->number }}</a></li>
            <li class="active">Session History</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Contract Summary</h3>
                        <div class="box-tools pull-right">
                            <a href="{{ route('Subscriber.show', $contract->id) }}" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Subscriber
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Contract#:</strong> {{ $contract->number }}
                            </div>
                            <div class="col-md-3">
                                <strong>Customer:</strong> {{ $contract->firstname }} {{ $contract->lastname }}
                            </div>
                            <div class="col-md-2">
                                <strong>QoS:</strong> {{ $contract->qos->name ?? '-' }}
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="description-block border-right">
                                            <span class="description-text">Total Sessions</span>
                                            <h5 class="description-header">{{ number_format($totalSessions) }}</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="description-block border-right">
                                            <span class="description-text">Total In</span>
                                            <h5 class="description-header">{{ round($totalTrafficIn / 1048576, 1) }} MB</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="description-block">
                                            <span class="description-text">Total Out</span>
                                            <h5 class="description-header">{{ round($totalTrafficOut / 1048576, 1) }} MB</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach($sessions as $username => $modemSessions)
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-server"></i> Modem: {{ $username }} ({{ $modemSessions->count() }} sessions)</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Framed IP</th>
                                    <th>NAS</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Duration</th>
                                    <th>In (MB)</th>
                                    <th>Out (MB)</th>
                                    <th>Cause</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modemSessions as $session)
                                <tr>
                                    <td>{{ $session->username }}</td>
                                    <td>{{ $session->framedipaddress }}</td>
                                    <td>{{ $session->nasipaddress }}</td>
                                    <td>{{ $session->acctstarttime }}</td>
                                    <td>{{ $session->acctstoptime ?? '-' }}</td>
                                    <td>{{ gmdate('H:i:s', $session->acctsessiontime) }}</td>
                                    <td>{{ round($session->acctinputoctets / 1048576, 2) }}</td>
                                    <td>{{ round($session->acctoutputoctets / 1048576, 2) }}</td>
                                    <td>{{ $session->acctterminatecause ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </section>
</div>
@endsection
