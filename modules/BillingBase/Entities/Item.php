<?php

namespace Modules\BillingBase\Entities;

class Item extends \BaseModel
{
    public $table = 'item';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'contract_id' => 'required|integer|exists:contract,id',
            'product_id' => 'required|integer|exists:product,id',
            'count' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date_format:Y-m-d',
            'valid_from_fixed' => 'nullable|boolean',
            'valid_to' => 'nullable|date_format:Y-m-d',
            'valid_to_fixed' => 'nullable|boolean',
            'credit_amount' => 'nullable|numeric|min:0',
            'costcenter_id' => 'nullable|integer|exists:costcenter,id',
            'accounting_text' => 'nullable|string',
            'payed_month' => 'nullable|string|max:10',
            'settlementrun_id' => 'nullable|integer|exists:settlementrun,id',
            'payed_until_before_sr' => 'nullable|date_format:Y-m-d',
            'payed_until_after_sr' => 'nullable|date_format:Y-m-d',
            'external_status' => 'nullable|string',
            'description' => 'nullable|string',
            'custom_data' => 'nullable|json',
        ];
    }

    public static function view_headline()
    {
        return 'Item';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-list"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.valid_from', $this->table.'.valid_to'],
            'header' => 'Item #'.$this->id,
            'bsclass' => $this->valid_to && $this->valid_to < date('Y-m-d') ? 'secondary' : 'success',
        ];
    }

    public function label()
    {
        return 'Item #'.$this->id;
    }

    public function contract()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Contract::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function settlementrun()
    {
        return $this->belongsTo(SettlementRun::class);
    }
}