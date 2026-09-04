<?php

namespace Modules\BillingBase\Entities;

class SettlementRun extends \BaseModel
{
    public $table = 'settlementrun';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'executed_at' => 'nullable|date',
            'uploaded_at' => 'nullable|date',
            'function_finished_at' => 'nullable|date',
            'description' => 'nullable|string',
            'verified' => 'nullable|boolean',
            'fullrun' => 'nullable|boolean',
            'finished_at' => 'nullable|date',
            'accounting_month' => 'nullable|string',
            'invoice_date' => 'nullable|date_format:Y-m-d',
            'rcd' => 'nullable|date_format:Y-m-d',
            'sepaaccount_id' => 'nullable|integer|exists:sepaaccount,id',
        ];
    }

    public static function view_headline()
    {
        return 'Settlement Run';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-clipboard"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.accounting_month', $this->table.'.executed_at', $this->table.'.verified'],
            'header' => $this->accounting_month ?? 'Settlement #'.$this->id,
            'bsclass' => $this->verified ? 'success' : 'warning',
        ];
    }

    public function label()
    {
        return $this->accounting_month ?? 'Settlement #'.$this->id;
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function accountingrecords()
    {
        return $this->hasMany(AccountingRecord::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function sepaaccount()
    {
        return $this->belongsTo(SepaAccount::class);
    }
}