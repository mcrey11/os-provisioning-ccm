<?php

namespace Modules\ProvBase\Http\Controllers\Monitoring;

use Illuminate\Support\Facades\DB;

class AlertController extends \BaseController
{
    public function index()
    {
        $alerts = DB::connection('pgsql-radius')
            ->table('radpostauth')
            ->where('authpass', '!=', 'Access-Accept')
            ->where('authpass', '!=', ' Accepted')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();
        
        $data = compact('alerts');
        
        return view('provbase::Monitoring.Alert.index', $this->compact_prep_view($data));
    }
}