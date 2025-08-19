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
@foreach (Module::collections()->keys() as $module)
    @if (file_exists(public_path('js/'.strtolower($module).'.js')))
        <script src="{{ mix('js/'.strtolower($module).'.js') }}"></script>
    @else
        <script>
            console.warn('Module "{{ $module }}" JavaScript file not found: js/{{ strtolower($module) }}.js - Run "npm run dev" to compile assets');
        </script>
    @endif
@endforeach
