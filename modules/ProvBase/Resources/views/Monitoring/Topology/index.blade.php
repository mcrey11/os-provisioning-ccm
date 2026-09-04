@extends('Layout.default')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-sitemap"></i> Network Topology</h3>
                </div>
                <div class="card-body">
                    @if(->count())
                        <div class="tree">
                            @foreach( as )
                                <div class="tree-node">
                                    <span class="tree-label">
                                        <i class="fa fa-server text-primary"></i>
                                        <strong>{{ ->name }}</strong>
                                        @if(->ip)
                                            <small class="text-muted">({{ ->ip }})</small>
                                        @endif
                                    </span>
                                    @if(->children && ->children->count())
                                        <div class="tree-children ml-4">
                                            @foreach(->children as )
                                                <div class="tree-node">
                                                    <span class="tree-label">
                                                        <i class="fa fa-sitemap text-info"></i>
                                                        {{ ->name }}
                                                        @if(->ip)
                                                            <small class="text-muted">({{ ->ip }})</small>
                                                        @endif
                                                    </span>
                                                    @if(->children && ->children->count())
                                                        <div class="tree-children ml-4">
                                                            @foreach(->children as )
                                                                <div class="tree-node">
                                                                    <span class="tree-label">
                                                                        <i class="fa fa-circle text-success"></i>
                                                                        {{ ->name }}
                                                                        @if(->ip)
                                                                            <small class="text-muted">({{ ->ip }})</small>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fa fa-sitemap fa-3x"></i>
                            <p>No network topology data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tree-node {
    padding: 5px 0;
}
.tree-label {
    cursor: pointer;
}
.tree-children {
    border-left: 1px solid #dee2e6;
    padding-left: 10px;
}
</style>
@endsection
