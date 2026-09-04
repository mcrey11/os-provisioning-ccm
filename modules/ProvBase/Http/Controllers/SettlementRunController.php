<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\SettlementRun;

class SettlementRunController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new SettlementRun;
        }

        $ret = [];

        $ret[] = ['form_type' => 'text', 'name' => 'accounting_month', 'description' => 'Accounting Month'];
        $ret[] = ['form_type' => 'date', 'name' => 'invoice_date', 'description' => 'Invoice Date'];
        $ret[] = ['form_type' => 'date', 'name' => 'rcd', 'description' => 'RCD'];
        $ret[] = ['form_type' => 'select', 'name' => 'sepaaccount_id', 'description' => 'SEPA Account', 'value' => \Modules\BillingBase\Entities\SepaAccount::pluck('name', 'id')->toArray()];
        $ret[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'verified', 'description' => 'Verified'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'fullrun', 'description' => 'Full Run'];

        return $ret;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\SettlementRun;
    }
}