<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\BillingBase\Entities\BillingBase;

class BillingBaseController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = BillingBase::first() ?? new BillingBase;
        }

        $ret = [];

        $ret[] = ['form_type' => 'date', 'name' => 'rcd', 'description' => 'RCD'];
        $ret[] = ['form_type' => 'text', 'name' => 'currency', 'description' => 'Currency'];
        $ret[] = ['form_type' => 'text', 'name' => 'tax', 'description' => 'Tax'];
        $ret[] = ['form_type' => 'text', 'name' => 'mandate_ref_template', 'description' => 'Mandate Reference Template'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'split', 'description' => 'Split'];
        $ret[] = ['form_type' => 'text', 'name' => 'termination_fix', 'description' => 'Termination Fix'];
        $ret[] = ['form_type' => 'text', 'name' => 'userlang', 'description' => 'User Language'];
        $ret[] = ['form_type' => 'text', 'name' => 'cdr_offset', 'description' => 'CDR Offset'];
        $ret[] = ['form_type' => 'text', 'name' => 'voip_extracharge_default', 'description' => 'VoIP Extra Charge Default'];
        $ret[] = ['form_type' => 'text', 'name' => 'voip_extracharge_mobile_national', 'description' => 'VoIP Extra Charge Mobile National'];
        $ret[] = ['form_type' => 'text', 'name' => 'cdr_retention_period', 'description' => 'CDR Retention Period'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'fluid_valid_dates', 'description' => 'Fluid Valid Dates'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'show_ags', 'description' => 'Show AGS'];
        $ret[] = ['form_type' => 'checkbox', 'name' => 'adapt_item_start', 'description' => 'Adapt Item Start'];

        return $ret;
    }

    public function index()
    {
        $model = BillingBase::first() ?? new BillingBase;
        $headline = trans('view.BillingConfig');

        return \View::make('Generic.edit', $this->compact_prep_view(compact('model', 'headline')));
    }

    public function update($id)
    {
        $model = BillingBase::first() ?? new BillingBase;
        $data = \Request::all();

        $model->fill($data);
        $model->save();

        return redirect()->route('BillingBase.index')->with('message', trans('messages.saveSuccess'));
    }
}