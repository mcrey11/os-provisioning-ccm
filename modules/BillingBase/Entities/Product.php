<?php

namespace Modules\BillingBase\Entities;

class Product extends \BaseModel
{
    public $table = 'product';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'name' => "required|string|unique:product,name,$id,id,deleted_at,NULL|max:255",
            'type' => 'nullable|string',
            'qos_id' => 'nullable|integer|exists:qos,id',
            'voip_sales_tariff_id' => 'nullable|integer',
            'voip_purchase_tariff_id' => 'nullable|integer',
            'billing_cycle' => 'nullable|string',
            'maturity' => 'nullable|integer|min:0',
            'costcenter_id' => 'nullable|integer|exists:costcenter,id',
            'price' => 'nullable|numeric|min:0',
            'tax' => 'nullable|boolean',
            'bundled_with_voip' => 'nullable|boolean',
            'email_count' => 'nullable|integer|min:0',
            'period_of_notice' => 'nullable|integer|min:0',
            'proportional' => 'nullable|boolean',
            'record_monthly' => 'nullable|boolean',
            'deprecated' => 'nullable|boolean',
            'markon' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|integer|exists:product,id',
            'description' => 'nullable|string',
            'description_html' => 'nullable|string',
            'reselling_partner' => 'nullable|string',
            'custom_data' => 'nullable|json',
        ];
    }

    public static function view_headline()
    {
        return 'Product';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-cube"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.name', $this->table.'.type', $this->table.'.price'],
            'header' => $this->name,
            'bsclass' => $this->deprecated ? 'secondary' : 'success',
            'edit' => ['price' => 'format_price'],
        ];
    }

    public function label()
    {
        return $this->name;
    }

    public function format_price()
    {
        return number_format($this->price, 2, '.', '');
    }

    public function qos()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Qos::class);
    }

    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function accountingrecords()
    {
        return $this->hasMany(AccountingRecord::class);
    }

    public function children()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }
}