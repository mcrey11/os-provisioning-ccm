<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Modules\ProvBase\Entities\RadAcct;
use Modules\ProvBase\Entities\RadPostAuth;
use Modules\ProvBase\Entities\Nas;
use Illuminate\Support\Facades\DB;

class RadiusController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS Overview';
        $headline = '';

        $stats = [
            'active_sessions' => RadAcct::whereNull('acctstoptime')->count(),
            'total_sessions' => RadAcct::count(),
            'total_users' => DB::connection('pgsql-radius')->table('radcheck')->distinct('username')->count('username'),
            'auth_attempts' => RadPostAuth::where('authdate', '>=', now()->subDay())->count(),
            'auth_success' => RadPostAuth::where('authdate', '>=', now()->subDay())->where('reply', 'Access-Accept')->count(),
            'auth_fail' => RadPostAuth::where('authdate', '>=', now()->subDay())->where('reply', '!=', 'Access-Accept')->count(),
            'nas_count' => Nas::count(),
        ];

        $recentSessions = RadAcct::with('nas')
            ->orderByDesc('acctstarttime')
            ->limit(10)
            ->get();

        $recentAuth = RadPostAuth::orderByDesc('authdate')
            ->limit(10)
            ->get();

        return \View::make('provbase::Radius.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'stats', 'recentSessions', 'recentAuth'))
        );
    }
}
