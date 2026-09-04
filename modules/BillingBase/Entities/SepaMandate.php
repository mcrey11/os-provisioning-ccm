<?php

namespace Modules\BillingBase\Entities;

class SepaMandate extends \BaseModel
{
    public $table = 'sepamandate';

    public function rules()
    {
        $id = $this->id ?: 0;

        return [
            'contract_id' => 'required|integer|exists:contract,id',
            'reference' => "nullable|string|unique:sepamandate,reference,$id,id,deleted_at,NULL|max:255",
            'signature_date' => 'nullable|date_format:Y-m-d',
            'holder' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'bic' => 'nullable|string|max:255',
            'institute' => 'nullable|string|max:255',
            'valid_from' => 'nullable|date_format:Y-m-d',
            'valid_to' => 'nullable|date_format:Y-m-d',
            'state' => 'nullable|string|max:255',
            'costcenter_id' => 'nullable|integer|exists:costcenter,id',
            'disable' => 'nullable|boolean',
            'description' => 'nullable|string',
        ];
    }

    public static function view_headline()
    {
        return 'SEPA Mandate';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-file-signature"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.reference', $this->table.'.holder', $this->table.'.iban'],
            'header' => $this->reference,
            'bsclass' => $this->disable ? 'secondary' : 'success',
        ];
    }

    public function label()
    {
        return $this->reference.' - '.$this->holder;
    }

    public function contract()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Contract::class);
    }

    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }
}