<?php

namespace Modules\BillingBase\Entities;

class DunningRun extends \BaseModel
{
    public $table = 'dunning_run';

    public function rules()
    {
        return [
            'job_batch_id' => 'nullable|string',
            'status' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public static function view_headline()
    {
        return 'Dunning Run';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-bell"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.status', $this->table.'.description', $this->table.'.created_at'],
            'header' => 'Dunning Run #'.$this->id,
            'bsclass' => $this->status === 'completed' ? 'success' : ($this->status === 'failed' ? 'danger' : 'warning'),
        ];
    }

    public function label()
    {
        return 'Dunning Run #'.$this->id;
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }
}