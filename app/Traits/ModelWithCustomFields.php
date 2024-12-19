<?php

namespace App\Traits;

trait ModelWithCustomFields
{
    /**
     * Get names of the custom fields.
     *
     * @author Patrick Reichel
     */
    public static function getCustomFields($key)
    {
        $fields = [];
        $fieldsRaw = self::$customFieldDefinitions[$key] ?? [];

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
    public static function getCustomRules($key)
    {
        $rules = [];
        $fieldsRaw = self::$customFieldDefinitions[$key] ?? [];

        foreach ($fieldsRaw as $field => $config) {
            if ($config['rule']) {
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
    public static function getCustomFormMethods($key)
    {
        $formMethods = [];
        $fieldsRaw = self::$customFieldDefinitions[$key] ?? [];

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
    public function expandCustomFields($key)
    {
        $customData = json_decode($this->custom_data, true);
        foreach (self::getCustomFields($key) as $field) {
            $this->$field = $customData[$field] ?? '';
        }
        unset($this->custom_data);
    }

    /**
     * Convert data in input array (custom fields to json in custom_data).
     *
     * @author Patrick Reichel
     */
    public static function collapseCustomFieldsInInput(&$data, $key)
    {
        $customData = [];
        foreach (self::getCustomFields($key) as $field) {
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
    public function collapseCustomFields($key)
    {
        $customData = [];
        foreach ($this->getCustomFields($key) as $field) {
            $customData[$field] = $this->{$field} ?? '';
            unset($this->{$field});
        }
        $this->custom_data = json_encode($customData);
    }
}
