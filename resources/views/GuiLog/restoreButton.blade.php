<div class="col-md-7 order-3">
	@if(isset($route))
    	<a class="btn btn-default btn-block" href="{{ $route }}"> {{ $text }} </a>
    @else
    	<div class="alert alert-primary"> {{ $text }} </div>
    @endif
</div>
