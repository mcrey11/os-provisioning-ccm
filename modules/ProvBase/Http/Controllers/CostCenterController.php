<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\CostCenter;

class CostCenterController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new CostCenter;
        }

        $ret = [];

        $ret[] = ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'];
        $ret[] = ['form_type' => 'text', 'name' => 'number', 'description' => 'Number'];
        $ret[] = ['form_type' => 'select', 'name' => 'sepaaccount_id', 'description' => 'SEPA Account', 'value' => \Modules\BillingBase\Entities\SepaAccount::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'text', 'name' => 'billing_month', 'description' => 'Billing Month'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];

        return $ret;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\CostCenter;
    }
}