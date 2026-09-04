<?php
namespace Modules\ProvBase\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\RadAcct;

class SubscriberSessionController extends BaseController
{
    public function index()
    {
        $request = request();
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $view_header = 'Subscriber Sessions';
        $headline = '';

        $sessions = RadAcct::select(
                'radacct.*',
                'modem.hostname as modem_hostname',
                'modem.contract_id',
                'contract.number as contract_number',
                'contract.firstname',
                'contract.lastname'
            )
            ->leftJoin('modem', 'radacct.username', '=', 'modem.ppp_username')
            ->leftJoin('contract', 'modem.contract_id', '=', 'contract.id');

        if ($search) {
            $sessions->where(function ($q) use ($search) {
                $q->where('radacct.username', 'like', "%{$search}%")
                  ->orWhere('radacct.framedipaddress', 'like', "%{$search}%")
                  ->orWhere('contract.number', 'like', "%{$search}%")
                  ->orWhere('contract.firstname', 'like', "%{$search}%")
                  ->orWhere('contract.lastname', 'like', "%{$search}%");
            });
        }

        if ($startDate) {
            $sessions->where('radacct.acctstarttime', '>=', $startDate);
        }
        if ($endDate) {
            $sessions->where('radacct.acctstarttime', '<=', $endDate . ' 23:59:59');
        }

        $sessions = $sessions->orderBy('radacct.acctstarttime', 'desc')->paginate(25);

        return \View::make('provbase::Subscriber.Session.index', $this->compact_prep_view(compact('sessions', 'search', 'startDate', 'endDate', 'view_header', 'headline')));
    }

    public function history($contractId)
    {
        $view_header = 'Session History';
        $headline = '';
        $contract = Contract::with(['modems'])->findOrFail($contractId);

        $modemIds = $contract->modems->pluck('id');
        $pppUsernames = $contract->modems->pluck('ppp_username')->filter();

        $sessions = RadAcct::whereIn('radacct.username', $pppUsernames)
            ->orderBy('radacct.username')
            ->orderBy('radacct.acctstarttime', 'desc')
            ->get()
            ->groupBy('username');

        $totalSessions = $sessions->flatten()->count();
        $totalTrafficIn = $sessions->flatten()->sum('acctinputoctets');
        $totalTrafficOut = $sessions->flatten()->sum('acctoutputoctets');
        $totalTime = $sessions->flatten()->sum('acctsessiontime');

        return \View::make('provbase::Subscriber.Session.history', $this->compact_prep_view(compact(
            'contract', 'sessions', 'totalSessions',
            'totalTrafficIn', 'totalTrafficOut', 'totalTime', 'view_header', 'headline'
        )));
    }
}
