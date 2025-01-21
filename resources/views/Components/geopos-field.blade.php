@php
    $errors = Session::get('errors');
    $latError = $errors && $errors->get('lat') ? $errors->get('lat')[0] : '';
    $lngError = $errors && $errors->get('lng') ? $errors->get('lng')[0] : '';
@endphp

<div class="col-md-3 order-3">
    <input class=form-control name='lng' type=text value="{{ $model['lng'] }}" id='lng' style='background-color:inherit'>
</div>
<div class="col-md-4 order-4">
    <input class=form-control name='lat' type=text value="{{ $model['lat'] }}" id='lat' style='background-color:inherit'>
</div>

@if ($latError || $lngError)
    <div class="order-4 col-md-4"></div>
    <div class="mb-2 order-5 col-md-8">
        <p align="left" class="help-block" style="color: red;"> {{ $lngError.' '.$latError }} </p>
    </div>
@endif
