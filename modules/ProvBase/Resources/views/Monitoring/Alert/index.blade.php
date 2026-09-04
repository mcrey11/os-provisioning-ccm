@extends('Layout.default')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-bell"></i> Alerts</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Reply</th>
                                <th>NAS IP</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse( as )
                            <tr class="{{ strpos(->authpass, 'Reject') !== false ? 'table-danger' : '' }}">
                                <td>{{ ->authdate ?? 'N/A' }}</td>
                                <td>{{ ->username ?? 'N/A' }}</td>
                                <td>
                                    @if(strpos(->authpass, 'Accept') !== false)
                                        <span class="badge badge-success">Accept</span>
                                    @else
                                        <span class="badge badge-danger">Reject</span>
                                    @endif
                                </td>
                                <td>{{ ->nasipaddress ?? 'N/A' }}</td>
                                <td>{{ ->authpass ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No alerts found</td>
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
