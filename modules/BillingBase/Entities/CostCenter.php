<?php

namespace Modules\BillingBase\Entities;

class CostCenter extends \BaseModel
{
    public $table = 'costcenter';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'name' => "required|string|unique:costcenter,name,$id,id,deleted_at,NULL|max:255",
            'number' => "nullable|string|unique:costcenter,number,$id,id,deleted_at,NULL",
            'sepaaccount_id' => 'nullable|integer|exists:sepaaccount,id',
            'billing_month' => 'nullable|integer|min:0|max:23',
            'description' => 'nullable|string',
        ];
    }

    public static function view_headline()
    {
        return 'Cost Center';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-briefcase"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.name', $this->table.'.number'],
            'header' => $this->name,
            'bsclass' => 'success',
        ];
    }

    public function label()
    {
        return $this->name;
    }

    public function sepaaccount()
    {
        return $this->belongsTo(SepaAccount::class);
    }

    public function contracts()
    {
        return $this->hasMany(\Modules\ProvBase\Entities\Contract::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function sepamandates()
    {
        return $this->hasMany(SepaMandate::class);
    }
}