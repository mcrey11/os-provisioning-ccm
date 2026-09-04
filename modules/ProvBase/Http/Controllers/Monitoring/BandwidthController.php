<?php

namespace Modules\ProvBase\Http\Controllers\Monitoring;

use Illuminate\Support\Facades\DB;
use Modules\HfcReq\Entities\NetElement;
use Modules\HfcReq\Entities\NetGw;

class BandwidthController extends \BaseController
{
    public function index()
    {
        $elements = NetElement::with('netelementtype')
            ->orderBy('name')
            ->get();
        
        $gateways = NetGw::orderBy('name')
            ->get();
        
        $data = compact('elements', 'gateways');
        
        return view('provbase::Monitoring.Bandwidth.index', $this->compact_prep_view($data));
    }
}