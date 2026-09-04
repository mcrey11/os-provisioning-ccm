<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\Invoice;

class InvoiceController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Invoice;
        }

        $ret = [];

        $ret[] = ['form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => \Modules\ProvBase\Entities\Contract::pluck('number', 'id')->toArray(), 'options' => ['class' => 'select2-ajax', 'ajax-route' => 'Contract.select2']];
        $ret[] = ['form_type' => 'select', 'name' => 'settlementrun_id', 'description' => 'Settlement Run', 'value' => \Modules\BillingBase\Entities\SettlementRun::pluck('accounting_month', 'id')->toArray()];
        $ret[] = ['form_type' => 'select', 'name' => 'sepaaccount_id', 'description' => 'SEPA Account', 'value' => \Modules\BillingBase\Entities\SepaAccount::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'text', 'name' => 'number', 'description' => 'Invoice Number'];
        $ret[] = ['form_type' => 'text', 'name' => 'year', 'description' => 'Year'];
        $ret[] = ['form_type' => 'text', 'name' => 'month', 'description' => 'Month'];
        $ret[] = ['form_type' => 'text', 'name' => 'charge', 'description' => 'Charge'];
        $ret[] = ['form_type' => 'text', 'name' => 'charge_gross', 'description' => 'Charge Gross'];
        $ret[] = ['form_type' => 'date', 'name' => 'rcd', 'description' => 'RCD'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'payed', 'description' => 'Paid'];

        return $ret;
    }

    public function prepare_input($data)
    {
        if (isset($data['charge'])) {
            $data['charge'] = (float) $data['charge'];
        }

        if (isset($data['charge_gross'])) {
            $data['charge_gross'] = (float) $data['charge_gross'];
        }

        return $data;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\Invoice;
    }
}