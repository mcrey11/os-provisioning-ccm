<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function index()
    {
        $view_header = 'Dashboard';
        $headline = '';

        $stats = $this->getStats();

        return \View::make('Dashboard.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'stats'))
        );
    }

    protected function getStats(): array
    {
        $stats = [];

        $stats['modems'] = $this->safeCount('modem');
        $stats['contracts'] = $this->safeCount('contract');
        $stats['users'] = $this->safeCount('users');
        $stats['net_elements'] = $this->safeCount('netelement');
        $stats['configfiles'] = $this->safeCount('configfile');
        $stats['products'] = $this->safeCount('product');
        $stats['tickets'] = $this->safeCount('ticket');
        $stats['support_requests'] = $this->safeCount('supportrequest');
        $stats['global_config'] = $this->safeCount('global_config');

        return $stats;
    }

    protected function safeCount(string $table): int
    {
        try {
            return DB::table($table)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
