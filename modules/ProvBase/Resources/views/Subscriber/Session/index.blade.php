@extends('Layout.default')

@section('title', 'Session-to-Contract Mapping')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Session-to-Contract Mapping</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('Dashboard.index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('Subscriber.index') }}">Subscribers</a></li>
            <li class="active">Sessions</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">All Sessions</h3>
                        <div class="box-tools pull-right">
                            <form method="GET" action="{{ route('Subscriber.Session.index') }}" class="form-inline">
                                <input type="text" name="search" class="form-control input-sm" placeholder="Search username, IP, contract..." value="{{ $search ?? '' }}">
                                <input type="date" name="start_date" class="form-control input-sm" value="{{ $startDate ?? '' }}" title="Start Date">
                                <input type="date" name="end_date" class="form-control input-sm" value="{{ $endDate ?? '' }}" title="End Date">
                                <button type="submit" class="btn btn-info btn-sm">Filter</button>
                            </form>
                        </div>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Contract#</th>
                                    <th>Customer</th>
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
                                    <td>{{ $session->username }}</td>
                                    <td>
                                        @if($session->contract_id)
                                            <a href="{{ route('Subscriber.show', $session->contract_id) }}">{{ $session->contract_number }}</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $session->firstname }} {{ $session->lastname }}</td>
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
                                    <td colspan="11" class="text-center">No sessions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer clearfix">
                        {{ $sessions->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
