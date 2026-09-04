<?php

namespace Modules\ProvBase\Http\Controllers\Monitoring;

use Illuminate\Support\Facades\DB;
use Modules\ProvBase\Entities\NetGw;

class MonitoringController extends \BaseController
{
    public function index()
    {
        $totalDevices = 0;
        $totalNetGws = 0;
        $activeSessions = 0;
        $totalSessions = 0;
        $failedAuths = 0;
        $successfulAuths = 0;
        $recentAlerts = [];

        try {
            $totalDevices = \Modules\HfcReq\Entities\NetElement::count();
        } catch (\Exception $e) {
            // NetElement table may not exist
        }

        try {
            $totalNetGws = NetGw::count();
        } catch (\Exception $e) {
            // NetGw table may not exist
        }

        try {
            $activeSessions = DB::connection('pgsql-radius')
                ->table('radacct')
                ->whereNull('AcctStopTime')
                ->count();

            $totalSessions = DB::connection('pgsql-radius')
                ->table('radacct')
                ->count();
        } catch (\Exception $e) {
            // radacct table may not exist yet
        }

        try {
            $failedAuths = DB::connection('pgsql-radius')
                ->table('radpostauth')
                ->where('reply', '!=', 'Access-Accept')
                ->count();

            $successfulAuths = DB::connection('pgsql-radius')
                ->table('radpostauth')
                ->where('reply', 'Like', '%Access-Accept%')
                ->count();
        } catch (\Exception $e) {
            // radpostauth table may not exist yet
        }

        $data = compact(
            'totalDevices',
            'totalNetGws',
            'activeSessions',
            'totalSessions',
            'failedAuths',
            'successfulAuths',
            'recentAlerts'
        );

        return view('provbase::Monitoring.index', $this->compact_prep_view($data));
    }
}