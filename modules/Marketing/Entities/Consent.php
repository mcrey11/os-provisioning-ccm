<?php

namespace Modules\Marketing\Entities;

class Consent extends \BaseModel
{
    public $table = 'consent';

    public $guarded = [];
    public static $icon = 'fa-check-square-o';

    public function rules()
    {
        return [
            'name' => ['required'],
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Consent';
    }

    public static function view_icon()
    {
        return '<i class="fa '.self::$icon.'"></i>';
    }

    // AJAX Index list function - generates datatable content and classes for model
    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => ['id', 'name'],
            'header' => $this->name,
        ];
    }
}
