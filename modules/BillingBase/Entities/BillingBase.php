<?php

namespace Modules\BillingBase\Entities;

class BillingBase extends \BaseModel
{
    public $table = 'billingbase';

    public function rules()
    {
        return [
            'rcd' => 'nullable|date_format:Y-m-d',
            'currency' => 'nullable|string|max:3',
            'tax' => 'nullable|numeric|min:0',
            'mandate_ref_template' => 'nullable|string',
            'split' => 'nullable|boolean',
            'termination_fix' => 'nullable|numeric|min:0',
            'userlang' => 'nullable|string',
            'cdr_offset' => 'nullable|integer',
            'voip_extracharge_default' => 'nullable|numeric|min:0',
            'voip_extracharge_mobile_national' => 'nullable|numeric|min:0',
            'cdr_retention_period' => 'nullable|integer|min:0',
            'fluid_valid_dates' => 'nullable|boolean',
            'show_ags' => 'nullable|boolean',
            'adapt_item_start' => 'nullable|boolean',
        ];
    }

    public static function view_headline()
    {
        return 'Billing Config';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-cogs"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.rcd', $this->table.'.currency'],
            'header' => $this->rcd ?? 'Billing Config',
            'bsclass' => 'success',
        ];
    }

    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class);
    }
}