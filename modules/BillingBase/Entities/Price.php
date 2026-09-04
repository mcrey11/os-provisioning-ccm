<?php

namespace Modules\BillingBase\Entities;

class Price extends \BaseModel
{
    public $table = 'price';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'qos_id' => 'required|integer|exists:qos,id',
            'name' => "nullable|string|unique:price,name,$id,id,deleted_at,NULL|max:255",
            'price' => 'nullable|numeric|min:0',
            'tax' => 'nullable|boolean',
            'valid_from' => 'nullable|date_format:Y-m-d',
            'valid_to' => 'nullable|date_format:Y-m-d',
        ];
    }

    public static function view_headline()
    {
        return 'Price';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-tag"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.name', $this->table.'.price', $this->table.'.valid_from'],
            'header' => $this->name ?? 'Price #'.$this->id,
            'bsclass' => 'success',
            'edit' => ['price' => 'format_price'],
        ];
    }

    public function label()
    {
        return $this->name ?? 'Price #'.$this->id;
    }

    public function format_price()
    {
        return number_format($this->price, 2, '.', '');
    }

    public function qos()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Qos::class);
    }
}