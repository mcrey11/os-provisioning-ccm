<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\Debt;

class DebtController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Debt;
        }

        $ret = [];

        $ret[] = ['form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => \Modules\ProvBase\Entities\Contract::pluck('number', 'id')->toArray(), 'options' => ['class' => 'select2-ajax', 'ajax-route' => 'Contract.select2']];
        $ret[] = ['form_type' => 'select', 'name' => 'sepamandate_id', 'description' => 'SEPA Mandate', 'value' => \Modules\BillingBase\Entities\SepaMandate::pluck('reference', 'id')->toArray()];
        $ret[] = ['form_type' => 'select', 'name' => 'invoice_id', 'description' => 'Invoice', 'value' => \Modules\BillingBase\Entities\Invoice::pluck('number', 'id')->toArray()];
        $ret[] = ['form_type' => 'date', 'name' => 'date', 'description' => 'Date'];
        $ret[] = ['form_type' => 'text', 'name' => 'amount', 'description' => 'Amount'];
        $ret[] = ['form_type' => 'text', 'name' => 'bank_fee', 'description' => 'Bank Fee'];
        $ret[] = ['form_type' => 'text', 'name' => 'number', 'description' => 'Number'];
        $ret[] = ['form_type' => 'date', 'name' => 'due_date', 'description' => 'Due Date'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'cleared', 'description' => 'Cleared'];
        $ret[] = ['form_type' => 'text', 'name' => 'indicator', 'description' => 'Indicator'];
        $ret[] = ['form_type' => 'date', 'name' => 'dunning_date', 'description' => 'Dunning Date'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];

        return $ret;
    }

    public function prepare_input($data)
    {
        if (isset($data['amount'])) {
            $data['amount'] = (float) $data['amount'];
        }

        if (isset($data['bank_fee'])) {
            $data['bank_fee'] = (float) $data['bank_fee'];
        }

        return $data;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\Debt;
    }
}