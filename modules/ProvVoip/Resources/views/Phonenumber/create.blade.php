<?php
/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
?>
@extends('Generic.create')

@section('content_right')

<?php

	// try to get free numbers; these can be given as string (HTML) or as array

	// get numbers for the active provider
	if (\Module::collections()->has('ProvVoipEnvia')) {

		$free_numbers_panel_headline = trans('view.provvoip.freeNumbersEnvia');

		if (!App::environment('testing')) {	// get data from envia
			try {
				$currently_free_numbers = \Modules\ProvVoipEnvia\Entities\ProvVoipEnvia::get_free_numbers_for_view();
			}
			catch (\Throwable $ex) {
                echo "<b style='color:red'>".trans('view.provvoip.errorGettingFreeNumbersFromEnvia').$ex->getMessage()."</b>";
			}
		}
		else {	// for testing: inject fake data
			$currently_free_numbers = [0 => "01234/11111111", 1 => "01234/22222222"];
		}
	}

?>
	{{-- show this panel if information about free numbers is available --}}
	@if (isset($currently_free_numbers))
		@section('free_numbers_panel')

			@if (is_array($currently_free_numbers))

				<?php
					// set flag to include the correct JavaScript function
					// used in resources/views/Generic/form-js-fill-input-from-href.blade.php
					$load_input_from_href_filler_for_free_numbers = True;
				?>

                <b><i>{{ trans('view.provvoip.successGettingFreeNumbers') }}</i></b>
				<div id="free_numbers_return">
                @php 
                    $lastPrefix = '';
                @endphp
				@foreach ($currently_free_numbers as $free_number)
                    @php
                        $currentPrefix = explode('/', $free_number['number'])[0];
                    @endphp
                    @if ($lastPrefix != $currentPrefix)
                        @php
                            $lastPrefix = $currentPrefix;
                        @endphp
                        <br>
                        <b><u>{{ $currentPrefix }}:</u></b><br>
                    @endif
                    @if ($free_number['free'])
                        <a href="#">{{ $free_number['number'] }}</a><br>
                    @else
                        <s>{{ $free_number['number'] }}</s> ({{ trans('view.provvoip.numberInUseInNmsPrime') }})<br>
                    @endif
				@endforeach
				</div>
			@elseif (is_string($currently_free_numbers))
				{!! $currently_free_numbers !!}
			@endif

		@stop

		@include ('bootstrap.panel', array ('content' => 'free_numbers_panel', 'view_header' => $free_numbers_panel_headline, 'md' => 4))
	@endif

@stop
