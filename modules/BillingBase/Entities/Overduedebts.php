<?php

namespace Modules\BillingBase\Entities;

class Overduedebts extends \BaseModel
{
    public $table = 'overduedebts';

    public function rules()
    {
        return [
            'fee' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'dunning_charge1' => 'nullable|numeric|min:0',
            'dunning_charge2' => 'nullable|numeric|min:0',
            'dunning_charge3' => 'nullable|numeric|min:0',
            'dunning_text1' => 'nullable|string',
            'dunning_text2' => 'nullable|string',
            'dunning_text3' => 'nullable|string',
            'payment_period' => 'nullable|integer|min:0',
            'import_inet_block_amount' => 'nullable|numeric|min:0',
            'import_inet_block_debts' => 'nullable|integer|min:0',
            'import_inet_block_indicator' => 'nullable|string',
            'clear_credit_balance' => 'nullable|boolean',
            'debt_per_costcenter' => 'nullable|boolean',
        ];
    }

    public static function view_headline()
    {
        return 'Overdue Debts Config';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-exclamation-circle"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.fee', $this->table.'.payment_period'],
            'header' => 'Overdue Debts Config',
            'bsclass' => 'success',
        ];
    }

    public function label()
    {
        return 'Overdue Debts Config';
    }
}