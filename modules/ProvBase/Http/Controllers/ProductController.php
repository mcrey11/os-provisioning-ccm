<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\Product;

class ProductController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Product;
        }

        $ret = [];

        $ret[] = ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'];
        $ret[] = ['form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => ['Internet', 'Voip', 'TV', 'Device', 'Credit', 'Other', 'Postal']];
        $ret[] = ['form_type' => 'select', 'name' => 'qos_id', 'description' => 'QoS', 'value' => \Modules\ProvBase\Entities\Qos::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'text', 'name' => 'price', 'description' => 'Price'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'tax', 'description' => 'Tax'];
        $ret[] = ['form_type' => 'select', 'name' => 'billing_cycle', 'description' => 'Billing Cycle', 'value' => ['monthly' => 'Monthly', 'quarterly' => 'Quarterly', '3m' => '3 Months', 'q1' => 'Q1', 'semiannual' => 'Semiannual', 'yearly' => 'Yearly']];
        $ret[] = ['form_type' => 'text', 'name' => 'maturity', 'description' => 'Maturity (months)'];
        $ret[] = ['form_type' => 'text', 'name' => 'period_of_notice', 'description' => 'Period of Notice (months)'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'proportional', 'description' => 'Proportional'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'record_monthly', 'description' => 'Record Monthly'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'deprecated', 'description' => 'Deprecated'];
        $ret[] = ['form_type' => 'text', 'name' => 'markon', 'description' => 'Markon'];
        $ret[] = ['form_type' => 'select', 'name' => 'parent_id', 'description' => 'Parent Product', 'value' => Product::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];

        return $ret;
    }

    public function prepare_input($data)
    {
        if (isset($data['price'])) {
            $data['price'] = (float) $data['price'];
        }

        if (isset($data['markon'])) {
            $data['markon'] = (float) $data['markon'];
        }

        return $data;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\Product;
    }
}