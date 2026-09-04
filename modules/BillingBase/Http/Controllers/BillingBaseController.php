<?php

namespace Modules\BillingBase\Http\Controllers;

class BillingBaseController extends \BaseController
{
    public function index()
    {
        $model = new \Modules\BillingBase\Entities\BillingBase();
        $headline = trans('view.BillingConfig');

        return \View::make('Generic.edit', $this->compact_prep_view(compact('model', 'headline')));
    }
}
