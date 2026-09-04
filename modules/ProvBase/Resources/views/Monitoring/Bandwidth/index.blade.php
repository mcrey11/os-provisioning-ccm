@extends('Layout.default')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-tachometer"></i> Bandwidth Monitoring</h3>
                </div>
                <div class="card-body">
                    <h4>Network Elements</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>IP Address</th>
                                <th>Type</th>
                                <th>Status</th>
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
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">No IP</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No network elements found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <h4 class="mt-4">Network Gateways</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>IP Address</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse( as )
                            <tr>
                                <td>{{ ->name }}</td>
                                <td>{{ ->ip ?? 'N/A' }}</td>
                                <td>
                                    @if(->ip)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">No IP</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No gateways found</td>
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
