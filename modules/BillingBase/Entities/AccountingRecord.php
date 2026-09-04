<?php

namespace Modules\BillingBase\Entities;

class AccountingRecord extends \BaseModel
{
    public $table = 'accountingrecord';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'contract_id' => 'required|integer|exists:contract,id',
            'name' => 'nullable|string|max:255',
            'product_id' => 'nullable|integer|exists:product,id',
            'ratio' => 'nullable|numeric|min:0',
            'count' => 'nullable|integer|min:0',
            'charge' => 'nullable|numeric',
            'sepaaccount_id' => 'nullable|integer|exists:sepaaccount,id',
            'invoice_nr' => 'nullable|string',
            'settlementrun_id' => 'nullable|integer|exists:settlementrun,id',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
            'period' => 'nullable|string',
            'booking_account_id' => 'nullable|integer',
            'tax' => 'nullable|numeric|min:0',
            'invoice_id' => 'nullable|integer|exists:invoice,id',
            'item_id' => 'nullable|integer|exists:item,id',
        ];
    }

    public static function view_headline()
    {
        return 'Accounting Record';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-calculator"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.name', $this->table.'.charge', $this->table.'.invoice_nr'],
            'header' => $this->name ?? 'Record #'.$this->id,
            'bsclass' => 'success',
            'edit' => ['charge' => 'format_charge'],
        ];
    }

    public function label()
    {
        return $this->name ?? 'Record #'.$this->id;
    }

    public function format_charge()
    {
        return number_format($this->charge, 2, '.', '');
    }

    public function contract()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Contract::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sepaaccount()
    {
        return $this->belongsTo(SepaAccount::class);
    }

    public function settlementrun()
    {
        return $this->belongsTo(SettlementRun::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}