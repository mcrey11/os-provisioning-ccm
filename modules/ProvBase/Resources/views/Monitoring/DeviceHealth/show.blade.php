@extends('Layout.default')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-stethoscope"></i> Device Health - {{ ->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Device Information</h4>
                            <table class="table">
                                <tr><th>Name</th><td>{{ ->name }}</td></tr>
                                <tr><th>IP Address</th><td>{{ ->ip ?? 'N/A' }}</td></tr>
                                <tr><th>Type</th><td>{{ ->netelementtype->name ?? 'Unknown' }}</td></tr>
                                <tr><th>Status</th>
                                    <td>
                                        @if( == 'reachable')
                                            <span class="badge badge-success">Reachable</span>
                                        @elseif( == 'unreachable')
                                            <span class="badge badge-danger">Unreachable</span>
                                        @else
                                            <span class="badge badge-warning">Unknown</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Network Details</h4>
                            <table class="table">
                                <tr><th>Cluster</th><td>{{ ->cluster ?? 'N/A' }}</td></tr>
                                <tr><th>CMTS</th><td>{{ ->cmts ?? 'N/A' }}</td></tr>
                                <tr><th>Node</th><td>{{ ->node ?? 'N/A' }}</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <a href="{{ route('Monitoring.DeviceHealth') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
