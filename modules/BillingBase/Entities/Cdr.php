<?php

namespace Modules\BillingBase\Entities;

class Cdr extends \BaseModel
{
    public $table = 'cdr';

    public function rules()
    {
        return [
            'contract_id' => 'required|integer|exists:contract,id',
            'acct_session_id' => 'nullable|string',
            'acct_session_time' => 'nullable|integer|min:0',
            'acct_input_octets' => 'nullable|integer|min:0',
            'acct_output_octets' => 'nullable|integer|min:0',
            'acct_terminate_cause' => 'nullable|string',
            'nas_ip_address' => 'nullable|string',
            'called_station_id' => 'nullable|string',
            'calling_station_id' => 'nullable|string',
            'framed_ip_address' => 'nullable|string',
            'framed_protocol' => 'nullable|string',
            'username' => 'nullable|string',
        ];
    }

    public static function view_headline()
    {
        return 'CDR';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-phone"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.username', $this->table.'.acct_session_time', $this->table.'.called_station_id'],
            'header' => $this->username ?? 'CDR #'.$this->id,
            'bsclass' => 'success',
        ];
    }

    public function label()
    {
        return $this->username ?? 'CDR #'.$this->id;
    }

    public function contract()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Contract::class);
    }
}