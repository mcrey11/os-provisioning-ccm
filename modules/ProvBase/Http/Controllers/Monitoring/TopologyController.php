<?php

namespace Modules\ProvBase\Http\Controllers\Monitoring;

use Modules\HfcReq\Entities\NetElement;

class TopologyController extends \BaseController
{
    public function index()
    {
        $topology = NetElement::with('netelementtype')
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with('netelementtype')
                    ->with(['children' => function ($q) {
                        $q->with('netelementtype')
                            ->with(['children' => function ($q2) {
                                $q2->with('netelementtype');
                            }]);
                    }]);
            }])
            ->orderBy('name')
            ->get();
        
        $data = compact('topology');
        
        return view('provbase::Monitoring.Topology.index', $this->compact_prep_view($data));
    }
}