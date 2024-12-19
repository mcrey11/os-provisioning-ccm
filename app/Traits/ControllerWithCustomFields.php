<?php

namespace App\Traits;

trait ControllerWithCustomFields
{
    private function addGeneralCustomFieldParams(&$fields, $generalCustomFieldParams)
    {
        foreach ($generalCustomFieldParams as $key => $value) {
            $fields[$key] = $value;
        }
    }

    /**
     * Return a form field entry for default custom field
     *
     * @author Patrick Reichel
     */
    protected function customFormTextDefault($model, $field, $generalCustomFieldParams)
    {
        $fields = [
            'form_type' => 'text',
            'name' => $field,
            'description' => trans('view.'.$model->table.'.'.$field),
        ];
        $this->addGeneralCustomFieldParams($fields, $generalCustomFieldParams);

        return $fields;
    }

    /**
     * Return a form field entry for hidden custom field
     *
     * @author Patrick Reichel
     */
    protected function customFormTextHidden($model, $field, $generalCustomFieldParams)
    {
        $fields = $this->customFormTextDefault($model, $field, $generalCustomFieldParams);
        $fields['hidden'] = 1;

        return $fields;
    }

    /**
     * Return a select form field for TicketTypes
     *
     * @author Patrick Reichel
     */
    protected function customFormSelectTickettype($model, $field, $generalCustomFieldParams)
    {
        $fields = [
            'form_type' => 'select',
            'value' => $model->html_list(\Modules\Ticketsystem\Entities\TicketType::orderBy('name')->get(), 'name', true),
            'name' => $field,
            'description' => trans('view.'.$model->table.'.'.$field),
        ];
        $this->addGeneralCustomFieldParams($fields, $generalCustomFieldParams);

        return $fields;
    }
}
