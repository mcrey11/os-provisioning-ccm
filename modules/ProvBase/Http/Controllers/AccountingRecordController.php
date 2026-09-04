<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\AccountingRecord;

class AccountingRecordController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new AccountingRecord;
        }

        $ret = [];

        $ret[] = ['form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => \Modules\ProvBase\Entities\Contract::pluck('number', 'id')->toArray(), 'options' => ['class' => 'select2-ajax', 'ajax-route' => 'Contract.select2']];
        $ret[] = ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'];
        $ret[] = ['form_type' => 'select', 'name' => 'product_id', 'description' => 'Product', 'value' => \Modules\BillingBase\Entities\Product::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'text', 'name' => 'ratio', 'description' => 'Ratio'];
        $ret[] = ['form_type' => 'text', 'name' => 'count', 'description' => 'Count'];
        $ret[] = ['form_type' => 'text', 'name' => 'charge', 'description' => 'Charge'];
        $ret[] = ['form_type' => 'select', 'name' => 'sepaaccount_id', 'description' => 'SEPA Account', 'value' => \Modules\BillingBase\Entities\SepaAccount::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'text', 'name' => 'invoice_nr', 'description' => 'Invoice Number'];
        $ret[] = ['form_type' => 'select', 'name' => 'settlementrun_id', 'description' => 'Settlement Run', 'value' => \Modules\BillingBase\Entities\SettlementRun::pluck('accounting_month', 'id')->toArray()];
        $ret[] = ['form_type' => 'date', 'name' => 'from', 'description' => 'From'];
        $ret[] = ['form_type' => 'date', 'name' => 'to', 'description' => 'To'];
        $ret[] = ['form_type' => 'text', 'name' => 'period', 'description' => 'Period'];
        $ret[] = ['form_type' => 'text', 'name' => 'tax', 'description' => 'Tax'];

        return $ret;
    }

    public function prepare_input($data)
    {
        if (isset($data['charge'])) {
            $data['charge'] = (float) $data['charge'];
        }

        if (isset($data['ratio'])) {
            $data['ratio'] = (float) $data['ratio'];
        }

        if (isset($data['tax'])) {
            $data['tax'] = (float) $data['tax'];
        }

        return $data;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\AccountingRecord;
    }
}