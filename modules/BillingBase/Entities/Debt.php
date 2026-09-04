<?php

namespace Modules\BillingBase\Entities;

class Debt extends \BaseModel
{
    public $table = 'debt';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'contract_id' => 'required|integer|exists:contract,id',
            'sepamandate_id' => 'nullable|integer|exists:sepamandate,id',
            'invoice_id' => 'nullable|integer|exists:invoice,id',
            'date' => 'nullable|date_format:Y-m-d',
            'amount' => 'nullable|numeric',
            'bank_fee' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'number' => "nullable|string|unique:debt,number,$id,id,deleted_at,NULL",
            'voucher_nr' => 'nullable|string',
            'due_date' => 'nullable|date_format:Y-m-d',
            'cleared' => 'nullable|boolean',
            'indicator' => 'nullable|string',
            'dunning_date' => 'nullable|date_format:Y-m-d',
            'parent_id' => 'nullable|integer|exists:debt,id',
            'missing_amount' => 'nullable|numeric|min:0',
            'extra_fee' => 'nullable|numeric|min:0',
            'debt_import_id' => 'nullable|integer',
            'settlementrun_id' => 'nullable|integer|exists:settlementrun,id',
            'last_sent_at' => 'nullable|timestamp',
            'send_tries' => 'nullable|integer|min:0',
            'trans_ref' => 'nullable|string',
        ];
    }

    public static function view_headline()
    {
        return 'Debt';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-exclamation-triangle"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.number', $this->table.'.amount', $this->table.'.date', $this->table.'.cleared'],
            'header' => $this->number ?? 'Debt #'.$this->id,
            'bsclass' => $this->cleared ? 'success' : 'danger',
            'edit' => ['amount' => 'format_amount'],
        ];
    }

    public function label()
    {
        return $this->number ?? 'Debt #'.$this->id;
    }

    public function format_amount()
    {
        return number_format($this->amount, 2, '.', '');
    }

    public function contract()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Contract::class);
    }

    public function sepamandate()
    {
        return $this->belongsTo(SepaMandate::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function parent()
    {
        return $this->belongsTo(Debt::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Debt::class, 'parent_id');
    }

    public function settlementrun()
    {
        return $this->belongsTo(SettlementRun::class);
    }
}