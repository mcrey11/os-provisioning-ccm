<?php

namespace Modules\BillingBase\Entities;

class Invoice extends \BaseModel
{
    public $table = 'invoice';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'contract_id' => 'required|integer|exists:contract,id',
            'settlementrun_id' => 'nullable|integer|exists:settlementrun,id',
            'sepaaccount_id' => 'nullable|integer|exists:sepaaccount,id',
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'filename' => 'nullable|string',
            'type' => 'nullable|string',
            'number' => "nullable|string|unique:invoice,number,$id,id,deleted_at,NULL",
            'charge' => 'nullable|numeric|min:0',
            'charge_gross' => 'nullable|numeric|min:0',
            'remaining_charge' => 'nullable|numeric|min:0',
            'rcd' => 'nullable|date_format:Y-m-d',
            'payed' => 'nullable|boolean',
        ];
    }

    public static function view_headline()
    {
        return 'Invoice';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-file-text"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.number', $this->table.'.year', $this->table.'.month', $this->table.'.charge'],
            'header' => $this->number ?? 'Invoice #'.$this->id,
            'bsclass' => $this->payed ? 'success' : 'warning',
            'edit' => ['charge' => 'format_charge', 'charge_gross' => 'format_charge_gross'],
        ];
    }

    public function label()
    {
        return $this->number ?? 'Invoice #'.$this->id;
    }

    public function format_charge()
    {
        return number_format($this->charge, 2, '.', '');
    }

    public function format_charge_gross()
    {
        return number_format($this->charge_gross, 2, '.', '');
    }

    public function contract()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Contract::class);
    }

    public function settlementrun()
    {
        return $this->belongsTo(SettlementRun::class);
    }

    public function sepaaccount()
    {
        return $this->belongsTo(SepaAccount::class);
    }

    public function accountingrecords()
    {
        return $this->hasMany(AccountingRecord::class);
    }
}