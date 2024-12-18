<?php

namespace App\Traits;

trait ControllerWithCustomFields
{
    /**
     * Return a form field entry for default custom field
     *
     * @author Patrick Reichel
     */
    protected function customFormTextDefault($model, $field, $options)
    {
        return [
            'form_type' => 'text',
            'name' => $field,
            'description' => trans('view.product.'.$field),
            'select' => 'Reseller',
            'options' => $options,
        ];
    }

    /**
     * Return a select form field for TicketTypes
     *
     * @author Patrick Reichel
     */
    protected function customFormSelectTickettype($model, $field, $options)
    {
        return [
            'form_type' => 'select',
            'value' => $model->html_list(\Modules\Ticketsystem\Entities\TicketType::orderBy('name')->get(), 'name', true),
            'name' => $field,
            'description' => trans('view.product.'.$field),
            'select' => 'Reseller',
            'options' => $options,
        ];
    }
}
