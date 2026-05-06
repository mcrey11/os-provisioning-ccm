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
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>NMS</title>
    @include ('bootstrap.header')

    <script>setTimeout("document.getElementById('error').style.display='none';", 3000);</script>
</head>

<body class="pace-top">

    {{-- Background Image --}}
    <div class="login-cover">
        <div class="login-cover-image">
            <img id="login-img" class="mh-100 w-100" data-id="login-cover-image" src="{{ $bgImageRoute }}">
        </div>
        <div class="login-cover-bg"></div>
        @if ($loginPage == 'customer')
            <div id="nmsprime-brand" class="brand">
                <img src="{{asset('images/nmsprime-logo-poweredby.png')}}" class="img-fluid">
            </div>
        @endif
    </div>

    {{-- begin login --}}
    <div class="login login-v2 animated fadeInDown">
        <div class="login-content">
            @if ($logo)
                <div class="brand">
                    <img src="{{$logo}}" class="img-fluid">
                </div>
            @endif

            @if ($head1 || $head2)
                <div class="row">
                    <div class="col-9 flex flex-column justify-content-center align-items-center">
                        <div style="font-size: 18px">{{ $head1 }}</div>
                        <div style="font-size: 14px">{{ $head2 }}</div>
                    </div>
                    <div class="icon col-3" style="font-size: 60px">
                        <i class="fa fa-sign-in" style="font-color:#b7b7b7;"></i>
                    </div>
                </div>
            @endif

            <div class="m-t-20">
                {{ html()->form('POST', $loginRoute)->open() }}
                @if (isset($intended) && $intended)
                    <div class="note note-warning">
                        <div class="mb-2">
                            {{ trans('view.redirectNote') }}:
                        </div>
                        <div class="badge font-weight-normal" style="font-family: monospace;">{{ $intended }}</div>
                    </div>
                @endif
                {{-- Username --}}
                <div class="form-group m-b-20">
                    {{ html()->text('login_name')->placeholder(\App\Http\Controllers\BaseViewController::translate_label($loginPage == 'admin' ? 'Username' : 'Customer number'))->style('simple')->class('form-control input-lg')->autofocus() }}
                </div>

                {{-- Password --}}
                <div class="form-group m-b-20">
                    {{ html()->password('password')->placeholder(\App\Http\Controllers\BaseViewController::translate_label('Password'))->style('simple')->class('form-control input-lg')->autofocus() }}
                </div>

                {{-- Remember Checkbox --}}
                <div class="form-group m-b-20 flex align-items-center">
                    <input align="left" class="mt-0 mb-2" name="remember" type="checkbox" value="1">
                    <label for="remember" class="control-label px-2">
                        {{ \App\Http\Controllers\BaseViewController::translate_label('Remember Me') . '!' }}
                    </label>
                </div>

                @isset($forgotPasswordRoute)
                    <div class="form-group m-b-10 text-center">
                        <a href="#ccc-forgot-password-modal" data-toggle="modal" class="text-muted">
                            {{ __('view.ccc.passwordReset.forgotLink') }}
                        </a>
                    </div>
                @endisset

                @if (session('status'))
                    <div class="note note-success m-b-15">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Error Message --}}
                <div class="m-t-20">
                    <p align="center"><span id="error" color="yellow">
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </span></p>
                </div>
                <br>
                {{-- Login Button --}}
                <div class="login-buttons">
                    <button type="submit" class="btn btn-success btn-block btn-lg">{{ \App\Http\Controllers\BaseViewController::translate_label('Sign me in') }}</button>
                </div>

                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
    {{-- end login --}}

    @isset($forgotPasswordRoute)
        <div class="modal fade" id="ccc-forgot-password-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ __('view.ccc.passwordReset.modalTitle') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    {{ html()->form('POST', $forgotPasswordRoute)->open() }}
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted">{{ __('view.ccc.passwordReset.modalHelp') }}</p>
                        <div class="form-group">
                            <label for="ccc-forgot-email">{{ __('view.ccc.passwordReset.emailLabel') }}</label>
                            {{ html()->email('email')->id('ccc-forgot-email')->class('form-control')->value(old('email'))->required() }}
                        </div>
                        @if ($errors->has('email'))
                            <div class="text-danger small m-t-5">{{ $errors->first('email') }}</div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('view.ccc.passwordReset.modalCancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('view.ccc.passwordReset.modalSubmit') }}</button>
                    </div>
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>

        @if ($errors->has('email'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                        jQuery('#ccc-forgot-password-modal').modal('show');
                    }
                });
            </script>
        @endif
    @endisset

    @include ('bootstrap.footer')

</body>
</html>
