<?php

namespace Modules\Marketing\Http\Controllers;

class ConsentController extends \BaseController
{
    /**
     * Defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        return [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
        ];
    }
}
