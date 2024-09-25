<?php
    if (in_array($checkboxActive, [true, 1, "1", "active", "activated", "checked"])) {
        $value = "1";
        $color = "#080";
        $symbol = "✔";
    } else {
        $value = "0";
        $color = "#f00";
        $symbol = "✘";
    };
?>


<div class="col-md-7 order-3 md:order-2">
    <input class="form-control" style name="{{ $checkboxName }}" type="hidden" id="{{ $checkboxId }}" value="{{ $value }}">
    <div style="color: {{ $color }}; text-align: center; font-size: 1.4em; padding-top:0.4em">{{ $symbol }}</div>
</div>
