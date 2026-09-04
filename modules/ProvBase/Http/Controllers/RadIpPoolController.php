<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class RadIpPoolController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS IP Pool';
        $headline = '';

        $query = DB::connection('pgsql-radius')->table('radippool');

        if ($pool = request('pool')) {
            $query->where('pool_name', $pool);
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('framedipaddress', 'like', "%{$search}%")
                  ->orWhere('callingstationid', 'ilike', "%{$search}%");
            });
        }

        $ippools = $query->orderByDesc('id')->paginate(25);

        return \View::make('provbase::Radius.IpPool.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'ippools'))
        );
    }
}
