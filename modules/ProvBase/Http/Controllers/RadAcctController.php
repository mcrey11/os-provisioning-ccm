<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Modules\ProvBase\Entities\RadAcct;

class RadAcctController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS Sessions';
        $headline = '';

        $query = RadAcct::with('nas');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('framedipaddress', 'like', "%{$search}%")
                  ->orWhere('callingstationid', 'ilike', "%{$search}%")
                  ->orWhere('nasipaddress', 'like', "%{$search}%");
            });
        }

        if (request('status') === 'active') {
            $query->whereNull('acctstoptime');
        }

        $sessions = $query->orderByDesc('acctstarttime')->paginate(25);

        return \View::make('provbase::Radius.Acct.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'sessions'))
        );
    }

    public function active()
    {
        $view_header = 'Active RADIUS Sessions';
        $headline = '';

        $sessions = RadAcct::with('nas')
            ->whereNull('acctstoptime')
            ->orderByDesc('acctstarttime')
            ->paginate(25);

        return \View::make('provbase::Radius.Acct.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'sessions'))
        );
    }

    public function show($id)
    {
        $view_header = 'Session Detail';
        $headline = '';

        $session = RadAcct::with('nas')->findOrFail($id);

        return \View::make('provbase::Radius.Acct.show',
            $this->compact_prep_view(compact('view_header', 'headline', 'session'))
        );
    }
}
