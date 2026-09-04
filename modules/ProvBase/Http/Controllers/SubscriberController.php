<?php
namespace Modules\ProvBase\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Modem;

class SubscriberController extends BaseController
{
    public function index()
    {
        $request = request();
        $search = $request->get('search');
        $view_header = 'Subscribers';
        $headline = '';
        $contracts = Contract::with(['modems'])
            ->where(function ($q) {
                $q->whereNull('contract_end')
                  ->orWhere('contract_end', '>', now()->toDateTimeString());
            });

        if ($search) {
            $contracts->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $contracts = $contracts->orderBy('number')->paginate(25);

        return \View::make('provbase::Subscriber.index', $this->compact_prep_view(compact('contracts', 'view_header', 'headline')));
    }

    public function show($id)
    {
        $view_header = 'Subscriber Details';
        $headline = '';
        $contract = Contract::with(['modems', 'modems.radacct', 'modems.netgw', 'items'])->findOrFail($id);
        $modems = $contract->modems;

        $activeSessions = collect();
        $recentSessions = collect();
        foreach ($modems as $modem) {
            $active = $modem->radacct()->whereNull('acctstoptime')->get();
            $recent = $modem->radacct()->whereNotNull('acctstoptime')
                ->orderBy('acctstarttime', 'desc')->take(10)->get();
            $activeSessions = $activeSessions->merge($active);
            $recentSessions = $recentSessions->merge($recent);
        }

        $recentSessions = $recentSessions->sortByDesc('acctstarttime')->take(20)->values();

        return \View::make('provbase::Subscriber.show', $this->compact_prep_view(compact('contract', 'modems', 'activeSessions', 'recentSessions', 'view_header', 'headline')));
    }
}
