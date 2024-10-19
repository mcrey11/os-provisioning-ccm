<?php
/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\extensions\html;

use App\Http\Controllers\BaseViewController;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Html\Elements\Div;
use Spatie\Html\Html;

class FormBuilder
{
    private static $layout_form_col_md = ['label'=>4, 'form'=>7, 'help'=>1];

    /**
     * An array containing the currently opened form groups.
     *
     * @var array
     */
    protected $groupStack = [];

    public static function get_layout_form_col_md()
    {
        return static::$layout_form_col_md;
    }

    /**
     * Append <div> block with col-md-7 </div>
     * NOTE: 4: col for label, 7: col for form field, 1: col for help image - if set
     */
    public function appendDiv($s, $col = 7, $classes = '', $isInput = true)
    {
        if (isset(static::$layout_form_col_md['form']) && ! $classes) {
            $col = static::$layout_form_col_md['form'];
        }

        $classes = $isInput ? 'order-3 md:order-2'.$classes : $classes;

        return Div::create()->class("col-md-{$col} {$classes}")->child($s);
    }

    /**
     * Create a form input field.
     */
    public function input($type, $name, $value = null, $options = [])
    {
        if ($type == 'hidden') {
            return $this->hidden($name, $value)->attributes($options);
        }

        // these 2 lines were moved before $options assignment -> in simple form there's no form-control class added - needed for Configfile index view
        if (isset($options['style']) && strpos($options['style'], 'simple') !== false) {
            return html()->input($type, $name, $value)->attributes($options);
        }

        return $this->appendDiv(
            html()->input($type, $name, $value)
                ->attributes($options)
                ->class('form-control')
        );
    }

    /**
     * Create a form input field
     *
     * Attention: method call Collective\Html\FormBuilder::label() has been changed in version 5.2.5
     * Patrick changed our derived call from
     *		public function label($name, $value = null, $options = [])
     * to
     *		public function label($name, $value = null, $options = [], $escape_html = true)
     */
    public function label($for, $value = '', $options = [], $wrapperCol = null, $wrapperClass = 'flex')
    {
        $col = 4;
        if (isset(static::$layout_form_col_md['label'])) {
            $col = static::$layout_form_col_md['label'];
        }

        return Div::create()->child(
            html()
                ->label(BaseViewController::translate_label($value), $for)
                ->class('control-label')
                ->attributes($options)
        )->class('col-md-'.($wrapperCol ? $wrapperCol : $col))
            ->class('order-1 col-10 items-center '.$wrapperClass);
    }

    /**
     * Create a form submit button.
     */
    public function submit($value = null, $options = [])
    {
        if (isset($options['style']) && $options['style'] == 'simple') {
            return html()
                ->submit(BaseViewController::translate_view($value, 'Button'))
                ->class('btn btn-primary')
                ->attributes($options);
        }

        $options['style'] = 'simple'; // style: required to auto width button to text length

        return Div::create()->class('col-md-12')->children([
            Div::create()->class('col-md-3'),
            Div::create()->class('col-md-6')->child($this->submit($value, $options)),
        ]);
    }

    /**
     * Create a form model field.
     */
    public function model($model, $method = 'PUT', $action = null)
    {
        return html()->modelForm($model, $method, $action);
    }

    /**
     * Create a select box field.
     */
    public function select(
        $name,
        $list = [],
        $selected = null,
        array $selectAttributes = [],
        array $optionsAttributes = [],
    ) {
        if (isset($optionsAttributes['translate'])) {
            foreach ($list as $key => $value) {
                $list[$key] = BaseViewController::translate_label($value);
            }
        }

        $selectField = html()
            ->select($name, $list, $selected)
            ->attributes($selectAttributes);

        if (array_key_exists('multiple', $selectAttributes)) {
            $selectField = html()
                ->multiselect($name, $list, $selected)
                ->attributes($selectAttributes);
        }

        if (isset($optionsAttributes['style']) && Str::contains($optionsAttributes['style'], 'simple')) {
            return $selectField;
        }

        return $this->appendDiv($selectField->class('form-control w-full'));
    }

