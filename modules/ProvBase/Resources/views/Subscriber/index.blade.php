@extends('Layout.default')

@section('title', 'Subscribers')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Subscribers</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('Dashboard.index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Subscribers</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">All Subscribers</h3>
                        <div class="box-tools pull-right">
                            <form method="GET" action="{{ route('Subscriber.index') }}" class="form-inline">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ $search ?? '' }}">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-info btn-flat">Search</button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Contract#</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Modems</th>
                                    <th>Active Sessions</th>
                                    <th>Current IP</th>
                                    <th>QoS</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                @php
                                    $activeCount = 0;
                                    $currentIp = '-';
                                    foreach ($contract->modems as $modem) {
                                        $active = $modem->radacct()->whereNull('acctstoptime')->first();
                                        if ($active) {
                                            $activeCount++;
                                            if ($currentIp === '-') {
                                                $currentIp = $active->framedipaddress;
                                            }
                                        }
                                    }
                                    $isOnline = $activeCount > 0;
                                @endphp
                                <tr>
                                    <td>{{ $contract->number }}</td>
                                    <td>{{ $contract->firstname }} {{ $contract->lastname }}</td>
                                    <td>{{ $contract->email }}</td>
                                    <td>{{ $contract->modems->count() }}</td>
                                    <td>
                                        @if($activeCount > 0)
                                            <span class="badge bg-green">{{ $activeCount }}</span>
                                        @else
                                            <span class="badge bg-gray">0</span>
                                        @endif
                                    </td>
                                    <td>{{ $currentIp }}</td>
                                    <td>{{ $contract->qos->name ?? '-' }}</td>
                                    <td>
                                        @if($isOnline)
                                            <span class="label label-success">Online</span>
                                        @else
                                            <span class="label label-default">Offline</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('Subscriber.show', $contract->id) }}" class="btn btn-info btn-xs">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No subscribers found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer clearfix">
                        {{ $contracts->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
