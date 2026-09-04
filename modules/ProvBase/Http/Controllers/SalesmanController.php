<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\Salesman;

class SalesmanController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Salesman;
        }

        $ret = [];

        $ret[] = ['form_type' => 'text', 'name' => 'firstname', 'description' => 'First Name'];
        $ret[] = ['form_type' => 'text', 'name' => 'lastname', 'description' => 'Last Name'];
        $ret[] = ['form_type' => 'text', 'name' => 'commission', 'description' => 'Commission (%)'];
        $ret[] = ['form_type' => 'text', 'name' => 'products', 'description' => 'Products'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];

        return $ret;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\Salesman;
    }
}