    /**
     * Create a checkbox input field.
     */
    public function checkbox($name, $value = 1, $label = null, $checked = null, $options = [])
    {
        $options['align'] = 'left';
        $options['class'] = '';
        $checkable = html()->checkbox($name, $checked, $value)->attributes($options);

        if (isset($options['style']) && $options['style'] == 'simple') {
            return $checkable;
        }

        return $this->appendDiv($checkable->class('form-control'));
    }

    /**
     * Creates Link looking like a button - See Parameter edit view
     *
     * more possible color names: primary, info, success, danger, warning, inverse, white, link
     *
     * @param   url
     */
    public function link($name, $url, $color = 'default')
    {
        return $this->appendDiv(html()->a($url, $name)->class("btn btn-{$color} btn-block"));
    }

    /**
     * Create a textarea input field.
     */
    public function textarea($name, $value = null, $options = [])
    {
        return $this->appendDiv(html()->textarea($name, $value)->attributes($options)->class('form-control'));
    }

    /**
     * Determine whether the form element with the given name
     * has any validation errors.
     */
    private function hasErrors($name)
    {
        if (! Session::has('errors')) {
            // If the session is not set, or the session doesn't contain
            // any errors, the form element does not have any errors
            // applied to it.
            return false;
        }

        // Get the errors from the session.
        $errors = Session::get('errors');

        // Check if the errors contain the form element with the given name.
        // This leverages Laravel's transformKey method to handle the
        // formatting of the form element's name.
        return $errors->has($name);
    }

    /**
     * Get the formatted errors for the form element with the given name.
     */
    private function getFormattedErrors($name)
    {
        // Remove parenthesises from multiselect column name to search in errors
        $name = str_replace(['[', ']'], '', $name);

        if (! $this->hasErrors($name)) {
            // If the form element does not have any errors, return
            // an emptry string.
            return '';
        }
        // Get the errors from the session.
        $errors = Session::get('errors');

        // Return the formatted error message, if the form element has any.
        return new HtmlString($errors->first($name, '<p align="left" class="help-block" style="color:red">:message</p>'));
    }

    /**
     * Open a new form group.
     */
    public function openGroup($name, $label = null, $options = [], $color = false)
    {
        $div = Div::create()->class('flex flex-wrap dark:bg-slate-900');

        // Append the name of the group to the groupStack.
        $this->groupStack[] = $name;

        if ($this->hasErrors(str_replace(['[', ']'], '', $name))) {
            $div->class('has-error');
        }

        // If a label is given, we set it up here. Otherwise, we will just
        // set it to an empty string.
        // NOTE: margin-top style moves label down to same horizontal
        //       line like html fields on right side (Torsten Schmidt)
        $label = $label ? $this->label($name, $label, ['style' => 'margin-top: 10px;'], false) : '';

        return Div::create()->class('col-md-12')->class($color)
            ->child($div->attributes($options ?? [])->child($label)->open())
            ->open();
    }

    /**
     * Close out the last opened form group.
     */
    public function closeGroup()
    {
        $html = [];
        // Get the last added name from the groupStack and remove it from the array
        $name = array_pop($this->groupStack);

        // Get the formatted errors for this form group.
        $errors = $this->getFormattedErrors($name);

        // Append the errors to the group and close it out.
        if ($errors) {
            $col = static::$layout_form_col_md['label'] ?? 4;

            $html = [
                Div::create()->class("order-4 col-md-{$col} "),
                Div::create()->class('mb-2 order-5 col-md-'.(12 - $col))->child($errors),
            ];
        }

        return [
            ...$html,
            Div::create()->close(),
            Div::create()->close(),
        ];
    }

