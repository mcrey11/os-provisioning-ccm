@extends('Layout.default')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-stethoscope"></i> Device Health</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>IP Address</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse( as )
                            <tr>
                                <td>{{ ->name }}</td>
                                <td>{{ ->ip ?? 'N/A' }}</td>
                                <td>{{ ->netelementtype->name ?? 'Unknown' }}</td>
                                <td>
                                    @if(->ip)
                                        <span class="badge badge-info">Check</span>
                                    @else
                                        <span class="badge badge-secondary">No IP</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('Monitoring.DeviceHealth.show', ->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No devices found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
