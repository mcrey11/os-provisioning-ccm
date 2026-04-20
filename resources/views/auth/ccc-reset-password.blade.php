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
    <title>{{ __('view.ccc.passwordReset.pageTitle') }}</title>
    @include ('bootstrap.header')
</head>

<body class="pace-top">

    <div class="login-cover">
        <div class="login-cover-image">
            <img id="login-img" class="mh-100 w-100" data-id="login-cover-image" src="{{ $bgImageRoute }}">
        </div>
        <div class="login-cover-bg"></div>
        <div id="nmsprime-brand" class="brand">
            <img src="{{ asset('images/nmsprime-logo-poweredby.png') }}" class="img-fluid">
        </div>
    </div>

    <div class="login login-v2 animated fadeInDown">
        <div class="login-content">
            @if ($logo)
                <div class="brand">
                    <img src="{{ $logo }}" class="img-fluid">
                </div>
            @endif

            @if ($head1 || $head2)
                <div class="row">
                    <div class="col-9 flex flex-column justify-content-center align-items-center">
                        <div style="font-size: 18px">{{ $head1 }}</div>
                        <div style="font-size: 14px">{{ $head2 }}</div>
                    </div>
                    <div class="icon col-3" style="font-size: 60px">
                        <i class="fa fa-key" style="font-color:#b7b7b7;"></i>
                    </div>
                </div>
            @endif

            <div class="m-t-20">
                <div class="modal-dialog" style="margin: 0; max-width: none;">
                    <div class="modal-content" style="border: none; box-shadow: none; background: transparent;">
                        <div class="modal-header" style="border: none; padding-left: 0; padding-right: 0;">
                            <h4 class="modal-title">{{ __('view.ccc.passwordReset.dialogTitle') }}</h4>
                        </div>
                        <div class="modal-body" style="padding-left: 0; padding-right: 0;">
                            {{ html()->form('POST', route('customer.password.reset.submit'))->open() }}
                            @csrf
                            <input type="hidden" name="cccauthuser" value="{{ $cccauthuser }}">
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group m-b-20">
                                {{ html()->password('password')->placeholder(__('view.ccc.passwordReset.newPassword'))->style('simple')->class('form-control input-lg')->required() }}
                            </div>
                            <div class="form-group m-b-20">
                                {{ html()->password('password_confirmation')->placeholder(__('view.ccc.passwordReset.newPasswordConfirm'))->style('simple')->class('form-control input-lg')->required() }}
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger m-b-15">
                                    @foreach ($errors->all() as $err)
                                        <div>{{ $err }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="login-buttons">
                                <button type="submit" class="btn btn-success btn-block btn-lg">{{ __('view.ccc.passwordReset.submit') }}</button>
                            </div>
                            {{ html()->form()->close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include ('bootstrap.footer')

</body>

</html>
