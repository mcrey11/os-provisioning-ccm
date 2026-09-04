<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\SepaAccount;

class SepaAccountController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new SepaAccount;
        }

        $ret = [];

        $ret[] = ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'];
        $ret[] = ['form_type' => 'text', 'name' => 'holder', 'description' => 'Holder'];
        $ret[] = ['form_type' => 'text', 'name' => 'creditorid', 'description' => 'Creditor ID'];
        $ret[] = ['form_type' => 'text', 'name' => 'iban', 'description' => 'IBAN'];
        $ret[] = ['form_type' => 'text', 'name' => 'bic', 'description' => 'BIC'];
        $ret[] = ['form_type' => 'text', 'name' => 'institute', 'description' => 'Institute'];
        $ret[] = ['form_type' => 'text', 'name' => 'company_id', 'description' => 'Company ID'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'invoice_headline', 'description' => 'Invoice Headline'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'invoice_text', 'description' => 'Invoice Text'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'invoice_text_negativ', 'description' => 'Invoice Text (Negative)'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'invoice_text_sepa', 'description' => 'Invoice Text SEPA'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'invoice_text_sepa_negativ', 'description' => 'Invoice Text SEPA (Negative)'];
        $ret[] = ['form_type' => 'text', 'name' => 'template_invoice', 'description' => 'Template Invoice'];
        $ret[] = ['form_type' => 'text', 'name' => 'template_cdr', 'description' => 'Template CDR'];
        $ret[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];
        $ret[] = ['form_type' => 'text', 'name' => 'invoice_nr_start', 'description' => 'Invoice Number Start'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'no_cdrs', 'description' => 'No CDRs'];

        return $ret;
    }
    public static function get_model_obj()
    {
        return new \Modules\BillingBase\Entities\SepaAccount;
    }
}