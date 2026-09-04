<?php

namespace Modules\BillingBase\Entities;

class BookingAccount extends \BaseModel
{
    public $table = 'booking_account';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'name' => "required|string|unique:booking_account,name,$id,id,deleted_at,NULL|max:255",
            'number' => "nullable|string|unique:booking_account,number,$id,id,deleted_at,NULL",
            'description' => 'nullable|string',
        ];
    }

    public static function view_headline()
    {
        return 'Booking Account';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-book"></i>';
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

    public function accountingrecords()
    {
        return $this->hasMany(AccountingRecord::class);
    }
}