<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\DunningRun;

class DunningRunController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new DunningRun;
        }

        $ret = [];

        $ret[] = ['form_type' => 'text', 'name' => 'status', 'description' => 'Status'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];

        return $ret;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\DunningRun;
    }
}