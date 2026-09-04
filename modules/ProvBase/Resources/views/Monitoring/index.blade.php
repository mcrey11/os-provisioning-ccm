@extends('Layout.default')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-heartbeat"></i> Network Monitoring Dashboard</h3>
                </div>
                <div class="card-body">
                    <!-- Stats Widgets -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $totalDevices }}</h3>
                                    <p>Total Devices</p>
                                </div>
                                <div class="icon"><i class="fa fa-server"></i></div>
                                <a href="{{ route('Monitoring.DeviceHealth') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $activeSessions }}</h3>
                                    <p>Active RADIUS Sessions</p>
                                </div>
                                <div class="icon"><i class="fa fa-exchange"></i></div>
                                <a href="{{ route('RadAcct.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $failedAuths }}</h3>
                                    <p>Failed Authentications</p>
                                </div>
                                <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
                                <a href="{{ route('Monitoring.Alerts') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $totalSessions }}</h3>
                                    <p>Total Sessions</p>
                                </div>
                                <div class="icon"><i class="fa fa-database"></i></div>
                                <a href="{{ route('RadAcct.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>Quick Links</h4>
                            <div class="btn-group">
                                <a href="{{ route('Monitoring.DeviceHealth') }}" class="btn btn-primary">
                                    <i class="fa fa-stethoscope"></i> Device Health
                                </a>
                                <a href="{{ route('Monitoring.Bandwidth') }}" class="btn btn-success">
                                    <i class="fa fa-tachometer"></i> Bandwidth
                                </a>
                                <a href="{{ route('Monitoring.Topology') }}" class="btn btn-info">
                                    <i class="fa fa-sitemap"></i> Topology
                                </a>
                                <a href="{{ route('Monitoring.Alerts') }}" class="btn btn-warning">
                                    <i class="fa fa-bell"></i> Alerts
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
