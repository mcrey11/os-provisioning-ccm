<?php

namespace Modules\BillingBase\Entities;

class Tax extends \BaseModel
{
    public $table = 'tax';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'name' => "nullable|string|unique:tax,name,$id,id,deleted_at,NULL|max:255",
            'category' => 'nullable|string|max:255',
            'rate' => 'nullable|numeric|min:0|max:100',
            'type' => 'nullable|string|max:50',
            'enabled' => 'nullable|boolean',
            'valid_from' => 'nullable|date_format:Y-m-d',
            'valid_to' => 'nullable|date_format:Y-m-d',
        ];
    }

    public static function view_headline()
    {
        return 'Tax';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-percent"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.name', $this->table.'.rate', $this->table.'.category'],
            'header' => $this->name ?? 'Tax #'.$this->id,
            'bsclass' => $this->enabled ? 'success' : 'secondary',
            'edit' => ['rate' => 'format_rate'],
        ];
    }

    public function label()
    {
        return $this->name ?? 'Tax #'.$this->id;
    }

    public function format_rate()
    {
        return number_format($this->rate, 2, '.', '').'%';
    }

    public function costcenters()
    {
        return $this->belongsToMany(CostCenter::class, 'tax_costcenter');
    }
}