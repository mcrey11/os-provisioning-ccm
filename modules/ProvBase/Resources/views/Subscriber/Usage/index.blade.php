@extends('Layout.default')

@section('title', 'Bandwidth Usage')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Bandwidth Usage (Last {{ $days }} Days)</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('Dashboard.index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('Subscriber.index') }}">Subscribers</a></li>
            <li class="active">Usage</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-arrow-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Download</span>
                        <span class="info-box-number">{{ round($totalTrafficOut / 1073741824, 2) }} GB</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-arrow-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Upload</span>
                        <span class="info-box-number">{{ round($totalTrafficIn / 1073741824, 2) }} GB</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-exchange"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Sessions</span>
                        <span class="info-box-number">{{ number_format($totalSessions) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Unique Users</span>
                        <span class="info-box-number">{{ number_format($uniqueUsers) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Top Users by Traffic</h3>
                        <div class="box-tools pull-right">
                            <form method="GET" action="{{ route('Subscriber.Usage.index') }}" class="form-inline">
                                <select name="days" class="form-control input-sm">
                                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                                </select>
                                <button type="submit" class="btn btn-info btn-sm">Filter</button>
                            </form>
                        </div>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Contract#</th>
                                    <th>Customer</th>
                                    <th>Sessions</th>
                                    <th>In (MB)</th>
                                    <th>Out (MB)</th>
                                    <th>Total (MB)</th>
                                    <th>Total Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topUsers as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>
                                        @if($user->contract_id)
                                            <a href="{{ route('Subscriber.show', $user->contract_id) }}">{{ $user->contract_number }}</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                    <td>{{ $user->session_count }}</td>
                                    <td>{{ round($user->total_in / 1048576, 2) }}</td>
                                    <td>{{ round($user->total_out / 1048576, 2) }}</td>
                                    <td><strong>{{ round(($user->total_in + $user->total_out) / 1048576, 2) }}</strong></td>
                                    <td>{{ gmdate('H:i:s', $user->total_time) }}</td>
                                    <td>
                                        <a href="{{ route('Subscriber.Session.history', $user->contract_id) }}" class="btn btn-info btn-xs">
                                            <i class="fa fa-history"></i> History
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No usage data found</td>
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
