<?php

namespace Modules\BillingBase\Entities;

class NumberRange extends \BaseModel
{
    public $table = 'numberrange';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'name' => "required|string|unique:numberrange,name,$id,id,deleted_at,NULL|max:255",
            'range_start' => 'required|integer|min:0',
            'range_end' => 'required|integer|min:0',
            'current_number' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ];
    }

    public static function view_headline()
    {
        return 'Number Range';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-sort-numeric-asc"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.name', $this->table.'.range_start', $this->table.'.range_end', $this->table.'.current_number'],
            'header' => $this->name,
            'bsclass' => 'success',
        ];
    }

    public function label()
    {
        return $this->name;
    }
}