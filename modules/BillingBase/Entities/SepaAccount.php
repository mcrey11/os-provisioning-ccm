<?php

namespace Modules\BillingBase\Entities;

class SepaAccount extends \BaseModel
{
    public $table = 'sepaaccount';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'name' => "required|string|unique:sepaaccount,name,$id,id,deleted_at,NULL|max:255",
            'holder' => 'nullable|string|max:255',
            'creditorid' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'bic' => 'nullable|string|max:255',
            'institute' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:255',
            'invoice_headline' => 'nullable|string',
            'invoice_text' => 'nullable|string',
            'invoice_text_negativ' => 'nullable|string',
            'invoice_text_sepa' => 'nullable|string',
            'invoice_text_sepa_negativ' => 'nullable|string',
            'template_invoice' => 'nullable|string',
            'template_cdr' => 'nullable|string',
            'description' => 'nullable|string',
            'invoice_nr_start' => 'nullable|integer|min:0',
            'no_cdrs' => 'nullable|boolean',
        ];
    }

    public static function view_headline()
    {
        return 'SEPA Account';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-university"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.name', $this->table.'.holder', $this->table.'.iban'],
            'header' => $this->name,
            'bsclass' => 'success',
        ];
    }

    public function label()
    {
        return $this->name;
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function costcenters()
    {
        return $this->hasMany(CostCenter::class);
    }

    public function sepamandates()
    {
        return $this->hasMany(SepaMandate::class);
    }

    public function accountingrecords()
    {
        return $this->hasMany(AccountingRecord::class);
    }
}