<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\Item;

class ItemController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Item;
        }

        $ret = [];

        $ret[] = ['form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => \Modules\ProvBase\Entities\Contract::pluck('number', 'id')->toArray(), 'options' => ['class' => 'select2-ajax', 'ajax-route' => 'Contract.select2']];
        $ret[] = ['form_type' => 'select', 'name' => 'product_id', 'description' => 'Product', 'value' => \Modules\BillingBase\Entities\Product::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'text', 'name' => 'count', 'description' => 'Count'];
        $ret[] = ['form_type' => 'date', 'name' => 'valid_from', 'description' => 'Valid From'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'valid_from_fixed', 'description' => 'Valid From Fixed'];
        $ret[] = ['form_type' => 'date', 'name' => 'valid_to', 'description' => 'Valid To'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'valid_to_fixed', 'description' => 'Valid To Fixed'];
        $ret[] = ['form_type' => 'text', 'name' => 'credit_amount', 'description' => 'Credit Amount'];
        $ret[] = ['form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center', 'value' => \Modules\BillingBase\Entities\CostCenter::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'text', 'name' => 'accounting_text', 'description' => 'Accounting Text'];
        $ret[] = ['form_type' => 'text', 'name' => 'payed_month', 'description' => 'Payed Month'];

        return $ret;
    }

    public function prepare_input($data)
    {
        if (isset($data['credit_amount'])) {
            $data['credit_amount'] = (float) $data['credit_amount'];
        }

        return $data;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\Item;
    }
}