    /**
     * Create a form range slider (Ion.RangeSlider).
     *
     * @author Roy Schneider
     *
     * @param  string  $name
     * @param  mixed  $value
     * @param  array  $options
     * @return HTML
     */
    public function slider($name, $value = null, $options = [])
    {
        $options['prefix'] = isset($options['prefix']) ? $options['prefix'] : null;
        $options['postfix'] = isset($options['postfix']) ? $options['postfix'] : null;
        $options['skin'] = isset($options['skin']) ? $options['skin'] : 'square';
        $options['step'] = isset($options['step']) ? $options['step'] : '1';

        return new HtmlString('<div class="col-md-7" style="padding: 15px">
                    <input type="text" id="slider" data-skin="'.$options['skin'].'" data-min="'.$options['min'].'" data-max="'.$options['max'].'" data-step="'.$options['step'].'" value="'.$value.'" data-postfix="'.$options['postfix'].'" data-prefix="'.$options['prefix'].'" name="'.$name.'"/>
                </div>');
    }

    /**
     * Create a form traffic light.
     * 0 = green, 1 = yellow , 2 = red, error/null = grey
     *
     * @author Roy Schneider
     *
     * @param  string  $name
     * @param  int  $value
     * @param  array  $options
     * @return HTML
     */
    public function trafficLight($name, $value = null, $options = [])
    {
        $color = $this->trafficLightColor($value, $options);

        return new HtmlString('<div class="col-md-7" style="text-align: center; margin-top: 5px;">
                    <div class="btn btn-'.$color[0].' btn-circle trafficLight"></div>
                    <div class="btn btn-'.$color[1].' btn-circle trafficLight"></div>
                    <div class="btn btn-'.$color[2].' btn-circle trafficLight"></div>
                </div>');
    }

    /**
     * Defines the color of the traffic light depending on the values in view_form_fields.
     *
     * @author Roy Schneider
     *
     * @param  int  $value
     * @param  array  $options
     * @return array [$color0, $color1, $color2]
     */
    public function trafficLightColor($value, $options)
    {
        if (empty($options) || $value == null) {
            return ['default', 'default', 'default'];
        }

        if (! isset($options['type'])) {
            isset($options['green']) && $value == $options['green'] ? $color0 = 'success' : $color0 = 'default';
            isset($options['yellow']) && $value == $options['yellow'] ? $color1 = 'warning' : $color1 = 'default';
            isset($options['red']) && $value == $options['red'] ? $color2 = 'danger' : $color2 = 'default';

            return [$color0, $color1, $color2];
        }
    }

    public function email($name = null, $value = null)
    {
        return $this->appendDiv(html()->email($name, $value)->class('form-control'));
    }

    public function fieldset($legend = null)
    {
        return $this->appendDiv(html()->fieldset($legend)->class('form-control '));
    }

    public function hidden($name = null, $value = null)
    {
        return $this->appendDiv(html()->hidden($name, $value)->class('form-control '));
    }

    public function legend($contents = null)
    {
        return $this->appendDiv(html()->legend($contents)->class('form-control '));
    }

    public function multiselect($name = null, $options = [], $value = null)
    {
        return $this->appendDiv(html()->multiselect($name, $options, $value)->class('form-control '));
    }

    public function option($text = null, $value = null, $selected = false)
    {
        return $this->appendDiv(html()->option($text, $value, $selected)->class('form-control '));
    }

    public function password($name = null)
    {
        return $this->appendDiv(html()->password($name)->class('form-control '));
    }

    public function radio($name = null, $value = null, $checked = false, $options = [])
    {
        return $this->appendDiv(html()->radio($name, $checked, $value)->attributes($options)->class('form-control '));
    }

    public function text($name = null, $value = null, $options = [])
    {
        return $this->appendDiv(html()->text($name, $value)->attributes($options)->class('form-control '));
    }

    public function search($name = null, $value = null)
    {
        return $this->appendDiv(html()->search($name, $value)->class('form-control '));
    }

    public function date($name = '', $value = null, $options = [], $format = true)
    {
        return $this->appendDiv(html()->date($name, $value, $format)->attributes($options)->class('form-control '));
    }

    public function datetime($name = '', $value = null, $options = [], $format = true)
    {
        return $this->appendDiv(html()->datetime($name, $value, $format)->attributes($options)->class('form-control '));
    }

    public function time($name = '', $value = null, $options = [], $format = true)
    {
        return $this->appendDiv(html()->time($name, $value, $format)->attributes($options)->class('form-control '));
    }

    public function range($name = '', $value = '', $min = null, $max = null, $step = null)
    {
        return $this->appendDiv(html()->range($name, $value, $min, $max, $step)->class('form-control '));
    }

    public function number($name = null, $value = null, $min = null, $max = null, $step = null)
    {
        return $this->appendDiv(html()->number($name, $value, $min, $max, $step)->class('form-control '));
    }

    public function file($name = null, $options = [])
    {
        return $this->appendDiv(html()->file($name)->attributes($options)->class('form-control '));
    }

    public function token()
    {
        return html()->token();
    }
}
