<?php
namespace Modules\ProvBase\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\RadAcct;

class SubscriberUsageController extends BaseController
{
    public function index()
    {
        $request = request();
        $days = (int) $request->get('days', 30);
        $since = now()->subDays($days);
        $view_header = 'Usage Statistics';
        $headline = '';

        $topUsers = RadAcct::select(
                'radacct.username',
                'modem.contract_id',
                'contract.number as contract_number',
                'contract.firstname',
                'contract.lastname',
                DB::raw('SUM(acctinputoctets) as total_in'),
                DB::raw('SUM(acctoutputoctets) as total_out'),
                DB::raw('COUNT(*) as session_count'),
                DB::raw('SUM(acctsessiontime) as total_time')
            )
            ->leftJoin('modem', 'radacct.username', '=', 'modem.ppp_username')
            ->leftJoin('contract', 'modem.contract_id', '=', 'contract.id')
            ->where('radacct.acctstarttime', '>=', $since)
            ->groupBy('radacct.username', 'modem.contract_id', 'contract.number', 'contract.firstname', 'contract.lastname')
            ->orderBy(DB::raw('SUM(acctinputoctets) + SUM(acctoutputoctets)'), 'desc')
            ->take(50)
            ->get();

        $totalTrafficIn = $topUsers->sum('total_in');
        $totalTrafficOut = $topUsers->sum('total_out');
        $totalSessions = RadAcct::where('acctstarttime', '>=', $since)->count();
        $uniqueUsers = RadAcct::where('acctstarttime', '>=', $since)->distinct('username')->count('username');

        return \View::make('provbase::Subscriber.Usage.index', $this->compact_prep_view(compact(
            'topUsers', 'days', 'totalTrafficIn', 'totalTrafficOut',
            'totalSessions', 'uniqueUsers', 'view_header', 'headline'
        )));
    }

    public function modem($modemId)
    {
        $view_header = 'Modem Usage';
        $headline = '';
        $modem = Modem::with(['contract', 'netgw'])->findOrFail($modemId);
        $pppUsername = $modem->ppp_username;

        $sessions = RadAcct::where('username', $pppUsername)
            ->orderBy('acctstarttime', 'desc')
            ->paginate(25);

        $totals = RadAcct::where('username', $pppUsername)
            ->selectRaw('SUM(acctinputoctets) as total_in')
            ->selectRaw('SUM(acctoutputoctets) as total_out')
            ->selectRaw('COUNT(*) as session_count')
            ->selectRaw('SUM(acctsessiontime) as total_time')
            ->first();

        return \View::make('provbase::Subscriber.Usage.modem', $this->compact_prep_view(compact('modem', 'sessions', 'totals', 'view_header', 'headline')));
    }
}
