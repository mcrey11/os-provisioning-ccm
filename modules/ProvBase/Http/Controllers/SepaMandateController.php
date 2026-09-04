<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\SepaMandate;

class SepaMandateController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new SepaMandate;
        }

        $ret = [];

        $ret[] = ['form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => \Modules\ProvBase\Entities\Contract::pluck('number', 'id')->toArray(), 'options' => ['class' => 'select2-ajax', 'ajax-route' => 'Contract.select2']];
        $ret[] = ['form_type' => 'text', 'name' => 'reference', 'description' => 'Reference'];
        $ret[] = ['form_type' => 'date', 'name' => 'signature_date', 'description' => 'Signature Date'];
        $ret[] = ['form_type' => 'text', 'name' => 'holder', 'description' => 'Holder'];
        $ret[] = ['form_type' => 'text', 'name' => 'iban', 'description' => 'IBAN'];
        $ret[] = ['form_type' => 'text', 'name' => 'bic', 'description' => 'BIC'];
        $ret[] = ['form_type' => 'text', 'name' => 'institute', 'description' => 'Institute'];
        $ret[] = ['form_type' => 'date', 'name' => 'valid_from', 'description' => 'Valid From'];
        $ret[] = ['form_type' => 'date', 'name' => 'valid_to', 'description' => 'Valid To'];
        $ret[] = ['form_type' => 'select', 'name' => 'state', 'description' => 'State', 'value' => ['active' => 'Active', 'inactive' => 'Inactive', 'pending' => 'Pending']];
        $ret[] = ['form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center', 'value' => \Modules\BillingBase\Entities\CostCenter::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'disable', 'description' => 'Disable'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];

        return $ret;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\SepaMandate;
    }
}