<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

BaseRoute::group([], function () {
    BaseRoute::get('Marketing/index', [
        'as' => 'Marketing.index',
        'uses' => 'MarketingController@index',
        'middleware' => ['can:view,Modules\Marketing\Entities\Consent'],
    ]);

    BaseRoute::resource('Consent', 'Modules\Marketing\Http\Controllers\ConsentController');
    // BaseRoute::resource('ContractConsent', 'Modules\Marketing\Http\Controllers\ContractConsentController');
});
