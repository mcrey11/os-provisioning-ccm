<?php

namespace Modules\BillingBase\Entities;

class Salesman extends \BaseModel
{
    public $table = 'salesman';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'commission' => 'nullable|numeric|min:0|max:100',
            'products' => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }

    public static function view_headline()
    {
        return 'Salesman';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-user"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.firstname', $this->table.'.lastname', $this->table.'.commission'],
            'header' => $this->firstname.' '.$this->lastname,
            'bsclass' => 'success',
        ];
    }

    public function label()
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function contracts()
    {
        return $this->hasMany(\Modules\ProvBase\Entities\Contract::class);
    }
}