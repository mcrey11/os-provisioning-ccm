object Host "{{$ne->id_name}}" {
    import "generic-host"

    display_name = "{{$ne->name}}"
    address = "{{$ne->ip}}"
    vars.isBubble = {{$ne->isbubble}}
    vars.netelementtype_id = "{{$ne->netelementtype_id}}"
@if(! is_null($ne->parent))
    vars.parents = {{$ne->parent}}
@endif
@if(! is_null($ne->port))
    vars.port = {{$ne->port}}
@endif
@if(! is_null($ne->ro_community))
    vars.ro_community = "{{$ne->ro_community}}"
@endif
@if(! is_null($ne->vendor))
    vars.vendor = "{{$ne->vendor}}"
@endif
}
