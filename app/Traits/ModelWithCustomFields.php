<?php

namespace App\Traits;

trait ModelWithCustomFields
{
    public static $customFieldsPrefix = 'custom_field__';

    /**
     * Get names of the custom fields.
     *
     * @author Patrick Reichel
     */
    public static function getCustomFields()
    {
        $fields = [];
        $fieldsRaw = self::$customFieldDefinitions ?? [];

        foreach ($fieldsRaw as $field => $rule) {
            $fields[] = self::$customFieldsPrefix.$field;
        }

        return $fields;
    }

    /**
     * Get validation rules for custom fields.
     *
     * @author Patrick Reichel
     */
    public static function getCustomRules()
    {
        $rules = [];
        $fieldsRaw = self::$customFieldDefinitions ?? [];

        foreach ($fieldsRaw as $field => $config) {
            if (! empty($config['rule'] ?? null)) {
                $rules[self::$customFieldsPrefix.$field] = $config['rule'];
            }
        }

        return $rules;
    }

    /**
     * Get methods to build form field entries for custom fields.
     *
     * @author Patrick Reichel
     */
    public static function getCustomFormMethods()
    {
        $formMethods = [];
        $fieldsRaw = self::$customFieldDefinitions ?? [];

        foreach ($fieldsRaw as $field => $config) {
            $formMethods[self::$customFieldsPrefix.$field] = $config['formMethod'];
        }

        return $formMethods;
    }

    /**
     * Convert json in custom_data to instance variables (named after custom fields).
     *
     * @author Patrick Reichel
     */
    public function expandCustomFields()
    {
        // property_exists() does not work with dynamically added ones
        if (! isset($this['custom_data'])) {
            // Already expanded
            return;
        }

        $customData = json_decode($this->custom_data, true);
        foreach (self::getCustomFields() as $field) {
            $this->$field = $customData[$field] ?? '';
        }
        unset($this->custom_data);
    }

    /**
     * Convert data in input array (custom fields to json in custom_data).
     *
     * @author Patrick Reichel
     */
    public static function collapseCustomFieldsInInput(&$data)
    {
        $customData = [];
        foreach (self::getCustomFields() as $field) {
            $customData[$field] = $data[$field] ?? '';
            unset($data[$field]);
        }
        $data['custom_data'] = json_encode($customData);
    }

    /**
     * Convert data in class instance (custom fields to json in $this->custom_data).
     *
     * @author Patrick Reichel
     */
    public function collapseCustomFields()
    {
        if (property_exists(get_class($this), 'custom_data')) {
            // Already collapsed
            return;
        }

        $customData = [];
        foreach ($this->getCustomFields() as $field) {
            $customData[$field] = $this->{$field} ?? '';
            unset($this->{$field});
        }
        $this->custom_data = json_encode($customData);
    }
}
